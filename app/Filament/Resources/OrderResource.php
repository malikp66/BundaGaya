<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Services\ConsignorService;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\App;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    public static function getNavigationGroup(): string
    {
        return 'Order Management';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-shopping-cart';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Order Information')
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(),
                        Forms\Components\TextInput::make('customer_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('instagram')
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending_payment' => 'Pending Payment',
                                'paid' => 'Paid',
                                'processing' => 'Processing',
                                'shipped' => 'Shipped',
                                'in_use' => 'In Use',
                                'returned' => 'Returned',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),
                        Forms\Components\Select::make('payment_status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                                'refunded' => 'Refunded',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Financial')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                        Forms\Components\TextInput::make('commission_fee')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                        Forms\Components\TextInput::make('admin_fee')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                        Forms\Components\TextInput::make('total')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                        Forms\Components\TextInput::make('dp_total')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                        Forms\Components\TextInput::make('shipping_cost')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                        Forms\Components\TextInput::make('grand_total')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                    ])->columns(3),

                Forms\Components\Section::make('Shipping Details')
                    ->schema([
                        Forms\Components\TextInput::make('shipping_courier')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('shipping_service')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('tracking_number')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('return_date')
                            ->required(),
                        Forms\Components\Textarea::make('shipping_address')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('city')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('province')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('postal_code')
                            ->maxLength(10),
                        Forms\Components\TextInput::make('district')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('suburb')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('DP (Security Deposit)')
                    ->schema([
                        Forms\Components\TextInput::make('dp_total')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->label('DP Total'),
                        Forms\Components\TextInput::make('dp_refunded')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->label('DP Refunded'),
                        Forms\Components\TextInput::make('dp_deducted')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->label('DP Deducted'),
                        Forms\Components\Select::make('dp_status')
                            ->options([
                                'pending' => 'Pending',
                                'transferred' => 'Transferred',
                                'completed' => 'Completed',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Refund Bank Account')
                    ->schema([
                        Forms\Components\TextInput::make('refund_bank_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('refund_bank_account')
                            ->required()
                            ->maxLength(50),
                        Forms\Components\TextInput::make('refund_bank_holder')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Customer Details')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('phone')
                            ->tel(),
                        Forms\Components\Textarea::make('notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Timestamps')
                    ->schema([
                        Forms\Components\DateTimePicker::make('paid_at')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('processed_at')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('shipped_at')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('returned_at')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('completed_at')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('cancelled_at')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Name (KTP)')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending_payment' => 'warning',
                        'paid' => 'info',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'in_use' => 'info',
                        'returned' => 'gray',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('grand_total')
                    ->money('IDR')
                    ->sortable()
                    ->label('Grand Total'),
                Tables\Columns\TextColumn::make('total')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('dp_total')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('DP'),
                Tables\Columns\TextColumn::make('shipping_cost')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Shipping'),
                Tables\Columns\TextColumn::make('return_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Return Date'),
                Tables\Columns\TextColumn::make('dp_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'transferred' => 'info',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->label('DP Status')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('commission_fee')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('admin_fee')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tracking_number')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),
                Tables\Columns\TextColumn::make('shipping_courier')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending_payment' => 'Pending Payment',
                        'paid' => 'Paid',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'in_use' => 'In Use',
                        'returned' => 'Returned',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
                Tables\Filters\SelectFilter::make('dp_status')
                    ->label('DP Status')
                    ->options([
                        'pending' => 'Pending',
                        'transferred' => 'Transferred',
                        'completed' => 'Completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('mark_paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->isPending())
                    ->action(fn (Order $record) => $record->update([
                        'status' => 'paid',
                        'payment_status' => 'paid',
                        'paid_at' => now(),
                    ])),
                Tables\Actions\Action::make('mark_processing')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->status === 'paid')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'processing', 'processed_at' => now()]);
                        $record->items()->update(['status' => 'processing']);
                    }),
                Tables\Actions\Action::make('mark_shipped')
                    ->icon('heroicon-o-truck')
                    ->color('primary')
                    ->form([
                        Forms\Components\TextInput::make('tracking_number')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('shipping_courier')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('shipping_service')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->visible(fn (Order $record) => $record->status === 'processing')
                    ->action(function (Order $record, array $data) {
                        $record->update([
                            'status' => 'shipped',
                            'tracking_number' => $data['tracking_number'],
                            'shipping_courier' => $data['shipping_courier'],
                            'shipping_service' => $data['shipping_service'],
                            'shipped_at' => now(),
                        ]);
                        $record->items()->update(['status' => 'shipped']);
                    }),
                Tables\Actions\Action::make('mark_returned')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->status === 'in_use')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'returned', 'returned_at' => now()]);
                        $record->load('items.product');
                        foreach ($record->items as $item) {
                            $item->update(['status' => 'returned']);
                            $item->product->increment('stock', $item->quantity);
                            $item->product->increment('rental_count', $item->quantity);
                        }
                    }),
                Tables\Actions\Action::make('complete_order')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->status === 'returned')
                    ->action(function (Order $record) {
                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                            'dp_refunded' => $record->dp_total,
                        ]);
                        $record->items()->update(['status' => 'completed']);
                        App::make(ConsignorService::class)->creditEarnings($record);
                    }),
                Tables\Actions\Action::make('process_damage')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\TextInput::make('deducted_amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0)
                            ->default(fn (Order $record) => $record->dp_total),
                    ])
                    ->visible(fn (Order $record) => $record->status === 'returned')
                    ->action(function (Order $record, array $data) {
                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                            'dp_deducted' => $data['deducted_amount'],
                            'dp_refunded' => $record->dp_total - $data['deducted_amount'],
                        ]);
                        $record->items()->update(['status' => 'completed']);
                        App::make(ConsignorService::class)->creditEarnings($record);
                    }),
                Tables\Actions\Action::make('mark_dp_transferred')
                    ->icon('heroicon-o-banknotes')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->status === 'completed' && $record->isDpPending())
                    ->action(fn (Order $record) => $record->update(['dp_status' => 'transferred'])),
                Tables\Actions\Action::make('mark_dp_completed')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->status === 'completed' && $record->isDpTransferred())
                    ->action(fn (Order $record) => $record->update(['dp_status' => 'completed'])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
