<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ $page['props']['seo']['title'] ?? config('app.name', 'BundaGaya') }}</title>

        @if(isset($page['props']['seo']['description']))
        <meta name="description" content="{{ $page['props']['seo']['description'] }}">
        @else
        <meta name="description" content="Sewa baju kondangan branded dengan harga terjangkau. Gaun pesta, kebaya, tas Myzasac, dan aksesoris lengkap.">
        @endif

        <meta property="og:type" content="{{ $page['props']['seo']['og_type'] ?? 'website' }}">
        <meta property="og:title" content="{{ $page['props']['seo']['title'] ?? config('app.name', 'BundaGaya') }}">
        <meta property="og:description" content="{{ $page['props']['seo']['description'] ?? 'Sewa baju kondangan branded dengan harga terjangkau.' }}">
        <meta property="og:site_name" content="{{ config('app.name', 'BundaGaya') }}">
        <meta property="og:url" content="{{ url()->current() }}">
        @if(isset($page['props']['seo']['og_image']))
        <meta property="og:image" content="{{ $page['props']['seo']['og_image'] }}">
        @endif

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $page['props']['seo']['title'] ?? config('app.name', 'BundaGaya') }}">
        <meta name="twitter:description" content="{{ $page['props']['seo']['description'] ?? 'Sewa baju kondangan branded dengan harga terjangkau.' }}">
        @if(isset($page['props']['seo']['og_image']))
        <meta name="twitter:image" content="{{ $page['props']['seo']['og_image'] }}">
        @endif

        <link rel="canonical" href="{{ url()->current() }}">

        <link rel="preload" as="image" href="/hero.webp" fetchpriority="high">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700|cormorant-garamond:400,400i,500,500i,600,600i,700,700i&display=swap" rel="stylesheet" />

        @routes
        @viteReactRefresh
        @vite(['resources/js/app.jsx', "resources/js/Pages/{$page['component']}.jsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
