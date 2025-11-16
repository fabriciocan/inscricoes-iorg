<?php

namespace App\Filament\Actions;

use App\Models\Event;
use App\Models\Package;
use App\Services\EventService;
use App\Services\RegistrationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class CreateRegistrationAction
{
    public static function make(?Package $package = null, ?Event $event = null): Action
    {
        return Action::make('add_registration')
            ->label('Adicionar Inscrição')
            ->icon('heroicon-o-plus-circle')
            ->color('success')
            ->modalHeading('Adicionar Nova Inscrição')
            ->modalDescription(function () use ($event) {
                if (!$event) {
                    return null;
                }
                
                $eventService = app(EventService::class);
                $currentPrice = $eventService->getCurrentPrice($event);
                
                return $currentPrice 
                    ? "Valor atual: R$ " . number_format($currentPrice, 2, ',', '.')
                    : null;
            })
            ->form([
                TextInput::make('participant_name')
                    ->label('Nome do Participante')
                    ->required()
                    ->minLength(3)
                    ->maxLength(255)
                    ->placeholder('Digite o nome completo')
                    ->rules(['regex:/^[a-zA-ZÀ-ÿ\s]+$/']),
                
                TextInput::make('participant_email')
                    ->label('Email do Participante')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->placeholder('email@exemplo.com')
                    ->unique('registrations', 'participant_email', ignoreRecord: true, modifyRuleUsing: function ($rule) use ($package) {
                        return $rule->where('package_id', $package?->id);
                    }),
                
                TextInput::make('participant_phone')
                    ->label('Telefone do Participante')
                    ->tel()
                    ->required()
                    ->minLength(14)
                    ->maxLength(15)
                    ->placeholder('(00) 00000-0000')
                    ->mask('(99) 99999-9999')
                    ->rules(['regex:/^\(\d{2}\)\s\d{4,5}-\d{4}$/']),
                
                Textarea::make('participant_data')
                    ->label('Informações Adicionais')
                    ->rows(3)
                    ->maxLength(1000)
                    ->placeholder('Informações extras sobre o participante (opcional)'),
            ])
            ->action(function (array $data) use ($package, $event) {
                if (!$package || !$event) {
                    Notification::make()
                        ->title('Erro')
                        ->body('Pacote ou evento não encontrado.')
                        ->danger()
                        ->send();
                    return;
                }

                try {
                    $registrationService = app(RegistrationService::class);
                    $registrationService->addRegistrationToPackage(
                        $package,
                        $event,
                        $data
                    );

                    Notification::make()
                        ->title('Inscrição adicionada com sucesso!')
                        ->body('A inscrição foi adicionada ao pacote.')
                        ->success()
                        ->send();
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
            ->modalWidth('lg');
    }
}
