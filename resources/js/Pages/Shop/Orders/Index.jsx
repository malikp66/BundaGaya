import ShopLayout from '@/Layouts/ShopLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ orderItems, filters, shop }) {
    const [selectedStatus, setSelectedStatus] = useState(filters?.status || '');

    const handleFilterChange = (status) => {
        setSelectedStatus(status);
        router.get(route('shop.orders.index'), status ? { status } : {}, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleConfirm = (orderId) => {
        if (confirm('Confirm this order?')) {
            router.post(route('shop.orders.confirm', orderId));
        }
    };

    const handlePickedUp = (orderId) => {
        if (confirm('Mark as picked up?')) {
            router.post(route('shop.orders.picked-up', orderId));
        }
    };

    const handleReturned = (orderId) => {
        if (confirm('Mark as returned?')) {
            router.post(route('shop.orders.returned', orderId));
        }
    };

    const getStatusColor = (status) => {
        const colors = {
            pending: 'bg-yellow-100 text-yellow-800',
            paid: 'bg-blue-100 text-blue-800',
            confirmed: 'bg-indigo-100 text-indigo-800',
            picked_up: 'bg-purple-100 text-purple-800',
            returned: 'bg-orange-100 text-orange-800',
            completed: 'bg-green-100 text-green-800',
            cancelled: 'bg-red-100 text-red-800',
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    };

    return (
        <ShopLayout shop={shop}>
            <Head title="Shop Orders" />
            
            <h1 className="mb-6 text-2xl font-bold text-gray-900">Incoming Orders</h1>

            <div className="mb-6 flex flex-wrap gap-2">
                <button
                    onClick={() => handleFilterChange('')}
                    className={`rounded-full px-4 py-2 text-sm ${
                        !selectedStatus ? 'bg-rose-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'
                    }`}
                >
                    All
                </button>
                {['pending', 'paid', 'confirmed', 'picked_up', 'returned', 'completed'].map((status) => (
                    <button
                        key={status}
                        onClick={() => handleFilterChange(status)}
                        className={`rounded-full px-4 py-2 text-sm capitalize ${
                            selectedStatus === status ? 'bg-rose-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'
                        }`}
                    >
                        {status.replace('_', ' ')}
                    </button>
                ))}
            </div>

            {orderItems.data.length === 0 ? (
                <div className="rounded-lg bg-white p-12 text-center">
                    <p className="text-gray-600">No orders found</p>
                </div>
            ) : (
                <div className="space-y-4">
                    {orderItems.data.map((item) => (
                        <div key={item.id} className="rounded-lg bg-white p-4 shadow-sm">
                            <div className="mb-3 flex items-start justify-between">
                                <div>
                                    <p className="text-sm text-gray-500">Order #{item.order?.order_number}</p>
                                    <p className="text-sm font-medium text-gray-900">{item.order?.user?.name}</p>
                                    <p className="text-xs text-gray-400">{new Date(item.order?.created_at).toLocaleDateString('id-ID')}</p>
                                </div>
                                <span className={`rounded-full px-3 py-1 text-xs font-medium ${getStatusColor(item.status)}`}>
                                    {item.status.replace('_', ' ')}
                                </span>
                            </div>

                            <div className="mb-3 border-t pt-3">
                                <div className="flex gap-3">
                                    <div className="h-16 w-16 flex-shrink-0 overflow-hidden rounded bg-gray-100">
                                        {item.product?.primary_photo?.photo_path ? (
                                            <img
                                                src={`/storage/${item.product.primary_photo.photo_path}`}
                                                alt={item.product.name}
                                                className="h-full w-full object-cover"
                                            />
                                        ) : (
                                            <div className="flex h-full items-center justify-center text-gray-400">
                                                <svg className="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        )}
                                    </div>
                                    <div className="flex-1">
                                        <p className="font-medium text-gray-900">{item.product?.name}</p>
                                        <p className="text-sm text-gray-500">{item.start_date} - {item.end_date}</p>
                                        <p className="text-sm text-gray-500">{item.days} days × {item.quantity}</p>
                                    </div>
                                    <div className="text-right">
                                        <p className="font-semibold text-rose-600">Rp {parseInt(item.subtotal).toLocaleString('id-ID')}</p>
                                        <p className="text-xs text-gray-500">Net: Rp {parseInt(item.net_amount).toLocaleString('id-ID')}</p>
                                    </div>
                                </div>
                            </div>

                            <div className="flex gap-2">
                                {item.status === 'paid' && (
                                    <button
                                        onClick={() => handleConfirm(item.order_id)}
                                        className="rounded-lg bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700"
                                    >
                                        Confirm Order
                                    </button>
                                )}
                                {item.status === 'confirmed' && (
                                    <button
                                        onClick={() => handlePickedUp(item.order_id)}
                                        className="rounded-lg bg-purple-600 px-4 py-2 text-sm text-white hover:bg-purple-700"
                                    >
                                        Mark Picked Up
                                    </button>
                                )}
                                {item.status === 'picked_up' && (
                                    <button
                                        onClick={() => handleReturned(item.order_id)}
                                        className="rounded-lg bg-orange-600 px-4 py-2 text-sm text-white hover:bg-orange-700"
                                    >
                                        Mark Returned
                                    </button>
                                )}
                                <Link
                                    href={route('shop.orders.show', item.order_id)}
                                    className="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                >
                                    View Details
                                </Link>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </ShopLayout>
    );
}
