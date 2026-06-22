import CustomerLayout from '@/Layouts/CustomerLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';

function formatDate(value) {
    if (!value) return '—';
    try {
        return new Date(value).toLocaleString('id-ID', {
            dateStyle: 'medium',
            timeStyle: 'short',
        });
    } catch {
        return '—';
    }
}

function CopyableTokenHint() {
    return (
        <div className="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <p className="font-semibold">Sesi perangkat ini permanen</p>
            <p className="mt-1 text-amber-800">
                Keranjang, pesanan, dan identitas kamu tersimpan di perangkat ini. Jangan hapus cookie
                <code className="mx-1 rounded bg-amber-100 px-1.5 py-0.5 font-mono text-xs">bg_guest_token</code>
                atau data akan hilang.
            </p>
        </div>
    );
}

function Stat({ label, value }) {
    return (
        <div className="rounded-2xl border border-gray-200 bg-white p-4">
            <p className="text-xs uppercase tracking-wider text-gray-500">{label}</p>
            <p className="mt-2 text-2xl font-bold text-gray-900">{value}</p>
        </div>
    );
}

export default function AccountIndex({ profile = {}, stats = {} }) {
    const { props } = usePage();
    const flashSuccess = props?.flash?.success;
    const [showDetails, setShowDetails] = useState(false);

    const { data, setData, patch, processing, errors, recentlySuccessful } = useForm({
        name: profile.display_name || profile.name || '',
        phone: profile.phone || '',
        address: profile.address || '',
    });

    const submit = (e) => {
        e.preventDefault();
        patch(route('account.update'), {
            preserveScroll: true,
        });
    };

    const isGuest = !!profile.is_guest;
    const displayName = profile.display_name || profile.name || 'Guest';

    return (
        <CustomerLayout>
            <Head title="Akun Saya" />
            <section className="mx-auto max-w-3xl px-4 py-8">
                <header className="mb-6 flex items-center gap-4">
                    <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-100 text-lg font-bold text-rose-600">
                        {(displayName || 'G').slice(-2).toUpperCase()}
                    </div>
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">{displayName}</h1>
                        <p className="text-sm text-gray-500">
                            {isGuest ? 'Akun tamu khusus perangkat ini' : 'Akun terdaftar'}
                        </p>
                    </div>
                </header>

                {flashSuccess && (
                    <div className="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">
                        {flashSuccess}
                    </div>
                )}
                {recentlySuccessful && !flashSuccess && (
                    <div className="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">
                        Identitas tersimpan.
                    </div>
                )}

                <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <Stat label="Total Pesanan" value={stats.orders_count ?? 0} />
                </div>

                <form onSubmit={submit} className="mt-6 space-y-4 rounded-2xl bg-white p-6 shadow-sm">
                    <h2 className="text-lg font-semibold text-gray-900">Identitas Kamu</h2>
                    <p className="text-sm text-gray-500">
                        Data ini akan otomatis terisi di formulir checkout berikutnya.
                    </p>

                    <div>
                        <label className="mb-1.5 block text-sm font-medium text-gray-700">Nama</label>
                        <input
                            type="text"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            className="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20"
                            placeholder="Nama lengkap kamu"
                        />
                        {errors.name && <p className="mt-1 text-xs text-rose-600">{errors.name}</p>}
                    </div>

                    <div>
                        <label className="mb-1.5 block text-sm font-medium text-gray-700">Nomor WhatsApp</label>
                        <input
                            type="tel"
                            value={data.phone}
                            onChange={(e) => setData('phone', e.target.value)}
                            className="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20"
                            placeholder="08xxxxxxxxxx"
                        />
                        {errors.phone && <p className="mt-1 text-xs text-rose-600">{errors.phone}</p>}
                    </div>

                    <div>
                        <label className="mb-1.5 block text-sm font-medium text-gray-700">Alamat</label>
                        <textarea
                            value={data.address}
                            onChange={(e) => setData('address', e.target.value)}
                            rows={3}
                            className="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20"
                            placeholder="Alamat lengkap untuk pengiriman"
                        />
                        {errors.address && <p className="mt-1 text-xs text-rose-600">{errors.address}</p>}
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700 disabled:opacity-60"
                    >
                        {processing ? 'Menyimpan…' : 'Simpan Identitas'}
                    </button>
                </form>

                <div className="mt-6">
                    <button
                        type="button"
                        onClick={() => setShowDetails((v) => !v)}
                        className="flex w-full items-center justify-between rounded-2xl bg-white p-4 text-left shadow-sm"
                    >
                        <span className="text-sm font-medium text-gray-700">Info sesi perangkat</span>
                        <svg
                            className={`h-4 w-4 text-gray-400 transition-transform ${showDetails ? 'rotate-180' : ''}`}
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    {showDetails && (
                        <div className="mt-2 space-y-2 rounded-2xl bg-white p-4 text-sm text-gray-600 shadow-sm">
                            <div className="flex justify-between">
                                <span>ID Akun</span>
                                <span className="font-mono text-xs text-gray-900">#{profile.id ?? '—'}</span>
                            </div>
                            <div className="flex justify-between">
                                <span>Sesi dibuat</span>
                                <span>{formatDate(profile.created_at)}</span>
                            </div>
                            <div className="flex justify-between">
                                <span>Terakhir aktif</span>
                                <span>{formatDate(profile.last_active_at)}</span>
                            </div>
                            <div className="flex justify-between">
                                <span>Token dibuat</span>
                                <span>{formatDate(profile.token_created_at)}</span>
                            </div>
                            <div className="flex justify-between">
                                <span>Token dipakai</span>
                                <span>{formatDate(profile.token_last_used_at)}</span>
                            </div>
                        </div>
                    )}
                </div>

                <div className="mt-6">
                    <CopyableTokenHint />
                </div>
            </section>
        </CustomerLayout>
    );
}
