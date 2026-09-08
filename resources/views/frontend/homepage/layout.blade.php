<!DOCTYPE html>
<html lang="vi">
<head>
    <base href="{{ url('/') }}/">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{--
        Font: da bo 2 ho khong dung den (Playfair Display, Roboto Condensed -
        grep toan bo CSS/view ra 0 lan) va bo truc italic cua Montserrat.
        Tai kieu khong chan render: trinh duyet ve chu bang font du phong roi
        doi sang font that khi CSS ve - dung voi display=swap dang dat san.
    --}}
    <link rel="preload" as="style"
          href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,100..900;1,100..900&family=Manrope:wght@200..800&family=Montserrat:wght@100..900&family=Unbounded:wght@200..900&family=Yeseva+One&display=swap">
    <link rel="stylesheet" media="print" onload="this.media='all';this.onload=null"
          href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,100..900;1,100..900&family=Manrope:wght@200..800&family=Montserrat:wght@100..900&family=Unbounded:wght@200..900&family=Yeseva+One&display=swap">
    <noscript>
        <link rel="stylesheet"
              href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,100..900;1,100..900&family=Manrope:wght@200..800&family=Montserrat:wght@100..900&family=Unbounded:wght@200..900&family=Yeseva+One&display=swap">
    </noscript>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta http-equiv="content-language" content="vi">
    <link rel="alternate" href="{{ url('/') }}" hreflang="vi-vn">
    <meta name="robots" content="index,follow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="author" content="{{ $system['homepage_brandname'] ?? $system['homepage_brand'] ?? '' }}">
    <meta name="copyright" content="{{ $system['homepage_brandname'] ?? $system['homepage_brand'] ?? '' }}">
    {{-- Da bo <meta http-equiv="refresh" content="1800">: the nay tu tai lai
         trang moi 30 phut, gay mat vi tri doc / mat du lieu dang nhap form, va
         Google tinh la trai nghiem xau. Khong lien quan gi den chuc nang. --}}

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

    <!-- Global CSS Overrides -->
    <style>
        /*
            BREADCRUMB TRANG CON
            --------------------
            Truoc day khoi nay la mot banner cao 300-450px: anh nen + lop phu
            den + tieu de 40-60px kem 2 duong ke trang tri. Yeu cau moi: chi con
            breadcrumb, bo anh nen.

            Dat o day vi 5 trang (danh muc/chi tiet bai viet, danh muc/chi tiet
            san pham, lien he) moi trang co mot khoi <style> inline rieng dinh
            nghia lai .about-hero. Block nay nam cuoi <body> nen nap sau tat ca,
            sua mot cho la ap dung cho ca 5 trang.
        */
        /*
            Chi ap dung khi khoi CO breadcrumb - dung :has(). Trang gioi-thieu /
            ve-chung-toi cung dung .about-hero nhung KHONG co breadcrumb (do la
            banner tieu de) va con dung header trong suot de len anh nen, nen
            phai de nguyen. Trinh duyet cu khong ho tro :has() se bo qua ca khoi
            nay va giu thiet ke cu - suy giam nhe nhang, khong vo layout.
        */
        .about-hero:has(.hero-breadcrumb) {
            height: auto !important;
            min-height: 0 !important;
            padding: 18px 0 !important;
            margin-top: 0 !important;
            /* Nen den dong bo voi khu vuc noi dung ben duoi, khong con anh nen */
            background: #000 !important;
            display: block !important;
            overflow: visible !important;
        }

        /* Anh nen va lop phu khong con dung den */
        .about-hero:has(.hero-breadcrumb) .about-hero__bg,
        .about-hero:has(.hero-breadcrumb) .hero-overlay {
            display: none !important;
        }

        .about-hero:has(.hero-breadcrumb) .hero-content {
            text-align: left !important;
        }

        /*
            Tieu de H1: an bang CSS chu KHONG bo khoi HTML. Day la the H1 duy
            nhat cua trang, bo han thi trang mat H1 va anh huong SEO. Cach nay
            hien thi dung nhu yeu cau (chi con breadcrumb) ma van giu the H1
            cho may tim kiem - giong cach trang chu dang lam.
        */
        .about-hero:has(.hero-breadcrumb) .hero-title {
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            margin: -1px !important;
            padding: 0 !important;
            overflow: hidden !important;
            clip: rect(0, 0, 0, 0) !important;
            white-space: nowrap !important;
            border: 0 !important;
        }

        /* Breadcrumb: mot dong gon gang, can trai */
        .about-hero:has(.hero-breadcrumb) .hero-breadcrumb {
            display: block !important;
            justify-content: flex-start !important;
            margin: 0 !important;
        }

        .about-hero:has(.hero-breadcrumb) .hero-breadcrumb .uk-breadcrumb {
            display: flex !important;
            flex-wrap: wrap !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 6px 10px !important;
            margin: 0 !important;
            padding: 0 !important;
            list-style: none !important;
        }

        .about-hero:has(.hero-breadcrumb) .hero-breadcrumb .uk-breadcrumb > li {
            color: rgba(255, 255, 255, 0.65) !important;
            font-size: 13px !important;
        }

        .about-hero:has(.hero-breadcrumb) .hero-breadcrumb .uk-breadcrumb > li > a {
            color: rgba(255, 255, 255, 0.65) !important;
            text-decoration: none !important;
        }

        .about-hero:has(.hero-breadcrumb) .hero-breadcrumb .uk-breadcrumb > li > a:hover {
            color: #00e0ff !important;
        }

        /* Dau phan cach giua cac cap */
        .about-hero:has(.hero-breadcrumb) .hero-breadcrumb .uk-breadcrumb > li + li::before {
            content: "/";
            margin-right: 10px;
            color: rgba(255, 255, 255, 0.3);
        }

        /* Cap cuoi la trang hien tai -> lam ro hon, khong phai link */
        .about-hero:has(.hero-breadcrumb) .hero-breadcrumb .uk-breadcrumb > li:last-child,
        .about-hero:has(.hero-breadcrumb) .hero-breadcrumb .uk-breadcrumb > li:last-child > a {
            color: #fff !important;
        }

        @media (max-width: 959px) {
            /* Dai breadcrumb: chi thu nho padding, khong can rule rieng khac */
            .about-hero:has(.hero-breadcrumb) {
                padding: 14px 0 !important;
            }

            /*
                Cac trang KHONG co breadcrumb (gioi-thieu / ve-chung-toi) van la
                banner tieu de nhu cu, nen giu nguyen cac dieu chinh mobile von
                co cho chung.
            */
            .about-hero:not(:has(.hero-breadcrumb)) {
                height: auto !important;
                min-height: 180px !important;
                padding: 30px 0 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            .about-hero:not(:has(.hero-breadcrumb)) .hero-title {
                font-size: 22px !important;
                line-height: 1.5 !important;
                padding: 0 15px !important;
                flex-wrap: wrap !important;
                display: flex !important;
                justify-content: center !important;
                text-align: center !important;
            }
            .about-hero:not(:has(.hero-breadcrumb)) .hero-title .decor-line {
                display: none !important;
            }
        }
    </style>
</body>
</html>
