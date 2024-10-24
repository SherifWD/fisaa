<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TripResource\Pages;
use App\Models\Trip;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class TripResource extends Resource
{
    protected static ?string $model = Trip::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('type_id')
                    ->relationship('type', 'name')
                    ->required()->disabled(),
                Forms\Components\Select::make('weight_id')
                    ->relationship('weight', 'weight')
                    ->required(),
                Forms\Components\Select::make('worker_id')
                    ->relationship('worker', 'count')
                    ->required(),
                // Forms\Components\Select::make('object_type')
                //     ->options([
                //         'Building materials' => 'Building materials',
                //         'Furniture' => 'Furniture',
                //         'Food' => 'Food',
                //     ])
                //     ->required(),
                Forms\Components\TextInput::make('sender_name')
                    ->maxLength(255)
                    ->required(),
                Forms\Components\TextInput::make('sender_phone')
                    ->tel()
                    ->maxLength(255)
                    ->required(),
                Forms\Components\TextInput::make('receiver_name')
                    ->maxLength(255)
                    ->required(),
                Forms\Components\TextInput::make('receiver_phone')
                    ->tel()
                    ->maxLength(255)
                    ->required(),
                // Forms\Components\Select::make('workers_needed')
                //     ->options([
                //         '0' => '0',
                //         '1' => '1',
                //         '2' => '2',
                //         '3+' => '3+',
                //     ]),
                Forms\Components\TextInput::make('from')
                    ->maxLength(255)
                    ->required(),
                Forms\Components\TextInput::make('from_lat')
                    ->maxLength(255),
                Forms\Components\TextInput::make('from_lng')
                    ->maxLength(255),
                Forms\Components\TextInput::make('to')
                    ->maxLength(255)
                    ->required(),
                Forms\Components\TextInput::make('to_lat')
                    ->maxLength(255),
                Forms\Components\TextInput::make('to_lng')
                    ->maxLength(255),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                Forms\Components\Toggle::make('is_cash')
                    ->label('Is Cash Payment')
                    ->default(true),
                Forms\Components\Select::make('payment_by')
                    ->options([
                        'sender' => 'Sender',
                        'receiver' => 'Receiver',
                    ])
                    ->required(),
                // Forms\Components\TextInput::make('estimated_distance')
                //     ->numeric(),
                Forms\Components\Select::make('status')
                    ->options([
                        'searching' => 'Searching',
                        'way' => 'Way',
                        'arrived' => 'Arrived',
                        'completed' => 'Completed',
                        'cancel' => 'Cancelled',
                    ])
                    ->required(),
                // Forms\Components\Select::make('stuff_type_id')
                //     ->relationship('stuffType', 'name')
                //     ->required(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('driver.name')
                    ->label('Driver')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('passenger.name')
                    ->label('Passenger')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type.name')
                    ->label('Type')
                    ->sortable(),
                Tables\Columns\TextColumn::make('weight.weight')
                    ->label('Weight')
                    ->sortable(),
                Tables\Columns\TextColumn::make('worker.count')
                    ->label('Worker')
                    ->sortable(),
                // Tables\Columns\TextColumn::make('object_type'),
                Tables\Columns\TextColumn::make('sender_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sender_phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('receiver_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('receiver_phone')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('workers_needed'),
                Tables\Columns\TextColumn::make('from')
                    ->searchable(),
                Tables\Columns\TextColumn::make('to')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('usd', true)
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_cash')
                    ->boolean(),
                Tables\Columns\TextColumn::make('payment_by'),
                // Tables\Columns\TextColumn::make('estimated_distance')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\SelectColumn::make('status')
                    ->options([
                        'searching' => 'Searching',
                        'way' => 'Way',
                        'arrived' => 'Arrived',
                        'completed' => 'Completed',
                        'cancel' => 'Cancelled',
                    ])
                    ->sortable()
                    ->searchable()
                    ->inline() // Allows inline editing directly from the table
                    ->default('searching'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // Add any filters if needed
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Define any relations if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrips::route('/'),
            'create' => Pages\CreateTrip::route('/create'),
            'edit' => Pages\EditTrip::route('/{record}/edit'),
        ];
    }
}