<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConsignorPayoutResource\Pages;
use App\Models\ConsignorPayout;
use App\Services\ConsignorService;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\App;

class ConsignorPayoutResource extends Resource
{
    protected static ?string $model = ConsignorPayout::class;

    public static function getNavigationGroup(): string
    {
        return 'Consignment';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-arrow-up-on-square';
    }

    public static function getNavigationLabel(): string
    {
        return 'Payout Requests';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Payout Info')
                    ->schema([
                        Forms\Components\TextInput::make('payout_number')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->disabled()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'completed' => 'Completed',
                            ])
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Bank Details')
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')
                            ->maxLength(255)
                            ->disabled(),
                        Forms\Components\TextInput::make('bank_account_number')
                            ->maxLength(50)
                            ->disabled(),
                        Forms\Components\TextInput::make('bank_account_name')
                            ->maxLength(255)
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Timestamps')
                    ->schema([
                        Forms\Components\DateTimePicker::make('approved_at')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('rejected_at')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('completed_at')
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
                Tables\Columns\TextColumn::make('payout_number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Consignor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Bank')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('bank_account_number')
                    ->label('Account No')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'info',
                        'rejected' => 'danger',
                        'completed' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'completed' => 'Completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ConsignorPayout $record) => $record->isPending())
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Admin notes (optional)')
                            ->rows(2),
                    ])
                    ->action(function (ConsignorPayout $record, array $data) {
                        App::make(ConsignorService::class)->approvePayout($record, $data['notes'] ?? null);
                    }),
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (ConsignorPayout $record) => $record->isPending())
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Rejection reason (required)')
                            ->required()
                            ->rows(2),
                    ])
                    ->action(function (ConsignorPayout $record, array $data) {
                        App::make(ConsignorService::class)->rejectPayout($record, $data['notes'] ?? '');
                    }),
                Tables\Actions\Action::make('complete')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ConsignorPayout $record) => $record->isApproved())
                    ->action(function (ConsignorPayout $record) {
                        App::make(ConsignorService::class)->completePayout($record);
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConsignorPayouts::route('/'),
            'view' => Pages\ViewConsignorPayout::route('/{record}'),
        ];
    }
}
