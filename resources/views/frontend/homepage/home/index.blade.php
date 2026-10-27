@extends('frontend.homepage.layout')
@section('content')
<h1 style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0;">{{ $system['seo_meta_title'] ?? '' }}</h1>
@include('frontend.homepage.component.karaoke-home')
@endsection
