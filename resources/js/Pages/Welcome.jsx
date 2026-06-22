import CustomerLayout from '@/Layouts/CustomerLayout';
import Splashscreen from '@/Components/Splashscreen';
import { BentoGrid, BentoCard } from '@/Components/BentoGrid';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/Select';
import { Head, Link, router } from '@inertiajs/react';
import { useRef, useState, useEffect } from 'react';

const SPLASH_TTL_MS = 24 * 60 * 60 * 1000; // 24 hours

function shouldShowSplash() {
    if (typeof window === 'undefined') return false;
    try {
        const raw = localStorage.getItem('bundaGaya.splash.lastSeen');
        if (!raw) return true;
        const lastSeen = Number.parseInt(raw, 10);
        if (!Number.isFinite(lastSeen)) return true;
        return Date.now() - lastSeen > SPLASH_TTL_MS;
    } catch {
        return true;
    }
}

export default function Welcome({ auth, categories = [], featuredProducts = [], seo }) {
    const carouselRef = useRef(null);
    const [showSplash, setShowSplash] = useState(shouldShowSplash);

    useEffect(() => {
        if (!showSplash && typeof window !== 'undefined') {
            try {
                localStorage.setItem('bundaGaya.splash.lastSeen', String(Date.now()));
            } catch {
                /* localStorage may be disabled */
            }
        }
    }, [showSplash]);

    const [faqTab, setFaqTab] = useState('customer');
    const [expandedFaq, setExpandedFaq] = useState(null);

    // Booking widget state
    const today = new Date().toISOString().split('T')[0];
    const [bookDate, setBookDate] = useState(today);
    const [bookDuration, setBookDuration] = useState(1);

    const toggleFaq = (index) => {
        setExpandedFaq(expandedFaq === index ? null : index);
    };

    const handleTabChange = (tab) => {
        setFaqTab(tab);
        setExpandedFaq(null);
    };

    const scrollCarousel = (direction) => {
        if (carouselRef.current) {
            const scrollAmount = 280;
            carouselRef.current.scrollBy({
                left: direction === 'left' ? -scrollAmount : scrollAmount,
                behavior: 'smooth',
            });
        }
    };

    const handleBookingSearch = () => {
        const start = bookDate;
        const end = new Date(new Date(bookDate).getTime() + bookDuration * 86400000 - 86400000)
            .toISOString().split('T')[0];
        router.get(route('products.index'), {
            start_date: start,
            end_date: end,
            duration: bookDuration,
        });
    };

    return (
        <>
            {showSplash && <Splashscreen onComplete={() => setShowSplash(false)} />}
            <CustomerLayout>
            <Head title="BundaGaya - Sewa Baju Kondangan Branded" />

            <section className="relative overflow-hidden bg-rose-800 px-4 pb-20 pt-20 text-white">
                <img
                    src="/hero.webp"
                    alt=""
                    fetchpriority="high"
                    loading="eager"
                    className="absolute inset-0 h-full w-full object-cover"
                    width="1920"
                    height="1081"
                />
                <div className="absolute inset-0 bg-gradient-to-br from-rose-600/70 via-pink-600/60 to-rose-700/70" />
                <div className="pointer-events-none absolute inset-0">
                    <div className="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-2xl" />
                    <div className="absolute -bottom-20 -left-20 h-72 w-72 rounded-full bg-pink-400/20 blur-3xl" />
                    <div className="absolute right-1/4 top-1/3 h-32 w-32 rounded-full bg-amber-300/10 blur-2xl" />
                    <svg className="absolute inset-0 h-full w-full opacity-[0.03]" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="grid" width="32" height="32" patternUnits="userSpaceOnUse">
                                <path d="M 32 0 L 0 0 0 32" fill="none" stroke="white" strokeWidth="1" />
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#grid)" />
                    </svg>
                </div>

                <div className="relative z-10 mx-auto max-w-7xl">
                    <div className="mx-auto max-w-2xl text-center">
                        <div className="mb-4 inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-1.5 text-sm font-medium backdrop-blur-sm">
                            <span className="h-2 w-2 rounded-full bg-green-400 animate-pulse" />
                            Sewa Baju Kondangan #1
                        </div>
                        <h1 className="mb-5 text-5xl font-extrabold leading-tight tracking-tight md:text-6xl font-display">
                            Tampil Elegan
                            <br />
                            <span className="bg-gradient-to-r from-amber-200 to-yellow-100 bg-clip-text text-transparent">
                                di Setiap Acara
                            </span>
                        </h1>
                        <p className="mb-8 text-lg leading-relaxed text-rose-100 md:text-xl">
                            Sewa baju branded dengan harga terjangkau.
                            Ribuan koleksi dari desainer lokal ternama.
                        </p>

                        <div className="mx-auto mb-8 max-w-xl">
                            <div className="rounded-2xl border border-white/20 bg-white/10 p-4 backdrop-blur-xl md:p-6">
                                <div className="mb-4 flex items-center gap-2">
                                    <svg className="h-5 w-5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span className="text-sm font-medium text-white/90">Cek Jadwal Sewa</span>
                                </div>

                                <div className="mb-4 grid grid-cols-2 gap-3">
                                    <div>
                                        <label className="mb-1.5 block text-xs font-medium text-white/70">Tanggal Pakai</label>
                                        <input
                                            type="date"
                                            value={bookDate}
                                            onChange={(e) => setBookDate(e.target.value)}
                                            min={today}
                                            className="w-full rounded-xl border border-white/20 bg-white/10 px-3 py-2.5 text-sm text-white backdrop-blur-sm focus:border-amber-300 focus:outline-none focus:ring-1 focus:ring-amber-300 [color-scheme:dark]"
                                        />
                                    </div>
                                    <div>
                                        <label className="mb-1.5 block text-xs font-medium text-white/70">Durasi</label>
                                        <Select value={String(bookDuration)} onValueChange={(v) => setBookDuration(Number(v))}>
                                            <SelectTrigger
                                                className="w-full rounded-xl border border-white/20 bg-white/10 px-3 py-2.5 text-sm text-white backdrop-blur-sm focus:border-amber-300 focus:ring-1 focus:ring-amber-300 data-[placeholder]:text-white/60"
                                            >
                                                <SelectValue placeholder="Pilih durasi" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {Array.from({length: 7}, (_, i) => i + 1).map(d => (
                                                    <SelectItem key={d} value={String(d)}>{d} Hari</SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>

                                <button
                                    onClick={handleBookingSearch}
                                    className="w-full rounded-xl bg-amber-400 px-5 py-3 font-bold text-rose-900 shadow-lg shadow-amber-400/30 transition hover:bg-amber-300"
                                >
                                    Cari Produk Tersedia
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section className="px-4 py-10">
                <div className="mx-auto max-w-7xl">
                    <div className="mb-6 flex items-end justify-between">
                        <div>
                            <h2 className="text-2xl font-bold text-gray-900 font-display">Kategori</h2>
                            <p className="text-sm text-gray-500">Temukan gaya yang cocok untukmu</p>
                        </div>
                        <Link href={route('products.index')} className="text-sm font-medium text-rose-600 hover:text-rose-700">
                            Lihat Semua &rarr;
                        </Link>
                    </div>

                    {categories.length > 0 ? (
                        <BentoGrid>
                            {categories.slice(0, 7).map((category, index) => (
                                <BentoCard
                                    key={category.id}
                                    name={category.name}
                                    description={index === 0 ? "Koleksi terlengkap untuk setiap acara" : undefined}
                                    href={`${route('products.index')}?category_id=${category.id}`}
                                    cta="Jelajahi"
                                    Icon={PhotoIcon}
                                    className={
                                        index === 0
                                            ? 'col-span-2 bg-gradient-to-br from-rose-500 to-pink-600 text-white [box-shadow:none]'
                                            : index === 1
                                            ? 'bg-amber-50 text-gray-800'
                                            : index === 2
                                            ? 'bg-purple-50 text-gray-800'
                                            : index === 3
                                            ? 'bg-sky-50 text-gray-800'
                                            : index === 4
                                            ? 'bg-emerald-50 text-gray-800'
                                            : index === 5
                                            ? 'bg-orange-50 text-gray-800'
                                            : 'bg-rose-50 text-gray-800'
                                    }
                                    background={
                                        index === 0 ? (
                                            <div className="absolute -inset-4 bg-gradient-to-br from-white/5 to-transparent" />
                                        ) : (
                                            <div className="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-black/[0.02] blur-xl" />
                                        )
                                    }
                                />
                            ))}
                        </BentoGrid>
                    ) : (
                        <BentoGrid>
                            <BentoCard
                                name="Semua Kategori"
                                description="Jelajahi semua koleksi kami"
                                href={route('products.index')}
                                cta="Jelajahi"
                                Icon={PhotoIcon}
                                className="col-span-2 bg-gradient-to-br from-rose-500 to-pink-600 text-white [box-shadow:none]"
                                background={
                                    <div className="absolute -inset-4 bg-gradient-to-br from-white/5 to-transparent" />
                                }
                            />
                            <BentoCard
                                name="Kategori"
                                description="Segera hadir"
                                Icon={PhotoIcon}
                                className="bg-amber-50 text-gray-400"
                            />
                        </BentoGrid>
                    )}
                </div>
            </section>

            <section className="px-4 py-10">
                <div className="mx-auto max-w-7xl">
                    <div className="mb-6 flex items-end justify-between">
                        <div>
                            <h2 className="text-2xl font-bold text-gray-900 font-display">Produk Unggulan</h2>
                            <p className="text-sm text-gray-500">Pilihan terbaik untuk kondangan</p>
                        </div>
                        <div className="flex gap-2">
                            <button
                                onClick={() => scrollCarousel('left')}
                                className="flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 shadow-sm transition hover:border-rose-300 hover:text-rose-600"
                            >
                                <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <button
                                onClick={() => scrollCarousel('right')}
                                className="flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 shadow-sm transition hover:border-rose-300 hover:text-rose-600"
                            >
                                <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div
                        ref={carouselRef}
                        className="-mx-4 flex gap-4 overflow-x-auto scroll-smooth px-4 pb-4 snap-x snap-mandatory scrollbar-hide md:mx-0 md:px-0"
                        style={{ scrollbarWidth: 'none', msOverflowStyle: 'none' }}
                    >
                        {featuredProducts.length > 0 ? (
                            featuredProducts.slice(0, 12).map((product) => (
                                <Link
                                    key={product.id}
                                    href={route('products.show', product.slug)}
                                    className="group w-[240px] flex-shrink-0 snap-start overflow-hidden rounded-2xl bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg md:w-[260px]"
                                >
                                    <div className="relative aspect-[3/4] overflow-hidden bg-gray-100">
                                        {product.primary_photo?.photo_path ? (
                                            <img
                                                src={`/storage/${product.primary_photo.photo_path}`}
                                                alt={product.name}
                                                className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                            />
                                        ) : (
                                            <div className="flex h-full items-center justify-center bg-gradient-to-br from-rose-50 to-pink-100 text-rose-300">
                                                <svg className="h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        )}
                                        {product.brand && (
                                            <div className="absolute left-3 top-3 rounded-lg bg-white/90 px-2 py-1 text-xs font-medium text-gray-700 backdrop-blur-sm">
                                                {product.brand.name}
                                            </div>
                                        )}
                                    </div>
                                    <div className="p-3.5">
                                        <h3 className="mb-1.5 line-clamp-2 text-sm font-medium leading-snug text-gray-900">
                                            {product.name}
                                        </h3>
                                        <p className="text-base font-bold text-rose-600">
                                            Rp {parseInt(product.price_per_day).toLocaleString('id-ID')}
                                            <span className="text-xs font-normal text-gray-400">/hari</span>
                                        </p>
                                    </div>
                                </Link>
                            ))
                        ) : (
                            <div className="flex w-full items-center justify-center py-16 text-gray-400">
                                <p>Belum ada produk unggulan</p>
                            </div>
                        )}
                    </div>
                </div>
            </section>

            <section className="px-4 py-10">
                <div className="mx-auto max-w-7xl">
                    <div className="mb-8 text-center">
                        <h2 className="text-2xl font-bold text-gray-900 font-display">Kenapa BundaGaya?</h2>
                        <p className="mt-1 text-sm text-gray-500">Alasan pelanggan memilih kami</p>
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div className="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-50 to-pink-50 p-6 transition hover:shadow-md">
                            <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 transition group-hover:scale-110">
                                <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <h3 className="mb-1.5 font-display text-lg font-bold text-gray-900">Kualitas Terjamin</h3>
                            <p className="text-sm leading-relaxed text-gray-600">
                                Semua produk melalui quality check ketat sebelum disewakan
                            </p>
                        </div>

                        <div className="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-50 to-orange-50 p-6 transition hover:shadow-md">
                            <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-600 transition group-hover:scale-110">
                                <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 className="mb-1.5 font-display text-lg font-bold text-gray-900">Harga Terjangkau</h3>
                            <p className="text-sm leading-relaxed text-gray-600">
                                Sewa mulai dari Rp 50.000/hari. Tampil mewah tanpa boros
                            </p>
                        </div>

                        <div className="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-purple-50 to-indigo-50 p-6 transition hover:shadow-md">
                            <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-purple-100 text-purple-600 transition group-hover:scale-110">
                                <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <h3 className="mb-1.5 font-display text-lg font-bold text-gray-900">Proses Cepat</h3>
                            <p className="text-sm leading-relaxed text-gray-600">
                                Booking online, ambil di toko. Tidak perlu ribet
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {/* FAQ Section */}
            <section className="bg-gradient-to-b from-white to-rose-50/20 py-20 px-4 border-t border-gray-100">
                <div className="mx-auto max-w-4xl">
                    <div className="mb-12 text-center">
                        <span className="mb-3 inline-block rounded-full bg-rose-100 px-4 py-1 text-xs font-semibold text-rose-600 tracking-wider uppercase">
                            Pertanyaan Umum (FAQ)
                        </span>
                        <h2 className="text-2xl font-bold text-gray-900 md:text-3xl font-display">
                            Ada Pertanyaan? Kami Punya Jawabannya
                        </h2>
                        <p className="mt-3 text-sm text-gray-500 md:text-base">
                            Panduan lengkap dan praktis seputar sewa-menyewa baju kondangan di BundaGaya.
                        </p>
                    </div>

                    {/* Tab Switcher */}
                    <div className="mb-10 flex justify-center">
                        <div className="inline-flex rounded-2xl bg-gray-100 p-1.5 shadow-inner">
                            <button
                                onClick={() => handleTabChange('customer')}
                                className={`flex items-center gap-2 rounded-xl px-6 py-2.5 text-sm font-bold transition duration-200 ${
                                    faqTab === 'customer'
                                        ? 'bg-white text-rose-600 shadow-sm'
                                        : 'text-gray-500 hover:text-gray-900'
                                }`}
                            >
                                <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                Sebagai Penyewa
                            </button>
                        </div>
                    </div>

                    {/* Accordion Questions */}
                    <div className="space-y-4">
                        {faqData[faqTab].map((item, index) => {
                            const isOpen = expandedFaq === index;
                            return (
                                <div
                                    key={index}
                                    className={`group rounded-2xl border border-gray-100 bg-white transition-all duration-300 ${
                                        isOpen ? 'shadow-md border-rose-100 ring-1 ring-rose-100/50' : 'shadow-sm hover:border-gray-200'
                                    }`}
                                >
                                    <button
                                        onClick={() => toggleFaq(index)}
                                        className="flex w-full items-center justify-between px-6 py-5 text-left transition-colors duration-200"
                                    >
                                        <span className="text-base font-bold text-gray-900 group-hover:text-rose-600 transition-colors md:text-lg">
                                            {item.q}
                                        </span>
                                        <span className={`ml-4 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-600 transition-transform duration-300 ${isOpen ? 'rotate-180 bg-rose-600 text-white' : ''}`}>
                                            <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </span>
                                    </button>

                                    {/* Collapsible Panel */}
                                    <div
                                        className={`grid transition-all duration-300 ease-in-out ${
                                            isOpen ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'
                                        }`}
                                    >
                                        <div className="overflow-hidden">
                                            <div className="border-t border-rose-50/50 px-6 pb-6 pt-5 text-sm text-gray-600 leading-relaxed">
                                                {renderAnswer(item.a)}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </section>

            <section className="px-4 py-16">
                <div className="mx-auto max-w-7xl">
                    <div className="overflow-hidden rounded-3xl bg-gradient-to-r from-rose-600 to-pink-600 px-6 py-10 text-center text-white md:px-16 md:py-14">
                        <h2 className="mb-3 text-2xl font-bold md:text-3xl font-display">Siap Tampil Memukau?</h2>
                        <p className="mb-6 text-sm text-rose-100 md:text-base">
                            Telusuri koleksi baju branded terbaik untuk acara spesialmu, langsung sewa tanpa perlu daftar
                        </p>
                        <Link
                            href={route('products.index')}
                            className="inline-flex items-center gap-2 rounded-2xl bg-white px-8 py-4 font-bold text-rose-600 shadow-lg transition hover:bg-rose-50"
                        >
                            Mulai Sekarang
                            <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </section>
            </CustomerLayout>
        </>
    );
}

// Detailed FAQ Content
const faqData = {
    customer: [
        {
            q: "Bagaimana langkah awal menyewa baju kondangan di BundaGaya?",
            a: "Proses sewa di platform kami dirancang agar sangat aman dan terpercaya:\n\n1. **Pilih Busana:** Telusuri koleksi kebaya modern, gaun pesta, atau tas branded, lalu sesuaikan dengan ukuran tubuh Anda (cek lingkar dada/panjang pada deskripsi).\n2. **Tentukan Tanggal & Bayar:** Pilih tanggal sewa pada kalender produk, kemudian lakukan pembayaran instan via **Midtrans** (QRIS, bank transfer, Gopay, dll.).\n3. **Hubungi Toko:** Setelah pembayaran terverifikasi, Anda akan mendapatkan akses kontak toko untuk koordinasi pengambilan baju (diambil sendiri/instant courier)."
        },
        {
            q: "Bagaimana sistem pengambilan dan pengembalian baju sewa?",
            a: "Semua pengiriman dilakukan atas kesepakatan antara penyewa dan pihak toko:\n\n* **Pengambilan:** Baju dapat diambil pada hari pertama masa sewa.\n* **Pengembalian:** Baju wajib dikembalikan ke toko pada hari terakhir sewa dalam kondisi utuh.\n\n*Catatan: Pastikan mengembalikan tepat waktu untuk menghindari denda keterlambatan harian.*"
        },
        {
            q: "Apakah baju yang saya terima sudah dicuci dan steril?",
            a: "Tentu saja. **BundaGaya mewajibkan setiap produk dikirimkan dalam kondisi bersih, steril, wangi, dan siap pakai.** Baju telah melalui proses *dry cleaning* atau pencucian khusus pakaian pesta sebelum diserahkan kepada penyewa."
        },
        {
            q: "Bagaimana jika baju yang saya sewa mengalami kerusakan atau terkena noda?",
            a: "Kami memahami risiko saat menghadiri acara:\n\n* **Noda Ringan:** Noda kosmetik tipis atau debu biasa akan dibersihkan oleh laundry profesional tanpa biaya tambahan.\n* **Kerusakan Serius:** Jika terdapat noda membandel (tinta, lem), robekan serat kain, kain terbakar/kena rokok, atau aksesoris hilang, penyewa wajib mengganti biaya perbaikan atau kompensasi sesuai kesepakatan dengan pihak toko."
        }
    ]
};

// RichText / Markdown Parser
function renderAnswer(text) {
    if (!text) return null;
    return text.split('\n').map((line, idx) => {
        const trimmed = line.trim();
        if (!trimmed) return <div key={idx} className="h-3" />;
        
        // List item check
        if (line.startsWith('* ')) {
            return (
                <ul key={idx} className="list-disc pl-5 mb-2 text-sm text-gray-600">
                    <li>{parseInlineMarkdown(line.substring(2))}</li>
                </ul>
            );
        }
        
        // Numbered list item check
        const numMatch = line.match(/^(\d+)\.\s(.*)/);
        if (numMatch) {
            return (
                <ol key={idx} className="list-decimal pl-5 mb-2 text-sm text-gray-600">
                    <li value={numMatch[1]}>{parseInlineMarkdown(numMatch[2])}</li>
                </ol>
            );
        }
        
        return (
            <p key={idx} className="mb-2 text-sm text-gray-600 leading-relaxed">
                {parseInlineMarkdown(line)}
            </p>
        );
    });
}

function parseInlineMarkdown(text) {
    if (!text) return "";
    const boldParts = text.split(/\*\*/g);
    return boldParts.map((boldPart, index) => {
        const isBold = index % 2 === 1;
        const italicParts = boldPart.split(/\*/g);
        const content = italicParts.map((italicPart, subIndex) => {
            const isItalic = subIndex % 2 === 1;
            if (isItalic) {
                return <em key={subIndex} className="italic text-gray-700">{italicPart}</em>;
            }
            return italicPart;
        });

        if (isBold) {
            return <strong key={index} className="font-semibold text-gray-900">{content}</strong>;
        }
        return <span key={index}>{content}</span>;
    });
}

function PhotoIcon({ className }) {
    return (
        <svg className={className} fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
    );
}
