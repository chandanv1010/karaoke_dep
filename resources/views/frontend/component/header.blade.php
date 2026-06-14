@php
    $mainNav = navigations_array('main', $config['language'] ?? 1);
    $sitelinkNav = navigations_array('sitelink', $config['language'] ?? 1);
    $filteredSitelink = array_filter($sitelinkNav, function($item) {
        return strpos(strtolower($item['title']), 'dmca') === false && strpos(strtolower($item['title']), 'img') === false;
    });
@endphp
<header class="header-new {{ (isset($postCatalogue) && in_array($postCatalogue->canonical, ['gioi-thieu', 've-chung-toi'])) ? 'header-transparent' : '' }}" id="header">
    <section class="topbar-new uk-visible-large">
        <div class="uk-container uk-container-center">
            <div class="container">
                @if(count($filteredSitelink))
                    <ul class="sitelink-list">
                        @foreach($filteredSitelink as $item)
                            <li>
                                <a href="{{ $item['href'] }}" title="{{ strip_tags($item['title']) }}">{!! strip_tags($item['title']) !!}</a>
                            </li>
                            @if(!$loop->last)
                                <span class="divider">|</span>
                            @endif
                        @endforeach
                    </ul>
                @endif
                <div class="social-links">
                    <a href="{{ $system['seo_facebook'] ?? '#' }}" title="Facebook"><i class="fa fa-facebook"></i></a>
                    <a href="{{ $system['seo_twitter'] ?? '#' }}" title="Twitter"><i class="fa fa-twitter"></i></a>
                    <a href="{{ $system['seo_google'] ?? '#' }}" title="Google"><i class="fa fa-google"></i></a>
                    <a href="{{ $system['seo_youtube'] ?? '#' }}" title="YouTube"><i class="fa fa-youtube"></i></a>
                </div>
            </div>
        </div>
    </section>

    <section class="main-header-new">
        <div class="uk-container uk-container-center">
            <div class="container">
                <div class="logo">
                    <a href="{{ url('/') }}" title="{{ $system['seo_meta_title'] ?? '' }}">
                        <img src="{{ $system['homepage_logo'] ?? '' }}" alt="{{ $system['seo_meta_title'] ?? '' }}">
                    </a>
                </div>
                
                @if(count($mainNav))
                    <div class="uk-flex uk-flex-middle">
                        <ul class="main-nav">
                            @foreach($mainNav as $item)
                                <li class="{{ (url()->current() == url($item['href'])) ? 'active' : '' }}">
                                    <a href="{{ $item['href'] }}" title="{{ $item['title'] }}">{{ $item['title'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                        <a href="#search-modal" data-uk-modal class="search-btn"><i class="fa fa-search"></i></a>
                    </div>
                @endif
            </div>
        </div>
    </section>

    

    <section class="mobile-header-new uk-hidden-large">
        <a href="#offcanvas" class="toggle-btn" data-uk-offcanvas="{target:'#offcanvas'}"><i class="fa fa-bars"></i></a>
        <div class="logo">
            <a href="{{ url('/') }}" title="{{ $system['seo_meta_title'] ?? '' }}">
                <img src="{{ $system['homepage_logo'] ?? '' }}" alt="{{ $system['seo_meta_title'] ?? '' }}">
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
