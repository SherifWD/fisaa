<?php

namespace App\Filament\Resources\ObjectWeightResource\Pages;

use App\Filament\Resources\ObjectWeightResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditObjectWeight extends EditRecord
{
    protected static string $resource = ObjectWeightResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
