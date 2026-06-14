@extends('mobile.homepage.layout')
@section('content')
@include('mobile.component.breadcrumb')
<section class="block-article">
    <div class="uk-container uk-container-center">
        <div class="uk-panel article-detail">
            <article class="uk-article">
                <h1 class="uk-article-title">{{ $DetailProducts['title'] ?? '' }}</h1>
                <div class="fc-product-thumb"><img src="{{ $DetailProducts['images'] ?? '' }}" alt="{{ $DetailProducts['title'] ?? '' }}"></div>
                <div class="fc-product-price uk-margin-small-top">
                    @php $price = (float) ($DetailProducts['saleoff'] ?: $DetailProducts['price'] ?? 0); @endphp
                    <div class="fc-product-price-new uk-margin-small-bottom"><span class="uk-text-bold">{{ $price > 0 ? number_format($price).'đ' : 'Liên hệ' }}</span></div>
                </div>
                <div class="article-description">{!! $DetailProducts['description'] ?? '' !!}</div>
                <div class="uk-article-lead">{!! $DetailProducts['content'] ?? '' !!}</div>
            </article>
        </div>
    </div>
</section>
@endsection
