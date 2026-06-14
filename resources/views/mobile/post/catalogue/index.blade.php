@extends('mobile.homepage.layout')
@section('content')
@include('mobile.component.breadcrumb')
<section class="block margin-bottom-20 uk-margin-top">
    <div class="uk-container uk-container-center">
        <div class="uk-panel">
            <header class="uk-clearfix"><h3 class="block-title uk-margin-remove uk-float-left"><a href="{{ rewrite_url($DetailCatalogues['canonical'] ?? '') }}" class="fc-text-uppercase">{{ $DetailCatalogues['title'] ?? '' }}</a></h3></header>
            <div class="fc-body">
                @foreach($ArticlesList ?? $highlight_post ?? [] as $post)
                    @php $href = rewrite_url($post['canonical'] ?? ''); @endphp
                    <div class="fc-featured uk-clearfix">
                        <a href="{{ $href }}" title="{{ $post['title'] ?? '' }}" class="fc-thumb"><img src="{{ getthumb($post['images'] ?? null) }}" alt="{{ $post['title'] ?? '' }}"></a>
                        <div class="fc-info">
                            <h3 class="title uk-margin-remove"><a href="{{ $href }}" title="{{ $post['title'] ?? '' }}" class="uk-text-bold">{{ $post['title'] ?? '' }}</a></h3>
                            <span class="uk-text-muted fc-article-meta">{{ $post['created'] ?? '' }}</span>
                            <div class="fc-article-desc">{{ cutnchar(strip_tags($post['description'] ?? ''), 180) }}</div>
                        </div>
                    </div>
                @endforeach
                <div class="pagination uk-text-right">{!! $PaginationList ?? '' !!}</div>
            </div>
        </div>
    </div>
</section>
@endsection
