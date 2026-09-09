@php
    $title = $product['title'] ?? '';
    $href = rewrite_url($product['canonical'] ?? '');
    // Khung card san pham theo CSS la 180-285px. Xin 600px: du net tren man
    // hinh retina 2x ma van nho hon rat nhieu so voi anh goc 500KB-1MB.
    $image = getthumb($product['images'] ?? null, 600);
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
