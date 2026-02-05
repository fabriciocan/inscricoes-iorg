<?php

namespace App\Filament\Pages;

use App\Models\Registration;
use BackedEnum;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class HotelRegistrationsPage extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected string $view = 'filament.pages.hotel-registrations-page';

    protected static ?string $navigationLabel = 'Inscrições Confirmadas';

    protected static ?string $title = 'Inscrições Confirmadas';

    protected static ?int $navigationSort = 10;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static UnitEnum|string|null $navigationGroup = null;

    public function getTitle(): string|Htmlable
    {
        return 'Inscrições Confirmadas';
    }

    public function getSubheading(): ?string
    {
        $count = $this->getFilteredTableQuery()->count();
        return "{$count} inscrição(ões) encontrada(s)";
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isHotel() ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isHotel() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Registration::query()
                    ->whereHas('package', function (Builder $query) {
                        $query->where('status', 'confirmed');
                    })
            )
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('participant_name')
                    ->label('Nome Completo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('birth_date_display')
                    ->label('Data de Nascimento')
                    ->getStateUsing(function (Registration $record): string {
                        $data = is_string($record->participant_data)
                            ? json_decode($record->participant_data, true)
                            : $record->participant_data;
                        if (!empty($data['birth_date'])) {
                            return \Carbon\Carbon::parse($data['birth_date'])->format('d/m/Y');
                        }
                        return '-';
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("JSON_EXTRACT(participant_data, '$.birth_date') {$direction}");
                    }),

                TextColumn::make('cpf_display')
                    ->label('CPF')
                    ->getStateUsing(function (Registration $record): string {
                        $data = is_string($record->participant_data)
                            ? json_decode($record->participant_data, true)
                            : $record->participant_data;
                        return $data['cpf'] ?? '-';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $cleanSearch = preg_replace('/[^0-9]/', '', $search);
                        return $query->where(function ($q) use ($search, $cleanSearch) {
                            // Busca direta no texto (CPF formatado)
                            $q->where('participant_data', 'LIKE', "%{$search}%");

                            // Se digitou só números, busca também o CPF formatado
                            if ($cleanSearch && strlen($cleanSearch) >= 3) {
                                // Formata como XXX.XXX.XXX-XX para buscar
                                $formatted = $cleanSearch;
                                if (strlen($cleanSearch) >= 3) {
                                    $formatted = substr($cleanSearch, 0, 3);
                                    if (strlen($cleanSearch) > 3) $formatted .= '.' . substr($cleanSearch, 3, 3);
                                    if (strlen($cleanSearch) > 6) $formatted .= '.' . substr($cleanSearch, 6, 3);
                                    if (strlen($cleanSearch) > 9) $formatted .= '-' . substr($cleanSearch, 9, 2);
                                }
                                $q->orWhere('participant_data', 'LIKE', "%{$formatted}%");
                            }
                        });
                    }),

                TextColumn::make('assembleia_display')
                    ->label('Assembleia')
                    ->getStateUsing(function (Registration $record): string {
                        $data = is_string($record->participant_data)
                            ? json_decode($record->participant_data, true)
                            : $record->participant_data;
                        return $data['assembleia'] ?? '-';
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("JSON_EXTRACT(participant_data, '$.assembleia') {$direction}");
                    }),
            ])
            ->filters([
                SelectFilter::make('assembleia')
                    ->label('Assembleia')
                    ->options([
                        'Assembleia Flores do Pantanal Nº 1' => 'Assembleia Flores do Pantanal Nº 1',
                        'Assembleia Biguaçu Nº 1' => 'Assembleia Biguaçu Nº 1',
                        'Assembleia Caminhos de Luz Nº 1' => 'Assembleia Caminhos de Luz Nº 1',
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
                            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(JSON_UNQUOTE(participant_data), '$.assembleia')) = ?", [$data['value']]);
                        }
                    }),
            ])
            ->recordActions([])
            ->bulkActions([])
            ->defaultSort('participant_name', 'asc')
            ->emptyStateHeading('Nenhuma inscrição confirmada')
            ->emptyStateDescription('Ainda não há inscrições confirmadas para exibir.')
            ->emptyStateIcon('heroicon-o-user-group');
    }
}
