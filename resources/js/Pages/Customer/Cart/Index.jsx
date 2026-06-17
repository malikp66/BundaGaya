import CustomerLayout from '@/Layouts/CustomerLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ cart }) {
    const { auth } = usePage().props;
    const [address, setAddress] = useState(auth.user?.address || '');
    const [phone, setPhone] = useState(auth.user?.phone || '');
    const [notes, setNotes] = useState('');

    const { post, processing } = useForm();

    const handleCheckout = (e) => {
        e.preventDefault();
        post(route('customer.checkout'), {
            data: { address, phone, notes },
        });
    };

    const handleRemoveItem = (itemId) => {
        router.delete(route('customer.cart.remove', itemId), {
            preserveScroll: true,
        });
    };

    const handleUpdateQuantity = (itemId, quantity) => {
        router.patch(route('customer.cart.update', itemId), {
            quantity,
        }, {
            preserveScroll: true,
        });
    };

    const handleClearCart = () => {
        if (confirm('Are you sure you want to clear your cart?')) {
            router.delete(route('customer.cart.clear'), {
                preserveScroll: true,
            });
        }
    };

    const shops = cart?.shops || [];
    const total = cart?.total || 0;
    const totalItems = cart?.total_items || 0;

    return (
        <CustomerLayout cartItemCount={totalItems}>
            <Head title="Shopping Cart" />
            
            <div className="px-4 py-6">
                <div className="mx-auto max-w-3xl">
                    <h1 className="mb-6 text-2xl font-bold text-gray-900">Shopping Cart</h1>

                    {shops.length === 0 ? (
                        <div className="rounded-lg bg-white p-12 text-center">
                            <svg className="mx-auto mb-4 h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p className="mb-4 text-gray-600">Your cart is empty</p>
                            <Link
                                href={route('products.index')}
                                className="inline-block rounded-lg bg-rose-600 px-6 py-2 text-white hover:bg-rose-700"
                            >
                                Start Shopping
                            </Link>
                        </div>
                    ) : (
                        <>
                            {shops.map((shopData) => (
                                <div key={shopData.shop.id} className="mb-6 rounded-lg bg-white p-4 shadow-sm">
                                    <div className="mb-3 border-b pb-3">
                                        <h2 className="font-semibold text-gray-900">{shopData.shop.name}</h2>
                                        <p className="text-sm text-gray-500">{shopData.shop.city}</p>
                                    </div>
                                    
                                    {shopData.items.map((item) => (
                                        <div key={item.id} className="mb-3 flex gap-3 border-b pb-3 last:border-b-0">
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
                                                    {item.start_date} - {item.end_date} ({item.days} days)
                                                </p>
                                                <p className="text-sm font-semibold text-rose-600">
                                                    Rp {parseInt(item.price_per_day).toLocaleString('id-ID')}/day
                                                </p>
                                                <div className="mt-2 flex items-center gap-2">
                                                    <label className="text-sm text-gray-600">Qty:</label>
                                                    <input
                                                        type="number"
                                                        value={item.quantity}
                                                        onChange={(e) => handleUpdateQuantity(item.id, parseInt(e.target.value))}
                                                        min="1"
                                                        className="w-16 rounded border px-2 py-1 text-sm"
                                                    />
                                                    <button
                                                        onClick={() => handleRemoveItem(item.id)}
                                                        className="ml-auto text-sm text-red-600 hover:underline"
                                                    >
                                                        Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    ))}

                                    <div className="mt-3 text-right">
                                        <p className="text-sm text-gray-600">Subtotal:</p>
                                        <p className="text-lg font-bold text-rose-600">
                                            Rp {shopData.subtotal.toLocaleString('id-ID')}
                                        </p>
                                    </div>
                                </div>
                            ))}

                            <div className="mb-6 rounded-lg bg-white p-4 shadow-sm">
                                <h2 className="mb-4 font-semibold text-gray-900">Delivery Details</h2>
                                <div className="space-y-3">
                                    <div>
                                        <label className="mb-1 block text-sm font-medium text-gray-700">Address</label>
                                        <textarea
                                            value={address}
                                            onChange={(e) => setAddress(e.target.value)}
                                            rows="3"
                                            className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label className="mb-1 block text-sm font-medium text-gray-700">Phone</label>
                                        <input
                                            type="tel"
                                            value={phone}
                                            onChange={(e) => setPhone(e.target.value)}
                                            className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label className="mb-1 block text-sm font-medium text-gray-700">Notes (Optional)</label>
                                        <textarea
                                            value={notes}
                                            onChange={(e) => setNotes(e.target.value)}
                                            rows="2"
                                            className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div className="mb-6 rounded-lg bg-white p-4 shadow-sm">
                                <div className="mb-3 flex justify-between text-sm">
                                    <span className="text-gray-600">Subtotal ({totalItems} items)</span>
                                    <span className="font-medium">Rp {total.toLocaleString('id-ID')}</span>
                                </div>
                                <div className="mb-3 flex justify-between text-sm">
                                    <span className="text-gray-600">Admin Fee</span>
                                    <span className="font-medium">Rp 5,000</span>
                                </div>
                                <div className="border-t pt-3">
                                    <div className="flex justify-between">
                                        <span className="text-lg font-semibold text-gray-900">Total</span>
                                        <span className="text-lg font-bold text-rose-600">
                                            Rp {(total + 5000).toLocaleString('id-ID')}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div className="flex gap-3">
                                <button
                                    onClick={handleClearCart}
                                    className="rounded-lg border border-gray-300 px-6 py-3 text-gray-700 hover:bg-gray-50"
                                >
                                    Clear Cart
                                </button>
                                <button
                                    onClick={handleCheckout}
                                    disabled={processing || !address || !phone}
                                    className="flex-1 rounded-lg bg-rose-600 px-6 py-3 font-semibold text-white transition hover:bg-rose-700 disabled:opacity-50"
                                >
                                    {processing ? 'Processing...' : 'Checkout'}
                                </button>
                            </div>
                        </>
                    )}
                </div>
            </div>
        </CustomerLayout>
    );
}
