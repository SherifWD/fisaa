<?php

namespace App\Filament\Resources\ObjectWeightResource\Pages;

use App\Filament\Resources\ObjectWeightResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListObjectWeights extends ListRecords
{
    protected static string $resource = ObjectWeightResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
