@extends('frontend.homepage.layout')
@section('content')
@php
    $languageId = $config['language'] ?? 1;
    $albums = json_decode($DetailProducts['albums'] ?? '[]', true) ?: [];
    $canonicalUrl = $seo['canonical'] ?? rewrite_url($DetailProducts['canonical'] ?? '');

    // Helpers for construction widget
    $languageOf = static function ($object) {
        $languages = $object->languages ?? null;
        return $languages instanceof \Illuminate\Support\Collection ? $languages->first() : $languages;
    };
    $objectName = static fn($object) => $languageOf($object)->name ?? ($object->name ?? '');
    $objectDescription = static fn($object) => $languageOf($object)->description ?? ($object->description ?? '');
    $objectUrl = static fn($object) => !empty($languageOf($object)->canonical ?? null)
        ? rewrite_url($languageOf($object)->canonical)
        : '#';
    $imageFallbacks = [
        '/uploads/images/thiet-ke/thiet-ke-phong-khach-01.jpg',
        '/uploads/images/thiet-ke/thiet-ke-phong-hop-01.jpg',
        '/uploads/images/thiet-ke/thiet-ke-phong-giam-doc-01.jpg',
        '/uploads/images/thiet-ke/thiet-ke-nha-hang-01.jpg',
    ];
    $imageUrl = static function ($path, $index = 0) use ($imageFallbacks) {
        $path = $path ?: '';
        if ($path && file_exists(public_path(ltrim($path, '/')))) {
            return asset($path);
        }
        return asset($imageFallbacks[$index % count($imageFallbacks)]);
    };

    // Construction Widget
    $constructionWidget = $widgets['karaoke-construction'] ?? null;
    $constructionBg = '';
    if ($constructionWidget) {
        $albumArray = is_string($constructionWidget->album) ? json_decode($constructionWidget->album, true) : $constructionWidget->album;
        $constructionBg = $albumArray[0] ?? '';
    }
    $constructionCards = collect($constructionWidget->object ?? []);
@endphp

