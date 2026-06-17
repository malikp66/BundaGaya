@extends('emails.layout')

@section('content')
<h2>Penarikan Dana Ditolak</h2>

<p>Halo {{ $withdrawal->user->name }},</p>

<p>Mohon maaf, permintaan penarikan dana Anda belum dapat kami proses.</p>

<div class="info-box">
    <strong>Nomor Penarikan:</strong> {{ $withdrawal->withdrawal_number }}<br>
    <strong>Jumlah:</strong> Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}<br>
    <strong>Status:</strong> <span class="status-badge status-cancelled">Ditolak</span>
</div>

@if($withdrawal->rejection_reason)
<h3>Alasan Penolakan:</h3>
<p>{{ $withdrawal->rejection_reason }}</p>
@endif

<p>Dana tetap tersedia di saldo toko Anda dan dapat diajukan kembali setelah memperbaiki hal yang diperlukan.</p>

<a href="{{ route('shop.withdrawals.index') }}" class="button">Lihat Riwayat Penarikan</a>

<p>Jika Anda memiliki pertanyaan, silakan hubungi kami di support@bundagaya.com</p>
@endsection
