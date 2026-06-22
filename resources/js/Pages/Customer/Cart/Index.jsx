import CustomerLayout from '@/Layouts/CustomerLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';

export default function Index({ cart }) {
    const { auth } = usePage().props;
    const [address, setAddress] = useState(auth.user?.address || '');
    const [phone, setPhone] = useState(auth.user?.phone || '');
    const [notes, setNotes] = useState('');
    const [customerName, setCustomerName] = useState(auth.user?.name || '');
    const [instagram, setInstagram] = useState('');
    const [returnDate, setReturnDate] = useState('');
    const [refundBankName, setRefundBankName] = useState('');
    const [refundBankAccount, setRefundBankAccount] = useState('');
    const [refundBankHolder, setRefundBankHolder] = useState('');
    const [shippingAddress, setShippingAddress] = useState('');
    const [city, setCity] = useState('');
    const [province, setProvince] = useState('');
    const [postalCode, setPostalCode] = useState('');
    const [district, setDistrict] = useState('');
    const [suburb, setSuburb] = useState('');
    const [shippingCourier, setShippingCourier] = useState('');
    const [shippingService, setShippingService] = useState('');
    const [shippingCost, setShippingCost] = useState(0);
    const [showShippingForm, setShowShippingForm] = useState(false);
    const [rates, setRates] = useState([]);
    const [loadingRates, setLoadingRates] = useState(false);

    const { post, processing } = useForm();

    const items = cart?.items || [];
    const total = cart?.total || 0;
    const dpTotal = cart?.dp_total || 0;
    const adminFee = cart?.admin_fee || 5000;
    const totalItems = cart?.total_items || 0;

    useEffect(() => {
        if (items.length > 0) {
            const dates = items.map(i => i.end_date);
            const maxDate = dates.reduce((a, b) => a > b ? a : b, '');
            if (maxDate) setReturnDate(maxDate);
        }
    }, [items]);

    useEffect(() => {
        if (postalCode.length >= 5 && showShippingForm) {
            const timer = setTimeout(() => {
                fetchShippingRates();
            }, 500);
            return () => clearTimeout(timer);
        }
    }, [postalCode, showShippingForm]);

    const fetchShippingRates = async () => {
        if (!postalCode) return;
        setLoadingRates(true);
        try {
            const res = await fetch(route('customer.shipping.rates'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
                body: JSON.stringify({
                    destination_postal_code: postalCode,
                    weight: 500,
                    length: 30,
                    width: 20,
                    height: 10,
                }),
            });
            const data = await res.json();
            setRates(data.rates || []);
        } catch (e) {
            console.error('Failed to fetch rates', e);
        } finally {
            setLoadingRates(false);
        }
    };

    const selectRate = (rate) => {
        setShippingCourier(rate.courier_code);
        setShippingService(rate.service);
        setShippingCost(rate.price);
    };

    const handleCheckout = (e) => {
        e.preventDefault();
        post(route('customer.checkout'), {
            data: {
                address, phone, notes,
                customer_name: customerName, instagram, return_date: returnDate,
                refund_bank_name: refundBankName,
                refund_bank_account: refundBankAccount,
                refund_bank_holder: refundBankHolder,
                shipping_address: shippingAddress,
                city, province, postal_code: postalCode,
                district, suburb,
                shipping_courier: shippingCourier,
                shipping_service: shippingService,
                shipping_cost: shippingCost,
            },
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
        if (confirm('Yakin ingin mengosongkan keranjang?')) {
            router.delete(route('customer.cart.clear'), {
                preserveScroll: true,
            });
        }
    };

    return (
        <CustomerLayout cartItemCount={totalItems}>
            <Head title="Keranjang Belanja" />

            <div className="px-4 py-6">
                <div className="mx-auto max-w-3xl">
                    <h1 className="mb-6 text-2xl font-bold text-gray-900">Keranjang Belanja</h1>

                    {items.length === 0 ? (
                        <div className="rounded-lg bg-white p-12 text-center">
                            <svg className="mx-auto mb-4 h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p className="mb-4 text-gray-600">Keranjang masih kosong</p>
                            <Link
                                href={route('products.index')}
                                className="inline-block rounded-lg bg-rose-600 px-6 py-2 text-white hover:bg-rose-700"
                            >
                                Mulai Belanja
                            </Link>
                        </div>
                    ) : (
                        <>
                            {items.map((item) => (
                                <div key={item.id} className="mb-6 rounded-lg bg-white p-4 shadow-sm">
                                    <div className="flex gap-3">
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
                                            <p className="text-sm font-semibold text-rose-600">
                                                Rp {parseInt(item.price_per_day).toLocaleString('id-ID')}/hari
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
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))}

                            <div className="mb-6 rounded-lg bg-white p-4 shadow-sm">
                                <h2 className="mb-4 font-semibold text-gray-900">Data Diri</h2>
                                <div className="space-y-3">
                                    <div>
                                        <label className="mb-1 block text-sm font-medium text-gray-700">
                                            Nama (sesuai KTP) <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            value={customerName}
                                            onChange={(e) => setCustomerName(e.target.value)}
                                            className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label className="mb-1 block text-sm font-medium text-gray-700">Instagram</label>
                                        <input
                                            type="text"
                                            value={instagram}
                                            onChange={(e) => setInstagram(e.target.value)}
                                            className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                            placeholder="@username"
                                        />
                                    </div>
                                    <div>
                                        <label className="mb-1 block text-sm font-medium text-gray-700">
                                            No. Telepon <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="tel"
                                            value={phone}
                                            onChange={(e) => setPhone(e.target.value)}
                                            className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label className="mb-1 block text-sm font-medium text-gray-700">
                                            Alamat Lengkap <span className="text-red-500">*</span>
                                        </label>
                                        <textarea
                                            value={address}
                                            onChange={(e) => setAddress(e.target.value)}
                                            rows="3"
                                            className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                            required
                                        />
                                    </div>
                                </div>
                            </div>

                            <div className="mb-6 rounded-lg bg-white p-4 shadow-sm">
                                <div className="flex items-center justify-between">
                                    <h2 className="font-semibold text-gray-900">Pengiriman via Kurir</h2>
                                    <button
                                        onClick={() => setShowShippingForm(!showShippingForm)}
                                        className="text-sm text-rose-600 hover:underline"
                                    >
                                        {showShippingForm ? 'Sembunyikan' : 'Atur Pengiriman'}
                                    </button>
                                </div>
                                {showShippingForm && (
                                    <div className="mt-4 space-y-3">
                                        <div>
                                            <label className="mb-1 block text-sm font-medium text-gray-700">Alamat Pengiriman</label>
                                            <textarea
                                                value={shippingAddress}
                                                onChange={(e) => setShippingAddress(e.target.value)}
                                                rows="2"
                                                className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                                placeholder="Jl. Contoh No. 123"
                                            />
                                        </div>
                                        <div className="grid grid-cols-2 gap-3">
                                            <div>
                                                <label className="mb-1 block text-sm font-medium text-gray-700">Provinsi</label>
                                                <input
                                                    type="text"
                                                    value={province}
                                                    onChange={(e) => setProvince(e.target.value)}
                                                    className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                                    placeholder="DKI Jakarta"
                                                />
                                            </div>
                                            <div>
                                                <label className="mb-1 block text-sm font-medium text-gray-700">Kota</label>
                                                <input
                                                    type="text"
                                                    value={city}
                                                    onChange={(e) => setCity(e.target.value)}
                                                    className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                                    placeholder="Jakarta Selatan"
                                                />
                                            </div>
                                            <div>
                                                <label className="mb-1 block text-sm font-medium text-gray-700">Kecamatan</label>
                                                <input
                                                    type="text"
                                                    value={district}
                                                    onChange={(e) => setDistrict(e.target.value)}
                                                    className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                                />
                                            </div>
                                            <div>
                                                <label className="mb-1 block text-sm font-medium text-gray-700">Kelurahan</label>
                                                <input
                                                    type="text"
                                                    value={suburb}
                                                    onChange={(e) => setSuburb(e.target.value)}
                                                    className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                                />
                                            </div>
                                        </div>
                                        <div>
                                            <label className="mb-1 block text-sm font-medium text-gray-700">Kode Pos</label>
                                            <input
                                                type="text"
                                                value={postalCode}
                                                onChange={(e) => setPostalCode(e.target.value)}
                                                className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                                placeholder="12345"
                                            />
                                        </div>

                                        {loadingRates && (
                                            <p className="text-sm text-gray-500">Memuat tarif pengiriman...</p>
                                        )}

                                        {rates.length > 0 && !loadingRates && (
                                            <div>
                                                <label className="mb-2 block text-sm font-medium text-gray-700">Pilih Kurir</label>
                                                <div className="space-y-2">
                                                    {rates.map((rate, idx) => (
                                                        <label
                                                            key={idx}
                                                            className={`flex cursor-pointer items-center justify-between rounded-lg border p-3 ${
                                                                shippingCourier === rate.courier_code && shippingService === rate.service
                                                                    ? 'border-rose-500 bg-rose-50'
                                                                    : 'border-gray-200 hover:border-gray-300'
                                                            }`}
                                                        >
                                                            <div className="flex items-center gap-3">
                                                                <input
                                                                    type="radio"
                                                                    name="courier"
                                                                    checked={shippingCourier === rate.courier_code && shippingService === rate.service}
                                                                    onChange={() => selectRate(rate)}
                                                                    className="h-4 w-4 text-rose-600"
                                                                />
                                                                <div>
                                                                    <p className="font-medium text-gray-900">{rate.courier_name}</p>
                                                                    <p className="text-sm text-gray-500">{rate.service} {rate.etd ? `(${rate.etd})` : ''}</p>
                                                                </div>
                                                            </div>
                                                            <p className="font-semibold text-gray-900">
                                                                Rp {rate.price.toLocaleString('id-ID')}
                                                            </p>
                                                        </label>
                                                    ))}
                                                </div>
                                            </div>
                                        )}

                                        <p className="text-xs text-gray-400">
                                            Produk dikirim dari gudang BundaGaya (BSD City, Tangerang Selatan).
                                        </p>
                                    </div>
                                )}
                            </div>

                            <div className="mb-6 rounded-lg bg-white p-4 shadow-sm">
                                <h2 className="mb-4 font-semibold text-gray-900">Pengembalian Deposit</h2>
                                <p className="mb-3 text-sm text-gray-500">
                                    DP (security deposit) akan dikembalikan ke rekening Anda setelah pesanan selesai.
                                </p>
                                <div className="space-y-3">
                                    <div>
                                        <label className="mb-1 block text-sm font-medium text-gray-700">
                                            Nama Bank <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            value={refundBankName}
                                            onChange={(e) => setRefundBankName(e.target.value)}
                                            className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                            placeholder="BCA, Mandiri, BRI, dll"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label className="mb-1 block text-sm font-medium text-gray-700">
                                            No. Rekening <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            value={refundBankAccount}
                                            onChange={(e) => setRefundBankAccount(e.target.value)}
                                            className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                            placeholder="1234567890"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label className="mb-1 block text-sm font-medium text-gray-700">
                                            Nama Pemilik Rekening <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            value={refundBankHolder}
                                            onChange={(e) => setRefundBankHolder(e.target.value)}
                                            className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                            placeholder="Nama sesuai rekening"
                                            required
                                        />
                                    </div>
                                </div>
                            </div>

                            <div className="mb-6 rounded-lg bg-white p-4 shadow-sm">
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Catatan (Opsional)</label>
                                    <textarea
                                        value={notes}
                                        onChange={(e) => setNotes(e.target.value)}
                                        rows="2"
                                        className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                    />
                                </div>
                            </div>

                            <div className="mb-6 rounded-lg bg-white p-4 shadow-sm">
                                <div className="mb-3 flex justify-between text-sm">
                                    <span className="text-gray-600">Subtotal ({totalItems} barang)</span>
                                    <span className="font-medium">Rp {total.toLocaleString('id-ID')}</span>
                                </div>
                                <div className="mb-3 flex justify-between text-sm">
                                    <span className="text-gray-600">Biaya Admin</span>
                                    <span className="font-medium">Rp {adminFee.toLocaleString('id-ID')}</span>
                                </div>
                                {dpTotal > 0 && (
                                    <div className="mb-3 flex justify-between text-sm">
                                        <span className="text-gray-600">DP (Deposit)</span>
                                        <span className="font-medium text-amber-600">Rp {dpTotal.toLocaleString('id-ID')}</span>
                                    </div>
                                )}
                                {shippingCost > 0 && (
                                    <div className="mb-3 flex justify-between text-sm">
                                        <span className="text-gray-600">Ongkos Kirim</span>
                                        <span className="font-medium">Rp {shippingCost.toLocaleString('id-ID')}</span>
                                    </div>
                                )}
                                {returnDate && (
                                    <div className="mb-3 flex justify-between text-sm">
                                        <span className="text-gray-600">Tgl. Pengembalian</span>
                                        <span className="font-medium">{returnDate}</span>
                                    </div>
                                )}
                                <div className="border-t pt-3">
                                    <div className="flex justify-between">
                                        <span className="text-lg font-semibold text-gray-900">Total</span>
                                        <span className="text-lg font-bold text-rose-600">
                                            Rp {(total + adminFee + dpTotal + shippingCost).toLocaleString('id-ID')}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div className="flex gap-3">
                                <button
                                    onClick={handleClearCart}
                                    className="rounded-lg border border-gray-300 px-6 py-3 text-gray-700 hover:bg-gray-50"
                                >
                                    Kosongkan Keranjang
                                </button>
                                <button
                                    onClick={handleCheckout}
                                    disabled={processing || !address || !phone || !customerName || !refundBankName || !refundBankAccount || !refundBankHolder}
                                    className="flex-1 rounded-lg bg-rose-600 px-6 py-3 font-semibold text-white transition hover:bg-rose-700 disabled:opacity-50"
                                >
                                    {processing ? 'Memproses...' : 'Checkout'}
                                </button>
                            </div>
                        </>
                    )}
                </div>
            </div>
        </CustomerLayout>
    );
}
