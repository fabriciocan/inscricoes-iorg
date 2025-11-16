<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Package extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'package_number',
        'user_id',
        'status',
        'total_amount',
        'payment_method',
        'payment_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
        ];
    }

    /**
     * Get the user that owns the package.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the registrations for the package.
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Generate a unique package number.
     */
    public static function generatePackageNumber(): string
    {
        do {
            $date = now()->format('Ymd');
            $random = strtoupper(Str::random(6));
            $packageNumber = "PKG-{$date}-{$random}";
        } while (self::where('package_number', $packageNumber)->exists());

        return $packageNumber;
    }

    /**
     * Calculate the total amount for the package.
     */
    public function calculateTotal(): float
    {
        return $this->registrations()->sum('price_paid');
    }
}
