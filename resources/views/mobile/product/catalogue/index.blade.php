@extends('mobile.homepage.layout')
@section('content')
@include('mobile.component.breadcrumb')
<section class="block margin-bottom-20 uk-margin-top">
    <div class="uk-container uk-container-center">
        <div class="uk-panel">
            <header class="uk-clearfix"><h3 class="block-title uk-margin-remove uk-float-left"><a href="{{ rewrite_url($DetailCatalogues['canonical'] ?? '') }}" class="fc-text-uppercase">{{ $DetailCatalogues['title'] ?? '' }}</a></h3></header>
            <div class="fc-body">
                @foreach(($child ?: [['post' => $productsList ?? [], 'description' => $DetailCatalogues['description'] ?? '']]) as $catalogue)
                    @if(!empty($catalogue['post']))
                        <div class="uk-grid uk-grid-small uk-grid-width-medium-1-3 uk-grid-width-small-1-2">
                            @foreach($catalogue['post'] as $product)
                                @include('mobile.component.product-card', ['product' => $product])
                            @endforeach
                        </div>
                    @endif
                    @if(!empty($catalogue['description'])){!! $catalogue['description'] !!}@endif
                @endforeach
                <div class="pagination uk-text-right">{!! $PaginationList ?? '' !!}</div>
            </div>
        </div>
    </div>
</section>
@endsection
