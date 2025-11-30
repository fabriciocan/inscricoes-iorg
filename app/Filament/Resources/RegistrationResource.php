<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistrationResource\Pages;
use App\Models\Registration;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use UnitEnum;

class RegistrationResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Inscrições';

    protected static ?string $modelLabel = 'Inscrição';

    protected static ?string $pluralModelLabel = 'Inscrições';

    protected static ?int $navigationSort = 20;

    protected static UnitEnum|string|null $navigationGroup = 'Gerenciamento';

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Section::make('Informações do Evento')
                    ->schema([
                        Forms\Components\Select::make('event_id')
                            ->label('Evento')
                            ->relationship('event', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('package_id')
                            ->label('Pacote')
                            ->relationship('package', 'package_number')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(1),

                Section::make('Dados do Participante')
                    ->schema([
                        Forms\Components\TextInput::make('participant_name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('participant_email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('participant_phone')
                            ->label('Telefone')
                            ->tel()
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(1),

                Section::make('Informações Detalhadas')
                    ->schema([
                        Forms\Components\TextInput::make('participant_data.cpf')
                            ->label('CPF')
                            ->mask('999.999.999-99')
                            ->maxLength(14),

                        Forms\Components\DatePicker::make('participant_data.birth_date')
                            ->label('Data de Nascimento')
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        Forms\Components\Select::make('participant_data.assembleia')
                            ->label('Assembleia')
                            ->options([
                                'Assembleia Caminhos de Luz Nº 1' => 'Assembleia Caminhos de Luz Nº 1',
                                'Assembleia Flores do Pantanal Nº 1' => 'Assembleia Flores do Pantanal Nº 1',
                                'Assembleia Biguaçu Nº 1' => 'Assembleia Biguaçu Nº 1',
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
                                'Assembleia Aliança Ibiporã Nº 22' => 'Assembleia Aliança Ibiporã Nº 22',
                                'Assembleia Filhas da Luz Nº 23' => 'Assembleia Filhas da Luz Nº 23',
                                'Visitantes/Outras Jurisdições' => 'Visitantes/Outras Jurisdições',
                            ])
                            ->searchable(),

                        Forms\Components\TextInput::make('participant_data.estado')
                            ->label('Estado')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('participant_data.cidade')
                            ->label('Cidade')
                            ->maxLength(255),

                        Forms\Components\Select::make('participant_data.tipo_inscricao')
                            ->label('Tipo de Inscrição')
                            ->options([
                                'Arco-íris Ativa' => 'Arco-íris Ativa',
                                'Maioridade' => 'Maioridade',
                                'Menina Promessa do Arco-íris' => 'Menina Promessa do Arco-íris',
                                'Tia Estrela do Oriente' => 'Tia Estrela do Oriente',
                                'Tia não iniciada na Estrela do Oriente' => 'Tia não iniciada na Estrela do Oriente',
                                'Tio Maçom' => 'Tio Maçom',
                                'Tio não iniciado na Maçonaria' => 'Tio não iniciado na Maçonaria',
                            ])
                            ->searchable(),

                        Forms\Components\TextInput::make('participant_data.cargo')
                            ->label('Cargo')
                            ->maxLength(255),

                        Forms\Components\Select::make('participant_data.mestre_cruz')
                            ->label('Mestre da Grande Cruz das Cores')
                            ->options([
                                'Sim' => 'Sim',
                                'Não' => 'Não',
                            ]),

                        Forms\Components\Select::make('participant_data.refeicao_especial')
                            ->label('Refeição Especial')
                            ->options([
                                'Sim' => 'Sim',
                                'Não' => 'Não',
                            ]),

                        Forms\Components\TextInput::make('participant_data.qual_refeicao_especial')
                            ->label('Qual Refeição Especial')
                            ->maxLength(255),
                    ])
                    ->columns(1)
                    ->collapsed(),

                Section::make('Informações de Pagamento')
                    ->schema([
                        Forms\Components\TextInput::make('price_paid')
                            ->label('Valor Pago')
                            ->required()
                            ->numeric()
                            ->prefix('R$')
                            ->minValue(0)
                            ->step(0.01),
                    ])
                    ->columns(1),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Section::make('Informações do Evento')
                    ->schema([
                        Infolists\Components\TextEntry::make('event.name')
                            ->label('Evento'),

                        Infolists\Components\TextEntry::make('package.package_number')
                            ->label('Pacote')
                            ->copyable(),
                    ])
                    ->columns(1),

                Section::make('Dados do Participante')
                    ->schema([
                        Infolists\Components\TextEntry::make('participant_name')
                            ->label('Nome'),

                        Infolists\Components\TextEntry::make('participant_email')
                            ->label('Email')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('participant_phone')
                            ->label('Telefone'),
                    ])
                    ->columns(1),

                Section::make('Informações Detalhadas')
                    ->schema([
                        Infolists\Components\TextEntry::make('cpf')
                            ->label('CPF')
                            ->state(function ($record) {
                                $data = is_string($record->participant_data)
                                    ? json_decode($record->participant_data, true)
                                    : $record->participant_data;
                                return $data['cpf'] ?? '-';
                            }),

                        Infolists\Components\TextEntry::make('birth_date')
                            ->label('Data de Nascimento')
                            ->state(function ($record) {
                                $data = is_string($record->participant_data)
                                    ? json_decode($record->participant_data, true)
                                    : $record->participant_data;
                                return isset($data['birth_date'])
                                    ? \Carbon\Carbon::parse($data['birth_date'])->format('d/m/Y')
                                    : '-';
                            }),

                        Infolists\Components\TextEntry::make('assembleia')
                            ->label('Assembleia')
                            ->state(function ($record) {
                                $data = is_string($record->participant_data)
                                    ? json_decode($record->participant_data, true)
                                    : $record->participant_data;
                                return $data['assembleia'] ?? '-';
                            }),

                        Infolists\Components\TextEntry::make('estado')
                            ->label('Estado')
                            ->state(function ($record) {
                                $data = is_string($record->participant_data)
                                    ? json_decode($record->participant_data, true)
                                    : $record->participant_data;
                                return $data['estado'] ?? '-';
                            }),

                        Infolists\Components\TextEntry::make('cidade')
                            ->label('Cidade')
                            ->state(function ($record) {
                                $data = is_string($record->participant_data)
                                    ? json_decode($record->participant_data, true)
                                    : $record->participant_data;
                                return $data['cidade'] ?? '-';
                            }),

                        Infolists\Components\TextEntry::make('tipo_inscricao')
                            ->label('Tipo de Inscrição')
                            ->state(function ($record) {
                                $data = is_string($record->participant_data)
                                    ? json_decode($record->participant_data, true)
                                    : $record->participant_data;
                                return $data['tipo_inscricao'] ?? '-';
                            }),

                        Infolists\Components\TextEntry::make('cargo')
                            ->label('Cargo')
                            ->state(function ($record) {
                                $data = is_string($record->participant_data)
                                    ? json_decode($record->participant_data, true)
                                    : $record->participant_data;
                                return $data['cargo'] ?? '-';
                            }),

                        Infolists\Components\TextEntry::make('mestre_cruz')
                            ->label('Mestre da Grande Cruz das Cores')
                            ->state(function ($record) {
                                $data = is_string($record->participant_data)
                                    ? json_decode($record->participant_data, true)
                                    : $record->participant_data;
                                return $data['mestre_cruz'] ?? '-';
                            }),

                        Infolists\Components\TextEntry::make('refeicao_especial')
                            ->label('Refeição Especial')
                            ->state(function ($record) {
                                $data = is_string($record->participant_data)
                                    ? json_decode($record->participant_data, true)
                                    : $record->participant_data;
                                return $data['refeicao_especial'] ?? '-';
                            }),

                        Infolists\Components\TextEntry::make('qual_refeicao_especial')
                            ->label('Qual Refeição Especial')
                            ->state(function ($record) {
                                $data = is_string($record->participant_data)
                                    ? json_decode($record->participant_data, true)
                                    : $record->participant_data;
                                return $data['qual_refeicao_especial'] ?? '-';
                            })
                            ->visible(function ($record) {
                                $data = is_string($record->participant_data)
                                    ? json_decode($record->participant_data, true)
                                    : $record->participant_data;
                                return !empty($data['qual_refeicao_especial'] ?? null);
                            }),
                    ])
                    ->columns(1)
                    ->collapsed(),

                Section::make('Informações de Pagamento')
                    ->schema([
                        Infolists\Components\TextEntry::make('package.status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'draft' => 'gray',
                                'pending' => 'warning',
                                'confirmed' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'draft' => 'Rascunho',
                                'pending' => 'Pendente',
                                'confirmed' => 'Confirmado',
                                'cancelled' => 'Cancelado',
                                default => $state,
                            }),

                        Infolists\Components\TextEntry::make('price_paid')
                            ->label('Valor Pago')
                            ->money('BRL'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Data de Inscrição')
                            ->dateTime('d/m/Y H:i'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Última Atualização')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('package.package_number')
                    ->label('Pacote')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('event.name')
                    ->label('Evento')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('participant_name')
                    ->label('Participante')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('participant_email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: false),
                
                Tables\Columns\TextColumn::make('participant_phone')
                    ->label('Telefone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('package.status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Rascunho',
                        'pending' => 'Pendente',
                        'confirmed' => 'Confirmado',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('price_paid')
                    ->label('Valor Pago')
                    ->money('BRL')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data de Inscrição')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_id')
                    ->label('Evento')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Rascunho',
                        'pending' => 'Pendente',
                        'confirmed' => 'Confirmado',
                        'cancelled' => 'Cancelado',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            $query->whereHas('package', function ($q) use ($data) {
                                $q->where('status', $data['value']);
                            });
                        }
                    }),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Inscrito de')
                            ->native(false),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Inscrito até')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),

                Tables\Filters\Filter::make('package_number')
                    ->form([
                        Forms\Components\TextInput::make('package_number')
                            ->label('Número do Pacote'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['package_number'],
                            fn (Builder $query, $number): Builder => $query->whereHas('package', function ($q) use ($number) {
                                $q->where('package_number', 'like', "%{$number}%");
                            })
                        );
                    }),

                Tables\Filters\Filter::make('cpf')
                    ->form([
                        Forms\Components\TextInput::make('cpf')
                            ->label('CPF')
                            ->mask('999.999.999-99'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['cpf'],
                            fn (Builder $query, $cpf): Builder => $query->whereRaw("JSON_EXTRACT(participant_data, '$.cpf') LIKE ?", ["%{$cpf}%"])
                        );
                    }),

                Tables\Filters\SelectFilter::make('assembleia')
                    ->label('Assembleia')
                    ->options([
                        'Assembleia Caminhos de Luz Nº 1' => 'Assembleia Caminhos de Luz Nº 1',
                        'Assembleia Flores do Pantanal Nº 1' => 'Assembleia Flores do Pantanal Nº 1',
                        'Assembleia Biguaçu Nº 1' => 'Assembleia Biguaçu Nº 1',
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
                        'Assembleia Aliança Ibiporã Nº 22' => 'Assembleia Aliança Ibiporã Nº 22',
                        'Assembleia Filhas da Luz Nº 23' => 'Assembleia Filhas da Luz Nº 23',
                        'Visitantes/Outras Jurisdições' => 'Visitantes/Outras Jurisdições',
                    ])
                    ->searchable()
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            $query->whereRaw("JSON_EXTRACT(participant_data, '$.assembleia') = ?", [$data['value']]);
                        }
                    }),

                Tables\Filters\Filter::make('estado')
                    ->form([
                        Forms\Components\TextInput::make('estado')
                            ->label('Estado'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['estado'],
                            fn (Builder $query, $estado): Builder => $query->whereRaw("JSON_EXTRACT(participant_data, '$.estado') LIKE ?", ["%{$estado}%"])
                        );
                    }),

                Tables\Filters\Filter::make('cidade')
                    ->form([
                        Forms\Components\TextInput::make('cidade')
                            ->label('Cidade'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['cidade'],
                            fn (Builder $query, $cidade): Builder => $query->whereRaw("JSON_EXTRACT(participant_data, '$.cidade') LIKE ?", ["%{$cidade}%"])
                        );
                    }),

                Tables\Filters\SelectFilter::make('tipo_inscricao')
                    ->label('Tipo de Inscrição')
                    ->options([
                        'Arco-íris Ativa' => 'Arco-íris Ativa',
                        'Maioridade' => 'Maioridade',
                        'Menina Promessa do Arco-íris' => 'Menina Promessa do Arco-íris',
                        'Tia Estrela do Oriente' => 'Tia Estrela do Oriente',
                        'Tia não iniciada na Estrela do Oriente' => 'Tia não iniciada na Estrela do Oriente',
                        'Tio Maçom' => 'Tio Maçom',
                        'Tio não iniciado na Maçonaria' => 'Tio não iniciado na Maçonaria',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            $query->whereRaw("JSON_EXTRACT(participant_data, '$.tipo_inscricao') = ?", [$data['value']]);
                        }
                    }),

                Tables\Filters\Filter::make('cargo')
                    ->form([
                        Forms\Components\TextInput::make('cargo')
                            ->label('Cargo'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['cargo'],
                            fn (Builder $query, $cargo): Builder => $query->whereRaw("JSON_EXTRACT(participant_data, '$.cargo') LIKE ?", ["%{$cargo}%"])
                        );
                    }),

                Tables\Filters\SelectFilter::make('mestre_cruz')
                    ->label('Mestre da Grande Cruz das Cores')
                    ->options([
                        'Sim' => 'Sim',
                        'Não' => 'Não',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            $query->whereRaw("JSON_EXTRACT(participant_data, '$.mestre_cruz') = ?", [$data['value']]);
                        }
                    }),

                Tables\Filters\SelectFilter::make('refeicao_especial')
                    ->label('Refeição Especial')
                    ->options([
                        'Sim' => 'Sim',
                        'Não' => 'Não',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            $query->whereRaw("JSON_EXTRACT(participant_data, '$.refeicao_especial') = ?", [$data['value']]);
                        }
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                ExportBulkAction::make()
                    ->label('Exportar para Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename(fn () => 'inscricoes_' . now()->format('Y-m-d_His'))
                            ->withColumns([
                                Column::make('id')->heading('ID'),
                                Column::make('package.package_number')->heading('Número do Pacote'),
                                Column::make('event.name')->heading('Evento'),
                                Column::make('participant_name')->heading('Nome do Participante'),
                                Column::make('participant_email')->heading('Email'),
                                Column::make('participant_phone')->heading('Telefone'),
                                Column::make('participant_data.cpf')->heading('CPF'),
                                Column::make('participant_data.birth_date')
                                    ->heading('Data de Nascimento')
                                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : ''),
                                Column::make('participant_data.assembleia')->heading('Assembleia'),
                                Column::make('participant_data.estado')->heading('Estado'),
                                Column::make('participant_data.cidade')->heading('Cidade'),
                                Column::make('participant_data.tipo_inscricao')->heading('Tipo de Inscrição'),
                                Column::make('participant_data.cargo')->heading('Cargo'),
                                Column::make('participant_data.mestre_cruz')->heading('Mestre da Grande Cruz das Cores'),
                                Column::make('participant_data.refeicao_especial')->heading('Refeição Especial'),
                                Column::make('participant_data.qual_refeicao_especial')->heading('Qual Refeição Especial'),
                                Column::make('package.status')
                                    ->heading('Status')
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'draft' => 'Rascunho',
                                        'pending' => 'Pendente',
                                        'confirmed' => 'Confirmado',
                                        'cancelled' => 'Cancelado',
                                        default => $state,
                                    }),
                                Column::make('price_paid')
                                    ->heading('Valor Pago')
                                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state, 2, ',', '.')),
                                Column::make('created_at')
                                    ->heading('Data de Inscrição')
                                    ->formatStateUsing(fn ($state) => $state->format('d/m/Y H:i')),
                            ]),
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistrations::route('/'),
            'create' => Pages\CreateRegistration::route('/create'),
            'view' => Pages\ViewRegistration::route('/{record}'),
            'edit' => Pages\EditRegistration::route('/{record}/edit'),
        ];
    }
}
