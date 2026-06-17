import ShopLayout from '@/Layouts/ShopLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Create({ categories, brands }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        category_id: '',
        brand_id: '',
        description: '',
        price_per_day: '',
        stock: 1,
        size: '',
        color: '',
        material: '',
        condition: 'good',
        status: 'draft',
        is_featured: false,
        photos: [],
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('shop.products.store'));
    };

    return (
        <ShopLayout>
            <Head title="Add Product" />
            
            <div className="mx-auto max-w-3xl">
                <Link href={route('shop.products.index')} className="mb-4 inline-flex items-center text-sm text-gray-600 hover:text-rose-600">
                    <svg className="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Products
                </Link>

                <h1 className="mb-6 text-2xl font-bold text-gray-900">Add New Product</h1>

                <form onSubmit={handleSubmit} className="space-y-6 rounded-lg bg-white p-6 shadow-sm">
                    <div>
                        <label className="mb-2 block text-sm font-medium text-gray-700">Product Name *</label>
                        <input
                            type="text"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                            required
                        />
                        {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Category *</label>
                            <select
                                value={data.category_id}
                                onChange={(e) => setData('category_id', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                required
                            >
                                <option value="">Select Category</option>
                                {categories.map((cat) => (
                                    <option key={cat.id} value={cat.id}>{cat.name}</option>
                                ))}
                            </select>
                            {errors.category_id && <p className="mt-1 text-sm text-red-600">{errors.category_id}</p>}
                        </div>
                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Brand</label>
                            <select
                                value={data.brand_id}
                                onChange={(e) => setData('brand_id', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                            >
                                <option value="">Select Brand</option>
                                {brands.map((brand) => (
                                    <option key={brand.id} value={brand.id}>{brand.name}</option>
                                ))}
                            </select>
                            {errors.brand_id && <p className="mt-1 text-sm text-red-600">{errors.brand_id}</p>}
                        </div>
                    </div>

                    <div>
                        <label className="mb-2 block text-sm font-medium text-gray-700">Description</label>
                        <textarea
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            rows="4"
                            className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                        />
                        {errors.description && <p className="mt-1 text-sm text-red-600">{errors.description}</p>}
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Price per Day (Rp) *</label>
                            <input
                                type="number"
                                value={data.price_per_day}
                                onChange={(e) => setData('price_per_day', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                required
                            />
                            {errors.price_per_day && <p className="mt-1 text-sm text-red-600">{errors.price_per_day}</p>}
                        </div>
                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Stock *</label>
                            <input
                                type="number"
                                value={data.stock}
                                onChange={(e) => setData('stock', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                required
                            />
                            {errors.stock && <p className="mt-1 text-sm text-red-600">{errors.stock}</p>}
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Size</label>
                            <input
                                type="text"
                                value={data.size}
                                onChange={(e) => setData('size', e.target.value)}
                                placeholder="e.g., S, M, L, XL"
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                            />
                            {errors.size && <p className="mt-1 text-sm text-red-600">{errors.size}</p>}
                        </div>
                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Color</label>
                            <input
                                type="text"
                                value={data.color}
                                onChange={(e) => setData('color', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                            />
                            {errors.color && <p className="mt-1 text-sm text-red-600">{errors.color}</p>}
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Material</label>
                            <input
                                type="text"
                                value={data.material}
                                onChange={(e) => setData('material', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                            />
                            {errors.material && <p className="mt-1 text-sm text-red-600">{errors.material}</p>}
                        </div>
                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Condition</label>
                            <select
                                value={data.condition}
                                onChange={(e) => setData('condition', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                            >
                                <option value="new">New</option>
                                <option value="good">Good</option>
                                <option value="fair">Fair</option>
                            </select>
                            {errors.condition && <p className="mt-1 text-sm text-red-600">{errors.condition}</p>}
                        </div>
                    </div>

                    <div>
                        <label className="mb-2 block text-sm font-medium text-gray-700">Product Photos</label>
                        <input
                            type="file"
                            multiple
                            accept="image/*"
                            onChange={(e) => setData('photos', Array.from(e.target.files))}
                            className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                        />
                        <p className="mt-1 text-xs text-gray-500">Upload up to 5 photos. First photo will be the main image.</p>
                        {errors.photos && <p className="mt-1 text-sm text-red-600">{errors.photos}</p>}
                    </div>

                    <div className="flex gap-3">
                        <button
                            type="submit"
                            name="status"
                            value="draft"
                            disabled={processing}
                            className="flex-1 rounded-lg border border-gray-300 px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-50 disabled:opacity-50"
                        >
                            Save as Draft
                        </button>
                        <button
                            type="submit"
                            name="status"
                            value="active"
                            disabled={processing}
                            className="flex-1 rounded-lg bg-rose-600 px-6 py-3 font-semibold text-white transition hover:bg-rose-700 disabled:opacity-50"
                        >
                            {processing ? 'Saving...' : 'Publish Product'}
                        </button>
                    </div>
                </form>
            </div>
        </ShopLayout>
    );
}
