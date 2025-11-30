<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PackageResource\Pages;
use App\Models\Package;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Pacotes';

    protected static ?string $modelLabel = 'Pacote';

    protected static ?string $pluralModelLabel = 'Pacotes';

    protected static ?int $navigationSort = 30;

    protected static UnitEnum|string|null $navigationGroup = 'Gerenciamento';

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return false; // Packages are created through the registration flow
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('package_number')
                    ->label('Número do Pacote')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),

                Forms\Components\Select::make('user_id')
                    ->label('Usuário')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabled()
                    ->columnSpanFull(),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Rascunho',
                        'pending' => 'Pendente',
                        'confirmed' => 'Confirmado',
                        'cancelled' => 'Cancelado',
                    ])
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('total_amount')
                    ->label('Valor Total')
                    ->numeric()
                    ->prefix('R$')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('payment_method')
                    ->label('Método de Pagamento')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('payment_id')
                    ->label('ID do Pagamento')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('package_number')
                    ->label('Número do Pacote')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuário')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('registrations_count')
                    ->label('Inscrições')
                    ->counts('registrations')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Valor Total')
                    ->money('BRL')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
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
                
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Método de Pagamento')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pix' => 'PIX',
                        'credit_card' => 'Cartão de Crédito',
                        null => '-',
                        default => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Rascunho',
                        'pending' => 'Pendente',
                        'confirmed' => 'Confirmado',
                        'cancelled' => 'Cancelado',
                    ]),
                
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Usuário')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Criado de')
                            ->native(false),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Criado até')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informações do Pacote')
                    ->schema([
                        Infolists\Components\TextEntry::make('package_number')
                            ->label('Número do Pacote')
                            ->copyable()
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Usuário')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('status')
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
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('total_amount')
                            ->label('Valor Total')
                            ->money('BRL')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('payment_method')
                            ->label('Método de Pagamento')
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'pix' => 'PIX',
                                'credit_card' => 'Cartão de Crédito',
                                null => '-',
                                default => $state,
                            })
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('payment_id')
                            ->label('ID do Pagamento')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Criado em')
                            ->dateTime('d/m/Y H:i')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Inscrições')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('registrations')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('event.name')
                                    ->label('Evento'),

                                Infolists\Components\TextEntry::make('participant_name')
                                    ->label('Participante'),

                                Infolists\Components\TextEntry::make('participant_email')
                                    ->label('Email')
                                    ->copyable(),

                                Infolists\Components\TextEntry::make('participant_phone')
                                    ->label('Telefone'),

                                Infolists\Components\TextEntry::make('price_paid')
                                    ->label('Valor Pago')
                                    ->money('BRL'),
                            ])
                            ->columns(1),
                    ]),
            ])
            ->columns(1);
    }

    public static function getRelations(): array
    {
        return [
            PackageResource\RelationManagers\RegistrationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPackages::route('/'),
            'view' => Pages\ViewPackage::route('/{record}'),
            'edit' => Pages\EditPackage::route('/{record}/edit'),
        ];
    }
}
