import ShopLayout from '@/Layouts/ShopLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ transactions, revenue, filters, shop }) {
    const [startDate, setStartDate] = useState(filters?.start_date || '');
    const [endDate, setEndDate] = useState(filters?.end_date || '');

    const handleFilter = () => {
        const params = {};
        if (startDate) params.start_date = startDate;
        if (endDate) params.end_date = endDate;
        
        router.get(route('shop.transactions.index'), params, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    return (
        <ShopLayout shop={shop}>
            <Head title="Transactions" />
            
            <h1 className="mb-6 text-2xl font-bold text-gray-900">Transactions</h1>

            <div className="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div className="rounded-lg bg-white p-4 shadow-sm">
                    <p className="text-sm text-gray-500">Total Revenue</p>
                    <p className="text-xl font-bold text-gray-900">Rp {(revenue?.total_revenue || 0).toLocaleString('id-ID')}</p>
                </div>
                <div className="rounded-lg bg-white p-4 shadow-sm">
                    <p className="text-sm text-gray-500">Total Commission</p>
                    <p className="text-xl font-bold text-gray-900">Rp {(revenue?.total_commission || 0).toLocaleString('id-ID')}</p>
                </div>
                <div className="rounded-lg bg-white p-4 shadow-sm">
                    <p className="text-sm text-gray-500">Pending</p>
                    <p className="text-xl font-bold text-yellow-600">Rp {(revenue?.pending_amount || 0).toLocaleString('id-ID')}</p>
                </div>
                <div className="rounded-lg bg-white p-4 shadow-sm">
                    <p className="text-sm text-gray-500">Settled</p>
                    <p className="text-xl font-bold text-green-600">Rp {(revenue?.settled_amount || 0).toLocaleString('id-ID')}</p>
                </div>
            </div>

            <div className="mb-6 rounded-lg bg-white p-4 shadow-sm">
                <div className="grid grid-cols-2 gap-3">
                    <div>
                        <label className="mb-1 block text-sm font-medium text-gray-700">Start Date</label>
                        <input
                            type="date"
                            value={startDate}
                            onChange={(e) => setStartDate(e.target.value)}
                            className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                        />
                    </div>
                    <div>
                        <label className="mb-1 block text-sm font-medium text-gray-700">End Date</label>
                        <input
                            type="date"
                            value={endDate}
                            onChange={(e) => setEndDate(e.target.value)}
                            className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                        />
                    </div>
                </div>
                <button
                    onClick={handleFilter}
                    className="mt-3 w-full rounded-lg bg-rose-600 px-4 py-2 text-white hover:bg-rose-700"
                >
                    Apply Filter
                </button>
            </div>

            {transactions.data.length === 0 ? (
                <div className="rounded-lg bg-white p-12 text-center">
                    <p className="text-gray-600">No transactions found</p>
                </div>
            ) : (
                <div className="space-y-3">
                    {transactions.data.map((trx) => (
                        <div key={trx.id} className="rounded-lg bg-white p-4 shadow-sm">
                            <div className="mb-2 flex items-start justify-between">
                                <div>
                                    <p className="text-sm text-gray-500">{trx.transaction_id}</p>
                                    <p className="text-sm font-medium text-gray-900">{trx.order?.order_number}</p>
                                    <p className="text-xs text-gray-400">{new Date(trx.created_at).toLocaleDateString('id-ID')}</p>
                                </div>
                                <span className={`rounded-full px-3 py-1 text-xs font-medium ${
                                    trx.status === 'settled' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                                }`}>
                                    {trx.status}
                                </span>
                            </div>
                            <div className="border-t pt-2">
                                <div className="flex justify-between text-sm">
                                    <span className="text-gray-600">Amount</span>
                                    <span className="font-medium">Rp {parseInt(trx.amount).toLocaleString('id-ID')}</span>
                                </div>
                                <div className="flex justify-between text-sm">
                                    <span className="text-gray-600">Commission ({trx.commission_rate}%)</span>
                                    <span className="text-red-600">-Rp {parseInt(trx.commission_fee).toLocaleString('id-ID')}</span>
                                </div>
                                <div className="flex justify-between border-t pt-2">
                                    <span className="font-semibold">Net Amount</span>
                                    <span className="font-bold text-green-600">Rp {parseInt(trx.net_amount).toLocaleString('id-ID')}</span>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </ShopLayout>
    );
}
