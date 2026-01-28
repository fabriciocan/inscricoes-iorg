<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function mount(): void
    {
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();

        if ($user->isHotel()) {
            $this->redirect(route('filament.admin.pages.hotel-registrations-page'), navigate: true);
            return;
        }

        if (!$user->isAdmin()) {
            $this->redirect(route('filament.admin.pages.available-events-page'), navigate: true);
        }
    }
}
