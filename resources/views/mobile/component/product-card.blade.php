@php
    $title = $product['title'] ?? '';
    $href = rewrite_url($product['canonical'] ?? '');
    $image = getthumb($product['images'] ?? null);
    $price = (float) ($product['saleoff'] ?: $product['price'] ?? 0);
@endphp
<div class="fc-product">
    <div class="fc-product-thumb"><a href="{{ $href }}" class="fc-fit-img" title="{{ $title }}"><img src="{{ $image }}" alt="{{ $title }}"></a></div>
    <div class="fc-product-title uk-margin-small-bottom"><a href="{{ $href }}" class="uk-text-bold" title="{{ $title }}">{{ $title }}</a></div>
    <div class="fc-product-price"><div class="fc-product-price-new uk-margin-small-bottom"><span class="uk-text-bold">{{ $price > 0 ? number_format($price).'đ' : 'Liên hệ' }}</span></div></div>
    <div class="fc-product-link"><a href="{{ $href }}" title="{{ $title }}" class="uk-button">Xem chi tiết</a></div>
</div>
