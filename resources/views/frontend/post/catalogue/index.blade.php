@extends('frontend.homepage.layout')
@section('content')
@php
    $languageId = $config['language'] ?? 1;

    // Get children of the current post catalogue
    $currentChildren = \App\Models\PostCatalogue::where('parentid', $postCatalogue->id)
        ->where('pubish', 2)
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
        $sidebarTitle = $DetailCatalogues['title'] ?? $postCatalogue->languages->first()->pivot->name ?? $postCatalogue->name ?? '';
    } else {
        // If no children, show siblings (children of the parent)
        if ($postCatalogue->parentid > 0) {
            $parentCat = \App\Models\PostCatalogue::where('id', $postCatalogue->parentid)
                ->with(['languages' => function($q) use ($languageId) {
                    $q->where('language_id', $languageId);
                }])
                ->first();
            
            if ($parentCat) {
                $sidebarTitle = $parentCat->languages->first()->pivot->name ?? $parentCat->name ?? '';
                $sidebarCategories = \App\Models\PostCatalogue::where('parentid', $postCatalogue->parentid)
                    ->where('pubish', 2)
                    ->with(['languages' => function($q) use ($languageId) {
                        $q->where('language_id', $languageId);
                    }])
                    ->orderBy('order', 'asc')
                    ->orderBy('id', 'desc')
                    ->get();
            }
        }
    }

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

    // Featured products in sidebar (take 3)
    $featuredProducts = [];
    if (!empty($widgets['featured-products']->object) && count($widgets['featured-products']->object)) {
        $featuredProducts = collect($widgets['featured-products']->object)->take(3);
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

<!-- Hero Banner (Breadcrumbs with Background Banner Image) -->
<section class="about-hero">
    @php
        $heroTitle = $DetailCatalogues['title'] ?? $postCatalogue->name ?? '';
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
<section class="main-content modules-posts">
    <div class="uk-container uk-container-center">
        <div class="uk-grid uk-grid-medium col-reverse-959">
            
            <!-- Left Column: Sidebar (1/4) -->
            <div class="uk-width-large-1-4">
                
                <!-- Sub/Sibling Categories Checklist -->
                @if($sidebarCategories->isNotEmpty())
                    <div class="aside-panel aside-categories-list">
                        <h3 class="aside-title">{{ $sidebarTitle }}</h3>
                        <ul class="category-list">
                            @foreach($sidebarCategories as $subCat)
                                @php
                                    $subName = $subCat->languages->first()->pivot->name ?? '';
                                    $subUrl = rewrite_url($subCat->languages->first()->pivot->canonical ?? '');
                                    $isActive = ($subCat->id === $postCatalogue->id);
                                @endphp
                                <li>
                                    <a href="{{ $subUrl }}" class="category-link {{ $isActive ? 'active' : '' }}">
                                        <span class="checkbox-box"></span>
                                        <span class="category-name">{{ $subName }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <!-- Most Viewed inside Sidebar -->
                @if(!empty($most_viewed))
                    <section class="mostViewed">
                        <header class="panel-head">
                            <div class="heading"><span>Bài đọc nhiều</span></div>
                        </header>
                        <section class="panel-body">
                            <div class="most-viewed-list">
                                @foreach($most_viewed as $post)
                                    @php
                                        $title = $post['title'] ?? '';
                                        $href = rewrite_url($post['canonical'] ?? '');
                                        $image = getthumb($post['images'] ?? null);
                                    @endphp
                                    <div class="article-item">
                                        <div class="thumb">
                                            <a class="image img-cover" href="{{ $href }}" title="{{ $title }}">
                                                <img src="{{ $image }}" alt="{{ $title }}">
                                            </a>
                                        </div>
                                        <h3 class="title"><a href="{{ $href }}" title="{{ $title }}">{{ $title }}</a></h3>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    </section>
                @endif
                
                <!-- Featured Products inside Sidebar -->
                @if(!empty($featuredProducts) && count($featuredProducts))
                    <div class="aside-panel sidebar-featured-products" style="margin-top: 25px;">
                        <h3 class="aside-title">Sản phẩm nổi bật</h3>
                        <div class="featured-list">
                            @foreach($featuredProducts as $item)
                                @php
                                    $pName = $item->languages->first()->pivot->name ?? $item->name ?? '';
                                    $pDesc = cutnchar(strip_tags($item->languages->first()->pivot->description ?? $item->description ?? ''), 100);
                                    $pHref = rewrite_url($item->languages->first()->pivot->canonical ?? $item->canonical ?? '');
                                    $pImage = getthumb($item->image ?? $item->images ?? '');
                                @endphp
                                <div class="featured-item">
                                    <a class="thumb-link img-shine" href="{{ $pHref }}" title="{{ $pName }}">
                                        <img src="{{ $pImage }}" alt="{{ $pName }}">
                                    </a>
                                    <h4 class="title"><a href="{{ $pHref }}" title="{{ $pName }}">{{ $pName }}</a></h4>
                                    <div class="desc">{{ $pDesc }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
            </div>
            
            <!-- Right Column: Articles Grid (3/4) -->
            <div class="uk-width-large-3-4">
                <div class="listArticleCatalogue">
                    
                    <!-- Category Description -->
                    @if(!empty($DetailCatalogues['description']))
                        <div class="category-description-wrapper">
                            <div class="category-description">
                                {!! $DetailCatalogues['description'] !!}
                            </div>
                            <a href="#" class="btn-readmore">Xem thêm <i class="fa fa-long-arrow-right"></i></a>
                        </div>
                    @endif
                    
                    <!-- Articles List -->
                    @if(!empty($ArticlesList))
                        <div class="listArticle">
                            @foreach($ArticlesList as $post)
                                @php
                                    $title = $post['title'] ?? '';
                                    $href = rewrite_url($post['canonical'] ?? '');
                                    $image = getthumb($post['images'] ?? null);
                                    $description = cutnchar(strip_tags($post['description'] ?? ''), 220);
                                    $created = $post['created'] ?? '';
                                @endphp
                                <div class="article-item">
                                    <article class="article-2 uk-grid uk-grid-collapse">
                                        <div class="uk-width-small-1-3">
                                            <div class="thumb img-flash">
                                                <a class="image img-cover" href="{{ $href }}" title="{{ $title }}">
                                                    <img src="{{ $image }}" alt="{{ $title }}">
                                                </a>
                                            </div>
                                        </div>
                                        <div class="uk-width-small-2-3">
                                            <div class="info">
                                                <h2 class="title"><a href="{{ $href }}" title="{{ $title }}">{{ $title }}</a></h2>
                                                <div class="meta-date"><i class="fa fa-calendar"></i> {{ $created }}</div>
                                                <div class="description">{{ $description }}</div>
                                            </div>
                                        </div>
                                    </article>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="pagination-wrapper">
                            {!! $PaginationList ?? '' !!}
                        </div>
                    @else
                        <p style="color: rgba(255,255,255,0.6); padding: 20px;">Dữ liệu bài viết đang được cập nhật...</p>
                    @endif
                    
                </div>
            </div>
            
        </div>
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

<style>
    .modules-posts {
        background-color: #000000 !important;
        color: #ffffff !important;
        padding: 60px 0 !important;
    }
    
    /* Breadcrumb styling */
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
    
    /* Left Sidebar Panel styles */
    .aside-panel {
        background-color: #090909 !important;
        border: 1px solid #1a1a1a !important;
        border-radius: 6px !important;
        padding: 20px !important;
        margin-bottom: 25px !important;
    }
    .aside-title {
        color: #00cbd6 !important;
        font-weight: bold !important;
        font-size: 18px !important;
        text-transform: uppercase !important;
        border-bottom: 1px solid #222;
        padding-bottom: 10px;
        margin: 0 0 20px 0 !important;
    }
    
    /* Checklist structure */
    .category-list {
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .category-list li {
        margin-bottom: 12px;
    }
    .category-list li:last-child {
        margin-bottom: 0;
    }
    .category-link {
        display: flex !important;
        align-items: center;
        text-decoration: none !important;
        color: rgba(255, 255, 255, 0.7) !important;
        font-size: 14px;
        transition: color 0.2s;
    }
    .category-link:hover, .category-link.active {
        color: #00cbd6 !important;
    }
    
    /* Checkbox box */
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
        border-color: #00cbd6;
    }
    .category-link.active .checkbox-box {
        border-color: #00cbd6;
        background-color: #00cbd6;
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
    
    /* Category Description styles */
    .category-description-wrapper {
        background-color: #090909;
        border: 1px solid #1a1a1a;
        border-radius: 6px;
        padding: 20px;
        margin-bottom: 30px;
    }
    .category-description {
        color: #ffffff !important;
        font-size: 14px;
        line-height: 1.6;
        position: relative;
        transition: all 0.3s ease;
    }
    .category-description.collapsed {
        max-height: 120px;
        overflow: hidden;
    }
    .category-description p, .category-description span, .category-description strong, .category-description li {
        color: #ffffff !important;
    }
    .category-description a {
        color: #00cbd6 !important;
        text-decoration: underline !important;
    }
    .category-description a:hover {
        color: #ffffff !important;
    }
    
    .btn-readmore {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #00cbd6 !important;
        font-size: 14px;
        font-weight: bold;
        margin-top: 15px;
        text-decoration: none !important;
    }
    .btn-readmore:hover {
        color: #ffffff !important;
    }
    
    /* Article item */
    .listArticle {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }
    .article-item {
        background-color: #090909 !important;
        border: 1px solid #1a1a1a;
        border-radius: 6px;
        padding: 15px;
        transition: transform 0.3s, border-color 0.3s;
    }
    .article-item:hover {
        transform: translateY(-3px);
        border-color: #00cbd6;
    }
    .article-item .thumb {
        border-radius: 4px;
        overflow: hidden;
    }
    .article-2 .image {
        height: 165px !important;
    }
    .article-item .thumb img {
        width: 100%;
        height: 165px;
        object-fit: cover;
    }
    .article-item .info {
        padding-left: 20px;
    }
    .article-item .title {
        font-size: 18px !important;
        font-weight: bold !important;
        margin: 0 0 10px 0 !important;
        line-height: 1.4;
    }
    .article-item .title a {
        color: #ffffff !important;
        text-decoration: none !important;
        transition: color 0.2s;
    }
    .article-item .title a:hover {
        color: #00cbd6 !important;
    }
    .article-item .meta-date {
        font-size: 13px;
        color: rgba(255,255,255,0.5);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .article-item .meta-date i {
        color: #00cbd6;
    }
    .article-item .description {
        font-size: 14px;
        color: rgba(255, 255, 255, 0.7) !important;
        line-height: 1.6;
    }
    
    /* Sidebar Most Viewed overrides */
    .mostViewed {
        background-color: #090909;
        border: 1px solid #1a1a1a;
        border-radius: 6px;
        padding: 20px;
        margin-top: 25px;
    }
    .mostViewed .panel-head {
        border-bottom: 1px solid #222;
        margin-bottom: 20px;
        padding-bottom: 10px;
        text-align: left !important;
    }
    .mostViewed .heading {
        margin: 0 !important;
        padding: 0 !important;
        text-align: left !important;
    }
    .mostViewed .heading:before,
    .mostViewed .heading:after {
        display: none !important;
        content: none !important;
    }
    .mostViewed .heading span {
        font-size: 18px !important;
        font-weight: bold !important;
        color: #00cbd6 !important;
        text-transform: uppercase;
        margin: 0 !important;
    }
    .most-viewed-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    .mostViewed .article-item {
        background-color: transparent !important;
        border: none !important;
        padding: 0 !important;
        display: flex;
        gap: 12px;
        transition: none;
        transform: none;
    }
    .mostViewed .article-item .thumb {
        width: 80px;
        height: 60px;
        flex-shrink: 0;
    }
    .mostViewed .article-item .thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .mostViewed .article-item .title {
        font-size: 14px !important;
        line-height: 1.4;
        font-weight: 500 !important;
        margin: 0 !important;
        text-align: left !important;
    }
    .mostViewed .article-item .title a {
        color: #ffffff !important;
        text-decoration: none !important;
    }
    .mostViewed .article-item .title a:hover {
        color: #00cbd6 !important;
    }
    
    /* Sidebar Featured Products overrides */
    .featured-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .featured-item {
        border-bottom: 1px dashed #222;
        padding-bottom: 20px;
    }
    .featured-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .featured-item .thumb-link {
        display: block;
        width: 100%;
        aspect-ratio: 16 / 10;
        margin-bottom: 10px;
        border-radius: 4px;
        overflow: hidden;
        border: 1px solid #1e293b;
    }
    .featured-item .thumb-link img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .featured-item .title {
        font-size: 14px !important;
        font-weight: bold !important;
        margin: 0 0 6px 0 !important;
        line-height: 1.3;
        text-align: left !important;
    }
    .featured-item .title a {
        color: #00cbd6 !important;
        text-decoration: none !important;
    }
    .featured-item .title a:hover {
        color: #fff !important;
    }
    .featured-item .desc {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.6);
        line-height: 1.4;
        text-align: left !important;
    }
    
    /* Pagination overrides */
    .pagination-wrapper {
        margin-top: 40px;
        text-align: center;
    }
    .uk-pagination {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
        gap: 10px;
    }
    .uk-pagination > li > a,
    .uk-pagination > li > span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        color: #ffffff !important;
        font-size: 15px;
        font-weight: bold;
        text-decoration: none !important;
        transition: all 0.2s;
        border: none !important;
        background: transparent !important;
        border-radius: 0 !important;
    }
    .uk-pagination > li.uk-active > a,
    .uk-pagination > li.uk-active > span {
        background-color: #00cbd6 !important;
        color: #000000 !important;
        border-radius: 50% !important;
    }
    .uk-pagination > li > a:hover {
        color: #00cbd6 !important;
    }
    .uk-pagination > li.uk-disabled > span {
        color: rgba(255,255,255,0.4) !important;
    }
    
    @media (max-width: 959px) {
        .col-reverse-959 {
            display: flex;
            flex-direction: column-reverse;
        }
        .article-item .info {
            padding-left: 0;
            margin-top: 15px;
        }
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
