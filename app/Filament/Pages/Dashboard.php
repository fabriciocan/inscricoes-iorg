<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function mount(): void
    {
        // Redireciona usuários não-admin para eventos disponíveis
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            $this->redirect(route('filament.admin.pages.available-events-page'), navigate: true);
        }
    }
}
