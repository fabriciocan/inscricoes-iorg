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
                        ->modifyQueryUsing(fn ($query) => $query
                            ->with(['package.user', 'event'])
                            ->whereHas('package', function ($q) {
                                $q->where('status', 'confirmed');
                            }))
                        ->withFilename(fn () => 'inscricoes_pagas_' . now()->format('Y-m-d_His'))
                        ->withColumns($this->getExportColumns()),
                ]),
            Actions\CreateAction::make(),
        ];
    }

    protected function getExportColumns(): array
    {
        return [
            Column::make('id')->heading('ID'),
            Column::make('package.package_number')->heading('Número do Pacote'),
            Column::make('package.user.name')->heading('Responsável pelo Pacote'),
            Column::make('event.name')->heading('Evento'),
            Column::make('participant_name')->heading('Nome Completo'),
            Column::make('participant_email')->heading('Email'),
            Column::make('participant_phone')->heading('Telefone'),
            Column::make('cpf')
                ->heading('CPF')
                ->getStateUsing(function ($record) {
                    $data = is_string($record->participant_data) 
                        ? json_decode($record->participant_data, true)
                        : $record->participant_data;
                    return $data['cpf'] ?? '';
                }),
            Column::make('birth_date')
                ->heading('Data de Nascimento')
                ->getStateUsing(function ($record) {
                    $data = is_string($record->participant_data) 
                        ? json_decode($record->participant_data, true)
                        : $record->participant_data;
                    $birthDate = $data['birth_date'] ?? null;
                    return $birthDate ? \Carbon\Carbon::parse($birthDate)->format('d/m/Y') : '';
                }),
            Column::make('assembleia')
                ->heading('Assembleia')
                ->getStateUsing(function ($record) {
                    $data = is_string($record->participant_data) 
                        ? json_decode($record->participant_data, true)
                        : $record->participant_data;
                    return $data['assembleia'] ?? '';
                }),
            Column::make('estado')
                ->heading('Estado')
                ->getStateUsing(function ($record) {
                    $data = is_string($record->participant_data) 
                        ? json_decode($record->participant_data, true)
                        : $record->participant_data;
                    return $data['estado'] ?? '';
                }),
            Column::make('cidade')
                ->heading('Cidade')
                ->getStateUsing(function ($record) {
                    $data = is_string($record->participant_data) 
                        ? json_decode($record->participant_data, true)
                        : $record->participant_data;
                    return $data['cidade'] ?? '';
                }),
            Column::make('tipo_inscricao')
                ->heading('Tipo de Inscrição')
                ->getStateUsing(function ($record) {
                    $data = is_string($record->participant_data) 
                        ? json_decode($record->participant_data, true)
                        : $record->participant_data;
                    return $data['tipo_inscricao'] ?? '';
                }),
            Column::make('mestre_cruz')
                ->heading('Mestre da Grande Cruz das Cores')
                ->getStateUsing(function ($record) {
                    $data = is_string($record->participant_data) 
                        ? json_decode($record->participant_data, true)
                        : $record->participant_data;
                    return $data['mestre_cruz'] ?? '';
                }),
            Column::make('refeicao_especial')
                ->heading('Kit Alimentação Separado')
                ->getStateUsing(function ($record) {
                    $data = is_string($record->participant_data) 
                        ? json_decode($record->participant_data, true)
                        : $record->participant_data;
                    return $data['refeicao_especial'] ?? '';
                }),
            Column::make('qual_refeicao_especial')
                ->heading('Qual Alergia')
                ->getStateUsing(function ($record) {
                    $data = is_string($record->participant_data) 
                        ? json_decode($record->participant_data, true)
                        : $record->participant_data;
                    return $data['qual_refeicao_especial'] ?? '';
                }),
            Column::make('package.status')
                ->heading('Status do Pacote')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'draft' => 'Rascunho',
                    'pending' => 'Pendente',
                    'confirmed' => 'Confirmado',
                    'cancelled' => 'Cancelado',
                    default => $state,
                }),
            Column::make('price_paid')
                ->heading('Valor')
                ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state, 2, ',', '.')),
            Column::make('created_at')
                ->heading('Data de Inscrição')
                ->formatStateUsing(fn ($state) => $state->format('d/m/Y H:i')),
        ];
    }
}
