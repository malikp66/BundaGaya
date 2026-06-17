<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Transaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends BaseWidget
{
    protected function getStats(): array
    {
        $totalRevenue = Transaction::where('status', 'settled')->sum('amount');
        $totalCommission = Transaction::where('status', 'settled')->sum('commission_fee');
        $totalAdminFee = Order::where('status', 'completed')->sum('admin_fee');
        $pendingOrders = Order::where('status', 'pending_payment')->count();
        $pendingWithdrawals = \App\Models\Withdrawal::where('status', 'pending')->count();

        return [
            Stat::make('Total Users', User::count())
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Total Shops', Shop::count())
                ->description(Shop::where('status', 'active')->count() . ' active shops')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('success'),

            Stat::make('Total Products', Product::count())
                ->description(Product::where('status', 'active')->count() . ' active products')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),

            Stat::make('Total Orders', Order::count())
                ->description($pendingOrders . ' pending')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('warning'),

            Stat::make('Total Revenue', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('From settled transactions')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Platform Commission', 'Rp ' . number_format($totalCommission, 0, ',', '.'))
                ->description('Total commission earned')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Admin Fee Revenue', 'Rp ' . number_format($totalAdminFee, 0, ',', '.'))
                ->description('Total admin fee collected')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),

            Stat::make('Pending Withdrawals', $pendingWithdrawals)
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingWithdrawals > 0 ? 'danger' : 'gray'),
        ];
    }
}