<!-- Hero Banner (Breadcrumbs with Background Banner Image) -->
<section class="about-hero">
    @php
        $heroTitle = $DetailCatalogues['title'] ?? $productCatalogue->name ?? '';
        $heroBg = '/userfiles/image/bg-about-hero.png';
    @endphp
    <img class="about-hero__bg" src="{{ asset($heroBg) }}" alt="{{ $heroTitle }}" loading="lazy">
    <div class="hero-overlay"></div>
    <div class="uk-container uk-container-center hero-content">
        <h1 class="hero-title">
            <span class="decor-line left"></span>
            {{ $heroTitle }}
            <span class="decor-line right"></span>
        </h1>
        <div class="hero-breadcrumb">
            <ul class="uk-breadcrumb">
                <li><a href="{{ url('/') }}" title="Trang chủ">Trang chủ</a></li>
                @foreach(($breadcrumb ?? $Breadcrumb ?? []) as $item)
                    <li><a href="{{ rewrite_url($item['canonical'] ?? '') }}" title="{{ $item['title'] ?? '' }}">{{ $item['title'] ?? '' }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

<section class="main-content">
    <div class="uk-container uk-container-center">
        <section class="product-detail">
            <section class="panel-body">
                <div class="uk-grid uk-grid-width-large-1-2">
                    
                    <!-- Left Column: White Card Gallery -->
                    <div class="gallery-container">
                        <div class="main-image-card">
                            <a id="main-image-link" href="{{ $DetailProducts['images'] }}" data-uk-lightbox="{group:'#product-gallery'}" title="{{ $DetailProducts['title'] }}">
                                <img id="main-product-image" src="{{ $DetailProducts['images'] }}" alt="{{ $DetailProducts['title'] }}">
                            </a>
                        </div>
                        
                        @if(count($albums))
                            <div class="thumbnail-grid">
                                <div class="thumbnail-card active" data-src="{{ $DetailProducts['images'] }}">
                                    <img src="{{ $DetailProducts['images'] }}" alt="{{ $DetailProducts['title'] }}">
                                </div>
                                @foreach($albums as $album)
                                    @php $albumImage = $album['images'] ?? $album['image'] ?? ''; @endphp
                                    @if($albumImage)
                                        <div class="thumbnail-card" data-src="{{ getthumb($albumImage) }}">
                                            <img src="{{ getthumb($albumImage) }}" alt="{{ $DetailProducts['title'] }}">
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Right Column: Product Info -->
                    <section class="productDetail-intro">
                        <h1 class="title">{{ $DetailProducts['title'] }}</h1>
                        
                        <div class="separator-line"></div>
                        
                        @php
                            $price = (float) ($DetailProducts['price'] ?? 0);
                            $saleoff = (float) ($DetailProducts['saleoff'] ?? 0);
                        @endphp
                        <div class="productDetail-price uk-flex uk-flex-middle">
                            @if($price > 0)
                                <div class="product-pricenew">{{ number_format($saleoff > 0 ? $saleoff : $price) }}đ</div>
                                @if($saleoff > 0)
                                    <div class="product-priceold">{{ number_format($price) }}đ</div>
                                @endif
                            @else
                                <div class="product-pricenew">Liên hệ</div>
                            @endif
                        </div>
                        
                        <div class="separator-line"></div>
                        
                        <div class="short-description-section">
                            <h4 class="section-title">Mô tả ngắn</h4>
                            <div class="desc-content">
                                {!! $DetailProducts['description'] ?? '' !!}
                            </div>
                        </div>
                        
                        <div class="productDetail-buy">
                            <form class="uk-form form">
                                <div class="quantity-row uk-flex uk-flex-middle">
                                    <span class="label">Chọn số lượng</span>
                                    <div class="quantity-control uk-flex uk-flex-middle">
                                        <button type="button" class="qty-btn btn-down">-</button>
                                        <input type="text" value="01" class="quantity-input" readonly>
                                        <button type="button" class="qty-btn btn-up">+</button>
                                    </div>
                                </div>
                                
                                <div class="action-buttons">
                                    <button type="button" class="btn-primary-action btn-lienhe-modal">LIÊN HỆ</button>
                                    <div class="sub-actions-flex">
                                        <a href="tel:{{ $system['contact_hotline'] ?? '' }}" class="btn-sub-action btn-call">GỌI NGAY</a>
                                        <a href="https://zalo.me/{{ preg_replace('/\D/', '', $system['contact_hotline'] ?? '') }}" target="_blank" class="btn-sub-action btn-zalo">CHAT ZALO</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <div class="share-section uk-flex uk-flex-middle">
                            <span class="share-label">Chia sẻ:</span>
                            <div class="share-icons uk-flex">
                                <a href="#" class="share-icon icon-link" title="Sao chép liên kết" onclick="copyLink(event)"><i class="fa fa-link"></i></a>
                                <a href="https://zalo.me/share?url={{ urlencode($canonicalUrl) }}" target="_blank" class="share-icon icon-zalo" title="Chia sẻ Zalo">
                                    <img src="https://chat.zalo.me/favicon.ico" alt="Zalo" style="width:18px;height:18px;object-fit:contain;filter:brightness(0) invert(1);">
                                </a>
                                <a href="https://www.messenger.com/t/" target="_blank" class="share-icon icon-messenger" title="Messenger"><i class="fa fa-commenting"></i></a>
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($canonicalUrl) }}" target="_blank" class="share-icon icon-facebook" title="Facebook"><i class="fa fa-facebook"></i></a>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Product Tabs Content -->
                <section class="product-content uk-margin-large-top">
                    <ul class="uk-list uk-clearfix tab-control uk-flex" data-uk-switcher="{connect:'#tab-content'}">
                        <li class="uk-active">Thông tin chi tiết</li>
                        <li>Đánh giá sản phẩm</li>
                    </ul>
                    <ul id="tab-content" class="uk-switcher tab-content">
                        <li class="tab-pane-content">
                            <div id="tocDiv"><ol id="tocListAncarat"></ol></div>
                            <article class="article content-detail-new">{!! $DetailProducts['content'] ?? '' !!}</article>
                        </li>
                        <li class="tab-pane-content">
                            <div class="comments"><div class="fb-comments" data-href="{{ $canonicalUrl }}" data-width="100%" data-numposts="3"></div></div>
                        </li>
                    </ul>
                </section>
            </section>
        </section>

        <!-- Related Products Section (Concept Image 3) -->
        @if(!empty($products_same))
            <section class="related-products-section uk-margin-large-top">
                <header class="related-section-head">
                    <h2 class="section-title-cyan">
                        <span class="line"></span>
                        <span class="dot">•</span>
                        SẢN PHẨM LIÊN QUAN
                        <span class="dot">•</span>
                        <span class="line"></span>
                    </h2>
                </header>
                <section class="panel-body">
                    <div class="uk-grid lib-grid-15 uk-grid-width-1-2 uk-grid-width-medium-1-3 list-related-products">
                        @foreach($products_same as $prod)
                            @php
                                $pTitle = $prod['title'] ?? '';
                                $pHref = rewrite_url($prod['canonical'] ?? '');
                                $pImage = getthumb($prod['images'] ?? $prod['image'] ?? '');
                                $pDesc = cutnchar(strip_tags($prod['description'] ?? ''), 120) ?: 'Thiết kế phòng hát karaoke sang trọng, hiện đại, mang phong cách đẳng cấp và thời thượng nhất hiện nay...';
                            @endphp
                            <div class="related-item-wrapper">
                                <div class="related-product-card">
                                    <div class="card-thumb img-shine">
                                        <a class="img-cover" href="{{ $pHref }}" title="{{ $pTitle }}">
                                            <img src="{{ $pImage }}" alt="{{ $pTitle }}">
                                        </a>
                                    </div>
                                    <div class="card-info">
                                        <h3 class="card-title"><a href="{{ $pHref }}" title="{{ $pTitle }}">{{ $pTitle }}</a></h3>
                                        <p class="card-desc">{{ $pDesc }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </section>
        @endif
    </div>
</section>

<!-- Construction Section from Homepage -->
@if ($constructionWidget)
    <section class="karaoke-card-section karaoke-card-section--construction uk-margin-large-top" style="margin-top: 0 !important;">
        @if (!empty($constructionBg))
            <img class="karaoke-section-bg" src="{{ asset($constructionBg) }}"
                alt="{{ $constructionWidget->name ?? '' }}" loading="lazy">
        @endif
        <div class="karaoke-card-section__overlay"></div>
        <div class="karaoke-shell">
            @if (!empty($constructionWidget->name))
                <header class="karaoke-section-heading">
                    <span></span>
                    <h2>{{ $constructionWidget->name }}</h2>
                    <span></span>
                </header>
            @endif
            @if ($constructionCards->isNotEmpty())
                <div class="karaoke-room-grid">
                    @foreach ($constructionCards as $card)
                        @php
                            $cardTitle = $objectName($card);
                            $cardImage = $card->image ?? '';
                            $cardUrl = $objectUrl($card);
                        @endphp
                        <a class="karaoke-room-card" href="{{ $cardUrl }}" title="{{ $cardTitle }}">
                            @if ($cardImage)
                                <img src="{{ $imageUrl($cardImage, $loop->index) }}" alt="{{ $cardTitle }}"
                                    loading="lazy">
                            @endif
                            <span>{{ $cardTitle }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif

<!-- Copy Link Toast Notification -->
<div id="copy-toast" style="display:none; position:fixed; bottom:20px; right:20px; background-color:#00cbd6; color:#000; padding:12px 24px; border-radius:4px; font-weight:bold; z-index:9999; box-shadow:0 4px 10px rgba(0,0,0,0.3);">
    Đã sao chép liên kết thành công!
</div>

<style>
    .main-content {
        background-color: #000000 !important;
        color: #ffffff !important;
        padding: 50px 0 !important;
    }
    
    /* Breadcrumb Banner */
    .about-hero {
        position: relative;
        height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .about-hero__bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 1;
    }
    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        z-index: 2;
    }
    .hero-content {
        position: relative;
        z-index: 3;
        text-align: center;
        width: 100%;
    }
    .hero-title {
        font-family: 'Yeseva One', serif;
        font-size: 40px !important;
        color: #ffffff !important;
        margin: 0 0 15px 0 !important;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
    }
    .hero-title .decor-line {
        height: 1px;
        background-color: #00cbd6;
        width: 60px;
    }
    .hero-breadcrumb {
        display: flex;
        justify-content: center;
        margin-top: 15px;
    }
    .uk-breadcrumb {
        display: inline-flex;
        align-items: center;
        list-style: none;
        padding: 0;
        margin: 0;
        gap: 8px;
    }
    .uk-breadcrumb > li {
        color: #ffffff;
        font-size: 14px;
    }
    .uk-breadcrumb > li > a {
        color: rgba(255,255,255,0.7) !important;
        text-decoration: none !important;
    }
    .uk-breadcrumb > li > a:hover {
        color: #00cbd6 !important;
    }
    .uk-breadcrumb > li:nth-child(n+2):before {
        content: '/';
        color: rgba(255,255,255,0.5) !important;
        margin-right: 8px;
    }
    
    /* Product Detail Gallery */
    .gallery-container {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    .main-image-card {
        background-color: #ffffff !important;
        border-radius: 8px;
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 350px;
        max-height: 480px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.5);
    }
    .main-image-card img {
        max-width: 100%;
        max-height: 400px;
        object-fit: contain;
    }
    .thumbnail-grid {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .thumbnail-card {
        background-color: #ffffff !important;
        border-radius: 6px;
        padding: 5px;
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s ease;
        overflow: hidden;
    }
    .thumbnail-card img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    .thumbnail-card.active, .thumbnail-card:hover {
        border-color: #00e0ff;
    }
    
    /* Product Intro Column */
    .productDetail-intro {
        padding-left: 30px;
    }
    .productDetail-intro .title {
        font-size: 28px !important;
        font-weight: bold !important;
        color: #00cbd6 !important; /* Cyan color matching the mockup */
        margin: 0 0 15px 0 !important;
        line-height: 1.3;
    }
    .separator-line {
        height: 1px;
        background-color: #222222;
        margin: 15px 0;
    }
    .productDetail-price {
        margin: 15px 0;
        gap: 15px;
    }
    .productDetail-price .product-pricenew {
        font-size: 30px !important;
        font-weight: bold !important;
        color: #ff1e1e !important; /* Bold red new price */
    }
    .productDetail-price .product-priceold {
        font-size: 18px !important;
        color: rgba(255, 255, 255, 0.5) !important;
        text-decoration: line-through !important;
    }
    
    .short-description-section .section-title {
        color: #ffffff !important;
        font-weight: bold !important;
        font-size: 16px !important;
        margin-bottom: 10px !important;
    }
    .short-description-section .desc-content {
        color: rgba(255, 255, 255, 0.8) !important;
        font-size: 14px;
        line-height: 1.6;
    }
    
    /* Quantity Selector */
    .quantity-row {
        gap: 20px;
        margin: 20px 0;
    }
    .quantity-row .label {
        font-size: 14px;
        color: #ffffff;
    }
    .quantity-control {
        border: 1px solid #333;
        border-radius: 20px;
        background-color: #111;
        padding: 2px;
        overflow: hidden;
    }
    .quantity-control .qty-btn {
        background: transparent;
        border: none;
        color: #ffffff;
        width: 30px;
        height: 30px;
        cursor: pointer;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.2s;
    }
    .quantity-control .qty-btn:hover {
        background-color: #222;
        border-radius: 50%;
    }
    .quantity-control .quantity-input {
        background: transparent;
        border: none;
        color: #ffffff;
        width: 40px;
        text-align: center;
        font-size: 14px;
        font-weight: bold;
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 25px;
        max-width: 100%;
    }
    .btn-primary-action {
        width: 100%;
        padding: 15px;
        background-color: #ff1e1e !important;
        color: #ffffff !important;
        font-size: 18px !important;
        font-weight: bold !important;
        font-family: var(--second-font), sans-serif !important;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .btn-primary-action:hover {
        background-color: #e00000 !important;
    }
    .sub-actions-flex {
        display: flex;
        gap: 12px;
        width: 100%;
    }
    .btn-sub-action {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 14px;
        border-radius: 4px;
        font-weight: bold;
        font-family: var(--second-font), sans-serif !important;
        text-decoration: none !important;
        color: #ffffff !important;
        font-size: 15px;
        transition: opacity 0.2s;
        box-sizing: border-box;
        text-transform: uppercase;
    }
    .btn-sub-action:hover {
        opacity: 0.9;
    }
    .btn-call {
        background-color: #3bcacb !important;
    }
    .btn-zalo {
        background-color: #1e88e5 !important;
    }
    
    /* Share section */
    .share-section {
        margin-top: 25px;
        gap: 15px;
    }
    .share-label {
        font-size: 14px;
        color: rgba(255,255,255,0.6);
    }
    .share-icons {
        gap: 10px;
    }
    .share-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        color: #ffffff !important;
        font-size: 14px;
        transition: transform 0.2s;
        text-decoration: none !important;
    }
    .share-icon:hover {
        transform: scale(1.1);
    }
    .icon-link {
        background-color: #475569 !important;
    }
    .icon-zalo {
        background-color: #0084ff !important;
        padding: 6px;
    }
    .icon-messenger {
        background-color: #0084ff !important;
    }
    .icon-facebook {
        background-color: #3b5998 !important;
    }
    
    /* Tabs styling Override (Image 3) */
    .tab-control {
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        background: transparent !important;
        display: flex !important;
        margin-bottom: 30px !important;
        padding-left: 0 !important;
        gap: 30px;
    }
    .tab-control li {
        background: transparent !important;
        background-color: transparent !important;
        border: none !important;
        border-radius: 0 !important;
        padding: 12px 0 !important;
        font-size: 16px !important;
        font-weight: bold !important;
        color: rgba(255, 255, 255, 0.6) !important;
        cursor: pointer !important;
        position: relative !important;
        transition: all 0.2s ease !important;
        list-style: none !important;
    }
    .tab-control li:before, .tab-control li:after {
        display: none !important;
        content: none !important;
    }
    .tab-control li:hover, .tab-control li.uk-active {
        background: transparent !important;
        background-color: transparent !important;
        color: #ffffff !important;
    }
    .tab-control li.uk-active {
        color: #00e0ff !important;
        border-bottom: 2px solid #00e0ff !important;
    }
    
    /* Content color styling (white text, cyan link) */
    .content-detail-new {
        color: #ffffff !important;
        font-size: 15px;
        line-height: 1.8;
    }
    .content-detail-new p, .content-detail-new span, .content-detail-new strong, .content-detail-new li, .content-detail-new h1, .content-detail-new h2, .content-detail-new h3, .content-detail-new h4 {
        color: #ffffff !important;
    }
    .content-detail-new a {
        color: #00e0ff !important;
        text-decoration: underline !important;
    }
    .content-detail-new a:hover {
        color: #ffffff !important;
    }
    
    /* Related Products Section (Image 3) */
    .section-title-cyan {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #00cbd6 !important;
        font-size: 24px !important;
        font-weight: bold !important;
        text-transform: uppercase;
        margin: 40px 0 30px 0 !important;
        gap: 15px;
    }
    .section-title-cyan .line {
        height: 1px;
        background-color: #00cbd6;
        width: 150px;
    }
    .section-title-cyan .dot {
        font-size: 16px;
    }
    .list-related-products > div {
        margin-bottom: 25px;
    }
    .related-product-card {
        border: 2px solid #00cbd6 !important;
        border-radius: 4px;
        background-color: transparent !important;
        overflow: hidden;
        transition: transform 0.3s;
        margin-bottom: 20px;
        height: 100%;
        box-sizing: border-box;
    }
    .related-product-card:hover {
        transform: translateY(-5px);
    }
    .related-product-card .card-thumb {
        width: 100%;
        height: 175px;
        overflow: hidden;
        border-bottom: 2px solid #00cbd6;
    }
    .related-product-card .card-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .related-product-card .card-info {
        padding: 15px;
    }
    .related-product-card .card-title {
        margin: 0 0 10px 0 !important;
        font-size: 16px !important;
        font-weight: bold !important;
        line-height: 1.4;
    }
    .related-product-card .card-title a {
        color: #ffffff !important;
        text-decoration: none !important;
        transition: color 0.2s;
    }
    .related-product-card .card-title a:hover {
        color: #00cbd6 !important;
    }
    .related-product-card .card-desc {
        font-size: 13px;
        color: rgba(255,255,255,0.7) !important;
        line-height: 1.5;
        margin: 0 !important;
    }
    
    @media (max-width: 959px) {
        .productDetail-intro {
            padding-left: 0;
            margin-top: 30px;
        }
        .section-title-cyan .line {
            width: 80px;
        }
    }
    
    /* Modal Close Button styling */
    #modal-contact-product .uk-close {
        background: #ff1e1e !important;
        color: #ffffff !important;
        opacity: 1 !important;
        border-radius: 50% !important;
        width: 30px !important;
        height: 30px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        top: -15px !important;
        right: -15px !important;
        text-decoration: none !important;
        position: absolute !important;
        padding: 0 !important;
        box-shadow: 0 0 10px rgba(255, 30, 30, 0.5) !important;
        z-index: 1010 !important;
        transition: transform 0.2s, background-color 0.2s !important;
    }
    #modal-contact-product .uk-close:hover {
        transform: scale(1.1);
        background-color: #e00000 !important;
    }
    #modal-contact-product .uk-close:after {
        content: "\f00d" !important;
        font-family: "FontAwesome" !important;
        font-size: 16px !important;
        display: block !important;
    }
