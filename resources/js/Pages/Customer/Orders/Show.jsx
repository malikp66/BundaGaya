import CustomerLayout from '@/Layouts/CustomerLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Show({ order }) {
    const { post, processing } = useForm({ reason: '' });

    const handleCancel = () => {
        if (confirm('Yakin ingin membatalkan pesanan ini?')) {
            post(route('customer.orders.cancel', order.id));
        }
    };

    const getStatusColor = (status) => {
        const colors = {
            pending_payment: 'bg-yellow-100 text-yellow-800',
            paid: 'bg-blue-100 text-blue-800',
            processing: 'bg-indigo-100 text-indigo-800',
            shipped: 'bg-purple-100 text-purple-800',
            in_use: 'bg-pink-100 text-pink-800',
            returned: 'bg-orange-100 text-orange-800',
            completed: 'bg-green-100 text-green-800',
            cancelled: 'bg-red-100 text-red-800',
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    };

    const getStatusLabel = (status) => {
        const labels = {
            pending_payment: 'Menunggu Pembayaran',
            paid: 'Dibayar',
            processing: 'Diproses',
            shipped: 'Dikirim',
            in_use: 'Sedang Digunakan',
            returned: 'Dikembalikan',
            completed: 'Selesai',
            cancelled: 'Dibatalkan',
        };
        return labels[status] || status;
    };

    const statusSteps = [
        { key: 'pending_payment', label: 'Pembayaran' },
        { key: 'paid', label: 'Dibayar' },
        { key: 'processing', label: 'Diproses' },
        { key: 'shipped', label: 'Dikirim' },
        { key: 'in_use', label: 'Digunakan' },
        { key: 'returned', label: 'Dikembalikan' },
        { key: 'completed', label: 'Selesai' },
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
                        Kembali ke Pesanan
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
                        <h2 className="mb-4 text-lg font-semibold text-gray-900">Barang Pesanan</h2>
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
                                        <p className="text-sm text-gray-500">
                                            {item.start_date} - {item.end_date} ({item.days} hari)
                                        </p>
                                        <div className="mt-2 flex items-center justify-between">
                                            <p className="text-sm text-gray-600">Jml: {item.quantity}</p>
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
                        <h2 className="mb-4 text-lg font-semibold text-gray-900">Ringkasan Pesanan</h2>
                        <div className="space-y-2">
                            <div className="flex justify-between text-sm">
                                <span className="text-gray-600">Subtotal</span>
                                <span>Rp {parseInt(order.subtotal).toLocaleString('id-ID')}</span>
                            </div>
                            <div className="flex justify-between text-sm">
                                <span className="text-gray-600">Biaya Admin</span>
                                <span>Rp {parseInt(order.admin_fee).toLocaleString('id-ID')}</span>
                            </div>
                            <div className="flex justify-between text-sm">
                                <span className="text-gray-600">Komisi</span>
                                <span>Rp {parseInt(order.commission_fee).toLocaleString('id-ID')}</span>
                            </div>
                            {parseInt(order.dp_total) > 0 && (
                                <div className="flex justify-between text-sm">
                                    <span className="text-gray-600">DP (Deposit)</span>
                                    <span className="font-medium text-amber-600">Rp {parseInt(order.dp_total).toLocaleString('id-ID')}</span>
                                </div>
                            )}
                            {parseInt(order.shipping_cost) > 0 && (
                                <div className="flex justify-between text-sm">
                                    <span className="text-gray-600">Ongkos Kirim</span>
                                    <span>Rp {parseInt(order.shipping_cost).toLocaleString('id-ID')}</span>
                                </div>
                            )}
                            <div className="border-t pt-2">
                                <div className="flex justify-between">
                                    <span className="text-lg font-semibold text-gray-900">Total</span>
                                    <span className="text-lg font-bold text-rose-600">
                                        Rp {parseInt(order.grand_total ?? order.total).toLocaleString('id-ID')}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="mb-6 rounded-lg bg-white p-6 shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold text-gray-900">Detail Pengiriman</h2>
                        <div className="space-y-2 text-sm">
                            <div>
                                <span className="text-gray-600">Alamat:</span>
                                <p className="mt-1">{order.address}</p>
                            </div>
                            <div>
                                <span className="text-gray-600">Telepon:</span>
                                <p className="mt-1">{order.phone}</p>
                            </div>
                            {order.shipping_address && (
                                <div>
                                    <span className="text-gray-600">Alamat Pengiriman:</span>
                                    <p className="mt-1">{order.shipping_address}</p>
                                </div>
                            )}
                            {order.city && (
                                <div>
                                    <span className="text-gray-600">Kota:</span>
                                    <p className="mt-1">{order.city}, {order.province} {order.postal_code}</p>
                                </div>
                            )}
                            {order.shipping_courier && (
                                <div>
                                    <span className="text-gray-600">Kurir:</span>
                                    <p className="mt-1">{order.shipping_courier} - {order.shipping_service}</p>
                                </div>
                            )}
                            {order.tracking_number && (
                                <div>
                                    <span className="text-gray-600">No. Resi:</span>
                                    <p className="mt-1 font-mono">{order.tracking_number}</p>
                                </div>
                            )}
                            {order.notes && (
                                <div>
                                    <span className="text-gray-600">Catatan:</span>
                                    <p className="mt-1">{order.notes}</p>
                                </div>
                            )}
                            {order.return_date && (
                                <div>
                                    <span className="text-gray-600">Tgl. Pengembalian:</span>
                                    <p className="mt-1">{order.return_date}</p>
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="mb-6 rounded-lg bg-white p-6 shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold text-gray-900">Data Penyewa</h2>
                        <div className="space-y-2 text-sm">
                            <div className="flex justify-between">
                                <span className="text-gray-600">Nama (KTP)</span>
                                <span className="font-medium">{order.customer_name}</span>
                            </div>
                            {order.instagram && (
                                <div className="flex justify-between">
                                    <span className="text-gray-600">Instagram</span>
                                    <span className="font-medium">{order.instagram}</span>
                                </div>
                            )}
                            <div className="flex justify-between">
                                <span className="text-gray-600">Telepon</span>
                                <span className="font-medium">{order.phone}</span>
                            </div>
                        </div>
                    </div>

                    <div className="mb-6 rounded-lg bg-white p-6 shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold text-gray-900">Deposit (DP)</h2>
                        <div className="space-y-3 text-sm">
                            <div className="flex justify-between">
                                <span className="text-gray-600">Total DP</span>
                                <span className="font-medium">Rp {parseInt(order.dp_total).toLocaleString('id-ID')}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-gray-600">Status DP</span>
                                <span className={`rounded-full px-3 py-1 text-xs font-medium ${
                                    order.dp_status === 'completed' ? 'bg-green-100 text-green-800' :
                                    order.dp_status === 'transferred' ? 'bg-blue-100 text-blue-800' :
                                    'bg-yellow-100 text-yellow-800'
                                }`}>
                                    {order.dp_status === 'completed' ? 'Selesai' :
                                     order.dp_status === 'transferred' ? 'Ditransfer' :
                                     'Menunggu'}
                                </span>
                            </div>
                            {order.dp_deducted > 0 && (
                                <div className="flex justify-between">
                                    <span className="text-red-600">Dipotong (kerusakan)</span>
                                    <span className="text-red-600">-Rp {parseInt(order.dp_deducted).toLocaleString('id-ID')}</span>
                                </div>
                            )}
                            <div className="border-t pt-2">
                                <p className="mb-2 font-medium text-gray-700">Rekening Pengembalian</p>
                                <div className="flex justify-between">
                                    <span className="text-gray-600">Bank</span>
                                    <span>{order.refund_bank_name}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-gray-600">No. Rekening</span>
                                    <span>{order.refund_bank_account}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-gray-600">Atas Nama</span>
                                    <span>{order.refund_bank_holder}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {(order.status === 'pending_payment' || order.status === 'paid') && (
                        <div className="flex gap-3">
                            <button
                                onClick={handleCancel}
                                disabled={processing}
                                className="rounded-lg border border-red-300 px-6 py-3 text-red-600 hover:bg-red-50 disabled:opacity-50"
                            >
                                Batalkan Pesanan
                            </button>
                        </div>
                    )}
                </div>
            </div>
        </CustomerLayout>
    );
}
