<?php

namespace App\Filament\Resources\PackageResource\RelationManagers;

use App\Services\RegistrationService;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RegistrationsRelationManager extends RelationManager
{
    protected static string $relationship = 'registrations';

    protected static ?string $title = 'Inscrições do Pacote';

    protected static ?string $modelLabel = 'Inscrição';

    protected static ?string $pluralModelLabel = 'Inscrições';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Form is disabled - registrations can only be deleted, not edited
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('participant_name')
            ->columns([
                TextColumn::make('participant_name')
                    ->label('Nome do Participante')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('participant_email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('participant_phone')
                    ->label('Telefone')
                    ->searchable(),

                TextColumn::make('event.name')
                    ->label('Evento')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price_paid')
                    ->label('Valor Pago')
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // No create or associate actions - registrations are created through the registration flow
            ])
            ->recordActions([
                DeleteAction::make()
                    ->label('Remover')
                    ->modalHeading('Remover Inscrição do Pacote')
                    ->modalDescription(fn ($record) => "Tem certeza que deseja remover a inscrição de {$record->participant_name}? O valor do pacote não será alterado.")
                    ->successNotificationTitle('Inscrição removida com sucesso')
                    ->after(function ($record) {
                        $package = $this->getOwnerRecord();

                        // If package is now empty, delete it
                        if ($package->registrations()->count() === 0) {
                            $packageNumber = $package->package_number;
                            $package->delete();

                            Notification::make()
                                ->title('Pacote removido')
                                ->body("O pacote {$packageNumber} foi removido pois não possui mais inscrições.")
                                ->warning()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()
                    ->label('Remover selecionados')
                    ->modalHeading('Remover Inscrições do Pacote')
                    ->modalDescription('Tem certeza que deseja remover as inscrições selecionadas? O valor do pacote não será alterado.')
                    ->successNotificationTitle('Inscrições removidas com sucesso')
                    ->after(function () {
                        $package = $this->getOwnerRecord();

                        // If package is now empty, delete it
                        if ($package->registrations()->count() === 0) {
                            $packageNumber = $package->package_number;
                            $package->delete();

                            Notification::make()
                                ->title('Pacote removido')
                                ->body("O pacote {$packageNumber} foi removido pois não possui mais inscrições.")
                                ->warning()
                                ->send();
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