</style>

<script>
    $(document).ready(function() {
        // Contact Modal trigger
        $('.btn-lienhe-modal').on('click', function(e) {
            e.preventDefault();
            UIkit.modal('#modal-contact-product').show();
        });

        // Thumbnail Click Switcher
        $('.thumbnail-card').on('click', function() {
            var src = $(this).attr('data-src');
            $('#main-product-image').attr('src', src);
            $('#main-image-link').attr('href', src);
            $('.thumbnail-card').removeClass('active');
            $(this).addClass('active');
        });

        // Quantity Adjuster
        $('.btn-up').click(function() {
            var input = $('.quantity-input');
            var val = parseInt(input.val()) || 1;
            val++;
            input.val(val < 10 ? '0' + val : val);
            $('.ajax-addtocart-btn').attr('data-quantity', val);
        });
        
        $('.btn-down').click(function() {
            var input = $('.quantity-input');
            var val = parseInt(input.val()) || 1;
            if (val > 1) {
                val--;
                input.val(val < 10 ? '0' + val : val);
                $('.ajax-addtocart-btn').attr('data-quantity', val);
            }
        });

        // Add to Cart AJAX click listener
        $('.ajax-addtocart-btn').click(function(e) {
            e.preventDefault();
            var quantity = parseInt($('.quantity-input').val()) || 1;
            var id = $(this).attr('data-id');
            var price = $(this).attr('data-price');
            
            // Re-trigger click handler setup in public scripts if any, or trigger custom request
            // Typically this calls /cart/create or ajax route in this codebase
            $.ajax({
                url: '{{ route("cart.store") }}',
                type: 'POST',
                data: {
                    id: id,
                    quantity: quantity,
                    price: price,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // Show a toast or modal
                    UIkit.modal('#modal-cart').show();
                    // Load cart content
                    $('.cart-content').html(response.html || '<p style="color:#000;padding:20px;">Đã thêm vào giỏ hàng thành công!</p>');
                },
                error: function() {
                    alert('Có lỗi xảy ra khi thêm vào giỏ hàng.');
                }
            });
        });

        // Table of contents
        tableOfContents("#tocListAncarat");
    });

    function tableOfContents(target) {
        $(target).empty();
        var index = 0;
        $(".content-detail-new h2, .content-detail-new h3").each(function() {
            $(this).before("<a name='toc-" + index + "'></a>");
            var li = "<li><a href='{{ $canonicalUrl }}#toc-" + index + "'>" + $(this).text() + "</a></li>";
            $(li).appendTo(target);
            index++;
        });
    }

    function copyLink(e) {
        e.preventDefault();
        var dummy = document.createElement('input'),
        text = window.location.href;
        document.body.appendChild(dummy);
        dummy.value = text;
        dummy.select();
        document.execCommand('copy');
        document.body.removeChild(dummy);
        
        // Show Toast
        var toast = document.getElementById('copy-toast');
        toast.style.display = 'block';
        setTimeout(function() {
            toast.style.display = 'none';
        }, 2500);
    }
