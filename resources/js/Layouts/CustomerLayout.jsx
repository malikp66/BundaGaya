import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function CustomerLayout({ children, cartItemCount = 0 }) {
    const { auth } = usePage().props;
    const [showMobileMenu, setShowMobileMenu] = useState(false);

    const displayName = auth?.user?.display_name || auth?.user?.name || 'Guest';

    return (
        <div className="min-h-screen bg-gray-50 pb-16">
            <a href="#main-content" className="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-rose-600 focus:px-4 focus:py-2 focus:text-white">
                Lewati ke konten
            </a>

            <header className="sticky top-0 z-50 bg-white shadow-sm relative">
                <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-3">
                    <Link href="/" className="text-2xl font-bold tracking-wider text-rose-600 font-display">
                        BundaGaya
                    </Link>

                    <div className="flex items-center gap-2">
                        <Link
                            href={route('customer.cart.index')}
                            className="relative p-2 text-gray-600 hover:text-rose-600"
                            aria-label={`Keranjang, ${cartItemCount} item`}
                        >
                            <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            {cartItemCount > 0 && (
                                <span className="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-rose-600 text-xs text-white">
                                    {cartItemCount}
                                </span>
                            )}
                        </Link>

                        <button
                            onClick={() => setShowMobileMenu(!showMobileMenu)}
                            className="flex items-center gap-2 rounded-full border border-gray-200 bg-white px-2 py-1 text-sm text-gray-700 hover:border-rose-300 hover:text-rose-600"
                            aria-label="Menu pengguna"
                            aria-expanded={showMobileMenu}
                        >
                            <span className="flex h-7 w-7 items-center justify-center rounded-full bg-rose-100 text-xs font-semibold text-rose-600">
                                {displayName.slice(-2).toUpperCase()}
                            </span>
                            <span className="hidden max-w-[8rem] truncate sm:inline">{displayName}</span>
                            <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 15l7-7 7 7" />
                            </svg>
                        </button>
                    </div>
                </div>

                {showMobileMenu && (
                    <div className="absolute left-0 right-0 top-full z-50 border-t bg-white px-4 py-3 shadow-lg">
                        <div className="mb-3 border-b pb-3">
                            <p className="font-medium text-gray-900">{displayName}</p>
                            <p className="text-xs text-gray-500">Sesi tersimpan permanen di perangkat ini</p>
                        </div>
                        <nav className="space-y-2">
                            <Link href={route('account.show')} className="block rounded-lg px-3 py-2 text-gray-700 hover:bg-gray-100">
                                Akun Saya
                            </Link>
                            <Link href={route('customer.orders.index')} className="block rounded-lg px-3 py-2 text-gray-700 hover:bg-gray-100">
                                Pesanan Saya
                            </Link>
                            <Link href={route('customer.cart.index')} className="block rounded-lg px-3 py-2 text-gray-700 hover:bg-gray-100">
                                Keranjang
                            </Link>
                        </nav>
                    </div>
                )}
            </header>

            <main id="main-content">{children}</main>

            <nav className="fixed bottom-0 left-0 right-0 z-50 border-t bg-white shadow-lg" aria-label="Navigasi utama">
                <div className="mx-auto flex max-w-7xl justify-around py-2">
                    <Link href={route('welcome')} className="flex flex-col items-center p-2 text-gray-600 hover:text-rose-600">
                        <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span className="text-xs">Beranda</span>
                    </Link>
                    <Link href={route('products.index')} className="flex flex-col items-center p-2 text-gray-600 hover:text-rose-600">
                        <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span className="text-xs">Jelajahi</span>
                    </Link>
                    <Link href={route('customer.cart.index')} className="relative flex flex-col items-center p-2 text-gray-600 hover:text-rose-600" aria-label={`Keranjang, ${cartItemCount} item`}>
                        <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        {cartItemCount > 0 && (
                            <span className="absolute -right-1 top-0 flex h-5 w-5 items-center justify-center rounded-full bg-rose-600 text-xs text-white">
                                {cartItemCount}
                            </span>
                        )}
                        <span className="text-xs">Keranjang</span>
                    </Link>
                    <Link href={route('customer.orders.index')} className="flex flex-col items-center p-2 text-gray-600 hover:text-rose-600">
                        <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span className="text-xs">Pesanan</span>
                    </Link>
                    <Link href={route('account.show')} className="flex flex-col items-center p-2 text-gray-600 hover:text-rose-600">
                        <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span className="text-xs">Akun</span>
                    </Link>
                </div>
            </nav>
        </div>
    );
}
