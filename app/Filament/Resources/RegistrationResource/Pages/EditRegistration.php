<?php

namespace App\Filament\Resources\RegistrationResource\Pages;

use App\Filament\Resources\RegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRegistration extends EditRecord
{
    protected static string $resource = RegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Decode participant_data JSON if it's a string
        if (isset($data['participant_data'])) {
            $participantData = is_string($data['participant_data'])
                ? json_decode($data['participant_data'], true)
                : $data['participant_data'];

            // Merge participant_data fields into the main data array
            if (is_array($participantData)) {
                $data['participant_data'] = $participantData;
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Encode participant_data back to JSON string if it's an array
        if (isset($data['participant_data']) && is_array($data['participant_data'])) {
            $data['participant_data'] = json_encode($data['participant_data']);
        }

        return $data;
    }
}
