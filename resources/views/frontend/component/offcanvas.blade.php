<div id="offcanvas" class="uk-offcanvas">
    <div class="uk-offcanvas-bar karaoke-offcanvas">
        <div class="offcanvas-logo">
            <a href="{{ url('/') }}" title="{{ $system['seo_meta_title'] ?? '' }}">
                <img src="{{ $system['homepage_logo'] ?? '' }}" alt="{{ $system['seo_meta_title'] ?? '' }}">
            </a>
        </div>
        @if(isset($menu['mobile']))
            <ul class="uk-nav uk-nav-offcanvas">
                @foreach($menu['mobile'] as $item)
                    @php
                        $name = $item['item']->languages->first()->pivot->name;
                        $canonical = ($name == 'Trang chủ' || $name == 'Home') ? '.' : write_url($item['item']->languages->first()->pivot->canonical, true, true);
                    @endphp
                    <li><a href="{{ $canonical }}" title="{{ $name }}">{{ $name }}</a></li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
<div id="offcanvas-2" class="uk-offcanvas">
    <div class="uk-offcanvas-bar">
        @include('frontend.component.categories')
    </div>
</div>
