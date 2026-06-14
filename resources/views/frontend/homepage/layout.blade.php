<!DOCTYPE html>
<html lang="vi">
<head>
    <base href="{{ url('/') }}/">
    <link href="https://fonts.googleapis.com/css?family=Yeseva+One" rel="stylesheet">
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
    <link href="https://fonts.googleapis.com/css?family=Roboto+Condensed" rel="stylesheet">
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
</body>
</html>
