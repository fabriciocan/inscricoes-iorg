<?php

namespace App\Filament\Pages;

use App\Models\Package;
use App\Services\PaymentService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class PaymentPage extends Page
{
    protected string $view = 'filament.pages.payment-page';

    protected static ?string $navigationLabel = null;

    protected static bool $shouldRegisterNavigation = false;

    public ?Package $package = null;
    
    public ?string $selectedMethod = null;

    public function mount(): void
    {
        $packageId = request()->query('package');
        
        if (!$packageId) {
            Notification::make()
                ->title('Pacote não encontrado')
                ->danger()
                ->send();
            
            redirect()->route('filament.admin.pages.my-registrations-page');
            return;
        }

        $this->package = Package::with(['registrations.event', 'user'])
            ->where('id', $packageId)
            ->where('user_id', auth()->id())
            ->first();
        
        if (!$this->package) {
            Notification::make()
                ->title('Pacote não encontrado ou você não tem permissão para acessá-lo')
                ->danger()
                ->send();
            
            redirect()->route('filament.admin.pages.my-registrations-page');
            return;
        }

        if ($this->package->registrations->count() === 0) {
            Notification::make()
                ->title('O pacote não possui inscrições')
                ->warning()
                ->send();
            
            redirect()->route('filament.admin.pages.my-registrations-page');
            return;
        }

        if (!in_array($this->package->status, ['draft', 'pending'])) {
            Notification::make()
                ->title('Este pacote não pode ser pago')
                ->body('O pacote já foi processado.')
                ->warning()
                ->send();

            redirect()->route('filament.admin.pages.my-registrations-page');
            return;
        }

        // If package is pending, revert to draft to allow payment retry
        if ($this->package->status === 'pending') {
            $this->package->update([
                'status' => 'draft',
                'payment_method' => null,
            ]);
            $this->package->refresh();
        }
    }

    public function getTitle(): string | Htmlable
    {
        return 'Pagamento';
    }



    protected function getHeaderActions(): array
    {
        return [];
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function selectMethod(string $method): void
    {
        $this->selectedMethod = $method;
    }

    public function processPayment(): void
    {
        if (!$this->selectedMethod) {
            Notification::make()
                ->title('Selecione um método de pagamento')
                ->warning()
                ->send();
            return;
        }

        try {
            $paymentService = app(PaymentService::class);

            // Verify package is in draft status
            if ($this->package->status !== 'draft') {
                Notification::make()
                    ->title('Pagamento não disponível')
                    ->body('Este pacote não pode ser pago no momento.')
                    ->warning()
                    ->send();

                redirect()->route('filament.admin.pages.my-registrations-page');
                return;
            }

            // Create payment preference or direct payment
            $result = $paymentService->createPaymentPreference($this->package, $this->selectedMethod);

            // For PIX, redirect to MercadoPago ticket URL
            if ($this->selectedMethod === 'pix') {
                if (!empty($result['ticket_url'])) {
                    redirect()->away($result['ticket_url']);
                } else {
                    Notification::make()
                        ->title('Erro ao gerar PIX')
                        ->body('Não foi possível obter o link de pagamento PIX.')
                        ->danger()
                        ->send();
                }
            } else {
                // For credit card, redirect to Mercado Pago checkout (production mode)
                $initPoint = $result['init_point'];

                redirect()->away($initPoint);
            }

        } catch (\App\Exceptions\InvalidPackageStateException $e) {
            Notification::make()
                ->title('Erro ao processar pagamento')
                ->body($e->getMessage())
                ->danger()
                ->send();

            redirect()->route('filament.admin.pages.my-registrations-page');

        } catch (\App\Exceptions\PaymentException $e) {
            Notification::make()
                ->title('Erro ao processar pagamento')
                ->body($e->getMessage())
                ->danger()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro inesperado')
                ->body('Ocorreu um erro ao processar o pagamento. Por favor, tente novamente.')
                ->danger()
                ->send();
        }
    }

    public function getPackageTotal(): float
    {
        return $this->package ? $this->package->calculateTotal() : 0;
    }

    public function getRegistrationsCount(): int
    {
        return $this->package ? $this->package->registrations->count() : 0;
    }
}
