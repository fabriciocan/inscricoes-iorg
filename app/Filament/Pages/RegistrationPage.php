<?php

namespace App\Filament\Pages;

use App\Filament\Actions\CreateRegistrationAction;
use App\Models\Event;
use App\Models\Package;
use App\Services\EventService;
use App\Services\RegistrationService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class RegistrationPage extends Page implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    protected string $view = 'filament.pages.registration-page';

    protected static ?string $navigationLabel = null;

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public ?Event $event = null;

    public ?Package $package = null;

    public array $registrations = [];

    public function mount(): void
    {
        $eventId = request()->query('event');

        if (!$eventId) {
            Notification::make()
                ->title('Evento não encontrado')
                ->danger()
                ->send();

            redirect()->route('filament.admin.pages.available-events-page');
            return;
        }

        $this->event = Event::find($eventId);

        if (!$this->event || !$this->event->is_active) {
            Notification::make()
                ->title('Evento não disponível')
                ->danger()
                ->send();

            redirect()->route('filament.admin.pages.available-events-page');
            return;
        }

        // Get draft package for this user and event (if exists)
        $this->package = Package::where('user_id', auth()->id())
            ->where('status', 'draft')
            ->whereHas('registrations', function ($query) {
                $query->where('event_id', $this->event->id);
            })
            ->first();

        // Don't create package here - it will be created when first registration is added

        $this->loadRegistrations();
    }

    public function getTitle(): string | Htmlable
    {
        return $this->event ? "Inscrição - {$this->event->name}" : 'Inscrição';
    }



    public static function canAccess(): bool
    {
        return auth()->check();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addRegistration')
                ->label('Adicionar Inscrição')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->modalHeading('Adicionar Nova Inscrição')
                ->modalDescription(function () {
                    if (!$this->event) {
                        return null;
                    }
                    
                    $eventService = app(EventService::class);
                    $currentPrice = $eventService->getCurrentPrice($this->event);
                    
                    return $currentPrice 
                        ? "Valor atual: R$ " . number_format($currentPrice, 2, ',', '.')
                        : null;
                })
                ->form([
                    \Filament\Forms\Components\TextInput::make('participant_name')
                        ->label('Nome do Participante')
                        ->required()
                        ->minLength(3)
                        ->maxLength(255),
                    
                    \Filament\Forms\Components\TextInput::make('participant_email')
                        ->label('Email do Participante')
                        ->email()
                        ->required()
                        ->maxLength(255),
                    
                    \Filament\Forms\Components\TextInput::make('participant_phone')
                        ->label('Telefone do Participante')
                        ->tel()
                        ->required()
                        ->mask('(99) 99999-9999')
                        ->maxLength(15),
                    
                    \Filament\Forms\Components\Textarea::make('participant_data')
                        ->label('Informações Adicionais')
                        ->rows(3)
                        ->maxLength(1000),
                ])
                ->action(function (array $data) {
                    try {
                        $registrationService = app(RegistrationService::class);
                        
                        // Create package if it doesn't exist (only when adding first registration)
                        if (!$this->package) {
                            $this->package = $registrationService->createPackage(auth()->user());
                        }
                        
                        $registrationService->addRegistrationToPackage(
                            $this->package,
                            $this->event,
                            $data
                        );

                        Notification::make()
                            ->title('Inscrição adicionada com sucesso!')
                            ->success()
                            ->send();
                        
                        $this->loadRegistrations();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Erro ao adicionar inscrição')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->modalSubmitActionLabel('Adicionar')
                ->modalCancelActionLabel('Cancelar')
                ->modalWidth('lg'),
        ];
    }

    public function addRegistration(): void
    {
        $this->validate([
            'data.cpf' => 'required|string|max:14',
            'data.participant_name' => 'required|string|max:255',
            'data.birth_date' => 'required|date',
            'data.participant_email' => 'required|email|max:255',
            'data.participant_phone' => 'required|string|max:15',
            'data.assembleia' => 'required|string',
            'data.assembleia_especificar' => 'nullable|string|max:255',
            'data.estado' => 'required|string|max:100',
            'data.cidade' => 'required|string|max:100',
            'data.tipo_inscricao' => 'required|string',
            'data.tipo_inscricao_especificar' => 'nullable|string|max:255',
            'data.cargo' => 'required|string',
            'data.sub_cargo' => 'nullable|string',
            'data.cargo_outro' => 'nullable|string|max:255',
            'data.alumni' => 'required|string',
            'data.mestre_cruz' => 'required|string',
            'data.alergia' => 'nullable|string|max:500',
            'data.medicamento' => 'nullable|string|max:500',
            'data.plano_saude' => 'nullable|string|max:255',
        ], [
            'data.cpf.required' => 'O CPF é obrigatório.',
            'data.participant_name.required' => 'O nome completo é obrigatório.',
            'data.birth_date.required' => 'A data de nascimento é obrigatória.',
            'data.participant_email.required' => 'O e-mail é obrigatório.',
            'data.participant_email.email' => 'Digite um e-mail válido.',
            'data.participant_phone.required' => 'O telefone é obrigatório.',
            'data.assembleia.required' => 'A assembleia é obrigatória.',
            'data.estado.required' => 'O estado é obrigatório.',
            'data.cidade.required' => 'A cidade é obrigatória.',
            'data.tipo_inscricao.required' => 'O tipo de inscrição é obrigatório.',
            'data.cargo.required' => 'O cargo é obrigatório.',
            'data.alumni.required' => 'Informe se faz parte da Alumni.',
            'data.mestre_cruz.required' => 'Informe se é Mestre da Grande Cruz das Cores.',
        ]);

        try {
            $registrationService = app(RegistrationService::class);

            // Create package if it doesn't exist (only when adding first registration)
            if (!$this->package) {
                $this->package = $registrationService->createPackage(auth()->user());
            }

            // Prepare participant_data as JSON with all additional fields
            $participantData = [
                'cpf' => $this->data['cpf'],
                'birth_date' => $this->data['birth_date'],
                'assembleia' => $this->data['assembleia'],
                'assembleia_especificar' => $this->data['assembleia_especificar'] ?? null,
                'estado' => $this->data['estado'],
                'cidade' => $this->data['cidade'],
                'tipo_inscricao' => $this->data['tipo_inscricao'],
                'tipo_inscricao_especificar' => $this->data['tipo_inscricao_especificar'] ?? null,
                'cargo' => $this->data['cargo'],
                'sub_cargo' => $this->data['sub_cargo'] ?? null,
                'cargo_outro' => $this->data['cargo_outro'] ?? null,
                'alumni' => $this->data['alumni'],
                'mestre_cruz' => $this->data['mestre_cruz'],
                'alergia' => $this->data['alergia'] ?? null,
                'medicamento' => $this->data['medicamento'] ?? null,
                'plano_saude' => $this->data['plano_saude'] ?? null,
            ];

            // Prepare registration data
            $registrationData = [
                'participant_name' => $this->data['participant_name'],
                'participant_email' => $this->data['participant_email'],
                'participant_phone' => $this->data['participant_phone'],
                'participant_data' => json_encode($participantData),
            ];

            $registrationService->addRegistrationToPackage(
                $this->package,
                $this->event,
                $registrationData
            );

            Notification::make()
                ->title('Inscrição adicionada com sucesso!')
                ->success()
                ->send();

            $this->loadRegistrations();
            $this->data = [];
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro ao adicionar inscrição')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function removeRegistration(int $registrationId): void
    {
        if (!$this->package) {
            return;
        }

        $registration = $this->package->registrations()->find($registrationId);

        if ($registration) {
            $registration->delete();

            $registrationService = app(RegistrationService::class);
            $registrationService->calculatePackageTotal($this->package);

            // If package is now empty, delete it
            if ($this->package->registrations()->count() === 0) {
                $this->package->delete();
                $this->package = null;
            }

            Notification::make()
                ->title('Inscrição removida')
                ->success()
                ->send();

            $this->loadRegistrations();
        }
    }

    public function proceedToPayment(): void
    {
        if (empty($this->registrations)) {
            Notification::make()
                ->title('Adicione pelo menos uma inscrição')
                ->warning()
                ->send();
            return;
        }

        redirect()->route('filament.admin.pages.payment-page', ['package' => $this->package->id]);
    }

    protected function loadRegistrations(): void
    {
        if (!$this->package) {
            $this->registrations = [];
            return;
        }

        $this->package->refresh();
        $this->registrations = $this->package->registrations()
            ->with('event')
            ->get()
            ->toArray();
    }

    public function getCurrentPrice(): ?float
    {
        if (!$this->event) {
            return null;
        }

        $eventService = app(EventService::class);
        return $eventService->getCurrentPrice($this->event);
    }

    public function getPackageTotal(): float
    {
        return $this->package ? $this->package->calculateTotal() : 0;
    }
}
