import CustomerLayout from '@/Layouts/CustomerLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ orders, filters }) {
    const [selectedStatus, setSelectedStatus] = useState(filters?.status || '');

    const handleFilterChange = (status) => {
        setSelectedStatus(status);
        router.get(route('customer.orders.index'), status ? { status } : {}, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const getStatusColor = (status) => {
        const colors = {
            pending_payment: 'bg-yellow-100 text-yellow-800',
            paid: 'bg-blue-100 text-blue-800',
            confirmed_by_owner: 'bg-indigo-100 text-indigo-800',
            picked_up: 'bg-purple-100 text-purple-800',
            in_use: 'bg-pink-100 text-pink-800',
            returned: 'bg-orange-100 text-orange-800',
            completed: 'bg-green-100 text-green-800',
            cancelled: 'bg-red-100 text-red-800',
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    };

    const getStatusLabel = (status) => {
        const labels = {
            pending_payment: 'Pending Payment',
            paid: 'Paid',
            confirmed_by_owner: 'Confirmed',
            picked_up: 'Picked Up',
            in_use: 'In Use',
            returned: 'Returned',
            completed: 'Completed',
            cancelled: 'Cancelled',
        };
        return labels[status] || status;
    };

    return (
        <CustomerLayout>
            <Head title="My Orders" />
            
            <div className="px-4 py-6">
                <div className="mx-auto max-w-4xl">
                    <h1 className="mb-6 text-2xl font-bold text-gray-900">My Orders</h1>

                    <div className="mb-6 flex flex-wrap gap-2">
                        <button
                            onClick={() => handleFilterChange('')}
                            className={`rounded-full px-4 py-2 text-sm ${
                                !selectedStatus ? 'bg-rose-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'
                            }`}
                        >
                            All
                        </button>
                        {['pending_payment', 'paid', 'completed', 'cancelled'].map((status) => (
                            <button
                                key={status}
                                onClick={() => handleFilterChange(status)}
                                className={`rounded-full px-4 py-2 text-sm ${
                                    selectedStatus === status ? 'bg-rose-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'
                                }`}
                            >
                                {getStatusLabel(status)}
                            </button>
                        ))}
                    </div>

                    {orders.data.length === 0 ? (
                        <div className="rounded-lg bg-white p-12 text-center">
                            <svg className="mx-auto mb-4 h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <p className="mb-4 text-gray-600">No orders found</p>
                            <Link
                                href={route('products.index')}
                                className="inline-block rounded-lg bg-rose-600 px-6 py-2 text-white hover:bg-rose-700"
                            >
                                Start Shopping
                            </Link>
                        </div>
                    ) : (
                        <div className="space-y-4">
                            {orders.data.map((order) => (
                                <Link
                                    key={order.id}
                                    href={route('customer.orders.show', order.id)}
                                    className="block rounded-lg bg-white p-4 shadow-sm transition hover:shadow-md"
                                >
                                    <div className="mb-3 flex items-start justify-between">
                                        <div>
                                            <p className="text-sm text-gray-500">Order #{order.order_number}</p>
                                            <p className="text-xs text-gray-400">{new Date(order.created_at).toLocaleDateString('id-ID')}</p>
                                        </div>
                                        <span className={`rounded-full px-3 py-1 text-xs font-medium ${getStatusColor(order.status)}`}>
                                            {getStatusLabel(order.status)}
                                        </span>
                                    </div>

                                    <div className="mb-3 border-t pt-3">
                                        {order.items?.slice(0, 2).map((item) => (
                                            <div key={item.id} className="mb-2 flex items-center gap-3">
                                                <div className="h-12 w-12 flex-shrink-0 overflow-hidden rounded bg-gray-100">
                                                    {item.product?.primary_photo?.photo_path ? (
                                                        <img
                                                            src={`/storage/${item.product.primary_photo.photo_path}`}
                                                            alt={item.product.name}
                                                            className="h-full w-full object-cover"
                                                        />
                                                    ) : (
                                                        <div className="flex h-full items-center justify-center text-gray-400">
                                                            <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                            </svg>
                                                        </div>
                                                    )}
                                                </div>
                                                <div className="flex-1">
                                                    <p className="text-sm font-medium text-gray-900">{item.product?.name}</p>
                                                    <p className="text-xs text-gray-500">{item.days} days</p>
                                                </div>
                                            </div>
                                        ))}
                                        {order.items?.length > 2 && (
                                            <p className="text-xs text-gray-500">+{order.items.length - 2} more items</p>
                                        )}
                                    </div>

                                    <div className="flex items-center justify-between border-t pt-3">
                                        <p className="text-sm text-gray-600">
                                            {order.items?.length || 0} items
                                        </p>
                                        <p className="text-lg font-bold text-rose-600">
                                            Rp {parseInt(order.total).toLocaleString('id-ID')}
                                        </p>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    )}

                    {orders.links && orders.links.length > 3 && (
                        <div className="mt-6 flex justify-center gap-1">
                            {orders.links.map((link, index) => (
                                <Link
                                    key={index}
                                    href={link.url}
                                    className={`rounded-lg px-3 py-2 text-sm ${
                                        link.active ? 'bg-rose-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'
                                    }`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </CustomerLayout>
    );
}
