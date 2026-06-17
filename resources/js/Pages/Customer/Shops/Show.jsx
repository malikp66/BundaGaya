import CustomerLayout from '@/Layouts/CustomerLayout';
import { Head, Link } from '@inertiajs/react';

export default function Show({ shop }) {
    const products = shop?.products || [];

    return (
        <CustomerLayout>
            <Head title={shop?.name || 'Shop'} />
            
            <div className="px-4 py-6">
                <div className="mx-auto max-w-7xl">
                    <div className="mb-6 overflow-hidden rounded-lg bg-white shadow-sm">
                        {shop?.banner ? (
                            <img
                                src={`/storage/${shop.banner}`}
                                alt={shop.name}
                                className="h-48 w-full object-cover"
                            />
                        ) : (
                            <div className="flex h-48 items-center justify-center bg-gradient-to-r from-rose-400 to-pink-500 text-white">
                                <h1 className="text-3xl font-bold">{shop?.name}</h1>
                            </div>
                        )}
                        <div className="p-6">
                            <div className="mb-4 flex items-start gap-4">
                                {shop?.logo ? (
                                    <img
                                        src={`/storage/${shop.logo}`}
                                        alt={shop.name}
                                        className="h-20 w-20 rounded-full object-cover"
                                    />
                                ) : (
                                    <div className="flex h-20 w-20 items-center justify-center rounded-full bg-rose-100 text-2xl font-bold text-rose-600">
                                        {shop?.name?.charAt(0)}
                                    </div>
                                )}
                                <div className="flex-1">
                                    <h1 className="text-2xl font-bold text-gray-900">{shop?.name}</h1>
                                    <p className="text-sm text-gray-500">{shop?.city}, {shop?.province}</p>
                                    {shop?.description && (
                                        <p className="mt-2 text-sm text-gray-600">{shop.description}</p>
                                    )}
                                </div>
                            </div>
                            <div className="grid grid-cols-3 gap-4 border-t pt-4 text-center">
                                <div>
                                    <p className="text-2xl font-bold text-gray-900">{products.length}</p>
                                    <p className="text-sm text-gray-500">Products</p>
                                </div>
                                <div>
                                    <p className="text-2xl font-bold text-gray-900">
                                        {shop?.is_verified ? '✓' : ''}
                                    </p>
                                    <p className="text-sm text-gray-500">Verified</p>
                                </div>
                                <div>
                                    <p className="text-2xl font-bold text-gray-900 capitalize">{shop?.status}</p>
                                    <p className="text-sm text-gray-500">Status</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h2 className="mb-4 text-xl font-bold text-gray-900">Products from {shop?.name}</h2>
                    
                    {products.length === 0 ? (
                        <div className="rounded-lg bg-white p-12 text-center">
                            <p className="text-gray-600">No products available yet</p>
                        </div>
                    ) : (
                        <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                            {products.map((product) => (
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
                    )}
                </div>
            </div>
        </CustomerLayout>
    );
}
