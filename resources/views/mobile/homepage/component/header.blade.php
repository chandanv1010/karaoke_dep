@php $mainNav = navigations_array('main', $config['language'] ?? 1); @endphp
<header id="header">
    <div class="uk-container uk-container-center">
        <div class="top uk-flex uk-flex-middle uk-flex-space-between uk-margin-bottom">
            <a href="#offcanvas" class="uk-navbar-toggle" data-uk-offcanvas=""></a>
            <div id="offcanvas" class="uk-offcanvas">
                <div class="uk-offcanvas-bar">
                    <form action="{{ url('tim-kiem') }}" method="get" class="uk-search">
                        <input class="uk-search-field" name="keyword" type="search" placeholder="Tìm kiếm...">
                    </form>
                    <ul class="l1 uk-nav uk-nav-offcanvas uk-nav" data-uk-nav>
                        @foreach($mainNav as $item)
                            <li class="l1 uk-parent uk-position-relative"><a href="{{ $item['href'] }}" title="{{ $item['title'] }}" class="l1">{{ $item['title'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="uk-text-center-medium">
                <a href="{{ url('/') }}" class="logo" title="{{ $system['seo_meta_title'] ?? '' }}">{{ $system['homepage_brandname'] ?? $system['homepage_company'] ?? '' }}</a>
            </div>
            <div class="cart uk-vertical-align uk-position-relative uk-text-center">
                <a href="{{ url('gio-hang.html') }}"><img src="{{ asset('templates/mobile/images/upload/icon-cart.png') }}" alt="giỏ hàng"></a>
                <span class="num">{{ $cartCount ?? 0 }}</span>
            </div>
        </div>
    </div>
</header>
