@extends('emails.layout')

@section('content')
<h2>Status Pesanan Berubah</h2>

<p>Halo {{ $order->user->name }},</p>

<p>Status pesanan Anda telah berubah. Berikut adalah detail terbaru:</p>

<div class="info-box">
    <strong>Nomor Pesanan:</strong> {{ $order->order_number }}<br>
    <strong>Status Baru:</strong> <span class="status-badge status-{{ strtolower(str_replace('_', '-', $order->status)) }}">{{ $statusLabel }}</span>
</div>

@if($order->status === 'paid')
<p>Pembayaran Anda telah kami terima dan dikonfirmasi. Pesanan Anda sedang diproses oleh pemilik toko.</p>
@elseif($order->status === 'confirmed_by_owner')
<p>Pemilik toko telah mengkonfirmasi pesanan Anda. Silakan ambil produk sesuai jadwal yang telah disepakati.</p>
@elseif($order->status === 'picked_up')
<p>Produk telah berhasil diambil. Selamat menikmati produk sewa Anda!</p>
@elseif($order->status === 'returned')
<p>Produk telah berhasil dikembalikan. Terima kasih telah menggunakan layanan kami.</p>
@elseif($order->status === 'completed')
<p>Pesanan Anda telah selesai. Terima kasih telah menggunakan layanan BundaGaya!</p>
@elseif($order->status === 'cancelled')
<p>Pesanan Anda telah dibatalkan. Jika Anda memiliki pertanyaan, silakan hubungi kami.</p>
@endif

<a href="{{ route('customer.orders.show', $order) }}" class="button">Lihat Detail Pesanan</a>

<p>Terima kasih telah menggunakan layanan BundaGaya.</p>
@endsection
