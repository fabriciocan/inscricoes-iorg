<?php

namespace App\Filament\Resources\RegistrationResource\Pages;

use App\Filament\Resources\RegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ListRegistrations extends ListRecords
{
    protected static string $resource = RegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->label('Exportar Inscrições Pagas')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->exports([
                    ExcelExport::make()
                        ->fromTable()
                        ->modifyQueryUsing(fn ($query) => $query->whereHas('package', function ($q) {
                            $q->where('status', 'confirmed');
                        }))
                        ->withFilename(fn () => 'inscricoes_pagas_' . now()->format('Y-m-d_His'))
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
            Actions\CreateAction::make(),
        ];
    }
}
