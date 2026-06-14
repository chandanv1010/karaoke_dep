@extends('frontend.homepage.layout')

@section('content')
<h1 class="about-page-title">{{ $system['seo_meta_title'] ?? '' }}</h1>

@include('frontend.post.catalogue.component.about-hero')
@include('frontend.post.catalogue.component.about-intro')
@include('frontend.post.catalogue.component.about-services')
@include('frontend.post.catalogue.component.about-banner')
@include('frontend.post.catalogue.component.about-construction')
@endsection
