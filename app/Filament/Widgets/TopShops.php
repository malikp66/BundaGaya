<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopShops extends BaseWidget
{
    protected static ?string $heading = 'Top Consignors';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::where('role', 'consignor')
                    ->withCount('consignedProducts')
                    ->withCount(['consignedProducts as total_rentals' => fn ($q) => $q->whereHas('orderItems', fn ($q) => $q->whereIn('status', ['completed', 'returned']))])
                    ->orderByDesc('total_rentals')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email'),
                Tables\Columns\TextColumn::make('consigned_products_count')
                    ->label('Products'),
                Tables\Columns\TextColumn::make('total_rentals')
                    ->label('Rentals'),
            ]);
    }
}
