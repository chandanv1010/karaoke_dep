<div class="fc-breadcrumb uk-margin-bottom uk-margin-top">
    <div class="uk-container uk-container-center">
        <ul class="uk-breadcrumb uk-margin-remove">
            <li><a href="{{ url('/') }}" title="Trang chủ">Trang chủ</a></li>
            @foreach($Breadcrumb ?? [] as $item)
                <li><a href="{{ rewrite_url($item['canonical'] ?? '') }}" title="{{ $item['title'] ?? '' }}">{{ $item['title'] ?? '' }}</a></li>
            @endforeach
        </ul>
    </div>
</div>
