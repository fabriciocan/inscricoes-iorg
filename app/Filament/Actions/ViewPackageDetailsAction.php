<?php

namespace App\Filament\Actions;

use App\Models\Package;
use Filament\Actions\Action;

class ViewPackageDetailsAction
{
    public static function make(): Action
    {
        return Action::make('view_details')
            ->label('Ver Detalhes')
            ->icon('heroicon-o-eye')
            ->color('info')
            ->modalHeading(fn (Package $record) => "Detalhes do Pacote {$record->package_number}")
            ->modalDescription(fn (Package $record) => self::getStatusDescription($record))
            ->modalContent(fn (Package $record) => view('filament.pages.components.package-details-modal', [
                'package' => $record->load(['registrations.event', 'user']),
            ]))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->slideOver();
    }

    protected static function getStatusDescription(Package $package): string
    {
        return match ($package->status) {
            'draft' => 'Este pacote está em rascunho. Complete o pagamento para confirmar as inscrições.',
            'pending' => 'Aguardando confirmação do pagamento.',
            'confirmed' => 'Pagamento confirmado! Suas inscrições estão ativas.',
            'cancelled' => 'Este pacote foi cancelado.',
            default => '',
        };
    }
}
