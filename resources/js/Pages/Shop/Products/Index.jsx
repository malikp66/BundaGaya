import ShopLayout from '@/Layouts/ShopLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ products, shop }) {
    const handleDelete = (productId) => {
        if (confirm('Are you sure you want to delete this product?')) {
            router.delete(route('shop.products.destroy', productId));
        }
    };

    return (
        <ShopLayout shop={shop}>
            <Head title="My Products" />
            
            <div className="mb-6 flex items-center justify-between">
                <h1 className="text-2xl font-bold text-gray-900">My Products</h1>
                <Link
                    href={route('shop.products.create')}
                    className="rounded-lg bg-rose-600 px-4 py-2 text-white hover:bg-rose-700"
                >
                    Add Product
                </Link>
            </div>

            {products.data.length === 0 ? (
                <div className="rounded-lg bg-white p-12 text-center">
                    <svg className="mx-auto mb-4 h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <p className="mb-4 text-gray-600">No products yet</p>
                    <Link
                        href={route('shop.products.create')}
                        className="inline-block rounded-lg bg-rose-600 px-6 py-2 text-white hover:bg-rose-700"
                    >
                        Add Your First Product
                    </Link>
                </div>
            ) : (
                <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    {products.data.map((product) => (
                        <div key={product.id} className="overflow-hidden rounded-lg bg-white shadow-sm">
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
                                <p className="mb-2 text-sm font-bold text-rose-600">
                                    Rp {parseInt(product.price_per_day).toLocaleString('id-ID')}
                                    <span className="text-xs font-normal text-gray-500">/hari</span>
                                </p>
                                <div className="mb-2 flex items-center justify-between text-xs">
                                    <span className="text-gray-500">Stock: {product.stock}</span>
                                    <span className={`rounded-full px-2 py-1 ${
                                        product.status === 'active' ? 'bg-green-100 text-green-800' :
                                        product.status === 'draft' ? 'bg-yellow-100 text-yellow-800' :
                                        'bg-red-100 text-red-800'
                                    }`}>
                                        {product.status}
                                    </span>
                                </div>
                                <div className="flex gap-2">
                                    <Link
                                        href={route('shop.products.edit', product.id)}
                                        className="flex-1 rounded border border-gray-300 px-2 py-1 text-center text-xs hover:bg-gray-50"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        onClick={() => handleDelete(product.id)}
                                        className="rounded border border-red-300 px-2 py-1 text-xs text-red-600 hover:bg-red-50"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            )}

            {products.links && products.links.length > 3 && (
                <div className="mt-6 flex justify-center gap-1">
                    {products.links.map((link, index) => (
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
        </ShopLayout>
    );
}
