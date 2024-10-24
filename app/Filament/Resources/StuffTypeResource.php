<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StuffTypeResource\Pages;
use App\Filament\Resources\StuffTypeResource\RelationManagers;
use App\Models\StuffType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StuffTypeResource extends Resource
{
    protected static ?string $model = StuffType::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->directory('stuff_images') // Save images in public/storage/stuff_types
                    ->required(), // Make it required if needed

                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->prefix('$'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ImageColumn::make('image')
                    ->url(fn($record) => $record->image) // Using accessor
                    ->label('Image')
                    ->circular(), // Optional: Display as a circular image

                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStuffTypes::route('/'),
            'create' => Pages\CreateStuffType::route('/create'),
            'edit' => Pages\EditStuffType::route('/{record}/edit'),
        ];
    }
}