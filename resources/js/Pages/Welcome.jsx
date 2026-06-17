import CustomerLayout from '@/Layouts/CustomerLayout';
import { Head, Link } from '@inertiajs/react';

export default function Welcome({ auth, categories = [], featuredProducts = [] }) {
    return (
        <CustomerLayout>
            <Head title="BundaGaya - Sewa Baju Kondangan" />
            
            <section className="bg-gradient-to-r from-rose-500 to-pink-600 px-4 py-12 text-white">
                <div className="mx-auto max-w-7xl text-center">
                    <h1 className="mb-4 text-3xl font-bold md:text-5xl">
                        Tampil Elegan di Setiap Kondangan
                    </h1>
                    <p className="mb-6 text-lg opacity-90">
                        Sewa baju branded dengan harga terjangkau
                    </p>
                    <Link
                        href={route('products.index')}
                        className="inline-block rounded-full bg-white px-8 py-3 font-semibold text-rose-600 shadow-lg transition hover:bg-gray-100"
                    >
                        Mulai Belanja
                    </Link>
                </div>
            </section>

            <section className="px-4 py-8">
                <div className="mx-auto max-w-7xl">
                    <h2 className="mb-4 text-xl font-bold text-gray-900">Kategori</h2>
                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        {categories.slice(0, 8).map((category) => (
                            <Link
                                key={category.id}
                                href={`${route('products.index')}?category_id=${category.id}`}
                                className="flex flex-col items-center rounded-xl bg-white p-4 shadow-sm transition hover:shadow-md"
                            >
                                <div className="mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                                    <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <span className="text-center text-sm font-medium text-gray-700">{category.name}</span>
                            </Link>
                        ))}
                    </div>
                </div>
            </section>

            <section className="px-4 py-8">
                <div className="mx-auto max-w-7xl">
                    <h2 className="mb-4 text-xl font-bold text-gray-900">Produk Unggulan</h2>
                    <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        {featuredProducts.slice(0, 8).map((product) => (
                            <Link
                                key={product.id}
                                href={route('products.show', product.slug)}
                                className="overflow-hidden rounded-xl bg-white shadow-sm transition hover:shadow-md"
                            >
                                <div className="aspect-square bg-gray-100">
                                    {product.primary_photo?.photo_path ? (
                                        <img
                                            src={`/storage/${product.primary_photo.photo_path}`}
                                            alt={product.name}
                                            className="h-full w-full object-cover"
                                        />
                                    ) : (
                                        <div className="flex h-full items-center justify-center text-gray-400">
                                            <svg className="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    )}
                                </div>
                                <div className="p-3">
                                    <h3 className="mb-1 line-clamp-2 text-sm font-medium text-gray-900">{product.name}</h3>
                                    {product.brand && (
                                        <p className="mb-1 text-xs text-gray-500">{product.brand.name}</p>
                                    )}
                                    <p className="text-sm font-bold text-rose-600">
                                        Rp {parseInt(product.price_per_day).toLocaleString('id-ID')}
                                        <span className="text-xs font-normal text-gray-500">/hari</span>
                                    </p>
                                </div>
                            </Link>
                        ))}
                    </div>
                </div>
            </section>

            <section className="bg-white px-4 py-8">
                <div className="mx-auto max-w-7xl text-center">
                    <h2 className="mb-4 text-xl font-bold text-gray-900">Kenapa BundaGaya?</h2>
                    <div className="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <div className="rounded-xl p-6">
                            <div className="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                                <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <h3 className="mb-2 font-semibold text-gray-900">Kualitas Terjamin</h3>
                            <p className="text-sm text-gray-600">Semua produk melalui quality check</p>
                        </div>
                        <div className="rounded-xl p-6">
                            <div className="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                                <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 className="mb-2 font-semibold text-gray-900">Harga Terjangkau</h3>
                            <p className="text-sm text-gray-600">Sewa mulai dari Rp 50.000/hari</p>
                        </div>
                        <div className="rounded-xl p-6">
                            <div className="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                                <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <h3 className="mb-2 font-semibold text-gray-900">Proses Cepat</h3>
                            <p className="text-sm text-gray-600">Booking online, ambil di toko</p>
                        </div>
                    </div>
                </div>
            </section>
        </CustomerLayout>
    );
}
