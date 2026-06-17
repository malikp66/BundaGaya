import ShopLayout from '@/Layouts/ShopLayout';
import { Head, Link } from '@inertiajs/react';

export default function Show({ order, orderItems, shop }) {
    return (
        <ShopLayout shop={shop}>
            <Head title={`Order ${order.order_number}`} />
            
            <Link href={route('shop.orders.index')} className="mb-4 inline-flex items-center text-sm text-gray-600 hover:text-rose-600">
                <svg className="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Orders
            </Link>

            <div className="mb-6 rounded-lg bg-white p-6 shadow-sm">
                <div className="mb-4 flex items-start justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Order #{order.order_number}</h1>
                        <p className="text-sm text-gray-500">{new Date(order.created_at).toLocaleDateString('id-ID')}</p>
                    </div>
                    <span className="rounded-full bg-blue-100 px-4 py-2 text-sm font-medium text-blue-800">
                        {order.status.replace('_', ' ')}
                    </span>
                </div>

                <div className="mb-4">
                    <h2 className="mb-2 font-semibold text-gray-900">Customer</h2>
                    <p className="text-gray-700">{order.user?.name}</p>
                    <p className="text-sm text-gray-500">{order.user?.email}</p>
                    <p className="text-sm text-gray-500">{order.phone}</p>
                </div>

                <div className="mb-4">
                    <h2 className="mb-2 font-semibold text-gray-900">Delivery Address</h2>
                    <p className="text-sm text-gray-700">{order.address}</p>
                </div>
            </div>

            <div className="mb-6 rounded-lg bg-white p-6 shadow-sm">
                <h2 className="mb-4 text-lg font-semibold text-gray-900">Order Items</h2>
                <div className="space-y-4">
                    {orderItems.map((item) => (
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
                                <p className="text-sm text-gray-500">{item.start_date} - {item.end_date} ({item.days} days)</p>
                                <p className="text-sm text-gray-500">Qty: {item.quantity}</p>
                                <div className="mt-2 flex items-center justify-between">
                                    <span className="text-sm text-gray-600">Commission: {item.commission_rate}%</span>
                                    <div className="text-right">
                                        <p className="font-semibold text-gray-900">Rp {parseInt(item.subtotal).toLocaleString('id-ID')}</p>
                                        <p className="text-sm text-green-600">Net: Rp {parseInt(item.net_amount).toLocaleString('id-ID')}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            <div className="rounded-lg bg-white p-6 shadow-sm">
                <h2 className="mb-4 text-lg font-semibold text-gray-900">Summary</h2>
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
                            <span className="text-lg font-semibold">Total</span>
                            <span className="text-lg font-bold text-rose-600">Rp {parseInt(order.total).toLocaleString('id-ID')}</span>
                        </div>
                    </div>
                </div>
            </div>
        </ShopLayout>
    );
}
