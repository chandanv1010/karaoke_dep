@extends('mobile.homepage.layout')
@section('content')
<section class="block margin-bottom-20 uk-margin-top">
    <div class="uk-container uk-container-center">
        <div class="uk-panel">
            <header class="uk-clearfix"><h3 class="block-title uk-margin-remove uk-float-left"><span class="fc-text-uppercase">Tìm kiếm</span></h3></header>
            <div class="fc-body">
                <div class="uk-grid uk-grid-small uk-grid-width-medium-1-3 uk-grid-width-small-1-2">
                    @foreach($productsList ?? [] as $product)
                        @include('mobile.component.product-card', ['product' => $product])
                    @endforeach
                </div>
                <div class="pagination uk-text-right">{!! $PaginationList ?? '' !!}</div>
            </div>
        </div>
    </div>
</section>
@endsection
