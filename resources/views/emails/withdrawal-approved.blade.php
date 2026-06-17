@extends('emails.layout')

@section('content')
<h2>Penarikan Dana Disetujui</h2>

<p>Halo {{ $withdrawal->user->name }},</p>

<p>Permintaan penarikan dana Anda telah disetujui dan sedang dalam proses pencairan.</p>

<div class="info-box">
    <strong>Nomor Penarikan:</strong> {{ $withdrawal->withdrawal_number }}<br>
    <strong>Jumlah:</strong> Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}<br>
    <strong>Bank:</strong> {{ $withdrawal->bank_name }}<br>
    <strong>No. Rekening:</strong> {{ $withdrawal->bank_account }}<br>
    <strong>Atas Nama:</strong> {{ $withdrawal->account_holder }}<br>
    <strong>Status:</strong> <span class="status-badge status-completed">Disetujui</span>
</div>

<p>Dana akan ditransfer ke rekening Anda dalam 1-3 hari kerja.</p>

<a href="{{ route('shop.withdrawals.index') }}" class="button">Lihat Riwayat Penarikan</a>

<p>Terima kasih telah menggunakan layanan BundaGaya.</p>
@endsection
