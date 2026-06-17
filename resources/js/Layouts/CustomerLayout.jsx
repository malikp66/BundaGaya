import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function CustomerLayout({ children, cartItemCount = 0 }) {
    const { auth } = usePage().props;
    const [showMobileMenu, setShowMobileMenu] = useState(false);

    return (
        <div className="min-h-screen bg-gray-50 pb-16">
            <header className="sticky top-0 z-50 bg-white shadow-sm">
                <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-3">
                    <Link href="/" className="text-xl font-bold text-rose-600">
                        BundaGaya
                    </Link>

                    <div className="flex items-center gap-4">
                        {auth.user ? (
                            <>
                                <Link
                                    href={route('customer.cart.index')}
                                    className="relative p-2 text-gray-600 hover:text-rose-600"
                                >
                                    <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                    className="p-2 text-gray-600 hover:text-rose-600"
                                >
                                    <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </button>
                            </>
                        ) : (
                            <div className="flex gap-2">
                                <Link
                                    href={route('login')}
                                    className="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:text-rose-600"
                                >
                                    Login
                                </Link>
                                <Link
                                    href={route('register')}
                                    className="rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700"
                                >
                                    Register
                                </Link>
                            </div>
                        )}
                    </div>
                </div>

                {showMobileMenu && auth.user && (
                    <div className="border-t bg-white px-4 py-3">
                        <div className="mb-3 border-b pb-3">
                            <p className="font-medium text-gray-900">{auth.user.name}</p>
                            <p className="text-sm text-gray-500">{auth.user.email}</p>
                        </div>
                        <nav className="space-y-2">
                            <Link href={route('customer.orders.index')} className="block rounded-lg px-3 py-2 text-gray-700 hover:bg-gray-100">
                                My Orders
                            </Link>
                            <Link href={route('profile.edit')} className="block rounded-lg px-3 py-2 text-gray-700 hover:bg-gray-100">
                                Profile
                            </Link>
                            {auth.user.role === 'shop_owner' && (
                                <Link href={route('shop.dashboard')} className="block rounded-lg px-3 py-2 text-rose-600 hover:bg-rose-50">
                                    Shop Dashboard
                                </Link>
                            )}
                            {auth.user.role === 'customer' && (
                                <Link href={route('shop.shop.create')} className="block rounded-lg px-3 py-2 text-rose-600 hover:bg-rose-50">
                                    Open a Shop
                                </Link>
                            )}
                            <Link href={route('logout')} method="post" as="button" className="block w-full text-left rounded-lg px-3 py-2 text-red-600 hover:bg-red-50">
                                Logout
                            </Link>
                        </nav>
                    </div>
                )}
            </header>

            <main>{children}</main>

            <nav className="fixed bottom-0 left-0 right-0 z-50 border-t bg-white shadow-lg">
                <div className="mx-auto flex max-w-7xl justify-around py-2">
                    <Link href="/" className="flex flex-col items-center p-2 text-gray-600 hover:text-rose-600">
                        <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span className="text-xs">Home</span>
                    </Link>
                    <Link href={route('products.index')} className="flex flex-col items-center p-2 text-gray-600 hover:text-rose-600">
                        <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span className="text-xs">Browse</span>
                    </Link>
                    {auth.user ? (
                        <>
                            <Link href={route('customer.cart.index')} className="relative flex flex-col items-center p-2 text-gray-600 hover:text-rose-600">
                                <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                {cartItemCount > 0 && (
                                    <span className="absolute -right-1 top-0 flex h-5 w-5 items-center justify-center rounded-full bg-rose-600 text-xs text-white">
                                        {cartItemCount}
                                    </span>
                                )}
                                <span className="text-xs">Cart</span>
                            </Link>
                            <Link href={route('customer.orders.index')} className="flex flex-col items-center p-2 text-gray-600 hover:text-rose-600">
                                <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <span className="text-xs">Orders</span>
                            </Link>
                        </>
                    ) : (
                        <>
                            <Link href={route('login')} className="flex flex-col items-center p-2 text-gray-600 hover:text-rose-600">
                                <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span className="text-xs">Account</span>
                            </Link>
                        </>
                    )}
                </div>
            </nav>
        </div>
    );
}
