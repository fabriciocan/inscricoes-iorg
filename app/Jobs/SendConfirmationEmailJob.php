<?php

namespace App\Jobs;

use App\Models\Package;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendConfirmationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Package $package
    ) {}

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        try {
            $notificationService->sendConfirmationEmail($this->package);
            
            Log::info("Confirmation email sent successfully for package: {$this->package->package_number}");
        } catch (\Exception $e) {
            Log::error("Failed to send confirmation email for package {$this->package->package_number}: {$e->getMessage()}");
            
            // Re-throw to allow retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SendConfirmationEmailJob failed permanently for package {$this->package->package_number}: {$exception->getMessage()}");
    }
}
