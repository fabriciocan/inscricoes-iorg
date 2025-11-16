<?php

namespace App\Filament\Pages;

use App\Models\Package;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MyRegistrationsPage extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected string $view = 'filament.pages.my-registrations-page';

    protected static ?string $navigationLabel = 'Minhas Inscrições';

    protected static ?string $title = 'Minhas Inscrições';

    protected static ?int $navigationSort = 20;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static UnitEnum|string|null $navigationGroup = 'Inscrições';

    public function getTitle(): string | Htmlable
    {
        return 'Minhas Inscrições';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && !auth()->user()->isAdmin();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Package::query()
                    ->where('user_id', auth()->id())
                    ->with(['registrations.event'])
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                TextColumn::make('package_number')
                    ->label('Número do Pacote')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('registrations_count')
                    ->label('Inscrições')
                    ->counts('registrations')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('event_name')
                    ->label('Evento')
                    ->limit(30)
                    ->getStateUsing(function (Package $record) {
                        // Pega apenas o evento da primeira inscrição
                        return $record->registrations->first()?->event?->name ?? '-';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('registrations.event', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('total_amount')
                    ->label('Valor Total')
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Rascunho',
                        'pending' => 'Pendente',
                        'confirmed' => 'Confirmado',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Data de Criação')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Rascunho',
                        'pending' => 'Pendente',
                        'confirmed' => 'Confirmado',
                        'cancelled' => 'Cancelado',
                    ]),

                SelectFilter::make('event')
                    ->label('Evento')
                    ->relationship('registrations.event', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('view_details')
                    ->label('Ver Detalhes')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (Package $record) => "Detalhes do Pacote {$record->package_number}")
                    ->modalContent(fn (Package $record) => view('filament.pages.components.package-details-modal', [
                        'package' => $record->load(['registrations.event', 'user']),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->slideOver(),

                Action::make('continue_payment')
                    ->label('Continuar Pagamento')
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->visible(fn (Package $record) => $record->status === 'draft' && $record->registrations->count() > 0)
                    ->url(fn (Package $record) => route('filament.admin.pages.payment-page', ['package' => $record->id])),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Nenhuma inscrição encontrada')
            ->emptyStateDescription('Você ainda não possui inscrições. Visite a página de eventos disponíveis para começar.')
            ->emptyStateIcon('heroicon-o-ticket')
            ->emptyStateActions([
                Action::make('view_events')
                    ->label('Ver Eventos Disponíveis')
                    ->url(route('filament.admin.pages.available-events-page'))
                    ->icon('heroicon-o-calendar'),
            ]);
    }
}
