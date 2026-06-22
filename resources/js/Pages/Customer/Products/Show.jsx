import CustomerLayout from '@/Layouts/CustomerLayout';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function Show({ product, relatedProducts, queryParams = {}, seo }) {
    const { auth } = usePage().props;
    const [selectedImage, setSelectedImage] = useState(0);
    const [startDate, setStartDate] = useState(queryParams?.start_date || '');
    const [endDate, setEndDate] = useState(queryParams?.end_date || '');
    const [quantity, setQuantity] = useState(1);
    const [stockAvailable, setStockAvailable] = useState(true);

    const { data, setData, post, processing, errors, reset } = useForm({
        product_id: product.id,
        start_date: '',
        end_date: '',
        quantity: 1,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('customer.cart.add'), {
            onSuccess: () => {
                reset();
            },
        });
    };

    const calculateDays = () => {
        if (!startDate || !endDate) return 0;
        const start = new Date(startDate);
        const end = new Date(endDate);
        const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
        return diff > 0 ? diff : 0;
    };

    const days = calculateDays();
    const totalPrice = days * parseInt(product.price_per_day) * quantity;

    const photos = product.photos?.length > 0 ? product.photos : [{ photo_path: null }];

    const jsonLd = {
        '@context': 'https://schema.org',
        '@type': 'Product',
        name: product.name,
        description: product.description || `Sewa ${product.name}`,
        image: photos.filter(p => p.photo_path).map(p => `/storage/${p.photo_path}`),
        brand: product.brand ? { '@type': 'Brand', name: product.brand.name } : undefined,
        offers: {
            '@type': 'Offer',
            url: route('products.show', product.slug),
            priceCurrency: 'IDR',
            price: product.price_per_day,
            availability: product.stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        },
    };

    return (
        <CustomerLayout>
            <Head title={seo?.title || product.name}>
                <script type="application/ld+json">{JSON.stringify(jsonLd)}</script>
            </Head>
            
            <div className="px-4 py-6">
                <div className="mx-auto max-w-7xl">
                    <Link href={route('products.index')} className="mb-4 inline-flex items-center text-sm text-gray-600 hover:text-rose-600">
                        <svg className="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali ke Produk
                    </Link>

                    <div className="grid gap-6 lg:grid-cols-2">
                        <div>
                            <div className="mb-4 overflow-hidden rounded-xl bg-gray-100">
                                {photos[selectedImage]?.photo_path ? (
                                    <img
                                        src={`/storage/${photos[selectedImage].photo_path}`}
                                        alt={product.name}
                                        width={600}
                                        height={600}
                                        className="aspect-square w-full object-cover"
                                    />
                                ) : (
                                    <div className="flex aspect-square items-center justify-center text-gray-400">
                                        <svg className="h-24 w-24" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                )}
                            </div>
                            {photos.length > 1 && (
                                <div className="grid grid-cols-4 gap-2">
                                    {photos.map((photo, index) => (
                                        <button
                                            key={index}
                                            onClick={() => setSelectedImage(index)}
                                            className={`aspect-square overflow-hidden rounded-lg border-2 ${
                                                selectedImage === index ? 'border-rose-600' : 'border-transparent'
                                            }`}
                                            aria-label={`Lihat foto ${index + 1}`}
                                        >
                                            {photo.photo_path ? (
                                                <img
                                                    src={`/storage/${photo.photo_path}`}
                                                    alt={`${product.name} - foto ${index + 1}`}
                                                    width={150}
                                                    height={150}
                                                    loading="lazy"
                                                    className="h-full w-full object-cover"
                                                />
                                            ) : (
                                                <div className="flex h-full items-center justify-center bg-gray-100 text-gray-400">
                                                    <svg className="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            )}
                                        </button>
                                    ))}
                                </div>
                            )}
                        </div>

                        <div>
                            <div className="mb-4">
                                {product.brand && (
                                    <p className="mb-1 text-sm text-gray-500">{product.brand.name}</p>
                                )}
                                <h1 className="mb-2 text-2xl font-bold text-gray-900">{product.name}</h1>
                                <div className="mb-4 flex items-center">
                                    {product.rating_average > 0 ? (
                                        <div className="flex items-center text-sm text-gray-600">
                                            <svg className="mr-1 h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            {product.rating_average.toFixed(1)} ({product.rating_count} ulasan)
                                        </div>
                                    ) : (
                                        <p className="text-sm text-gray-500">Belum ada ulasan</p>
                                    )}
                                </div>
                                <p className="mb-4 text-3xl font-bold text-rose-600">
                                    Rp {parseInt(product.price_per_day).toLocaleString('id-ID')}
                                    <span className="text-base font-normal text-gray-500">/hari</span>
                                </p>
                            </div>

                            <div className="mb-6 rounded-xl bg-gray-50 p-4">
                                <h2 className="mb-3 text-sm font-semibold text-gray-900">Detail Produk</h2>
                                <div className="grid grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <span className="text-gray-500">Kategori:</span>
                                        <p className="font-medium text-gray-900">{product.category?.name}</p>
                                    </div>
                                    <div>
                                        <span className="text-gray-500">Stok:</span>
                                        <p className="font-medium text-gray-900">{product.stock} tersedia</p>
                                    </div>
                                    {product.size && (
                                        <div>
                                            <span className="text-gray-500">Ukuran:</span>
                                            <p className="font-medium text-gray-900">{product.size}</p>
                                        </div>
                                    )}
                                    {product.color && (
                                        <div>
                                            <span className="text-gray-500">Warna:</span>
                                            <p className="font-medium text-gray-900">{product.color}</p>
                                        </div>
                                    )}
                                    {product.material && (
                                        <div>
                                            <span className="text-gray-500">Bahan:</span>
                                            <p className="font-medium text-gray-900">{product.material}</p>
                                        </div>
                                    )}
                                    <div>
                                        <span className="text-gray-500">Kondisi:</span>
                                        <p className="font-medium capitalize text-gray-900">{product.condition}</p>
                                    </div>
                                </div>
                            </div>

                            <div className="mb-6">
                                <h2 className="mb-2 text-sm font-semibold text-gray-900">Deskripsi</h2>
                                <p className="text-sm text-gray-600">{product.description || 'Tidak ada deskripsi.'}</p>
                            </div>

                            <form onSubmit={handleSubmit} className="space-y-4">
                                <div>
                                    <label htmlFor="start-date" className="mb-2 block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                                    <input
                                        id="start-date"
                                        type="date"
                                        value={startDate}
                                        onChange={(e) => {
                                            setStartDate(e.target.value);
                                            setData('start_date', e.target.value);
                                            setStockAvailable(product.stock > 0);
                                        }}
                                        min={new Date().toISOString().split('T')[0]}
                                        className="w-full rounded-xl border-gray-300 shadow-sm transition-colors focus:border-rose-500 focus:ring-rose-500"
                                        required
                                    />
                                </div>
                                <div>
                                    <label htmlFor="end-date" className="mb-2 block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                                    <input
                                        id="end-date"
                                        type="date"
                                        value={endDate}
                                        onChange={(e) => {
                                            setEndDate(e.target.value);
                                            setData('end_date', e.target.value);
                                            setStockAvailable(product.stock > 0);
                                        }}
                                        min={startDate || new Date().toISOString().split('T')[0]}
                                        className="w-full rounded-xl border-gray-300 shadow-sm transition-colors focus:border-rose-500 focus:ring-rose-500"
                                        required
                                    />
                                </div>

                                {startDate && endDate && (
                                    <div className={`rounded-xl p-3 text-sm ${stockAvailable ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'}`}>
                                        {stockAvailable
                                            ? `✓ Tersedia untuk tanggal ${new Date(startDate).toLocaleDateString('id-ID')} - ${new Date(endDate).toLocaleDateString('id-ID')}`
                                            : '✗ Stok tidak mencukupi untuk tanggal yang dipilih'}
                                    </div>
                                )}

                                <div>
                                    <label htmlFor="quantity" className="mb-2 block text-sm font-medium text-gray-700">Jumlah</label>
                                    <input
                                        id="quantity"
                                        type="number"
                                        value={quantity}
                                        onChange={(e) => {
                                            const val = parseInt(e.target.value);
                                            setQuantity(val);
                                            setData('quantity', val);
                                        }}
                                        min="1"
                                        max={product.stock}
                                        className="w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                        required
                                    />
                                </div>

                                {days > 0 && (
                                    <div className="rounded-xl bg-rose-50 p-3">
                                        <div className="flex justify-between text-sm">
                                            <span>Rp {parseInt(product.price_per_day).toLocaleString('id-ID')} x {days} hari x {quantity}</span>
                                            <span className="font-semibold text-rose-600">Rp {totalPrice.toLocaleString('id-ID')}</span>
                                        </div>
                                    </div>
                                )}

                                {errors.start_date && <p className="text-sm text-red-600">{errors.start_date}</p>}
                                {errors.end_date && <p className="text-sm text-red-600">{errors.end_date}</p>}

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full rounded-xl bg-rose-600 px-6 py-3 font-semibold text-white transition hover:bg-rose-700 disabled:opacity-50"
                                >
                                    {processing ? 'Menambahkan...' : 'Tambah ke Keranjang'}
                                </button>
                            </form>

                        </div>
                    </div>

                    {relatedProducts.length > 0 && (
                        <div className="mt-12">
                            <h2 className="mb-4 text-xl font-bold text-gray-900">Produk Serupa</h2>
                            <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                {relatedProducts.map((related) => (
                                    <Link
                                        key={related.id}
                                        href={route('products.show', related.slug)}
                                        className="overflow-hidden rounded-xl bg-white shadow-sm transition hover:shadow-md"
                                    >
                                        <div className="aspect-square bg-gray-100">
                                            {related.primary_photo?.photo_path ? (
                                                <img
                                                    src={`/storage/${related.primary_photo.photo_path}`}
                                                    alt={related.name}
                                                    width={400}
                                                    height={400}
                                                    loading="lazy"
                                                    className="h-full w-full object-cover"
                                                />
                                            ) : (
                                                <div className="flex h-full items-center justify-center text-gray-400">
                                                    <svg className="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            )}
                                        </div>
                                        <div className="p-3">
                                            <h3 className="mb-1 line-clamp-2 text-sm font-medium text-gray-900">{related.name}</h3>
                                            <p className="text-sm font-bold text-rose-600">
                                                Rp {parseInt(related.price_per_day).toLocaleString('id-ID')}
                                                <span className="text-xs font-normal text-gray-500">/hari</span>
                                            </p>
                                        </div>
                                    </Link>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </CustomerLayout>
    );
}
