@extends('emails.layout')

@section('content')
<h2>Pendaftaran Toko Ditolak</h2>

<p>Halo {{ $shop->user->name }},</p>

<p> Mohon maaf, pendaftaran toko <strong>{{ $shop->name }}</strong> belum dapat kami setujui pada saat ini.</p>

<div class="info-box">
    <strong>Nama Toko:</strong> {{ $shop->name }}<br>
    <strong>Status:</strong> <span class="status-badge status-cancelled">Ditolak</span>
</div>

@if($shop->rejection_reason)
<h3>Alasan Penolakan:</h3>
<p>{{ $shop->rejection_reason }}</p>
@endif

<p>Anda dapat memperbaiki hal-hal yang diperlukan dan mengajukan kembali pendaftaran toko Anda.</p>

<a href="{{ route('shop.shop.create') }}" class="button">Ajukan Ulang Pendaftaran</a>

<p>Jika Anda memiliki pertanyaan mengenai penolakan ini, silakan hubungi kami di support@bundagaya.com</p>
@endsection
