@extends('mobile.homepage.layout')
@section('content')
@include('mobile.component.breadcrumb')
<section class="block-article">
    <div class="uk-container uk-container-center">
        <div class="uk-panel article-detail">
            <article class="uk-article">
                <h1 class="uk-article-title">{{ $DetailArticles['title'] ?? '' }}</h1>
                <div class="uk-article-meta uk-clearfix"><time class="uk-float-right">{{ $DetailArticles['created'] ?? '' }}</time></div>
                <div class="article-description">{!! $DetailArticles['description'] ?? '' !!}</div>
                <div class="uk-article-lead">{!! $DetailArticles['content'] ?? '' !!}</div>
            </article>
        </div>
    </div>
</section>
@endsection
