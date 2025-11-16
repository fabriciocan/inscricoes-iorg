<?php

namespace App\Services;

use App\Exceptions\BatchOverlapException;
use App\Models\Event;
use App\Models\PaymentBatch;
use Carbon\Carbon;

class PaymentBatchService
{
    /**
     * Create a new payment batch for an event.
     *
     * @param Event $event
     * @param array $data
     * @return PaymentBatch
     * @throws BatchOverlapException
     */
    public function createBatch(Event $event, array $data): PaymentBatch
    {
        // Validate that end_date is after start_date
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        
        if ($endDate->lte($startDate)) {
            throw BatchOverlapException::invalidDateRange('A data de fim deve ser posterior à data de início.');
        }

        // Validate batch dates don't overlap
        if (!$this->validateBatchDates($event, $data)) {
            throw BatchOverlapException::datesOverlap(
                $startDate->format('d/m/Y'),
                $endDate->format('d/m/Y')
            );
        }

        $data['event_id'] = $event->id;
        
        return PaymentBatch::create($data);
    }

    /**
     * Update an existing payment batch.
     *
     * @param PaymentBatch $batch
     * @param array $data
     * @return PaymentBatch
     * @throws BatchOverlapException
     */
    public function updateBatch(PaymentBatch $batch, array $data): PaymentBatch
    {
        // Validate that end_date is after start_date
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        
        if ($endDate->lte($startDate)) {
            throw BatchOverlapException::invalidDateRange('A data de fim deve ser posterior à data de início.');
        }

        // Validate batch dates don't overlap (excluding current batch)
        if (!$this->validateBatchDates($batch->event, $data, $batch->id)) {
            throw BatchOverlapException::datesOverlap(
                $startDate->format('d/m/Y'),
                $endDate->format('d/m/Y')
            );
        }

        $batch->update($data);
        return $batch->fresh();
    }

    /**
     * Get the currently active payment batch for an event.
     *
     * @param Event $event
     * @return PaymentBatch|null
     */
    public function getActiveBatch(Event $event): ?PaymentBatch
    {
        return $event->paymentBatches()
            ->active()
            ->first();
    }

    /**
     * Validate that batch dates don't overlap with existing batches.
     *
     * @param Event $event
     * @param array $data
     * @param int|null $excludeBatchId
     * @return bool
     */
    public function validateBatchDates(Event $event, array $data, ?int $excludeBatchId = null): bool
    {
        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate = Carbon::parse($data['end_date'])->startOfDay();

        $query = $event->paymentBatches()
            ->where(function ($q) use ($startDate, $endDate) {
                // Check if new batch overlaps with existing batches
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      // Check if existing batch encompasses new batch
                      $q2->where('start_date', '<=', $startDate)
                         ->where('end_date', '>=', $endDate);
                  });
            });

        // Exclude current batch when updating
        if ($excludeBatchId) {
            $query->where('id', '!=', $excludeBatchId);
        }

        return $query->count() === 0;
    }
}
