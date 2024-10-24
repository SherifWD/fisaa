<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                Forms\Components\FileUpload::make('front_side_image')
                    ->image()
                    ->directory('documents/front')
                    ->required(),
                Forms\Components\FileUpload::make('back_side_image')
                    ->image()
                    ->directory('documents/back')
                    ->required(),
                Forms\Components\FileUpload::make('left_side_image')
                    ->image()
                    ->directory('documents/left')
                    ->required(),
                Forms\Components\FileUpload::make('right_side_image')
                    ->image()
                    ->directory('documents/right')
                    ->required(),
                Forms\Components\TextInput::make('plate_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('license_image')
                    ->image()
                    ->directory('documents/license')
                    ->required(),
                Forms\Components\TextInput::make('car_type')
                    ->required(),
                Forms\Components\Toggle::make('is_verified')
                    ->label('Is Verified')
                    ->default(false),
                Forms\Components\TextInput::make('verification_status')
                    ->required(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                self::downloadableImageColumn('front_side_image', 'Front Side'),
                self::downloadableImageColumn('back_side_image', 'Back Side'),
                self::downloadableImageColumn('left_side_image', 'Left Side'),
                self::downloadableImageColumn('right_side_image', 'Right Side'),
                TextColumn::make('plate_number')
                    ->searchable(),
                self::downloadableImageColumn('license_image', 'License'),
                TextColumn::make('car_type'),
                Tables\Columns\SelectColumn::make('is_verified')
                    ->label('Is Verified')
                    ->options([
                        true => 'True',
                        false => 'False',
                    ])
                    ->sortable()
                    ->inline(),
                TextColumn::make('verification_status'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    /**
     * Helper method to create a downloadable image column.
     */
    private static function downloadableImageColumn(string $column, string $label): ImageColumn
    {
        return ImageColumn::make($column)
            ->label($label)
            ->url(fn($record) => asset($record->$column))
            ->openUrlInNewTab()
            ->extraAttributes([
                'download' => true, // HTML attribute to trigger download on click
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}