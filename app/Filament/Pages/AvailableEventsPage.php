<?php

namespace App\Filament\Pages;

use App\Models\Event;
use App\Services\EventService;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class AvailableEventsPage extends Page
{
    protected string $view = 'filament.pages.available-events-page';

    protected static ?string $navigationLabel = 'Eventos Disponíveis';

    protected static ?string $title = 'Eventos Disponíveis';

    protected static ?int $navigationSort = 10;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static UnitEnum|string|null $navigationGroup = 'Inscrições';

    public function getTitle(): string | Htmlable
    {
        return 'Eventos Disponíveis';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public static function canAccess(): bool
    {
        return auth()->check() && !auth()->user()->isHotel();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && !auth()->user()->isAdmin() && !auth()->user()->isHotel();
    }

    public function getEvents()
    {
        $eventService = app(EventService::class);
        $events = $eventService->getActiveEvents();

        return $events->map(function ($event) use ($eventService) {
            $prices = $eventService->getCurrentPrices($event);

            return [
                'id' => $event->id,
                'name' => $event->name,
                'description' => $event->description,
                'logo' => $event->logo,
                'event_date' => $event->event_date,
                'current_price' => $prices ? $prices['pix'] : null, // Keep for backward compatibility
                'price_card' => $prices ? $prices['card'] : null,
                'price_pix' => $prices ? $prices['pix'] : null,
            ];
        });
    }
}
