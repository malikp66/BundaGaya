import CustomerLayout from '@/Layouts/CustomerLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState, useEffect, useRef } from 'react';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/Select';

export default function Index({ products, categories, brands, filters, seo }) {
    const [search, setSearch] = useState(filters?.search || '');
    const [selectedCategory, setSelectedCategory] = useState(filters?.category_id || '');
    const [selectedBrand, setSelectedBrand] = useState(filters?.brand_id || '');
    const [minPrice, setMinPrice] = useState(filters?.min_price || '');
    const [maxPrice, setMaxPrice] = useState(filters?.max_price || '');
    const [showFilters, setShowFilters] = useState(false);
    const [processing, setProcessing] = useState(false);
    const debounceRef = useRef(null);
    const isFirstRender = useRef(true);

    const startDate = filters?.start_date;
    const endDate = filters?.end_date;

    const applyFilters = (overrides = {}) => {
        setProcessing(true);
        const params = {};
        const s = overrides.search !== undefined ? overrides.search : search;
        const cat = overrides.category_id !== undefined ? overrides.category_id : selectedCategory;
        const brand = overrides.brand_id !== undefined ? overrides.brand_id : selectedBrand;
        const minP = overrides.min_price !== undefined ? overrides.min_price : minPrice;
        const maxP = overrides.max_price !== undefined ? overrides.max_price : maxPrice;
        if (s) params.search = s;
        if (cat) params.category_id = cat;
        if (brand) params.brand_id = brand;
        if (minP) params.min_price = minP;
        if (maxP) params.max_price = maxP;
        if (startDate) params.start_date = startDate;
        if (endDate) params.end_date = endDate;

        router.get(route('products.index'), params, {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }
        if (debounceRef.current) clearTimeout(debounceRef.current);
        debounceRef.current = setTimeout(() => {
            applyFilters();
        }, 500);
        return () => {
            if (debounceRef.current) clearTimeout(debounceRef.current);
        };
    }, [search, selectedCategory, selectedBrand, minPrice, maxPrice]);

    const handleSearch = (e) => {
        e.preventDefault();
        if (debounceRef.current) clearTimeout(debounceRef.current);
        applyFilters();
    };

    const clearFilters = () => {
        if (debounceRef.current) clearTimeout(debounceRef.current);
        setSearch('');
        setSelectedCategory('');
        setSelectedBrand('');
        setMinPrice('');
        setMaxPrice('');
        setProcessing(true);
        const params = {};
        if (startDate) params.start_date = startDate;
        if (endDate) params.end_date = endDate;
        router.get(route('products.index'), params, {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <CustomerLayout>
            <Head title={seo?.title || 'Jual Sewa Baju & Aksesoris Kondangan'} />
            
            <div className="px-4 py-6">
                <div className="mx-auto max-w-7xl">
                    {startDate && endDate ? (
                        <div className="mb-4">
                            <h1 className="text-2xl font-bold text-gray-900">Produk Tersedia</h1>
                            <p className="text-sm text-gray-500">
                                {new Date(startDate).toLocaleDateString('id-ID')} - {new Date(endDate).toLocaleDateString('id-ID')} ({filters.duration || ''} hari)
                            </p>
                        </div>
                    ) : (
                        <h1 className="mb-4 text-2xl font-bold text-gray-900">Jual Sewa Baju & Aksesoris Kondangan</h1>
                    )}
                    
                    <form onSubmit={handleSearch} className="mb-4">
                        <div className="flex gap-2">
                            <label htmlFor="product-search" className="sr-only">Cari produk</label>
                            <input
                                id="product-search"
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Cari gaun, kebaya, tas..."
                                className="flex-1 rounded-xl border-gray-300 shadow-sm transition-colors focus:border-rose-500 focus:ring-rose-500"
                            />
                            <button
                                type="submit"
                                className="rounded-xl bg-rose-600 px-4 py-2.5 text-white hover:bg-rose-700"
                                aria-label="Cari produk"
                            >
                                <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </button>
                            <button
                                type="button"
                                onClick={() => setShowFilters(!showFilters)}
                                className="rounded-xl border border-gray-300 px-4 py-2.5 text-gray-700 hover:bg-gray-50"
                                aria-label="Filter produk"
                                aria-expanded={showFilters}
                            >
                                <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                            </button>
                        </div>
                    </form>

                    {showFilters && (
                        <div className="mb-6 rounded-xl bg-white p-4 shadow-sm">
                            <div className="mb-4">
                                <label htmlFor="filter-category" className="mb-2 block text-sm font-medium text-gray-700">Kategori</label>
                                <Select value={selectedCategory} onValueChange={setSelectedCategory}>
                                    <SelectTrigger id="filter-category" className="w-full">
                                        <SelectValue placeholder="Semua Kategori" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Semua Kategori</SelectItem>
                                        {categories.map((cat) => (
                                            <SelectItem key={cat.id} value={String(cat.id)}>{cat.name}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="mb-4">
                                <label htmlFor="filter-brand" className="mb-2 block text-sm font-medium text-gray-700">Brand</label>
                                <Select value={selectedBrand} onValueChange={setSelectedBrand}>
                                    <SelectTrigger id="filter-brand" className="w-full">
                                        <SelectValue placeholder="Semua Brand" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Semua Brand</SelectItem>
                                        {brands.map((brand) => (
                                            <SelectItem key={brand.id} value={String(brand.id)}>{brand.name}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="mb-4 grid grid-cols-2 gap-3">
                                <div>
                                    <label className="mb-2 block text-sm font-medium text-gray-700">Harga Min</label>
                                    <input
                                        type="number"
                                        value={minPrice}
                                        onChange={(e) => setMinPrice(e.target.value)}
                                        placeholder="Rp 0"
                                        className="w-full rounded-xl border-gray-300 shadow-sm transition-colors focus:border-rose-500 focus:ring-rose-500"
                                    />
                                </div>
                                <div>
                                    <label className="mb-2 block text-sm font-medium text-gray-700">Harga Max</label>
                                    <input
                                        type="number"
                                        value={maxPrice}
                                        onChange={(e) => setMaxPrice(e.target.value)}
                                        placeholder="Rp 1.000.000"
                                        className="w-full rounded-xl border-gray-300 shadow-sm transition-colors focus:border-rose-500 focus:ring-rose-500"
                                    />
                                </div>
                            </div>
                            <div className="flex gap-2">
                                <button
                                    onClick={() => { if (debounceRef.current) clearTimeout(debounceRef.current); applyFilters(); }}
                                    className="flex-1 rounded-xl bg-rose-600 px-4 py-2 text-white hover:bg-rose-700"
                                >
                                    Terapkan Filter
                                </button>
                                <button
                                    onClick={clearFilters}
                                    className="rounded-xl border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50"
                                >
                                    Reset Filter
                                </button>
                            </div>
                        </div>
                    )}

                    <div className="mb-4 text-sm text-gray-600">
                        {products.total} produk ditemukan
                    </div>

                    <div className="relative">
                        {processing && (
                            <div className="absolute inset-0 z-10 flex items-center justify-center rounded-xl bg-white/70" aria-hidden="true">
                                <div className="flex items-center gap-2 text-rose-600">
                                    <svg className="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    <span className="text-sm font-medium">Mencari...</span>
                                </div>
                            </div>
                        )}
                        <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                            {products.data.map((product) => {
                                const available = product.available_qty > 0;
                                return (
                                    <Link
                                        key={product.id}
                                        href={route('products.show', product.slug) + (startDate ? `?start_date=${startDate}&end_date=${endDate}` : '')}
                                        className={`overflow-hidden rounded-xl bg-white shadow-sm transition hover:shadow-md ${
                                            !available ? 'opacity-55 grayscale' : ''
                                        }`}
                                    >
                                        <div className="relative aspect-square bg-gray-100">
                                            {product.primary_photo?.photo_path ? (
                                                <img
                                                    src={`/storage/${product.primary_photo.photo_path}`}
                                                    alt={product.name}
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
                                            <div className="absolute left-2 top-2">
                                                {available ? (
                                                    <span className="inline-flex items-center gap-1 rounded-full bg-green-500 px-2.5 py-1 text-xs font-medium text-white shadow">
                                                        <span className="h-1.5 w-1.5 rounded-full bg-white" />
                                                        Tersedia
                                                    </span>
                                                ) : (
                                                    <span className="inline-flex items-center gap-1 rounded-full bg-red-500 px-2.5 py-1 text-xs font-medium text-white shadow">
                                                        <span className="h-1.5 w-1.5 rounded-full bg-white" />
                                                        Tidak Tersedia
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                        <div className="p-3">
                                            <h2 className="mb-1 line-clamp-2 text-sm font-medium text-gray-900">{product.name}</h2>
                                            {product.brand && (
                                                <p className="mb-1 text-xs text-gray-500">{product.brand.name}</p>
                                            )}
                                            <p className="text-sm font-bold text-rose-600">
                                                Rp {parseInt(product.price_per_day).toLocaleString('id-ID')}
                                                <span className="text-xs font-normal text-gray-500">/hari</span>
                                            </p>
                                            {product.rating_average > 0 && (
                                                <div className="mt-1 flex items-center text-xs text-gray-600">
                                                    <svg className="mr-1 h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                    {product.rating_average.toFixed(1)} ({product.rating_count})
                                                </div>
                                            )}
                                        </div>
                                    </Link>
                                );
                            })}
                        </div>
                    </div>

                    {products.links && products.links.length > 3 && (
                        <nav className="mt-6 flex justify-center gap-1" aria-label="Navigasi halaman">
                            {products.links.map((link, index) => (
                                <Link
                                    key={index}
                                    href={link.url}
                                    className={`rounded-lg px-3 py-2 text-sm ${
                                        link.active
                                            ? 'bg-rose-600 text-white'
                                            : 'bg-white text-gray-700 hover:bg-gray-50'
                                    }`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </nav>
                    )}
                </div>
            </div>
        </CustomerLayout>
    );
}
