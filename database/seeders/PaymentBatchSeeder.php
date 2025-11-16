<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\PaymentBatch;
use Illuminate\Database\Seeder;

class PaymentBatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all active events
        $events = Event::where('is_active', true)->get();

        foreach ($events as $event) {
            // Create multiple payment batches for each event
            
            // First batch (early bird) - already passed
            PaymentBatch::create([
                'event_id' => $event->id,
                'price' => 100.00,
                'start_date' => now()->subDays(30),
                'end_date' => now()->subDays(15),
            ]);

            // Second batch (current/active)
            PaymentBatch::create([
                'event_id' => $event->id,
                'price' => 150.00,
                'start_date' => now()->subDays(14),
                'end_date' => now()->addDays(14),
            ]);

            // Third batch (future)
            PaymentBatch::create([
                'event_id' => $event->id,
                'price' => 200.00,
                'start_date' => now()->addDays(15),
                'end_date' => $event->event_date->subDay(),
            ]);
        }

        // Add special pricing for the Hackathon
        $hackathon = Event::where('name', 'Hackathon 2025')->first();
        if ($hackathon) {
            // Remove default batches for hackathon
            $hackathon->paymentBatches()->delete();

            // Create custom batches
            PaymentBatch::create([
                'event_id' => $hackathon->id,
                'price' => 50.00,
                'start_date' => now()->subDays(10),
                'end_date' => now()->addDays(30),
            ]);

            PaymentBatch::create([
                'event_id' => $hackathon->id,
                'price' => 75.00,
                'start_date' => now()->addDays(31),
                'end_date' => $hackathon->event_date->subDay(),
            ]);
        }
    }
}
