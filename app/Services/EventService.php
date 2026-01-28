<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Database\Eloquent\Collection;

class EventService
{
    /**
     * Create a new event.
     *
     * @param array $data
     * @return Event
     */
    public function createEvent(array $data): Event
    {
        return Event::create($data);
    }

    /**
     * Update an existing event.
     *
     * @param Event $event
     * @param array $data
     * @return Event
     */
    public function updateEvent(Event $event, array $data): Event
    {
        $event->update($data);
        return $event->fresh();
    }

    /**
     * Get all active events.
     *
     * @return Collection
     */
    public function getActiveEvents(): Collection
    {
        return Event::active()
            ->orderBy('event_date', 'asc')
            ->get();
    }

    /**
     * Get the current price for an event based on the active payment batch.
     *
     * @param Event $event
     * @return float|null
     * @deprecated Use getCurrentPrices() instead to get both card and PIX prices
     */
    public function getCurrentPrice(Event $event): ?float
    {
        $currentBatch = $event->getCurrentBatch();

        return $currentBatch ? (float) $currentBatch->price_pix : null;
    }

    /**
     * Get the current prices (card and PIX) for an event based on the active payment batch.
     *
     * @param Event $event
     * @return array|null Array with 'card' and 'pix' keys, or null if no active batch
     */
    public function getCurrentPrices(Event $event): ?array
    {
        $currentBatch = $event->getCurrentBatch();

        if (!$currentBatch) {
            return null;
        }

        return [
            'card' => (float) $currentBatch->price_card,
            'pix' => (float) $currentBatch->price_pix,
        ];
    }
}
