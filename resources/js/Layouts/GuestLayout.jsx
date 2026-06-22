import { Link } from '@inertiajs/react';

export default function GuestLayout({ children }) {
    return (
        <div className="relative flex min-h-screen flex-col bg-gradient-to-br from-rose-50 via-pink-50 to-white">
            <div className="pointer-events-none absolute inset-0 overflow-hidden">
                <div className="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-rose-200/30 blur-3xl" />
                <div className="absolute -bottom-32 -left-20 h-80 w-80 rounded-full bg-pink-200/20 blur-3xl" />
                <div className="absolute right-1/3 top-1/2 h-48 w-48 rounded-full bg-amber-100/20 blur-3xl" />
            </div>

            <div className="relative z-10 flex flex-1 flex-col items-center justify-center px-4 py-8">
                <Link href="/" className="mb-8 flex items-center gap-3">
                    <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-600 text-white shadow-lg shadow-rose-200">
                        <svg viewBox="0 0 24 24" className="h-7 w-7" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                            <path d="M6.3 2h3.4c.4 0 .7.1 1 .3l.8.6c.3.2.6.3 1 .3h3c1.8 0 3.2 1.4 3.2 3.2v1c0 .4.1.7.3 1l.6.8c.2.3.3.6.3 1v3.4c0 .4-.1.7-.3 1l-.6.8c-.2.3-.3.6-.3 1v1c0 1.8-1.4 3.2-3.2 3.2h-3.4c-.4 0-.7.1-1 .3l-.8.6c-.3.2-.6.3-1 .3h-1c-1.8 0-3.2-1.4-3.2-3.2v-1c0-.4-.1-.7-.3-1l-.6-.8c-.2-.3-.3-.6-.3-1V6.3c0-.4.1-.7.3-1l.6-.8c.2-.3.3-.6.3-1V3.2C3.1 2.4 3.8 2 4.5 2z" />
                        </svg>
                    </div>
                    <div>
                        <span className="text-2xl font-bold tracking-tight text-gray-900">Bunda</span>
                        <span className="text-2xl font-bold tracking-tight text-rose-600">Gaya</span>
                    </div>
                </Link>

                <div className="w-full max-w-md">
                    <div className="rounded-3xl border border-white/60 bg-white/80 px-6 py-8 shadow-xl shadow-rose-100/50 backdrop-blur-sm sm:px-8">
                        {children}
                    </div>
                </div>
            </div>

            <div className="relative z-10 pb-4 text-center text-xs text-gray-400">
                &copy; {new Date().getFullYear()} BundaGaya
            </div>
        </div>
    );
}
