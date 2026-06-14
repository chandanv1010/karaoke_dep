@php
    $title = $product['title'] ?? '';
    $href = rewrite_url($product['canonical'] ?? '');
    $image = getthumb($product['images'] ?? null);
    $price = (float) ($product['price'] ?? 0);
    $saleoff = (float) ($product['saleoff'] ?? 0);
    $percent = percent($price, $saleoff);
    $skinClass = $skinClass ?? '';
@endphp
<div class="product-item">
    <div class="product-1 {{ $skinClass }} {{ $saleoff > 0 ? 'double' : '' }}">
        <div class="product-thumb img-shine">
            <a class="product-image img-cover" href="{{ $href }}" title="{{ $title }}"><img class="lazy" data-original="{{ $image }}" src="{{ $image }}" alt="{{ $title }}"></a>
        </div>
        <div class="product-info">
            <h3 class="product-title"><a href="{{ $href }}" title="{{ $title }}">{{ $title }}</a></h3>
        </div>
    </div>
</div>