</script>

<!-- Modal Contact Product -->
<div id="modal-contact-product" class="uk-modal">
    <div class="uk-modal-dialog" style="background: #0b0b0b; border: 1px solid rgba(0, 224, 255, 0.2); border-radius: 8px; max-width: 500px; padding: 30px;">
        <a class="uk-modal-close uk-close" style="color: #ffffff;"></a>
        <h3 style="font-family: var(--main-font); color: #00e0ff; font-weight: bold; text-transform: uppercase; margin: 0 0 10px 0; font-size: 20px;">Yêu cầu tư vấn sản phẩm</h3>
        <p style="color: rgba(255,255,255,0.6); font-size: 13px; margin: 0 0 20px 0;">Vui lòng gửi thông tin, kỹ sư thiết kế của chúng tôi sẽ liên hệ lại ngay để tư vấn cho quý khách.</p>
        
        <form action="{{ route('contact.save') }}" method="post" class="uk-form form-premium">
            @csrf
            <input type="hidden" name="address" value="Yêu cầu tư vấn: {{ $DetailProducts['title'] }} (ID: {{ $DetailProducts['id'] }})">
            
            <div class="form-group" style="margin-bottom: 15px;">
                <input type="text" name="name" required class="form-input" placeholder="Họ &amp; tên quý khách *" style="width:100%; background:rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.1); color:#fff; padding:12px; border-radius:6px; box-sizing:border-box; font-family:var(--second-font), sans-serif;">
            </div>
            
            <div class="form-group" style="margin-bottom: 15px;">
                <input type="text" name="phone" required class="form-input" placeholder="Số điện thoại di động *" style="width:100%; background:rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.1); color:#fff; padding:12px; border-radius:6px; box-sizing:border-box; font-family:var(--second-font), sans-serif;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <input type="email" name="email" class="form-input" placeholder="Địa chỉ Email (nếu có)" style="width:100%; background:rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.1); color:#fff; padding:12px; border-radius:6px; box-sizing:border-box; font-family:var(--second-font), sans-serif;">
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <textarea name="message" required class="form-textarea" style="width:100%; height:100px; background:rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.1); color:#fff; padding:12px; border-radius:6px; resize:none; box-sizing:border-box; line-height:1.5; font-family:var(--second-font), sans-serif;">Tôi muốn nhận tư vấn & báo giá chi tiết cho sản phẩm: {{ $DetailProducts['title'] }} (Link: {{ $canonicalUrl }})</textarea>
            </div>
            
            <button type="submit" class="btn-submit-premium" style="width: 100%; padding: 14px; background: linear-gradient(135deg, #00e0ff 0%, #0099ff 100%) !important; color: #030712 !important; font-size: 15px !important; font-weight: 800 !important; text-transform: uppercase; border: none !important; border-radius: 6px !important; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-family:var(--second-font), sans-serif;">Gửi yêu cầu ngay <i class="fa fa-paper-plane"></i></button>
        </form>
    </div>
</div>
@endsection
