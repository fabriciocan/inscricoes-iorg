<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistrationResource\Pages;
use App\Models\Registration;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
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

                        Forms\Components\TextInput::make('participant_data.estado')
                            ->label('Estado')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('participant_data.cidade')
                            ->label('Cidade')
                            ->maxLength(255),

                        Forms\Components\Select::make('participant_data.tipo_inscricao')
                            ->label('Tipo de Inscrição')
                            ->options([
                                'Ativa' => 'Ativa',
                                'Maioridade' => 'Maioridade',
                                'Promessa' => 'Promessa',
                                'Tia Estrela do Oriente' => 'Tia Estrela do Oriente',
                                'Tia NÃO Estrela do Oriente' => 'Tia',
                                'Maçom' => 'Maçom',
                                'Tio NÃO Maçom' => 'Tio NÃO Maçom',
                            ])
                            ->searchable(),

                        Forms\Components\Select::make('participant_data.cargo')
                            ->label('Cargo')
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
                            ])
                            ->searchable(),

                        Forms\Components\Select::make('participant_data.alumni')
                            ->label('Alumni')
                            ->options([
                                'Sim' => 'Sim',
                                'Não' => 'Não',
                            ]),

                        Forms\Components\Select::make('participant_data.mestre_cruz')
                            ->label('Mestre da Grande Cruz')
                            ->options([
                                'Sim' => 'Sim',
                                'Não' => 'Não',
                            ]),
                    ])
                    ->columns(2)
                    ->collapsed(),

                Section::make('Informações de Saúde')
                    ->schema([
                        Forms\Components\Textarea::make('participant_data.alergia')
                            ->label('Alergias')
                            ->rows(2)
                            ->maxLength(500),

                        Forms\Components\Textarea::make('participant_data.medicamento')
                            ->label('Medicamentos')
                            ->rows(2)
                            ->maxLength(500),

                        Forms\Components\Textarea::make('participant_data.plano_saude')
                            ->label('Plano de Saúde')
                            ->rows(2)
                            ->maxLength(500),
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
                        '24' => 'Visitantes/Outras Jurisdições',
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
                        'Ativa' => 'Ativa',
                        'Maioridade' => 'Maioridade',
                        'Promessa' => 'Promessa',
                        'Tia Estrela do Oriente' => 'Tia Estrela do Oriente',
                        'Tia NÃO Estrela do Oriente' => 'Tia',
                        'Maçom' => 'Maçom',
                        'Tio NÃO Maçom' => 'Tio NÃO Maçom',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            $query->whereRaw("JSON_EXTRACT(participant_data, '$.tipo_inscricao') = ?", [$data['value']]);
                        }
                    }),

                Tables\Filters\SelectFilter::make('cargo')
                    ->label('Cargo')
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
                    ])
                    ->searchable()
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            $query->whereRaw("JSON_EXTRACT(participant_data, '$.cargo') = ?", [$data['value']]);
                        }
                    }),

                Tables\Filters\SelectFilter::make('alumni')
                    ->label('Alumni')
                    ->options([
                        'Sim' => 'Sim',
                        'Não' => 'Não',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            $query->whereRaw("JSON_EXTRACT(participant_data, '$.alumni') = ?", [$data['value']]);
                        }
                    }),

                Tables\Filters\SelectFilter::make('mestre_cruz')
                    ->label('Mestre da Grande Cruz')
                    ->options([
                        'Sim' => 'Sim',
                        'Não' => 'Não',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            $query->whereRaw("JSON_EXTRACT(participant_data, '$.mestre_cruz') = ?", [$data['value']]);
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
                                Column::make('participant_data.alumni')->heading('Alumni'),
                                Column::make('participant_data.mestre_cruz')->heading('Mestre da Grande Cruz'),
                                Column::make('participant_data.alergia')->heading('Alergias'),
                                Column::make('participant_data.medicamento')->heading('Medicamentos'),
                                Column::make('participant_data.plano_saude')->heading('Plano de Saúde'),
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
