@extends('frontend.homepage.layout')

@section('content')
<div class="breadcrumb">
    <div class="uk-container uk-container-center">
        <ul class="uk-breadcrumb">
            <li><a href="{{ url('/') }}" title="Trang chủ">Trang chủ</a></li>
            @foreach($Breadcrumb ?? [] as $item)
                <li><a href="{{ rewrite_url($item['canonical'] ?? '') }}" title="{{ $item['title'] ?? '' }}">{{ $item['title'] ?? '' }}</a></li>
            @endforeach
        </ul>
    </div>
</div>
<section class="main-content">
    <div class="uk-container uk-container-center">
        <div class="uk-grid uk-grid-medium">
            <div class="uk-width-large-3-4">
                <section class="uk-panel article-detail">
                    <header class="panel-head">
                        <h1 class="heading-1"><span>{{ $DetailCatalogues['title'] ?? $postCatalogue->name ?? '' }}</span></h1>
                    </header>
                    <section class="panel-body">
                        @if(!empty($DetailCatalogues['description']))
                            <div class="description mb20">{!! $DetailCatalogues['description'] !!}</div>
                        @endif
                        @if(!empty($DetailCatalogues['content']))
                            <div class="content">{!! $DetailCatalogues['content'] !!}</div>
                        @elseif(!empty($introduce))
                            <div class="content">
                                @foreach($introduce as $value)
                                    @if(!empty($value))
                                        <div class="mb20">{!! $value !!}</div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </section>
                </section>
            </div>
            <div class="uk-width-large-1-4">
                @include('frontend.component.aside-2')
            </div>
        </div>
    </div>
</section>
@endsection
