<?php

namespace App\Console\Commands;

use App\Models\Package;
use Illuminate\Console\Command;

class RevertPendingPackagesToDraft extends Command
{
    protected $signature = 'packages:revert-pending {package_id?}';

    protected $description = 'Revert pending packages back to draft status';

    public function handle()
    {
        $packageId = $this->argument('package_id');

        if ($packageId) {
            $package = Package::find($packageId);

            if (!$package) {
                $this->error("Package with ID {$packageId} not found.");
                return 1;
            }

            if ($package->status === 'pending') {
                $package->update(['status' => 'draft', 'payment_method' => null]);
                $this->info("Package {$package->package_number} reverted to draft status.");
            } else {
                $this->warn("Package {$package->package_number} is not pending (current status: {$package->status}).");
            }
        } else {
            $pendingPackages = Package::where('status', 'pending')->get();

            if ($pendingPackages->isEmpty()) {
                $this->info('No pending packages found.');
                return 0;
            }

            $this->info("Found {$pendingPackages->count()} pending package(s):");

            foreach ($pendingPackages as $package) {
                $this->line("- Package #{$package->package_number} (ID: {$package->id})");
            }

            if ($this->confirm('Do you want to revert all pending packages to draft?', true)) {
                foreach ($pendingPackages as $package) {
                    $package->update(['status' => 'draft', 'payment_method' => null]);
                    $this->info("Reverted package {$package->package_number}");
                }

                $this->info('All pending packages have been reverted to draft.');
            }
        }

        return 0;
    }
}
