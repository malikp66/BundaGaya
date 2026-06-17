import CustomerLayout from '@/Layouts/CustomerLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Show({ order }) {
    const { post, processing } = useForm({ reason: '' });

    const handleCancel = () => {
        if (confirm('Are you sure you want to cancel this order?')) {
            post(route('customer.orders.cancel', order.id));
        }
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

    const statusSteps = [
        { key: 'pending_payment', label: 'Payment' },
        { key: 'paid', label: 'Paid' },
        { key: 'confirmed_by_owner', label: 'Confirmed' },
        { key: 'picked_up', label: 'Picked Up' },
        { key: 'returned', label: 'Returned' },
        { key: 'completed', label: 'Completed' },
    ];

    const currentStepIndex = statusSteps.findIndex(step => step.key === order.status);

    return (
        <CustomerLayout>
            <Head title={`Order ${order.order_number}`} />
            
            <div className="px-4 py-6">
                <div className="mx-auto max-w-4xl">
                    <Link href={route('customer.orders.index')} className="mb-4 inline-flex items-center text-sm text-gray-600 hover:text-rose-600">
                        <svg className="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Orders
                    </Link>

                    <div className="mb-6 rounded-lg bg-white p-6 shadow-sm">
                        <div className="mb-4 flex items-start justify-between">
                            <div>
                                <h1 className="text-2xl font-bold text-gray-900">Order #{order.order_number}</h1>
                                <p className="text-sm text-gray-500">{new Date(order.created_at).toLocaleDateString('id-ID', { 
                                    year: 'numeric', 
                                    month: 'long', 
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                })}</p>
                            </div>
                            <span className={`rounded-full px-4 py-2 text-sm font-medium ${getStatusColor(order.status)}`}>
                                {getStatusLabel(order.status)}
                            </span>
                        </div>

                        <div className="mb-6">
                            <div className="relative">
                                {statusSteps.map((step, index) => (
                                    <div key={step.key} className="mb-4 flex items-center">
                                        <div className={`flex h-8 w-8 items-center justify-center rounded-full ${
                                            index <= currentStepIndex ? 'bg-rose-600 text-white' : 'bg-gray-200 text-gray-500'
                                        }`}>
                                            {index + 1}
                                        </div>
                                        <div className="ml-3 flex-1">
                                            <p className={`text-sm font-medium ${
                                                index <= currentStepIndex ? 'text-gray-900' : 'text-gray-500'
                                            }`}>
                                                {step.label}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>

                    <div className="mb-6 rounded-lg bg-white p-6 shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold text-gray-900">Order Items</h2>
                        <div className="space-y-4">
                            {order.items?.map((item) => (
                                <div key={item.id} className="flex gap-4 border-b pb-4 last:border-b-0">
                                    <div className="h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg bg-gray-100">
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
                                        <h3 className="font-medium text-gray-900">{item.product?.name}</h3>
                                        <p className="text-sm text-gray-500">Shop: {item.shop?.name}</p>
                                        <p className="text-sm text-gray-500">
                                            {item.start_date} - {item.end_date} ({item.days} days)
                                        </p>
                                        <div className="mt-2 flex items-center justify-between">
                                            <p className="text-sm text-gray-600">Qty: {item.quantity}</p>
                                            <p className="font-semibold text-rose-600">
                                                Rp {parseInt(item.subtotal).toLocaleString('id-ID')}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="mb-6 rounded-lg bg-white p-6 shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold text-gray-900">Order Summary</h2>
                        <div className="space-y-2">
                            <div className="flex justify-between text-sm">
                                <span className="text-gray-600">Subtotal</span>
                                <span>Rp {parseInt(order.subtotal).toLocaleString('id-ID')}</span>
                            </div>
                            <div className="flex justify-between text-sm">
                                <span className="text-gray-600">Admin Fee</span>
                                <span>Rp {parseInt(order.admin_fee).toLocaleString('id-ID')}</span>
                            </div>
                            <div className="flex justify-between text-sm">
                                <span className="text-gray-600">Commission</span>
                                <span>Rp {parseInt(order.commission_fee).toLocaleString('id-ID')}</span>
                            </div>
                            <div className="border-t pt-2">
                                <div className="flex justify-between">
                                    <span className="text-lg font-semibold text-gray-900">Total</span>
                                    <span className="text-lg font-bold text-rose-600">
                                        Rp {parseInt(order.total).toLocaleString('id-ID')}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="mb-6 rounded-lg bg-white p-6 shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold text-gray-900">Delivery Details</h2>
                        <div className="space-y-2 text-sm">
                            <div>
                                <span className="text-gray-600">Address:</span>
                                <p className="mt-1">{order.address}</p>
                            </div>
                            <div>
                                <span className="text-gray-600">Phone:</span>
                                <p className="mt-1">{order.phone}</p>
                            </div>
                            {order.notes && (
                                <div>
                                    <span className="text-gray-600">Notes:</span>
                                    <p className="mt-1">{order.notes}</p>
                                </div>
                            )}
                        </div>
                    </div>

                    {(order.status === 'pending_payment' || order.status === 'paid') && (
                        <div className="flex gap-3">
                            <button
                                onClick={handleCancel}
                                disabled={processing}
                                className="rounded-lg border border-red-300 px-6 py-3 text-red-600 hover:bg-red-50 disabled:opacity-50"
                            >
                                Cancel Order
                            </button>
                        </div>
                    )}
                </div>
            </div>
        </CustomerLayout>
    );
}
