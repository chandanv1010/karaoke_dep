@extends('frontend.homepage.layout')

@php
    $languageId = $config['language'] ?? 1;

    // Helpers from homepage for karaoke-construction
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

    // Left sidebar categories checklist logic
    $currentChildren = \App\Models\ProductCatalogue::where('parent_id', $productCatalogue->id)
        ->where('publish', 2)
        ->with(['languages' => function($q) use ($languageId) {
            $q->where('language_id', $languageId);
        }])
        ->orderBy('order', 'asc')
        ->orderBy('id', 'desc')
        ->get();

    $sidebarCategories = collect();
    $sidebarTitle = '';

    if ($currentChildren->isNotEmpty()) {
        $sidebarCategories = $currentChildren;
        $sidebarTitle = $DetailCatalogues['title'] ?? $productCatalogue->languages->first()->pivot->name ?? $productCatalogue->name ?? '';
    } else {
        // If no children, show siblings (children of the parent)
        if ($productCatalogue->parent_id > 0) {
            $parentCat = \App\Models\ProductCatalogue::where('id', $productCatalogue->parent_id)
                ->with(['languages' => function($q) use ($languageId) {
                    $q->where('language_id', $languageId);
                }])
                ->first();
            
            if ($parentCat) {
                $sidebarTitle = $parentCat->languages->first()->pivot->name ?? $parentCat->name ?? '';
                $sidebarCategories = \App\Models\ProductCatalogue::where('parent_id', $productCatalogue->parent_id)
                    ->where('publish', 2)
                    ->with(['languages' => function($q) use ($languageId) {
                        $q->where('language_id', $languageId);
                    }])
                    ->orderBy('order', 'asc')
                    ->orderBy('id', 'desc')
                    ->get();
            }
        }
    }

    // Featured products in sidebar (take 3)
    $featuredProducts = [];
    if (!empty($widgets['featured-products']->object) && count($widgets['featured-products']->object)) {
        $featuredProducts = $widgets['featured-products']->object->take(3);
    } else {
        $featuredProducts = \App\Models\Product::where('publish', 2)
            ->with(['languages' => function($q) use ($languageId) {
                $q->where('language_id', $languageId);
            }])
            ->orderBy('id', 'desc')
            ->limit(3)
            ->get();
    }
@endphp

