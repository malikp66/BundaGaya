@extends('emails.layout')

@section('content')
<h2>Pembayaran Diterima!</h2>

<p>Halo {{ $order->user->name }},</p>

<p>Pembayaran untuk pesanan Anda telah berhasil diterima. Berikut adalah detail pembayaran:</p>

<div class="info-box">
    <strong>Nomor Pesanan:</strong> {{ $order->order_number }}<br>
    <strong>Nomor Pembayaran:</strong> {{ $payment->payment_number }}<br>
    <strong>Metode Pembayaran:</strong> {{ ucfirst(str_replace('_', ' ', $payment->method)) }}<br>
    <strong>Jumlah:</strong> Rp {{ number_format($payment->amount, 0, ',', '.') }}<br>
    <strong>Tanggal Pembayaran:</strong> {{ $payment->paid_at->format('d M Y, H:i') }}
</div>

<p>Pesanan Anda sekarang akan diproses oleh pemilik toko. Anda akan menerima notifikasi ketika pesanan telah dikonfirmasi.</p>

<a href="{{ route('customer.orders.show', $order) }}" class="button">Lihat Detail Pesanan</a>

<p>Terima kasih telah melakukan pembayaran tepat waktu.</p>
@endsection
