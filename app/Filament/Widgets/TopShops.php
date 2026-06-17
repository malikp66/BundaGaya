<?php

namespace App\Filament\Widgets;

use App\Models\Shop;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TopShops extends BaseWidget
{
    protected static ?string $heading = 'Top Performing Shops';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Shop::query()
                    ->withCount('products')
                    ->withCount(['orderItems as total_orders'])
                    ->where('status', 'active')
                    ->orderByDesc('total_orders')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner'),
                Tables\Columns\TextColumn::make('city'),
                Tables\Columns\TextColumn::make('products_count')
                    ->label('Products')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_orders')
                    ->label('Orders')
                    ->sortable(),
                Tables\Columns\TextColumn::make('commission_rate')
                    ->suffix('%'),
                Tables\Columns\IconColumn::make('is_verified')
                    ->boolean()
                    ->label('Verified'),
            ]);
    }
}
