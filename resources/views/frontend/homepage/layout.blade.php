<!DOCTYPE html>
<html lang="vi">
<head>
    <base href="{{ url('/') }}/">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,100..900;1,100..900&family=Manrope:wght@200..800&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Unbounded:wght@200..900&family=Yeseva+One&family=Roboto+Condensed:ital,wght@0,300..700;1,300..700&display=swap" rel="stylesheet">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta http-equiv="content-language" content="vi">
    <link rel="alternate" href="{{ url('/') }}" hreflang="vi-vn">
    <meta name="robots" content="index,follow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="author" content="{{ $system['homepage_brandname'] ?? $system['homepage_brand'] ?? '' }}">
    <meta name="copyright" content="{{ $system['homepage_brandname'] ?? $system['homepage_brand'] ?? '' }}">
    <meta http-equiv="refresh" content="1800">

    <title>{{ $seo['meta_title'] ?? '' }}</title>
    <meta name="keywords" content="{{ $seo['meta_keyword'] ?? '' }}">
    <meta name="description" content="{{ $seo['meta_description'] ?? '' }}">
    @if(!empty($seo['canonical']))
        <link rel="canonical" href="{{ $seo['canonical'] }}">
    @endif

    <meta property="og:title" content="{{ $seo['meta_title'] ?? '' }}">
    <meta property="og:type" content="article">
    <meta property="og:image" content="{{ $seo['meta_image'] ?? $system['seo_meta_images'] ?? $system['seo_meta_image'] ?? '' }}">
    <meta property="og:url" content="{{ $seo['canonical'] ?? url('/') }}">
    <meta property="og:description" content="{{ $seo['meta_description'] ?? '' }}">
    <meta property="og:site_name" content="{{ $system['homepage_brandname'] ?? $system['homepage_brand'] ?? '' }}">

    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $seo['meta_title'] ?? '' }}">
    <meta name="twitter:description" content="{{ $seo['meta_description'] ?? '' }}">
    <meta name="twitter:image" content="{{ $seo['meta_image'] ?? $system['seo_meta_images'] ?? $system['seo_meta_image'] ?? '' }}">

    <link rel="icon" href="{{ $system['homepage_favicon'] ?? '' }}" type="image/png" sizes="30x30">
    @include('frontend.component.head')
    @if(isset($schema))
        {!! $schema !!}
    @endif
    {!! $system['script_header'] ?? '' !!}
</head>
<body>
    {!! $system['script_body'] ?? '' !!}
    @include('frontend.component.header')

    @if(session('success') || session('error'))
        <div class="uk-container uk-container-center uk-margin-top">
            <div class="uk-alert {{ session('success') ? 'uk-alert-success' : 'uk-alert-danger' }} uk-margin-remove" data-uk-alert>
                <a href="#" class="uk-alert-close uk-close"></a>
                <p>{{ session('success') ?: session('error') }}</p>
            </div>
        </div>
    @endif

    @yield('content')

    @include('frontend.component.footer')
    @include('frontend.component.offcanvas')
    @include('frontend.component.script')

    <div id="modal-cart" class="uk-modal">
        <div class="uk-modal-dialog" style="width:768px;">
            <a class="uk-modal-close uk-close"></a>
            <div class="cart-content"></div>
        </div>
    </div>

    <div id="modal-buynow" class="uk-modal">
        <div class="uk-modal-dialog uk-modal-dialog-large">
            <a class="uk-modal-close uk-close"></a>
            <div class="cart-content"></div>
        </div>
    </div>

    <!-- Global CSS Overrides for Mobile -->
    <style>
        @media (max-width: 959px) {
            .about-hero {
                height: auto !important;
                min-height: 180px !important;
                padding: 30px 0 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            .about-hero .hero-title {
                font-size: 22px !important;
                line-height: 1.5 !important;
                padding: 0 15px !important;
                flex-wrap: wrap !important;
                display: flex !important;
                justify-content: center !important;
                text-align: center !important;
            }
            .about-hero .hero-title .decor-line {
                display: none !important;
            }
            .about-hero .hero-breadcrumb {
                margin-top: 10px !important;
            }
        }
    </style>
</body>
</html>
