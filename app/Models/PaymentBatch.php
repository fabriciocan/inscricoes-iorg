<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentBatch extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'event_id',
        'price',
        'start_date',
        'end_date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Get the event that owns the payment batch.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Check if the payment batch is currently active.
     */
    public function isActive(): bool
    {
        $now = now()->startOfDay();
        return $this->start_date <= $now && $this->end_date >= $now;
    }

    /**
     * Scope a query to only include active payment batches.
     */
    public function scopeActive($query)
    {
        $now = now()->startOfDay();
        return $query->where('start_date', '<=', $now)
                     ->where('end_date', '>=', $now);
    }
}
