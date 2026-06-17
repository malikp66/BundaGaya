import ShopLayout from '@/Layouts/ShopLayout';
import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ withdrawals, availableBalance, shop }) {
    const [showForm, setShowForm] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({
        amount: '',
        bank_name: '',
        bank_account: '',
        account_holder: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('shop.withdrawals.store'), {
            onSuccess: () => {
                reset();
                setShowForm(false);
            },
        });
    };

    return (
        <ShopLayout shop={shop}>
            <Head title="Withdrawals" />
            
            <div className="mb-6 flex items-center justify-between">
                <h1 className="text-2xl font-bold text-gray-900">Withdrawals</h1>
                <button
                    onClick={() => setShowForm(!showForm)}
                    className="rounded-lg bg-rose-600 px-4 py-2 text-white hover:bg-rose-700"
                >
                    Request Withdrawal
                </button>
            </div>

            <div className="mb-6 rounded-lg bg-green-50 p-6">
                <p className="text-sm text-gray-600">Available Balance</p>
                <p className="text-3xl font-bold text-green-600">
                    Rp {(availableBalance || 0).toLocaleString('id-ID')}
                </p>
            </div>

            {showForm && (
                <form onSubmit={handleSubmit} className="mb-6 space-y-4 rounded-lg bg-white p-6 shadow-sm">
                    <h2 className="text-lg font-semibold text-gray-900">Request Withdrawal</h2>
                    
                    <div>
                        <label className="mb-2 block text-sm font-medium text-gray-700">Amount (Rp) *</label>
                        <input
                            type="number"
                            value={data.amount}
                            onChange={(e) => setData('amount', e.target.value)}
                            className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                            required
                            min="50000"
                            max={availableBalance || 0}
                        />
                        {errors.amount && <p className="mt-1 text-sm text-red-600">{errors.amount}</p>}
                    </div>

                    <div>
                        <label className="mb-2 block text-sm font-medium text-gray-700">Bank Name *</label>
                        <select
                            value={data.bank_name}
                            onChange={(e) => setData('bank_name', e.target.value)}
                            className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                            required
                        >
                            <option value="">Select Bank</option>
                            <option value="BCA">BCA</option>
                            <option value="BNI">BNI</option>
                            <option value="BRI">BRI</option>
                            <option value="Mandiri">Mandiri</option>
                            <option value="BSI">BSI</option>
                        </select>
                        {errors.bank_name && <p className="mt-1 text-sm text-red-600">{errors.bank_name}</p>}
                    </div>

                    <div>
                        <label className="mb-2 block text-sm font-medium text-gray-700">Bank Account Number *</label>
                        <input
                            type="text"
                            value={data.bank_account}
                            onChange={(e) => setData('bank_account', e.target.value)}
                            className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                            required
                        />
                        {errors.bank_account && <p className="mt-1 text-sm text-red-600">{errors.bank_account}</p>}
                    </div>

                    <div>
                        <label className="mb-2 block text-sm font-medium text-gray-700">Account Holder Name *</label>
                        <input
                            type="text"
                            value={data.account_holder}
                            onChange={(e) => setData('account_holder', e.target.value)}
                            className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                            required
                        />
                        {errors.account_holder && <p className="mt-1 text-sm text-red-600">{errors.account_holder}</p>}
                    </div>

                    <div className="flex gap-3">
                        <button
                            type="button"
                            onClick={() => setShowForm(false)}
                            className="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="flex-1 rounded-lg bg-rose-600 px-4 py-2 text-white hover:bg-rose-700 disabled:opacity-50"
                        >
                            {processing ? 'Submitting...' : 'Submit Request'}
                        </button>
                    </div>
                </form>
            )}

            {withdrawals.data.length === 0 ? (
                <div className="rounded-lg bg-white p-12 text-center">
                    <p className="text-gray-600">No withdrawal requests yet</p>
                </div>
            ) : (
                <div className="space-y-3">
                    {withdrawals.data.map((wd) => (
                        <div key={wd.id} className="rounded-lg bg-white p-4 shadow-sm">
                            <div className="mb-2 flex items-start justify-between">
                                <div>
                                    <p className="text-sm text-gray-500">{wd.withdrawal_number}</p>
                                    <p className="text-xs text-gray-400">{new Date(wd.created_at).toLocaleDateString('id-ID')}</p>
                                </div>
                                <span className={`rounded-full px-3 py-1 text-xs font-medium ${
                                    wd.status === 'processed' ? 'bg-green-100 text-green-800' :
                                    wd.status === 'approved' ? 'bg-blue-100 text-blue-800' :
                                    wd.status === 'rejected' ? 'bg-red-100 text-red-800' :
                                    'bg-yellow-100 text-yellow-800'
                                }`}>
                                    {wd.status}
                                </span>
                            </div>
                            <div className="border-t pt-2">
                                <div className="flex justify-between text-sm">
                                    <span className="text-gray-600">Amount</span>
                                    <span className="font-semibold text-rose-600">Rp {parseInt(wd.amount).toLocaleString('id-ID')}</span>
                                </div>
                                <div className="flex justify-between text-sm">
                                    <span className="text-gray-600">Bank</span>
                                    <span>{wd.bank_name}</span>
                                </div>
                                <div className="flex justify-between text-sm">
                                    <span className="text-gray-600">Account</span>
                                    <span>{wd.bank_account}</span>
                                </div>
                                <div className="flex justify-between text-sm">
                                    <span className="text-gray-600">Holder</span>
                                    <span>{wd.account_holder}</span>
                                </div>
                                {wd.rejection_reason && (
                                    <div className="mt-2 rounded bg-red-50 p-2 text-sm text-red-600">
                                        Reason: {wd.rejection_reason}
                                    </div>
                                )}
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </ShopLayout>
    );
}
