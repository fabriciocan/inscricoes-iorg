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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

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

    public bool $showForm = false;

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

        $this->form->fill();
    }

    public function getTitle(): string | Htmlable
    {
        return $this->event ? "Inscrição - {$this->event->name}" : 'Inscrição';
    }



    public static function canAccess(): bool
    {
        return auth()->check();
    }

    protected function getFormSchema(): array
    {
        return [
                Section::make('Informações Pessoais')
                    ->description('Dados pessoais do participante')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('cpf')
                                    ->label('CPF')
                                    ->required()
                                    ->mask('999.999.999-99')
                                    ->placeholder('000.000.000-00')
                                    ->maxLength(14),

                                TextInput::make('participant_name')
                                    ->label('Nome Completo')
                                    ->required()
                                    ->columnSpan(2)
                                    ->maxLength(255),
                            ]),

                        Grid::make(3)
                            ->schema([
                                DatePicker::make('birth_date')
                                    ->label('Data de Nascimento')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->maxDate(now()),

                                TextInput::make('participant_email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('participant_phone')
                                    ->label('Telefone')
                                    ->tel()
                                    ->required()
                                    ->mask('(99) 99999-9999')
                                    ->maxLength(15),
                            ]),
                    ])
                    ->collapsible()
                    ->columns(1),

                Section::make('Assembleia e Localização')
                    ->description('Informações sobre assembleia e cidade')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('assembleia')
                                    ->label('Assembleia')
                                    ->required()
                                    ->placeholder('Selecione sua Assembleia...')
                                    ->options([
                                        'Assembleia Caminho de Luz Nº 1' => 'Assembleia Caminho de Luz Nº 1',
                                        'Assembleia Pitágoras Nº 2' => 'Assembleia Pitágoras Nº 2',
                                        'Assembleia Filhos de Hiram Nº 3' => 'Assembleia Filhos de Hiram Nº 3',
                                        'Assembleia Acácia Nº 4' => 'Assembleia Acácia Nº 4',
                                        'Assembleia Portal da Vida Nº 5' => 'Assembleia Portal da Vida Nº 5',
                                        'Assembleia Divina Flor Nº 6' => 'Assembleia Divina Flor Nº 6',
                                        'Assembleia Estrela da Paz Nº 9' => 'Assembleia Estrela da Paz Nº 9',
                                        'Assembleia Anjos da Paz Nº 10' => 'Assembleia Anjos da Paz Nº 10',
                                        'Assembleia Flores de Acácia Nº 11' => 'Assembleia Flores de Acácia Nº 11',
                                        'Assembleia Lírios do Vale Nº 12' => 'Assembleia Lírios do Vale Nº 12',
                                        'Assembleia Guardiãs da Luz Nº 13' => 'Assembleia Guardiãs da Luz Nº 13',
                                        'Assembleia Harmonia das Cores Nº 14' => 'Assembleia Harmonia das Cores Nº 14',
                                        'Assembleia Luz das Águas Nº 15' => 'Assembleia Luz das Águas Nº 15',
                                        'Assembleia Rosa dos Ventos Nº 16' => 'Assembleia Rosa dos Ventos Nº 16',
                                        'Assembleia Água Viva Nº 17' => 'Assembleia Água Viva Nº 17',
                                        'Assembleia Guardiã das Cores Nº 18' => 'Assembleia Guardiã das Cores Nº 18',
                                        'Assembleia Renascer Nº 19' => 'Assembleia Renascer Nº 19',
                                        'Assembleia Luz do Oriente Nº 20' => 'Assembleia Luz do Oriente Nº 20',
                                        'Assembleia Guardiãs do Manacá Nº 21' => 'Assembleia Guardiãs do Manacá Nº 21',
                                        'Assembleia Flores do Pantanal Nº 22' => 'Assembleia Flores do Pantanal Nº 22',
                                        'Assembleia Biguaçu Nº 23' => 'Assembleia Biguaçu Nº 23',
                                        'Visitantes/Outras Jurisdições' => 'Visitantes/Outras Jurisdições',
                                    ])
                                    ->searchable()
                                    ->columnSpan(2),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('estado')
                                    ->label('Estado')
                                    ->required()
                                    ->maxLength(100),

                                TextInput::make('cidade')
                                    ->label('Cidade')
                                    ->required()
                                    ->maxLength(100),
                            ]),
                    ])
                    ->collapsible()
                    ->columns(1),

                Section::make('Informações de Inscrição')
                    ->description('Tipo de inscrição e cargo')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('tipo_inscricao')
                                    ->label('Tipo de Inscrição')
                                    ->required()
                                    ->placeholder('Selecione o tipo de inscrição...')
                                    ->options([
                                        'Ativa' => 'Ativa',
                                        'Maioridade' => 'Maioridade',
                                        'Promessa' => 'Promessa',
                                        'Tia Estrela do Oriente' => 'Tia Estrela do Oriente',
                                        'Tia NÃO Estrela do Oriente' => 'Tia',
                                        'Maçom' => 'Maçom',
                                        'Tio NÃO Maçom' => 'Tio NÃO Maçom',
                                        'Outro' => 'Outro',
                                    ])
                                    ->searchable()
                                    ->live(),

                                Select::make('cargo')
                                    ->label('Qual seu cargo?')
                                    ->required()
                                    ->placeholder('Selecione seu cargo...')
                                    ->options([
                                        'Grande Cargo' => 'Grande Cargo',
                                        'Ilustre Preceptora' => 'Ilustre Preceptora',
                                        'Ilustre Preceptora Adjunta' => 'Ilustre Preceptora Adjunta',
                                        'Esperança' => 'Esperança',
                                        'Caridade' => 'Caridade',
                                        'Fé' => 'Fé',
                                        'Arquivista' => 'Arquivista',
                                        'Tesoureira' => 'Tesoureira',
                                        'Capelã' => 'Capelã',
                                        'Chefe do Cerimonial' => 'Chefe do Cerimonial',
                                        'Amor' => 'Amor',
                                        'Religião' => 'Religião',
                                        'Natureza' => 'Natureza',
                                        'Imortalidade' => 'Imortalidade',
                                        'Fidelidade' => 'Fidelidade',
                                        'Patriostismo' => 'Patriostismo',
                                        'Serviço' => 'Serviço',
                                        'Observadora Confidencial' => 'Observadora Confidencial',
                                        'Observadora Externa' => 'Observadora Externa',
                                        'Música' => 'Música',
                                        'Regente do Coro' => 'Regente do Coro',
                                        'Coro' => 'Coro',
                                        'Preceptora Mãe' => 'Preceptora Mãe',
                                        'Preceptora Mãe Adjunta' => 'Preceptora Mãe Adjunta',
                                        'Presidente do Conselho Consultivo' => 'Presidente do Conselho Consultivo',
                                        'Membro do Conselho Consultivo' => 'Membro do Conselho Consultivo',
                                        'Outro' => 'Outro',
                                    ])
                                    ->searchable()
                                    ->live(),
                            ]),

                        TextInput::make('tipo_inscricao_outro')
                            ->label('Especifique o tipo de inscrição')
                            ->placeholder('Digite o tipo de inscrição...')
                            ->maxLength(255)
                            ->required(fn ($get) => $get('tipo_inscricao') === 'Outro')
                            ->visible(fn ($get) => $get('tipo_inscricao') === 'Outro')
                            ->dehydrated(fn ($get) => $get('tipo_inscricao') === 'Outro'),

                        TextInput::make('cargo_outro')
                            ->label('Especifique seu cargo')
                            ->placeholder('Digite seu cargo...')
                            ->maxLength(255)
                            ->required(fn ($get) => $get('cargo') === 'Outro')
                            ->visible(fn ($get) => $get('cargo') === 'Outro')
                            ->dehydrated(fn ($get) => $get('cargo') === 'Outro'),

                        Grid::make(2)
                            ->schema([
                                Select::make('alumni')
                                    ->label('Faz parte da Alumni?')
                                    ->required()
                                    ->placeholder('Selecione...')
                                    ->options([
                                        'Sim' => 'Sim',
                                        'Não' => 'Não',
                                    ]),

                                Select::make('mestre_cruz')
                                    ->label('É Mestre da Grande Cruz das Cores?')
                                    ->required()
                                    ->placeholder('Selecione...')
                                    ->options([
                                        'Sim' => 'Sim',
                                        'Não' => 'Não',
                                    ]),
                            ]),
                    ])
                    ->collapsible()
                    ->columns(1),

                Section::make('Informações de Saúde')
                    ->description('Alergias, medicamentos e plano de saúde')
                    ->schema([
                        Textarea::make('alergia')
                            ->label('Você tem alguma alergia?')
                            ->placeholder('Liste aqui alergias, se não possuir deixe em branco')
                            ->rows(2)
                            ->maxLength(500),

                        Textarea::make('medicamento')
                            ->label('Você faz uso de algum medicamento?')
                            ->placeholder('Liste aqui medicamentos, se não utilizar deixe em branco')
                            ->rows(2)
                            ->maxLength(500),

                        TextInput::make('plano_saude')
                            ->label('Você tem plano de saúde?')
                            ->placeholder('Digite aqui seu plano, se não possuir deixe em branco')
                            ->maxLength(255),
                    ])
                    ->collapsible()
                    ->columns(1),
            ];
    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }

    public function toggleForm(): void
    {
        $this->showForm = !$this->showForm;
        if (!$this->showForm) {
            $this->form->fill();
        }
    }

    public function submitForm(): void
    {
        try {
            $data = $this->form->getState();

            $registrationService = app(RegistrationService::class);

            // Create package if it doesn't exist (only when adding first registration)
            if (!$this->package) {
                $this->package = $registrationService->createPackage(auth()->user());
            }

            // Prepare participant_data as JSON with all additional fields
            $tipoInscricao = $data['tipo_inscricao'];
            if ($tipoInscricao === 'Outro' && isset($data['tipo_inscricao_outro'])) {
                $tipoInscricao = $data['tipo_inscricao_outro'];
            }

            $cargo = $data['cargo'];
            if ($cargo === 'Outro' && isset($data['cargo_outro'])) {
                $cargo = $data['cargo_outro'];
            }

            $participantData = [
                'cpf' => $data['cpf'],
                'birth_date' => $data['birth_date'],
                'assembleia' => $data['assembleia'],
                'estado' => $data['estado'],
                'cidade' => $data['cidade'],
                'tipo_inscricao' => $tipoInscricao,
                'cargo' => $cargo,
                'alumni' => $data['alumni'],
                'mestre_cruz' => $data['mestre_cruz'],
                'alergia' => $data['alergia'] ?? null,
                'medicamento' => $data['medicamento'] ?? null,
                'plano_saude' => $data['plano_saude'] ?? null,
            ];

            // Prepare registration data
            $registrationData = [
                'participant_name' => $data['participant_name'],
                'participant_email' => $data['participant_email'],
                'participant_phone' => $data['participant_phone'],
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
            $this->form->fill();
            $this->showForm = false;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro ao adicionar inscrição')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function addRegistration(): void
    {
        $this->validate([
            'data.cpf' => 'required|string|max:14',
            'data.participant_name' => 'required|string|max:255',
            'data.birth_date' => 'required|date',
            'data.participant_email' => 'required|email|max:255',
            'data.participant_phone' => 'required|string|max:15',
            'data.assembleia' => 'required|string|max:255',
            'data.estado' => 'required|string|max:100',
            'data.cidade' => 'required|string|max:100',
            'data.tipo_inscricao' => 'required|string|max:255',
            'data.cargo' => 'required|string|max:255',
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
            $tipoInscricao = $this->data['tipo_inscricao'];
            if ($tipoInscricao === 'Outro' && isset($this->data['tipo_inscricao_outro'])) {
                $tipoInscricao = $this->data['tipo_inscricao_outro'];
            }

            $cargo = $this->data['cargo'];
            if ($cargo === 'Outro' && isset($this->data['cargo_outro'])) {
                $cargo = $this->data['cargo_outro'];
            }

            $participantData = [
                'cpf' => $this->data['cpf'],
                'birth_date' => $this->data['birth_date'],
                'assembleia' => $this->data['assembleia'],
                'estado' => $this->data['estado'],
                'cidade' => $this->data['cidade'],
                'tipo_inscricao' => $tipoInscricao,
                'cargo' => $cargo,
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
