<?php

namespace App\Services;

use App\Exceptions\InvalidPackageStateException;
use App\Models\Event;
use App\Models\Package;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RegistrationService
{
    /**
     * Create a new package for a user.
     *
     * @param User $user
     * @return Package
     */
    public function createPackage(User $user): Package
    {
        return Package::create([
            'package_number' => Package::generatePackageNumber(),
            'user_id' => $user->id,
            'status' => 'draft',
            'total_amount' => 0,
        ]);
    }

    /**
     * Add a registration to a package.
     *
     * @param Package $package
     * @param Event $event
     * @param array $data
     * @return Registration
     * @throws InvalidPackageStateException
     * @throws ValidationException
     */
    public function addRegistrationToPackage(Package $package, Event $event, array $data): Registration
    {
        // Validate package state
        if ($package->status === 'confirmed') {
            throw InvalidPackageStateException::cannotModifyConfirmedPackage();
        }

        if ($package->status === 'cancelled') {
            throw new \Exception('Não é possível adicionar inscrições a um pacote cancelado.');
        }

        // Validate event is active
        if (!$event->is_active) {
            throw ValidationException::withMessages([
                'event' => 'Este evento não está mais ativo para inscrições.',
            ]);
        }

        // Get the current price for the event
        $currentBatch = $event->getCurrentBatch();
        
        if (!$currentBatch) {
            throw ValidationException::withMessages([
                'event' => 'Não há lote de pagamento ativo para este evento no momento.',
            ]);
        }

        // Validate required fields
        $this->validateRegistrationData($data);

        $data['package_id'] = $package->id;
        $data['event_id'] = $event->id;
        $data['price_paid'] = $currentBatch->price;

        $registration = Registration::create($data);

        // Update package total
        $this->calculatePackageTotal($package);

        return $registration;
    }

    /**
     * Validate registration data.
     *
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    protected function validateRegistrationData(array $data): void
    {
        $errors = [];

        if (empty($data['participant_name']) || strlen($data['participant_name']) < 3) {
            $errors['participant_name'] = 'O nome do participante deve ter pelo menos 3 caracteres.';
        }

        if (empty($data['participant_email']) || !filter_var($data['participant_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['participant_email'] = 'Email do participante inválido.';
        }

        if (empty($data['participant_phone']) || strlen($data['participant_phone']) < 10) {
            $errors['participant_phone'] = 'Telefone do participante inválido.';
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Calculate and update the total amount for a package.
     *
     * @param Package $package
     * @return float
     */
    public function calculatePackageTotal(Package $package): float
    {
        $total = $package->calculateTotal();
        
        $package->update([
            'total_amount' => $total,
        ]);

        return $total;
    }

    /**
     * Update the status of a package.
     *
     * @param Package $package
     * @param string $status
     * @return void
     * @throws InvalidPackageStateException
     */
    public function updatePackageStatus(Package $package, string $status): void
    {
        $validStatuses = ['draft', 'pending', 'confirmed', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException("Status inválido: {$status}");
        }

        // Validate status transitions
        $currentStatus = $package->status;
        
        // Cannot change status of confirmed packages
        if ($currentStatus === 'confirmed' && $status !== 'confirmed') {
            throw InvalidPackageStateException::cannotModifyConfirmedPackage();
        }

        // Cannot change status of cancelled packages (except to cancelled)
        if ($currentStatus === 'cancelled' && $status !== 'cancelled') {
            throw InvalidPackageStateException::invalidStatusTransition($currentStatus, $status);
        }

        $package->update([
            'status' => $status,
        ]);
    }
}
