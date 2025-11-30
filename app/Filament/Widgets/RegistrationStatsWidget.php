<?php

namespace App\Filament\Widgets;

use App\Models\Package;
use App\Models\Registration;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RegistrationStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $totalRegistrations = Registration::count();
        $confirmedPackages = Package::where('status', 'confirmed')->count();
        $pendingPackages = Package::where('status', 'pending')->count();
        $totalRevenue = Package::where('status', 'confirmed')->sum('total_amount');

        return [
            Stat::make('Total de Inscrições', $totalRegistrations)
                ->description('Todas as inscrições registradas')
                ->icon('heroicon-o-user-group')
                ->color('success'),

            Stat::make('Pacotes Confirmados', $confirmedPackages)
                ->description('Pagamentos confirmados')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Pacotes Pendentes', $pendingPackages)
                ->description('Aguardando pagamento')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Receita Total', 'R$ ' . number_format($totalRevenue, 2, ',', '.'))
                ->description('De pacotes confirmados')
                ->icon('heroicon-o-banknotes')
                ->color('primary'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
