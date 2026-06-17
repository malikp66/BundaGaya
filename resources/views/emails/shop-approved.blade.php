@extends('emails.layout')

@section('content')
<h2>Toko Anda Disetujui!</h2>

<p>Halo {{ $shop->user->name }},</p>

<p>Selamat! Toko Anda <strong>{{ $shop->name }}</strong> telah disetujui oleh tim admin BundaGaya.</p>

<div class="info-box">
    <strong>Nama Toko:</strong> {{ $shop->name }}<br>
    <strong>Slug:</strong> {{ $shop->slug }}<br>
    <strong>Status:</strong> <span class="status-badge status-completed">Aktif</span><br>
    <strong>Komisi:</strong> {{ $shop->commission_rate }}%
</div>

<p>Anda sekarang dapat:</p>
<ul>
    <li>Menambahkan produk ke toko Anda</li>
    <li>Mengelola pesanan yang masuk</li>
    <li>Memantau transaksi dan pendapatan</li>
    <li>Melakukan penarikan dana</li>
</ul>

<a href="{{ route('shop.dashboard') }}" class="button">Kunjungi Dashboard Toko</a>

<p>Mulai tambahkan produk dan raih pelanggan pertama Anda!</p>
@endsection