@section('content')
<!-- Hero Banner -->
<section class="about-hero">
    @php
        $heroTitle = $DetailCatalogues['title'] ?? $productCatalogue->name ?? '';
        // Fixed background image same as gioi-thieu page
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
                @foreach($Breadcrumb ?? [] as $item)
                    <li><a href="{{ rewrite_url($item['canonical'] ?? '') }}" title="{{ $item['title'] ?? '' }}">{{ $item['title'] ?? '' }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

<!-- Main Content (Grid 1/4 and 3/4) -->
<section class="main-content modules-products">
    <div class="uk-container uk-container-center">
        <div class="uk-grid uk-grid-medium col-reverse-959">
            
            <!-- Left Column (1/4) -->
            <div class="uk-width-large-1-4">
                
                <!-- Child/Sibling Categories Checklist Block -->
                @if($sidebarCategories->isNotEmpty())
                    <div class="aside-panel aside-categories-list">
                        <h3 class="aside-title">{{ $sidebarTitle }}</h3>
                        <ul class="category-list">
                            @foreach($sidebarCategories as $subCat)
                                @php
                                    $subName = $subCat->languages->first()->pivot->name ?? '';
                                    $subUrl = rewrite_url($subCat->languages->first()->pivot->canonical ?? '');
                                    $isActive = ($subCat->id === $productCatalogue->id);
                                @endphp
                                <li>
                                    <a href="{{ $subUrl }}" class="category-link {{ $isActive ? 'active' : '' }}">
                                        <span class="checkbox-box"></span>
                                        {{ $subName }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Featured Products Block -->
                @if(count($featuredProducts))
                    <div class="aside-panel aside-featured-products">
                        <h3 class="aside-title">Sản phẩm nổi bật</h3>
                        <div class="featured-list">
                            @foreach($featuredProducts as $item)
                                @php
                                    $pName = '';
                                    $pCanonical = '';
                                    $pDescription = '';
                                    $pImage = '';
                                    
                                    if ($item instanceof \App\Models\Product) {
                                        $pName = $item->languages->first()->pivot->name ?? $item->name ?? '';
                                        $pCanonical = $item->languages->first()->pivot->canonical ?? $item->canonical ?? '';
                                        $pDescription = $item->languages->first()->pivot->description ?? $item->description ?? '';
                                        $pImage = $item->image;
                                    } elseif (is_object($item)) {
                                        $pName = $item->pivot->name ?? $item->name ?? $item->title ?? '';
                                        $pCanonical = $item->pivot->canonical ?? $item->canonical ?? $item->slug ?? '';
                                        $pDescription = $item->pivot->description ?? $item->description ?? '';
                                        $pImage = $item->image ?? $item->images ?? '';
                                    } else {
                                        $pName = $item['pivot']['name'] ?? $item['name'] ?? $item['title'] ?? '';
                                        $pCanonical = $item['pivot']['canonical'] ?? $item['canonical'] ?? $item['slug'] ?? '';
                                        $pDescription = $item['pivot']['description'] ?? $item['description'] ?? '';
                                        $pImage = $item['image'] ?? $item['images'] ?? '';
                                    }
                                    
                                    $pUrl = rewrite_url($pCanonical);
                                    $pImage = \App\Support\LegacyFrontend::image($pImage);
                                    $pDesc = cutnchar(strip_tags($pDescription), 120);
                                @endphp
                                <div class="featured-item">
                                    <a href="{{ $pUrl }}" class="thumb-link img-cover">
                                        <img src="{{ $pImage }}" alt="{{ $pName }}" loading="lazy">
                                    </a>
                                    <h4 class="title"><a href="{{ $pUrl }}">{{ $pName }}</a></h4>
                                    <div class="desc">{{ $pDesc }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column (3/4) -->
            <div class="uk-width-large-3-4">
                <div class="rightContent">
                    <h2 class="category-title">{{ $DetailCatalogues['title'] ?? $productCatalogue->name ?? '' }}</h2>
                    
                    <!-- Category Description -->
                    @if(!empty($DetailCatalogues['description']))
                        <div class="category-description-wrapper">
                            <div class="category-description">
                                {!! $DetailCatalogues['description'] !!}
                            </div>
                            <a href="#" class="btn-readmore">Xem thêm <i class="fa fa-long-arrow-right"></i></a>
                        </div>
                    @endif

                    <!-- Category Products Grid -->
                    @if(!empty($productsList))
                        <section class="panel-products productCatalogue uk-margin-large-top">
                            <section class="panel-body">
                                <div class="uk-grid lib-grid-15 uk-grid-width-1-2 uk-grid-width-medium-1-3 list-product" data-uk-grid-match="{target:'.product-1 .product-title'}">
                                    @foreach($productsList as $product)
                                        @include('frontend.component.legacy-product-item', ['product' => $product])
                                    @endforeach
                                </div>
                                <div class="pagination-wrapper">
                                    {!! $PaginationList ?? '' !!}
                                </div>
                            </section>
                        </section>
                    @else
                        <p style="color:#888;">Dữ liệu đang được cập nhật...</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Construction Section from Homepage -->
@if ($constructionWidget)
    <section class="karaoke-card-section karaoke-card-section--construction">
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

<!-- Styles & Scripts -->
<style>
    .modules-products {
        background-color: #000 !important;
        color: #fff !important;
        padding: 60px 0 !important;
    }
    
    /* Category Title & Description */
    .category-title {
        color: #00e0ff !important;
        font-size: 28px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        margin-top: 0 !important;
        margin-bottom: 20px !important;
        font-family: var(--main-font);
    }
    
    .category-description-wrapper {
        margin-bottom: 40px;
    }
    
    .category-description {
        font-size: 14px;
        line-height: 1.6;
        color: #ffffff !important;
        transition: max-height 0.3s ease-out;
    }
    
    .category-description * {
        color: #ffffff !important;
    }
    
    .category-description a,
    .category-description a * {
        color: #00e0ff !important;
    }
    
    .category-description.collapsed {
        max-height: 120px;
        overflow: hidden;
        position: relative;
    }
    
    .category-description.collapsed::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 50px;
        background: linear-gradient(to bottom, transparent, #000);
        pointer-events: none;
    }
    
    .btn-readmore {
        display: none;
        align-items: center;
        gap: 8px;
        color: #00e0ff !important;
        font-weight: 700;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 15px;
        text-decoration: none !important;
        transition: color 0.2s;
    }
    
    .btn-readmore:hover {
        color: #fff !important;
    }
    
    /* Sidebar styling */
    .aside-panel {
        background-color: #080a10 !important;
        border: 1px solid #1f2833 !important;
        border-radius: 8px !important;
        padding: 24px !important;
        margin-bottom: 30px !important;
    }
    
    .aside-title {
        color: #00e0ff !important;
        font-size: 18px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        border-bottom: 2px solid #00e0ff !important;
        padding-bottom: 12px !important;
        margin-top: 0 !important;
        margin-bottom: 24px !important;
        font-family: var(--main-font);
    }
    
    /* Category checklist */
    .category-list {
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .category-list li {
        margin-bottom: 14px;
    }
    
    .category-list li:last-child {
        margin-bottom: 0;
    }
    
    .category-link {
        display: flex;
        align-items: center;
        color: rgba(255, 255, 255, 0.7) !important;
        text-decoration: none !important;
        font-size: 14px;
        transition: color 0.2s, font-weight 0.2s;
    }
    
    .category-link:hover, .category-link.active {
        color: #00e0ff !important;
    }
    
    .checkbox-box {
        width: 16px;
        height: 16px;
        border: 1px solid #334155;
        border-radius: 3px;
        margin-right: 12px;
        display: inline-block;
        flex-shrink: 0;
        position: relative;
        background-color: #020617;
        transition: border-color 0.2s, background-color 0.2s;
    }
    
    .category-link:hover .checkbox-box {
        border-color: #00e0ff;
    }
    
    .category-link.active .checkbox-box {
        border-color: #00e0ff;
        background-color: #00e0ff;
    }
    
    .category-link.active .checkbox-box::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 8px;
        height: 8px;
        background-color: #000;
        border-radius: 1px;
    }
    
    /* Sidebar Featured Products */
    .featured-list {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }
    
    .featured-item {
        border-bottom: 1px dashed #1e293b;
        padding-bottom: 24px;
    }
    
    .featured-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    
    .featured-item .thumb-link {
        display: block;
        width: 100%;
        aspect-ratio: 16 / 10;
        margin-bottom: 12px;
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid #1e293b;
    }
    
    .featured-item .thumb-link img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }
    
    .featured-item:hover .thumb-link img {
        transform: scale(1.05);
    }
    
    .featured-item .title {
        font-size: 14px !important;
        font-weight: 700 !important;
        margin: 0 0 6px 0 !important;
        line-height: 1.3;
    }
    
    .featured-item .title a {
        color: #00e0ff !important;
        text-decoration: none !important;
    }
    
    .featured-item .title a:hover {
        color: #fff !important;
    }
    
    .featured-item .desc {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.6);
        line-height: 1.4;
    }
    
    /* Product grid override */
    .product-item {
        background: transparent !important;
        box-shadow: none !important;
        border: none !important;
    }
    
    .product-1 {
        background: transparent !important;
        border: none !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        overflow: visible !important;
        text-align: center !important;
        transition: none !important;
    }
    
    .product-1 .product-thumb {
        background-color: #ffffff !important;
        padding: 0 !important;
        border-radius: 0 !important;
        border: none !important;
        height: 175px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        overflow: hidden !important;
        box-shadow: none !important;
    }

    .product-1 .product-thumb img {
        width: 100% !important;
        height: 175px !important;
        object-fit: cover !important;
    }
    
    .product-1 .product-info {
        padding: 15px 0 0 0 !important;
        background: transparent !important;
        border: none !important;
    }
    
    .product-1 .product-title {
        margin: 0 !important;
        text-align: center !important;
    }
    
    .product-1 .product-title a {
        color: #ffffff !important;
        font-weight: 600 !important;
        font-size: 15px !important;
        line-height: 1.4 !important;
        text-decoration: none !important;
        transition: color 0.2s;
    }
    
    .product-1:hover .product-title a {
        color: #00e0ff !important;
    }
    
    .about-hero .hero-content {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    .about-hero .hero-content .hero-title {
        line-height: 1.3 !important;
        margin-bottom: 0 !important;
    }

    /* Hero Breadcrumb styling */
    .about-hero .hero-breadcrumb {
        margin-top: 30px !important;
        display: flex !important;
        justify-content: center !important;
    }
    
    .about-hero .uk-breadcrumb {
        display: inline-flex !important;
        align-items: center !important;
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .about-hero .uk-breadcrumb > li {
        display: inline-flex !important;
        align-items: center !important;
        color: #ffffff !important;
        font-size: 14px !important;
    }
    
    .about-hero .uk-breadcrumb > li > a {
        color: #ffffff !important;
        text-decoration: none !important;
        transition: color 0.2s !important;
        font-weight: 500 !important;
    }
    
    .about-hero .uk-breadcrumb > li > a:hover {
        color: #00e0ff !important;
    }
    
    .about-hero .uk-breadcrumb > li > span {
        color: #ffffff !important;
        font-weight: 500 !important;
    }
    
    .about-hero .uk-breadcrumb > li:nth-child(n+2):before {
        color: rgba(255, 255, 255, 0.6) !important;
    }
    
    /* Pagination styling */
    .pagination-wrapper {
        margin-top: 50px;
        text-align: center;
    }
    
    .uk-pagination {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
        gap: 12px;
    }
    
    .uk-pagination > li {
        display: inline-block;
        margin: 0;
    }
    
    .uk-pagination > li > a,
    .uk-pagination > li > span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        color: #ffffff !important;
        font-size: 16px;
        font-weight: 600;
        text-decoration: none !important;
        transition: all 0.2s;
        box-sizing: border-box;
        border: none !important;
        background: transparent !important;
        border-radius: 0 !important;
    }
    
    .uk-pagination > li.uk-active > a,
    .uk-pagination > li.uk-active > span {
        background-color: #00e0ff !important;
        border: none !important;
        color: #000000 !important;
        width: 36px;
        height: 36px;
        border-radius: 50% !important;
    }
    
    .uk-pagination > li > a:hover {
        color: #00e0ff !important;
        background: transparent !important;
    }
    
    .uk-pagination > li.uk-disabled > span {
        color: rgba(255, 255, 255, 0.4) !important;
        cursor: not-allowed;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const readmoreBtn = document.querySelector('.btn-readmore');
        const desc = document.querySelector('.category-description');
        if (readmoreBtn && desc) {
            if (desc.scrollHeight > 120) {
                desc.classList.add('collapsed');
                readmoreBtn.style.display = 'inline-flex';
                readmoreBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (desc.classList.contains('collapsed')) {
                        desc.classList.remove('collapsed');
                        readmoreBtn.innerHTML = 'Thu gọn <i class="fa fa-long-arrow-left"></i>';
                    } else {
                        desc.classList.add('collapsed');
                        readmoreBtn.innerHTML = 'Xem thêm <i class="fa fa-long-arrow-right"></i>';
                    }
                });
            } else {
                readmoreBtn.style.display = 'none';
            }
        }
    });
</script>
@endsection
