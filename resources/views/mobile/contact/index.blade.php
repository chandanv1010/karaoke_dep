@extends('mobile.homepage.layout')
@section('content')
<div class="fc-breadcrumb uk-margin-bottom uk-margin-top">
    <div class="uk-container uk-container-center">
        <ul class="uk-breadcrumb uk-margin-remove">
            <li><a href="{{ url('/') }}">Trang chủ</a></li>
            <li><a href="{{ route('contact.index') }}">Liên hệ</a></li>
        </ul>
    </div>
</div>
<section class="block-article">
    <div class="uk-container uk-container-center">
        <div class="uk-panel article-detail">
            <h1 class="uk-article-title">{{ $system['homepage_brandname'] ?? $system['homepage_company'] ?? 'Liên hệ' }}</h1>
            <div class="article-description">{!! $system['contact_contact'] ?? '' !!}</div>
            <div class="uk-article-lead">{!! $system['contact_map'] ?? '' !!}</div>
            <form action="{{ route('contact.save') }}" method="post" class="uk-form">
                @csrf
                <p><input type="text" name="name" class="uk-width-1-1" placeholder="Họ & tên *"></p>
                <p><input type="text" name="email" class="uk-width-1-1" placeholder="Email *"></p>
                <p><input type="text" name="phone" class="uk-width-1-1" placeholder="Số điện thoại"></p>
                <p><input type="text" name="address" class="uk-width-1-1" placeholder="Địa chỉ"></p>
                <p><textarea name="message" class="uk-width-1-1" placeholder="Nội dung *"></textarea></p>
                <p><input type="submit" class="uk-button" value="Gửi đi"></p>
            </form>
        </div>
    </div>
</section>
@endsection
