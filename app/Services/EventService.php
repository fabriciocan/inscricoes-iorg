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
     */
    public function getCurrentPrice(Event $event): ?float
    {
        $currentBatch = $event->getCurrentBatch();
        
        return $currentBatch ? (float) $currentBatch->price : null;
    }
}
