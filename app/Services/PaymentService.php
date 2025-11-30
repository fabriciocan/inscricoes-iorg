<?php

namespace App\Services;

use App\Exceptions\InvalidPackageStateException;
use App\Exceptions\PaymentException;
use App\Models\Package;
use App\Jobs\SendConfirmationEmailJob;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;

class PaymentService
{
    protected RegistrationService $registrationService;

    public function __construct(RegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;

        // Initialize Mercado Pago SDK v3
        MercadoPagoConfig::setAccessToken(config('mercadopago.access_token'));
    }

    /**
     * Create a payment preference for Mercado Pago.
     *
     * @param Package $package
     * @param string $method
     * @return array
     * @throws InvalidPackageStateException
     * @throws PaymentException
     */
    public function createPaymentPreference(Package $package, string $method): array
    {
        try {
            // Validate package state
            if ($package->status === 'confirmed') {
                throw InvalidPackageStateException::cannotPayConfirmedPackage();
            }

            if ($package->status === 'cancelled') {
                throw InvalidPackageStateException::cannotPayCancelledPackage();
            }

            // Validate package has registrations
            if ($package->registrations->isEmpty()) {
                throw InvalidPackageStateException::emptyPackage();
            }

            // Validate payment method
            if (!in_array($method, ['pix', 'credit_card'])) {
                throw PaymentException::invalidPaymentData('Método de pagamento inválido. Use "pix" ou "credit_card".');
            }

            // For PIX, create direct payment instead of preference
            if ($method === 'pix') {
                return $this->createPixPayment($package);
            }

            // Add items (registrations)
            $items = [];
            foreach ($package->registrations as $registration) {
                $items[] = [
                    'title' => "Inscrição - {$registration->event->name}",
                    'quantity' => 1,
                    'unit_price' => (float) $registration->price_paid,
                ];
            }

            // Build preference request following SDK v3 documentation
            $request = [
                'items' => $items,
                'payer' => [
                    'email' => $package->user->email,
                ],
                'back_urls' => [
                    'success' => route('payment.success', ['package' => $package->id]),
                    'failure' => route('payment.failure', ['package' => $package->id]),
                    'pending' => route('payment.pending', ['package' => $package->id]),
                ],
                'external_reference' => $package->package_number,
                'notification_url' => route('payment.callback'),
                'statement_descriptor' => 'INSCRICAO EVENTO',
                'expires' => false,
            ];

            // Set payment methods based on SDK v3 documentation
            if ($method === 'pix') {
                // PIX redirects to direct payment - this code shouldn't run
                // because PIX uses createPixPayment() method
            } else {
                // Only allow credit card payment
                $request['payment_methods'] = [
                    'excluded_payment_methods' => [
                        ['id' => 'pix'],
                    ],
                    'excluded_payment_types' => [
                        ['id' => 'debit_card'],
                        ['id' => 'ticket'],
                    ],
                    'installments' => 12,
                    'default_installments' => 1,
                ];
            }

            // Log the request for debugging (after adding payment_methods)
            Log::info("Creating MercadoPago preference with request:", [
                'package_id' => $package->id,
                'method' => $method,
                'request' => $request,
            ]);

            // Create preference using SDK v3
            $client = new PreferenceClient();
            $preference = $client->create($request);

            // Update package status to pending ONLY after successful preference creation
            $this->registrationService->updatePackageStatus($package, 'pending');
            $package->update(['payment_method' => $method]);

            Log::info("Payment preference created successfully for package: {$package->package_number}", [
                'preference_id' => $preference->id,
                'method' => $method,
            ]);

            return [
                'preference_id' => $preference->id,
                'init_point' => $preference->init_point,
                'sandbox_init_point' => $preference->sandbox_init_point,
            ];
        } catch (InvalidPackageStateException $e) {
            Log::warning("Invalid package state for payment: {$e->getMessage()}", [
                'package_id' => $package->id,
                'package_status' => $package->status,
            ]);

            // Send user notification
            Notification::make()
                ->title('Erro ao processar pagamento')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $statusCode = $apiResponse ? $apiResponse->getStatusCode() : 'N/A';
            $content = $apiResponse ? $apiResponse->getContent() : 'No content';

            Log::error("Mercado Pago API error: {$e->getMessage()}", [
                'package_id' => $package->id,
                'status_code' => $statusCode,
                'response_content' => $content,
                'exception' => $e,
            ]);

            // Send user notification
            Notification::make()
                ->title('Erro ao processar pagamento')
                ->body('Não foi possível conectar com o sistema de pagamento. Tente novamente mais tarde.')
                ->danger()
                ->send();

            throw PaymentException::mercadoPagoError($e->getMessage());
        } catch (\Exception $e) {
            Log::error("Unexpected error creating payment preference: {$e->getMessage()}", [
                'package_id' => $package->id,
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            // Send user notification
            Notification::make()
                ->title('Erro ao processar pagamento')
                ->body('Ocorreu um erro inesperado. Por favor, tente novamente.')
                ->danger()
                ->send();

            throw PaymentException::processingFailed($e->getMessage());
        }
    }

    /**
     * Process payment callback from Mercado Pago.
     *
     * @param string $paymentId
     * @return void
     * @throws PaymentException
     */
    public function processPaymentCallback(string $paymentId): void
    {
        if (!$paymentId) {
            Log::warning('Payment callback received without payment ID');
            throw PaymentException::callbackValidationFailed('ID de pagamento não fornecido');
        }

        try {
            // Get payment using SDK v3
            $client = new PaymentClient();
            $payment = $client->get($paymentId);

            if (!$payment) {
                Log::warning("Payment not found in Mercado Pago: {$paymentId}");
                throw PaymentException::callbackValidationFailed("Pagamento {$paymentId} não encontrado no Mercado Pago");
            }

            // Find package by external reference
            $package = Package::where('package_number', $payment->external_reference)->first();

            if (!$package) {
                Log::warning("Package not found for external reference: {$payment->external_reference}");
                throw PaymentException::packageNotFound($payment->external_reference);
            }

            Log::info("Processing payment callback for package {$package->package_number}, status: {$payment->status}", [
                'payment_id' => $paymentId,
                'payment_status' => $payment->status,
                'package_status' => $package->status,
            ]);

            // Update package based on payment status
            switch ($payment->status) {
                case 'approved':
                    $this->confirmPayment($package, $paymentId);
                    break;

                case 'rejected':
                case 'cancelled':
                    // Revert to draft so user can try again
                    $this->registrationService->updatePackageStatus($package, 'draft');
                    $package->update([
                        'payment_id' => $paymentId,
                        'payment_method' => null, // Clear payment method to allow new selection
                    ]);
                    Log::info("Payment cancelled/rejected for package: {$package->package_number}, reverted to draft for retry");
                    break;

                case 'pending':
                case 'in_process':
                    // Keep as pending
                    $package->update(['payment_id' => $paymentId]);
                    Log::info("Payment pending for package: {$package->package_number}");
                    break;

                default:
                    Log::warning("Unknown payment status received: {$payment->status}", [
                        'payment_id' => $paymentId,
                        'package_number' => $package->package_number,
                    ]);
                    break;
            }
        } catch (PaymentException $e) {
            // Re-throw payment exceptions
            throw $e;
        } catch (MPApiException $e) {
            Log::error("Mercado Pago API error in callback for payment {$paymentId}: {$e->getMessage()}", [
                'exception' => $e,
            ]);
            throw PaymentException::mercadoPagoError($e->getMessage());
        } catch (\Exception $e) {
            Log::error("Unexpected error in payment callback for payment {$paymentId}: {$e->getMessage()}", [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            throw PaymentException::processingFailed("Erro ao processar callback: {$e->getMessage()}");
        }
    }

    /**
     * Confirm payment and update package status.
     *
     * @param Package $package
     * @param string $paymentId
     * @return void
     * @throws PaymentException
     */
    public function confirmPayment(Package $package, string $paymentId): void
    {
        try {
            // Validate package can be confirmed
            if ($package->status === 'confirmed') {
                Log::info("Package {$package->package_number} is already confirmed, skipping");
                return;
            }

            if ($package->status === 'cancelled') {
                Log::warning("Attempting to confirm cancelled package: {$package->package_number}");
                throw InvalidPackageStateException::invalidStatusTransition('cancelled', 'confirmed');
            }

            $package->update([
                'status' => 'confirmed',
                'payment_id' => $paymentId,
            ]);

            // Dispatch job to send confirmation email
            SendConfirmationEmailJob::dispatch($package);
            
            Log::info("Payment confirmed and email job dispatched for package: {$package->package_number}", [
                'payment_id' => $paymentId,
            ]);
        } catch (InvalidPackageStateException $e) {
            Log::error("Invalid package state when confirming payment for package {$package->package_number}: {$e->getMessage()}");
            throw PaymentException::processingFailed($e->getMessage());
        } catch (\Exception $e) {
            Log::error("Error confirming payment for package {$package->package_number}: {$e->getMessage()}", [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            throw PaymentException::processingFailed("Erro ao confirmar pagamento: {$e->getMessage()}");
        }
    }

    /**
     * Create a PIX payment directly via MercadoPago API.
     *
     * @param Package $package
     * @return array
     * @throws PaymentException
     */
    protected function createPixPayment(Package $package): array
    {
        try {
            // Calculate total amount
            $totalAmount = $package->calculateTotal();

            // Build payment request
            $request = [
                'transaction_amount' => (float) $totalAmount,
                'description' => "Pacote de inscrições - {$package->package_number}",
                'payment_method_id' => 'pix',
                'payer' => [
                    'email' => $package->user->email,
                    'first_name' => $package->user->name,
                ],
                'external_reference' => $package->package_number,
                'notification_url' => route('payment.callback'),
            ];

            Log::info("Creating PIX payment with request:", [
                'package_id' => $package->id,
                'request' => $request,
            ]);

            // Create payment using SDK v3
            $client = new PaymentClient();
            $payment = $client->create($request);

            // Update package status to pending
            $this->registrationService->updatePackageStatus($package, 'pending');
            $package->update([
                'payment_method' => 'pix',
                'payment_id' => $payment->id,
            ]);

            Log::info("PIX payment created successfully for package: {$package->package_number}", [
                'payment_id' => $payment->id,
                'status' => $payment->status,
            ]);

            // Return PIX QR Code data
            return [
                'payment_id' => $payment->id,
                'status' => $payment->status,
                'qr_code' => $payment->point_of_interaction->transaction_data->qr_code ?? null,
                'qr_code_base64' => $payment->point_of_interaction->transaction_data->qr_code_base64 ?? null,
                'ticket_url' => $payment->point_of_interaction->transaction_data->ticket_url ?? null,
            ];
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $statusCode = $apiResponse ? $apiResponse->getStatusCode() : 'N/A';
            $content = $apiResponse ? $apiResponse->getContent() : 'No content';

            Log::error("Mercado Pago API error creating PIX payment: {$e->getMessage()}", [
                'package_id' => $package->id,
                'status_code' => $statusCode,
                'response_content' => $content,
                'exception' => $e,
            ]);

            Notification::make()
                ->title('Erro ao gerar PIX')
                ->body('Não foi possível gerar o código PIX. Tente novamente mais tarde.')
                ->danger()
                ->send();

            throw PaymentException::mercadoPagoError($e->getMessage());
        } catch (\Exception $e) {
            Log::error("Unexpected error creating PIX payment: {$e->getMessage()}", [
                'package_id' => $package->id,
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->title('Erro ao gerar PIX')
                ->body('Ocorreu um erro inesperado. Por favor, tente novamente.')
                ->danger()
                ->send();

            throw PaymentException::processingFailed($e->getMessage());
        }
    }
}
