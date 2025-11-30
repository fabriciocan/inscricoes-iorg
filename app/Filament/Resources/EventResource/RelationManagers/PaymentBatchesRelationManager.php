<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Exceptions\BatchOverlapException;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class PaymentBatchesRelationManager extends RelationManager
{
    protected static string $relationship = 'paymentBatches';

    protected static ?string $title = 'Lotes de Pagamento';

    protected static ?string $modelLabel = 'Lote';

    protected static ?string $pluralModelLabel = 'Lotes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('price')
                    ->label('Preço')
                    ->required()
                    ->numeric()
                    ->prefix('R$')
                    ->minValue(0)
                    ->step(0.01),
                
                Forms\Components\DatePicker::make('start_date')
                    ->label('Data de Início')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y'),

                Forms\Components\DatePicker::make('end_date')
                    ->label('Data de Fim')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->after('start_date'),
            ])
            ->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('price')
            ->columns([
                Tables\Columns\TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Data de Início')
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Data de Fim')
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->getStateUsing(function ($record) {
                        return $record->isActive();
                    }),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Status')
                    ->placeholder('Todos')
                    ->trueLabel('Apenas ativos')
                    ->falseLabel('Apenas inativos')
                    ->queries(
                        true: fn (Builder $query) => $query->where('start_date', '<=', now())
                            ->where('end_date', '>=', now()),
                        false: fn (Builder $query) => $query->where(function ($q) {
                            $q->where('end_date', '<', now())
                                ->orWhere('start_date', '>', now());
                        }),
                    ),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Validate date overlap before creating
                        $this->validateDateOverlap($data);
                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateFormDataUsing(function (array $data, $record): array {
                        // Validate date overlap before updating (excluding current record)
                        $this->validateDateOverlap($data, $record->id);
                        return $data;
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('start_date', 'desc');
    }

    protected function validateDateOverlap(array $data, ?int $excludeId = null): void
    {
        $eventId = $this->getOwnerRecord()->id;
        
        // Validate that end_date is after start_date
        if (isset($data['start_date']) && isset($data['end_date'])) {
            if ($data['end_date'] <= $data['start_date']) {
                throw ValidationException::withMessages([
                    'end_date' => 'A data de fim deve ser posterior à data de início.',
                ]);
            }
        }
        
        $overlapping = \App\Models\PaymentBatch::where('event_id', $eventId)
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->where(function ($query) use ($data) {
                $query->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                    ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']])
                    ->orWhere(function ($q) use ($data) {
                        $q->where('start_date', '<=', $data['start_date'])
                            ->where('end_date', '>=', $data['end_date']);
                    });
            })
            ->exists();

        if ($overlapping) {
            throw ValidationException::withMessages([
                'start_date' => 'As datas deste lote se sobrepõem a outro lote existente.',
                'end_date' => 'As datas deste lote se sobrepõem a outro lote existente.',
            ]);
        }
    }
}
