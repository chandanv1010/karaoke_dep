@extends('frontend.homepage.layout')
@section('content')
<section class="main-content modules-products">
    <div class="uk-container uk-container-center">
        <section class="panel-products productCatalogue">
            <header class="panel-head skin-1 uk-flex uk-flex-middle uk-flex-space-between">
                <h1 class="heading-1"><span>{{ $seo['meta_title'] ?? 'Tìm kiếm' }}</span></h1>
            </header>
            <section class="panel-body">
                @if(!empty($productsList))
                    <div class="uk-grid lib-grid-15 uk-grid-width-1-2 uk-grid-width-medium-1-3 uk-grid-width-xlarge-1-4 list-product">
                        @foreach($productsList as $product)
                            @include('frontend.component.legacy-product-item', ['product' => $product])
                        @endforeach
                    </div>
                    {!! $PaginationList ?? '' !!}
                @else
                    <p style="color:#666;">Không tìm thấy sản phẩm phù hợp.</p>
                @endif
            </section>
        </section>
    </div>
</section>
@endsection
