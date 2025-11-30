<?php

namespace App\Services;

use App\Models\Package;
use App\Mail\ConfirmationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send confirmation email for a package.
     *
     * @param Package $package
     * @return void
     */
    public function sendConfirmationEmail(Package $package): void
    {
        try {
            Mail::to($package->user->email)
                ->send(new ConfirmationMail($package));
        } catch (\Exception $e) {
            Log::error('Failed to send confirmation email: ' . $e->getMessage());
            throw $e;
        }
    }
}
