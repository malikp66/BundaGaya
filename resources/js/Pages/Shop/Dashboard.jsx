import ShopLayout from '@/Layouts/ShopLayout';
import { Head, Link } from '@inertiajs/react';

export default function Dashboard({ shop, stats, revenue, availableBalance, recentOrders, topProducts }) {
    if (!shop) {
        return (
            <ShopLayout shop={shop}>
                <Head title="Shop Dashboard" />
                <div className="rounded-lg bg-white p-12 text-center">
                    <svg className="mx-auto mb-4 h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <h2 className="mb-2 text-xl font-semibold text-gray-900">You don't have a shop yet</h2>
                    <p className="mb-6 text-gray-600">Create your shop to start selling products</p>
                    <Link
                        href={route('shop.shop.create')}
                        className="inline-block rounded-lg bg-rose-600 px-6 py-3 text-white hover:bg-rose-700"
                    >
                        Create Shop
                    </Link>
                </div>
            </ShopLayout>
        );
    }

    if (shop.status === 'pending') {
        return (
            <ShopLayout shop={shop}>
                <Head title="Shop Dashboard" />
                <div className="rounded-lg bg-yellow-50 p-6 text-center">
                    <svg className="mx-auto mb-4 h-16 w-16 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h2 className="mb-2 text-xl font-semibold text-gray-900">Shop Pending Approval</h2>
                    <p className="text-gray-600">Your shop is being reviewed by our admin team. You'll be notified once it's approved.</p>
                </div>
            </ShopLayout>
        );
    }

    return (
        <ShopLayout shop={shop}>
            <Head title="Shop Dashboard" />
            
            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Dashboard</h1>
                    <p className="text-sm text-gray-500">Welcome back, {shop.name}</p>
                </div>

                <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div className="rounded-lg bg-white p-4 shadow-sm">
                        <p className="text-sm text-gray-500">Total Products</p>
                        <p className="text-2xl font-bold text-gray-900">{stats?.total_products || 0}</p>
                        <p className="text-xs text-gray-400">{stats?.active_products || 0} active</p>
                    </div>
                    <div className="rounded-lg bg-white p-4 shadow-sm">
                        <p className="text-sm text-gray-500">Total Orders</p>
                        <p className="text-2xl font-bold text-gray-900">{stats?.total_orders || 0}</p>
                        <p className="text-xs text-gray-400">{stats?.pending_orders || 0} pending</p>
                    </div>
                    <div className="rounded-lg bg-white p-4 shadow-sm">
                        <p className="text-sm text-gray-500">Total Revenue</p>
                        <p className="text-2xl font-bold text-rose-600">
                            Rp {(stats?.total_revenue || 0).toLocaleString('id-ID')}
                        </p>
                        <p className="text-xs text-gray-400">All time</p>
                    </div>
                    <div className="rounded-lg bg-white p-4 shadow-sm">
                        <p className="text-sm text-gray-500">Available Balance</p>
                        <p className="text-2xl font-bold text-green-600">
                            Rp {(availableBalance || 0).toLocaleString('id-ID')}
                        </p>
                        <Link href={route('shop.withdrawals.index')} className="text-xs text-rose-600 hover:underline">
                            Withdraw →
                        </Link>
                    </div>
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <div className="rounded-lg bg-white p-6 shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold text-gray-900">Recent Orders</h2>
                        {recentOrders?.length > 0 ? (
                            <div className="space-y-3">
                                {recentOrders.slice(0, 5).map((item) => (
                                    <Link
                                        key={item.id}
                                        href={route('shop.orders.show', item.order_id)}
                                        className="flex items-center justify-between rounded-lg border p-3 hover:bg-gray-50"
                                    >
                                        <div>
                                            <p className="font-medium text-gray-900">{item.order?.user?.name}</p>
                                            <p className="text-sm text-gray-500">{item.product?.name}</p>
                                        </div>
                                        <div className="text-right">
                                            <p className="font-semibold text-rose-600">
                                                Rp {parseInt(item.subtotal).toLocaleString('id-ID')}
                                            </p>
                                            <p className="text-xs text-gray-500 capitalize">{item.status}</p>
                                        </div>
                                    </Link>
                                ))}
                            </div>
                        ) : (
                            <p className="text-center text-gray-500">No orders yet</p>
                        )}
                    </div>

                    <div className="rounded-lg bg-white p-6 shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold text-gray-900">Top Products</h2>
                        {topProducts?.length > 0 ? (
                            <div className="space-y-3">
                                {topProducts.slice(0, 5).map((product) => (
                                    <div key={product.id} className="flex items-center justify-between rounded-lg border p-3">
                                        <div>
                                            <p className="font-medium text-gray-900">{product.name}</p>
                                            <p className="text-sm text-gray-500">{product.rental_count} rentals</p>
                                        </div>
                                        <p className="font-semibold text-rose-600">
                                            Rp {parseInt(product.price_per_day).toLocaleString('id-ID')}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-center text-gray-500">No products yet</p>
                        )}
                    </div>
                </div>
            </div>
        </ShopLayout>
    );
}
