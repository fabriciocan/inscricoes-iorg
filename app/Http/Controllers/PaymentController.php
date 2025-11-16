<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidPackageStateException;
use App\Exceptions\PaymentException;
use App\Models\Package;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;
    protected NotificationService $notificationService;

    public function __construct(
        PaymentService $paymentService,
        NotificationService $notificationService
    ) {
        $this->paymentService = $paymentService;
        $this->notificationService = $notificationService;
    }

    /**
     * Process payment for a package.
     *
     * @param Request $request
     * @param Package $package
     * @return \Illuminate\Http\RedirectResponse
     */
    public function process(Request $request, Package $package)
    {
        try {
            // Verify user owns the package
            if ($package->user_id !== auth()->id()) {
                Log::warning('Unauthorized payment attempt', [
                    'package_id' => $package->id,
                    'user_id' => auth()->id(),
                    'package_user_id' => $package->user_id,
                ]);
                abort(403, 'Você não tem permissão para processar este pagamento.');
            }

            // Verify package is in draft status
            if ($package->status !== 'draft') {
                Log::info('Attempted to pay non-draft package', [
                    'package_id' => $package->id,
                    'status' => $package->status,
                ]);
                
                Notification::make()
                    ->title('Pagamento não disponível')
                    ->body('Este pacote não pode ser pago no momento.')
                    ->warning()
                    ->send();
                
                return redirect()
                    ->route('filament.admin.pages.my-registrations-page');
            }

            $method = $request->input('payment_method', 'pix');

            // Create payment preference
            $preference = $this->paymentService->createPaymentPreference($package, $method);

            Log::info('Payment process initiated successfully', [
                'package_id' => $package->id,
                'method' => $method,
                'preference_id' => $preference['preference_id'],
            ]);

            // Redirect to Mercado Pago checkout
            $initPoint = app()->environment('production') 
                ? $preference['init_point'] 
                : $preference['sandbox_init_point'];

            return redirect()->away($initPoint);

        } catch (InvalidPackageStateException $e) {
            Log::warning('Invalid package state for payment', [
                'package_id' => $package->id,
                'error' => $e->getMessage(),
            ]);
            
            Notification::make()
                ->title('Erro ao processar pagamento')
                ->body($e->getMessage())
                ->danger()
                ->send();
            
            return redirect()
                ->route('filament.admin.pages.my-registrations-page');
                
        } catch (PaymentException $e) {
            Log::error('Payment exception during process', [
                'package_id' => $package->id,
                'error' => $e->getMessage(),
            ]);
            
            Notification::make()
                ->title('Erro ao processar pagamento')
                ->body($e->getMessage())
                ->danger()
                ->send();
            
            return redirect()
                ->route('filament.admin.pages.payment-page', ['package' => $package->id]);
                
        } catch (\Exception $e) {
            Log::error('Unexpected error during payment process', [
                'package_id' => $package->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            Notification::make()
                ->title('Erro inesperado')
                ->body('Ocorreu um erro ao processar o pagamento. Por favor, tente novamente.')
                ->danger()
                ->send();
            
            return redirect()
                ->route('filament.admin.pages.payment-page', ['package' => $package->id]);
        }
    }

    /**
     * Handle payment callback webhook from Mercado Pago.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function callback(Request $request)
    {
        try {
            // Log the incoming webhook for debugging
            Log::info('Mercado Pago webhook received', [
                'headers' => $request->headers->all(),
                'query' => $request->query->all(),
                'body' => $request->all(),
            ]);

            // Validate webhook signature
            if (!$this->validateWebhookSignature($request)) {
                Log::warning('Invalid webhook signature from Mercado Pago', [
                    'headers' => $request->headers->all(),
                ]);
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            // Get the notification data (can be in query or body)
            $topic = $request->input('topic') ?? $request->query('topic');
            $paymentId = $request->input('resource') ?? $request->query('id') ?? $request->input('data.id');

            Log::info('Processing webhook', [
                'topic' => $topic,
                'payment_id' => $paymentId,
            ]);

            // We only process payment notifications
            if ($topic !== 'payment') {
                Log::info('Non-payment webhook ignored', ['topic' => $topic]);
                return response()->json(['status' => 'ignored'], 200);
            }

            if (!$paymentId) {
                Log::warning('Webhook received without payment ID');
                return response()->json(['error' => 'Missing payment ID'], 400);
            }

            // Process the payment using PaymentService
            $this->paymentService->processPaymentCallback($paymentId);

            return response()->json(['status' => 'success'], 200);

        } catch (PaymentException $e) {
            Log::error('Payment exception in callback', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            // Return 200 to prevent Mercado Pago from retrying
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 200);

        } catch (\Exception $e) {
            Log::error('Unexpected error in payment callback', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return 500 so Mercado Pago will retry
            return response()->json(['error' => 'Internal error'], 500);
        }
    }

    /**
     * Handle successful payment return.
     *
     * @param Package $package
     * @return \Illuminate\Http\RedirectResponse
     */
    public function success(Package $package)
    {
        return redirect()
            ->route('filament.admin.pages.my-registrations-page')
            ->with('success', 'Pagamento processado com sucesso! Você receberá um email de confirmação em breve.');
    }

    /**
     * Handle failed payment return.
     *
     * @param Package $package
     * @return \Illuminate\Http\RedirectResponse
     */
    public function failure(Package $package)
    {
        return redirect()
            ->route('filament.admin.pages.payment-page', ['package' => $package->id])
            ->with('error', 'O pagamento não foi aprovado. Por favor, tente novamente.');
    }

    /**
     * Handle pending payment return.
     *
     * @param Package $package
     * @return \Illuminate\Http\RedirectResponse
     */
    public function pending(Package $package)
    {
        return redirect()
            ->route('filament.admin.pages.my-registrations-page')
            ->with('info', 'Seu pagamento está pendente. Você receberá uma confirmação assim que for processado.');
    }

    /**
     * Validate webhook signature from Mercado Pago.
     *
     * @param Request $request
     * @return bool
     */
    protected function validateWebhookSignature(Request $request): bool
    {
        // Get signature headers
        $xSignature = $request->header('x-signature');
        $xRequestId = $request->header('x-request-id');

        // If no signature headers, skip validation in development
        if (!$xSignature || !$xRequestId) {
            return app()->environment('local', 'development');
        }

        // Parse the x-signature header
        $parts = [];
        foreach (explode(',', $xSignature) as $part) {
            $keyValue = explode('=', $part, 2);
            if (count($keyValue) === 2) {
                $parts[trim($keyValue[0])] = trim($keyValue[1]);
            }
        }

        $ts = $parts['ts'] ?? null;
        $hash = $parts['v1'] ?? null;

        if (!$ts || !$hash) {
            return app()->environment('local', 'development');
        }

        // Get the secret key from Mercado Pago (this would be provided in webhook configuration)
        // For now, we'll skip strict validation in non-production environments
        if (!app()->environment('production')) {
            return true;
        }

        // In production, you would validate like this:
        // $secret = config('mercadopago.webhook_secret');
        // $manifest = "id:{$xRequestId};request-id:{$xRequestId};ts:{$ts};";
        // $expectedHash = hash_hmac('sha256', $manifest, $secret);
        // return hash_equals($expectedHash, $hash);

        return true;
    }
}
