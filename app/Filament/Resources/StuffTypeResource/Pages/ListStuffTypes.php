<?php

namespace App\Filament\Resources\StuffTypeResource\Pages;

use App\Filament\Resources\StuffTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStuffTypes extends ListRecords
{
    protected static string $resource = StuffTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
