<?php

namespace App\Filament\Resources\ConsignorPayoutResource\Pages;

use App\Filament\Resources\ConsignorPayoutResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConsignorPayouts extends ListRecords
{
    protected static string $resource = ConsignorPayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
