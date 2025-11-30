<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ActiveEventsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Event::query()
                    ->where('is_active', true)
                    ->where('event_date', '>=', now())
                    ->orderBy('event_date', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Evento')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('event_date')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('location')
                    ->label('Local')
                    ->limit(30),

                Tables\Columns\TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('registrations_count')
                    ->label('Inscrições')
                    ->counts('registrations')
                    ->sortable()
                    ->badge()
                    ->color('success'),
            ])
            ->heading('Eventos Ativos');
    }

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
