@extends('frontend.homepage.layout')
@section('content')
<h1 style="display:none">{{ $system['seo_meta_title'] ?? '' }}</h1>
@include('frontend.homepage.component.karaoke-home')
@endsection
