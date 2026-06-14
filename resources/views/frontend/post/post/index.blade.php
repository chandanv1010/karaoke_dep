@extends('frontend.homepage.layout')

@section('content')
@php 
    $canonicalUrl = $seo['canonical'] ?? rewrite_url($DetailArticles['canonical'] ?? ''); 
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

    // Most Viewed posts in sidebar
    $most_viewed = \App\Support\LegacyFrontend::mostViewedPosts($languageId, 5);

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

<!-- Main Content Area -->
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
                
                <!-- Table of Contents sticky box -->
                <div id="tocDiv" class="hidden-mb hehe">
                    <h4 style="color: #00cbd6; font-weight: bold; text-transform: uppercase; margin: 0 0 15px 0; border-bottom: 1px solid #222; padding-bottom: 8px;">Mục lục bài viết</h4>
                    <ol id="tocListAncarat"></ol>
                </div>
                
                <!-- Most Viewed inside Sidebar (Clean, no orange line) -->
                @if(!empty($most_viewed))
                    <section class="mostViewed">
                        <header class="panel-head">
                            <div class="heading"><span>Bài đọc nhiều</span></div>
                        </header>
                        <section class="panel-body">
                            <div class="most-viewed-list">
                                @foreach($most_viewed as $postItem)
                                    @php
                                        $title = $postItem['title'] ?? '';
                                        $href = rewrite_url($postItem['canonical'] ?? '');
                                        $image = getthumb($postItem['images'] ?? null);
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
            
            <!-- Right Column: Content Body (3/4) -->
            <div class="uk-width-large-3-4">
                <div class="rightContent">
                    <section class="uk-panel article-detail">
                        <section class="panel-body">
                            <h1 class="title">{{ $DetailArticles['title'] ?? '' }}</h1>
                            
                            <div class="meta uk-flex uk-flex-middle">
                                <div class="time"><i class="fa fa-calendar" style="color:#00cbd6;"></i> Cập nhật: {{ $DetailArticles['created'] ?? '' }}</div>
                                <div class="viewed"><i class="fa fa-eye" style="color:#00cbd6;"></i> Lượt xem: {{ $DetailArticles['viewed'] ?? 0 }}</div>
                            </div>
                            
                            <article class="article">
                                <div class="content-detail-new">{!! $contentWithToc ?? $DetailArticles['content'] ?? '' !!}</div>
                            </article>
                            
                            <div class="share-box uk-flex uk-flex-middle mb10" style="margin-top: 30px; border-top: 1px solid #222; padding-top: 20px;">
                                <div class="facebook"><div class="fb-like" data-href="{{ $canonicalUrl }}" data-layout="button_count" data-action="like" data-show-faces="true" data-share="true"></div></div>
                            </div>
                            
                            <div class="comments" style="margin-top: 20px;"><div class="fb-comments" data-href="{{ $canonicalUrl }}" data-width="100%" data-numposts="3"></div></div>
                        </section>
                    </section>
                </div>
            </div>
            
        </div>
        
        <!-- Full-Width Related Articles (outside columns split, showing 8 posts in grid) -->
        @if(!empty($articles_same) && count($articles_same))
            <section class="uk-panel article-related-grid uk-margin-large-top">
                <header class="panel-head-clean">
                    <h2 class="heading-clean">CÁC BÀI VIẾT KHÁC</h2>
                </header>
                <div class="grid-8-posts">
                    @foreach(collect($articles_same)->take(8) as $postItem)
                        @php
                            $title = $postItem['title'] ?? '';
                            $href = rewrite_url($postItem['canonical'] ?? '');
                            $image = getthumb($postItem['images'] ?? null);
                            $description = cutnchar(strip_tags($postItem['description'] ?? ''), 120);
                        @endphp
                        <div class="post-card">
                            <div class="image-wrapper">
                                <a href="{{ $href }}" title="{{ $title }}">
                                    <img src="{{ $image }}" alt="{{ $title }}" loading="lazy">
                                </a>
                            </div>
                            <h3 class="title"><a href="{{ $href }}" title="{{ $title }}">{{ $title }}</a></h3>
                            <p class="description">{{ $description }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
        
    </div>
</section>

<style>
    .modules-posts {
        background-color: #000000 !important;
        color: #ffffff !important;
        padding: 60px 0 !important;
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
    
    /* Category Checklist */
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
    
    /* Table of contents sticky box */
    #tocDiv {
        background-color: #090909 !important;
        border: 1px solid #1a1a1a !important;
        border-radius: 6px;
        padding: 20px;
        margin-bottom: 25px;
    }
    .hehe {
        position: sticky;
        top: 20px;
    }
    #tocDiv #tocListAncarat {
        border: none !important;
        padding: 0 0 0 20px !important;
        margin: 0 !important;
        list-style: decimal !important;
    }
    #tocDiv #tocListAncarat li {
        color: #00cbd6 !important;
        margin-bottom: 10px;
    }
    #tocDiv a {
        font-size: 14px;
        color: #ffffff !important;
        font-weight: 500;
        text-decoration: none !important;
        transition: color 0.2s;
        display: inline !important;
    }
    #tocDiv a:hover {
        color: #00e0ff !important;
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
    
    /* Article Detail Area (Background transparent) */
    .article-detail {
        background-color: transparent !important;
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
        margin-bottom: 30px;
    }
    .article-detail .title {
        font-size: 32px !important;
        font-weight: 800 !important;
        color: #ffffff !important;
        margin: 0 0 20px 0 !important;
        line-height: 1.4;
        font-family: var(--main-font), sans-serif;
    }
    .article-detail .meta {
        background: transparent !important;
        background-color: transparent !important;
        border: none !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        font-size: 13px;
        color: rgba(255, 255, 255, 0.5);
        gap: 20px;
        margin-bottom: 30px;
        padding-bottom: 15px;
    }
    .article-detail .meta .time, .article-detail .meta .viewed {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    /* Content text and links */
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
    .content-detail-new img, .content-detail-new iframe {
        max-width: 100% !important;
        height: auto !important;
        margin: 20px auto !important;
        display: block !important;
        border-radius: 6px;
    }
    
    /* Related Articles Grid */
    .article-related-grid {
        background: transparent !important;
        border: none !important;
        margin-top: 40px !important;
        padding-top: 30px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
    }
    .panel-head-clean {
        margin-bottom: 30px;
        text-align: left;
    }
    .heading-clean {
        font-family: var(--main-font), sans-serif;
        font-size: 22px !important;
        font-weight: 800 !important;
        color: #ffffff !important;
        letter-spacing: 1.5px;
        margin: 0 !important;
        text-transform: uppercase;
    }
    .grid-8-posts {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 30px;
    }
    .post-card {
        display: flex;
        flex-direction: column;
    }
    .post-card .image-wrapper {
        border: 1px solid #00cbd6;
        margin-bottom: 15px;
        overflow: hidden;
        aspect-ratio: 1.52 / 1;
        border-radius: 4px;
    }
    .post-card .image-wrapper img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .3s ease;
    }
    .post-card:hover .image-wrapper img {
        transform: scale(1.045);
    }
    .post-card .title {
        font-family: var(--second-font), sans-serif !important;
        font-size: 15px !important;
        line-height: 1.4 !important;
        font-weight: 700 !important;
        margin: 0 0 10px 0 !important;
    }
    .post-card .title a {
        color: #ffffff !important;
        text-decoration: none !important;
        transition: color 0.2s;
    }
    .post-card .title a:hover {
        color: #00e0ff !important;
    }
    .post-card .description {
        font-size: 13px;
        line-height: 1.5;
        color: rgba(255, 255, 255, 0.72);
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    @media (max-width: 959px) {
        .col-reverse-959 {
            display: flex;
            flex-direction: column-reverse;
        }
        .grid-8-posts {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }
    }
    @media (max-width: 640px) {
        .grid-8-posts {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    $(document).ready(function(){
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
</script>
@endsection
