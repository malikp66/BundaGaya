@extends('emails.layout')

@section('content')
<h2>Pesanan Berhasil Dibuat!</h2>

<p>Halo {{ $order->user->name }},</p>

<p>Terima kasih telah melakukan pemesanan di BundaGaya. Pesanan Anda telah berhasil dibuat dengan detail sebagai berikut:</p>

<div class="info-box">
    <strong>Nomor Pesanan:</strong> {{ $order->order_number }}<br>
    <strong>Tanggal Pesanan:</strong> {{ $order->created_at->format('d M Y, H:i') }}<br>
    <strong>Status:</strong> <span class="status-badge status-pending">Menunggu Pembayaran</span>
</div>

<h3>Detail Pesanan:</h3>
<table>
    <thead>
        <tr>
            <th>Produk</th>
            <th>Toko</th>
            <th>Durasi</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->items as $item)
        <tr>
            <td>{{ $item->product->name }}</td>
            <td>{{ $item->shop->name }}</td>
            <td>{{ $item->days }} hari</td>
            <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="info-box">
    <table>
        <tr>
            <td><strong>Subtotal:</strong></td>
            <td style="text-align: right;">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Biaya Admin:</strong></td>
            <td style="text-align: right;">Rp {{ number_format($order->admin_fee, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Total:</strong></td>
            <td style="text-align: right; font-size: 18px; color: #e11d48;"><strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong></td>
        </tr>
    </table>
</div>

<p>Silakan lakukan pembayaran untuk melanjutkan proses pesanan Anda.</p>

<a href="{{ route('customer.orders.show', $order) }}" class="button">Lihat Detail Pesanan</a>

<p>Jika Anda memiliki pertanyaan, jangan ragu untuk menghubungi kami.</p>
@endsection
