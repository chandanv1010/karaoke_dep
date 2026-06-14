<!DOCTYPE html>
<html>
<head>
    <base href="{{ url('/') }}/">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,100..900;1,100..900&family=Manrope:wght@200..800&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Unbounded:wght@200..900&family=Yeseva+One&family=Roboto+Condensed:ital,wght@0,300..700;1,300..700&display=swap" rel="stylesheet">
    <meta charset="utf-8">
    <meta http-equiv="content-language" content="vi">
    <meta name="robots" content="index,follow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>{{ $seo['meta_title'] ?? $system['seo_meta_title'] ?? config('app.name') }}</title>
    <meta name="keywords" content="{{ $seo['meta_keyword'] ?? '' }}">
    <meta name="description" content="{{ $seo['meta_description'] ?? '' }}">
    @if(!empty($seo['canonical']))<link rel="canonical" href="{{ $seo['canonical'] }}">@endif
    <meta property="og:title" content="{{ $seo['meta_title'] ?? '' }}">
    <meta property="og:type" content="article">
    <meta property="og:image" content="{{ $seo['meta_image'] ?? $system['seo_meta_image'] ?? $system['homepage_logo'] ?? '' }}">
    <meta property="og:description" content="{{ $seo['meta_description'] ?? '' }}">
    <meta property="og:site_name" content="{{ $system['homepage_brandname'] ?? $system['homepage_company'] ?? '' }}">
    <link rel="shortcut icon" href="{{ $system['homepage_favicon'] ?? '' }}" type="image/x-icon">
    <link href="{{ asset('templates/mobile/uikit/css/uikit.docs.simp.min.css') }}" rel="stylesheet">
    <link href="{{ asset('templates/mobile/plugins/flex-slider/flexslider.css') }}" rel="stylesheet">
    <link href="{{ asset('templates/mobile/css/style.css') }}" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('templates/mobile/js/jquery-2.1.3.min.js') }}"></script>
</head>
<body>
    @include('mobile.homepage.component.header')
    <section id="body">
        @yield('content')
        @include('mobile.homepage.component.slide-partner')
    </section>
    @include('mobile.homepage.component.footer')
    @include('mobile.homepage.component.script')
</body>
</html>
