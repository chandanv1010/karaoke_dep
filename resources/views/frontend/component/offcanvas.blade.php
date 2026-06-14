@php
    $mainNav = navigations_array('main', $config['language'] ?? 1);
@endphp
<div id="offcanvas" class="uk-offcanvas">
    <div class="uk-offcanvas-bar karaoke-offcanvas">
        <div class="offcanvas-logo">
            <a href="{{ url('/') }}" title="{{ $system['seo_meta_title'] ?? '' }}">
                <img src="{{ $system['homepage_logo'] ?? '' }}" alt="{{ $system['seo_meta_title'] ?? '' }}">
            </a>
        </div>
        <ul class="uk-nav uk-nav-offcanvas">
            @foreach($mainNav as $item)
                <li><a href="{{ $item['href'] }}" title="{{ $item['title'] }}">{{ $item['title'] }}</a></li>
            @endforeach
        </ul>
    </div>
</div>
<div id="offcanvas-2" class="uk-offcanvas">
    <div class="uk-offcanvas-bar">
        @include('frontend.component.categories')
    </div>
</div>
