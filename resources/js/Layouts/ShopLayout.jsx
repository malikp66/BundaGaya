import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function ShopLayout({ children, shop }) {
    const { auth } = usePage().props;
    const [showSidebar, setShowSidebar] = useState(false);

    const navItems = [
        { name: 'Dashboard', href: route('shop.dashboard'), icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
        { name: 'Products', href: route('shop.products.index'), icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' },
        { name: 'Orders', href: route('shop.orders.index'), icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
        { name: 'Transactions', href: route('shop.transactions.index'), icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
        { name: 'Withdrawals', href: route('shop.withdrawals.index'), icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z' },
    ];

    return (
        <div className="min-h-screen bg-gray-50">
            <header className="sticky top-0 z-50 bg-white shadow-sm">
                <div className="flex items-center justify-between px-4 py-3">
                    <button onClick={() => setShowSidebar(!showSidebar)} className="p-2 text-gray-600">
                        <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 className="text-lg font-bold text-gray-900">
                        {shop ? shop.name : 'Shop Dashboard'}
                    </h1>
                    <Link href={route('profile.edit')} className="p-2 text-gray-600">
                        <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </Link>
                </div>
            </header>

            {showSidebar && (
                <div className="fixed inset-0 z-50 flex">
                    <div className="fixed inset-0 bg-black/50" onClick={() => setShowSidebar(false)} />
                    <div className="relative w-64 bg-white shadow-xl">
                        <div className="border-b p-4">
                            <h2 className="font-bold text-gray-900">{auth.user.name}</h2>
                            <p className="text-sm text-gray-500">{shop?.status || 'No shop'}</p>
                        </div>
                        <nav className="p-2">
                            {navItems.map((item) => (
                                <Link
                                    key={item.name}
                                    href={item.href}
                                    onClick={() => setShowSidebar(false)}
                                    className="flex items-center gap-3 rounded-lg px-3 py-2 text-gray-700 hover:bg-rose-50 hover:text-rose-600"
                                >
                                    <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d={item.icon} />
                                    </svg>
                                    {item.name}
                                </Link>
                            ))}
                            <hr className="my-2" />
                            <Link href="/" onClick={() => setShowSidebar(false)} className="flex items-center gap-3 rounded-lg px-3 py-2 text-gray-700 hover:bg-gray-100">
                                <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Back to Store
                            </Link>
                            <Link href={route('logout')} method="post" as="button" onClick={() => setShowSidebar(false)} className="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-red-600 hover:bg-red-50">
                                <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Logout
                            </Link>
                        </nav>
                    </div>
                </div>
            )}

            <main className="px-4 py-6">{children}</main>
        </div>
    );
}
