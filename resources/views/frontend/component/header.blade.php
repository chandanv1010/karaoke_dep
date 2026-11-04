@php
    $sitelinkNav = $menu['sitelink'] ?? [];
    $filteredSitelink = array_filter($sitelinkNav, function($item) {
        $name = $item['item']->languages->first()->pivot->name ?? '';
        return strpos(strtolower($name), 'dmca') === false && strpos(strtolower($name), 'img') === false;
    });
@endphp
<header class="header-new {{ (isset($postCatalogue) && in_array($postCatalogue->canonical, ['gioi-thieu', 've-chung-toi'])) ? 'header-transparent' : '' }}" id="header">
    <section class="topbar-new uk-visible-large">
        <div class="uk-container uk-container-center">
            <div class="container">
                @if(count($filteredSitelink))
                    <ul class="sitelink-list">
                        @foreach($filteredSitelink as $item)
                            @php
                                $name = $item['item']->languages->first()->pivot->name ?? '';
                                $canonical = write_url($item['item']->languages->first()->pivot->canonical ?? '', true, true);
                            @endphp
                            <li>
                                <a href="{{ $canonical }}" title="{{ strip_tags($name) }}">{!! strip_tags($name) !!}</a>
                            </li>
                            @if(!$loop->last)
                                <span class="divider">|</span>
                            @endif
                        @endforeach
                    </ul>
                @endif
                <div class="social-links">
                    {{-- Da bo icon Google+ (dich vu dong cua tu 2019, link chi 301 ve blog Google). --}}
                    <a href="{{ $system['seo_facebook'] ?? '#' }}" title="Facebook" aria-label="Facebook" rel="noopener"><i class="fa fa-facebook" aria-hidden="true"></i></a>
                    <a href="{{ $system['seo_twitter'] ?? '#' }}" title="Twitter" aria-label="Twitter" rel="noopener"><i class="fa fa-twitter" aria-hidden="true"></i></a>
                    <a href="{{ $system['seo_youtube'] ?? '#' }}" title="YouTube" aria-label="YouTube" rel="noopener"><i class="fa fa-youtube" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>
    </section>

    <section class="main-header-new">
        <div class="uk-container uk-container-center">
            <div class="container">
                <div class="logo">
                    <a href="{{ url('/') }}" title="{{ $system['seo_meta_title'] ?? '' }}">
                        <img src="{{ $system['homepage_logo'] ?? '' }}" alt="{{ $system['seo_meta_title'] ?? '' }}" width="396" height="162" fetchpriority="high" decoding="async">
                    </a>
                </div>
                
                @if(isset($menu['main']))
                    <nav class="uk-flex uk-flex-middle navigation" aria-label="Menu chính">
                        <ul class="main-nav">
                            {!! $menu['main'] !!}
                        </ul>
                        <a href="#search-modal" data-uk-modal class="search-btn" aria-label="Tìm kiếm"><i class="fa fa-search"></i></a>
                    </nav>
                @endif
            </div>
        </div>
    </section>

    

    <section class="mobile-header-new uk-hidden-large">
        <a href="#offcanvas" class="toggle-btn" data-uk-offcanvas="{target:'#offcanvas'}"><i class="fa fa-bars"></i></a>
        <div class="logo">
            <a href="{{ url('/') }}" title="{{ $system['seo_meta_title'] ?? '' }}">
                <img src="{{ $system['homepage_logo'] ?? '' }}" alt="{{ $system['seo_meta_title'] ?? '' }}" width="396" height="162" fetchpriority="high" decoding="async">
            </a>
        </div>
        <a href="#search-modal" data-uk-modal class="toggle-btn"><i class="fa fa-search"></i></a>
    </section>
</header>

<div id="search-modal" class="uk-modal">
    <div class="uk-modal-dialog">
        <a class="uk-modal-close uk-close"></a>
        <form action="{{ url('tim-kiem') }}" method="get" class="uk-form">
            <input type="text" name="keyword" class="uk-width-1-1 uk-form-large" placeholder="Nhập từ khóa tìm kiếm...">
            <button class="uk-button uk-button-primary uk-width-1-1 uk-margin-top" type="submit">Tìm kiếm</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var header = document.querySelector('.main-header-new');
        var topbar = document.querySelector('.topbar-new');
        
        if(header && topbar) {
            window.addEventListener('scroll', function() {
                if (window.scrollY > topbar.offsetHeight) {
                    header.classList.add('is-sticky');
                    document.body.style.paddingTop = header.offsetHeight + 'px';
                } else {
                    header.classList.remove('is-sticky');
                    document.body.style.paddingTop = 0;
                }
            });
        }
    });
</script>
