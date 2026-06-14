@extends('frontend.homepage.layout')

@section('content')
<!-- Hero Banner -->
<section class="about-hero contact-hero-section">
    <div class="contact-hero-overlay"></div>
    <div class="uk-container uk-container-center hero-content">
        <span class="hero-eyebrow">AZKTV KARAOKE</span>
        <h1 class="hero-title">LIÊN HỆ VỚI CHÚNG TÔI</h1>
        <p class="hero-subtitle">Hệ thống Thiết kế & Thi công Phòng hát Karaoke chuyên nghiệp hàng đầu Việt Nam. Liên hệ ngay với chúng tôi để được tư vấn & nhận báo giá tối ưu nhất.</p>
        <div class="hero-breadcrumb">
            <ul class="uk-breadcrumb">
                <li><a href="{{ url('/') }}" title="Trang chủ">Trang chủ</a></li>
                <li class="uk-active"><span>Liên hệ</span></li>
            </ul>
        </div>
    </div>
</section>

<section class="main-content contact-page-section">
    <div class="uk-container uk-container-center">
        
        <!-- Section 1: Contact Information Cards Grid -->
        <div class="uk-grid uk-grid-medium uk-grid-width-medium-1-2 uk-grid-width-large-1-4 contact-info-cards" data-uk-grid-match="{target:'.info-card'}">
            
            <!-- Card 1: Hanoi Branch -->
            <div class="info-card-wrapper">
                <div class="info-card">
                    <div class="card-badge">Trụ sở chính</div>
                    <div class="card-icon"><i class="fa fa-map-marker"></i></div>
                    <h3 class="card-title">VĂN PHÒNG HÀ NỘI</h3>
                    <div class="card-desc">
                        <p class="address-text"><strong>Địa chỉ:</strong> {{ $system['contact_address'] ?? 'Tầng 4, Số 25 Vũ Ngọc Phan, Đống Đa, Hà Nội' }}</p>
                        <div class="card-action-links">
                            @if(!empty($system['contact_hotline']))
                                <a href="tel:{{ $system['contact_hotline'] }}" class="link-tel"><i class="fa fa-phone"></i> {{ $system['contact_hotline'] }}</a>
                            @endif
                            <a href="https://www.google.com/maps?q={{ urlencode($system['contact_address'] ?? 'Tầng 4, Số 25 Vũ Ngọc Phan, Đống Đa, Hà Nội') }}" target="_blank" class="link-map"><i class="fa fa-location-arrow"></i> Chỉ đường đi</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Card 2: Da Nang Branch -->
            <div class="info-card-wrapper">
                <div class="info-card">
                    <div class="card-badge">Miền Trung</div>
                    <div class="card-icon"><i class="fa fa-map-marker"></i></div>
                    <h3 class="card-title">VĂN PHÒNG ĐÀ NẴNG</h3>
                    <div class="card-desc">
                        <p class="address-text"><strong>Địa chỉ:</strong> {{ $system['contact_address_2'] ?? 'Số 119 Nguyễn Thị Cận, Hòa An, Cẩm Lệ, Đà Nẵng' }}</p>
                        <div class="card-action-links">
                            @if(!empty($system['contact_hotline']))
                                <a href="tel:{{ $system['contact_hotline'] }}" class="link-tel"><i class="fa fa-phone"></i> {{ $system['contact_hotline'] }}</a>
                            @endif
                            <a href="https://www.google.com/maps?q={{ urlencode($system['contact_address_2'] ?? 'Số 119 Nguyễn Thị Cận, Hòa An, Cẩm Lệ, Đà Nẵng') }}" target="_blank" class="link-map"><i class="fa fa-location-arrow"></i> Chỉ đường đi</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Card 3: HCM Branch & Factory -->
            @php
                $hcm_address = explode('-', $system['contact_address_3'] ?? '');
                $hcm_vp = trim($hcm_address[0] ?? '');
                $hcm_xuong = trim($hcm_address[1] ?? '');
            @endphp
            <div class="info-card-wrapper">
                <div class="info-card">
                    <div class="card-badge">Miền Nam & Xưởng</div>
                    <div class="card-icon"><i class="fa fa-industry"></i></div>
                    <h3 class="card-title">VP & XƯỞNG TP.HCM</h3>
                    <div class="card-desc">
                        <p class="address-text"><strong>VP:</strong> {{ str_replace('Trụ sở VP:', '', $hcm_vp) }}</p>
                        <p class="address-text"><strong>Xưởng:</strong> {{ str_replace('Xưởng sản xuất và chi nhánh VP:', '', $hcm_xuong) }}</p>
                        <div class="card-action-links">
                            @if(!empty($system['contact_hotline']))
                                <a href="tel:{{ $system['contact_hotline'] }}" class="link-tel"><i class="fa fa-phone"></i> {{ $system['contact_hotline'] }}</a>
                            @endif
                            <a href="https://www.google.com/maps?q={{ urlencode($hcm_xuong ?: ($hcm_vp ?: '')) }}" target="_blank" class="link-map"><i class="fa fa-location-arrow"></i> Chỉ đường đi</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Card 4: Support Channels -->
            <div class="info-card-wrapper">
                <div class="info-card">
                    <div class="card-badge">Hỗ trợ 24/7</div>
                    <div class="card-icon"><i class="fa fa-comments"></i></div>
                    <h3 class="card-title">KÊNH LIÊN HỆ ONLINE</h3>
                    <div class="card-desc">
                        <p class="address-text" style="margin-bottom: 12px;">Hotline tư vấn, hỗ trợ trực tuyến và các kênh mạng xã hội chính thức.</p>
                        <div class="card-action-links">
                            @if(!empty($system['contact_sell_tuvan']))
                                <a href="tel:{{ $system['contact_sell_tuvan'] }}" class="link-tel hot-pulse"><i class="fa fa-phone-square"></i> Tư vấn: {{ $system['contact_sell_tuvan'] }}</a>
                            @endif
                            @if(!empty($system['contact_email']))
                                <a href="mailto:{{ $system['contact_email'] }}" class="link-email"><i class="fa fa-envelope"></i> {{ $system['contact_email'] }}</a>
                            @endif
                        </div>
                        <div class="action-buttons-compact">
                            @if(!empty($system['contact_Zalo'] ?? $system['contact_zalo'] ?? ''))
                                <a href="https://zalo.me/{{ $system['contact_Zalo'] ?? $system['contact_zalo'] ?? '' }}" target="_blank" class="chat-btn-new btn-zalo-new">
                                    <i class="fa fa-comment"></i> Zalo Chat
                                </a>
                            @endif
                            <a href="{{ $system['seo_facebook'] ?? 'https://facebook.com' }}" target="_blank" class="chat-btn-new btn-fb-new">
                                <i class="fa fa-facebook-square"></i> Fanpage
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Section 2: Form & Map Split -->
        <div class="uk-grid uk-grid-large split-form-map">
            
            <!-- Left Side: Map Container (3/5) -->
            <div class="uk-width-large-3-5" style="margin-bottom: 30px;">
                <div class="contact-map-card">
                    <div class="map-card-header">
                        <i class="fa fa-globe"></i> BẢN ĐỒ BỘ CHỈ ĐƯỜNG VP CHÍNH
                    </div>
                    <div class="contact-map">
                        {!! $system['contact_map'] ?? '' !!}
                    </div>
                    <p class="map-footer-text">
                        <i class="fa fa-info-circle" style="color:#00e0ff;margin-right:5px;"></i> 
                        Quý khách hàng có thể phóng to, thu nhỏ hoặc nhấn vào nút "Chỉ đường" trên bản đồ để dễ dàng tìm kiếm đường đi đến showroom văn phòng của AZKTV.
                    </p>
                </div>
            </div>
            
            <!-- Right Side: Contact Form (2/5) -->
            <div class="uk-width-large-2-5" style="margin-bottom: 30px;">
                <div class="contact-form-card">
                    <h3 class="form-title">ĐĂNG KÝ <span>TƯ VẤN & BÁO GIÁ</span></h3>
                    <p class="form-subtitle">Điền thông tin dự án của bạn dưới đây, kỹ sư thiết kế của chúng tôi sẽ liên hệ tư vấn trong 15 phút.</p>

                    @if(session('success'))
                        <div class="uk-alert uk-alert-success" style="background-color:rgba(0,224,255,0.1); border:1px solid #00e0ff; color:#00e0ff; padding:15px; border-radius:8px; margin-bottom:20px; font-weight:bold;">
                            <i class="fa fa-check-circle"></i> {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="uk-alert uk-alert-danger" style="background-color:rgba(255,30,30,0.1); border:1px solid #ff1e1e; color:#ff1e1e; padding:15px; border-radius:8px; margin-bottom:20px; font-weight:bold;">
                            <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('contact.save') }}" method="post" class="uk-form form-premium">
                        @csrf
                        @if (isset($errors) && $errors->any())
                            <div class="callout callout-danger" style="padding:15px; background:rgba(255,30,30,0.1); border:1px solid #ff1e1e; color:#ff1e1e; border-radius:8px; margin-bottom:20px; font-size:13px; line-height:1.5;">
                                @foreach ($errors->all() as $error)
                                    <div><i class="fa fa-warning"></i> {{ $error }}</div>
                                @endforeach
                            </div>
                        @endif
                        
                        <div class="form-group">
                            <input type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="Họ &amp; tên quý khách *">
                        </div>
                        
                        <div class="form-group">
                            <input type="email" name="email" value="{{ old('email') }}" required class="form-input" placeholder="Địa chỉ Email *">
                        </div>
                        
                        <div class="form-group">
                            <input type="text" name="phone" value="{{ old('phone') }}" required class="form-input" placeholder="Số điện thoại di động *">
                        </div>
                        
                        <div class="form-group">
                            <input type="text" name="address" value="{{ old('address') }}" class="form-input" placeholder="Địa chỉ công trình (Tỉnh thành, quận huyện)">
                        </div>
                        
                        <div class="form-group">
                            <textarea name="message" required class="form-textarea" placeholder="Mô tả nhu cầu tư vấn thiết kế thi công (VD: số lượng phòng, phong cách cổ điển/hiện đại, ngân sách dự kiến...) *">{{ old('message') }}</textarea>
                        </div>
                        
                        <div class="form-submit-row">
                            <button type="submit" name="create" class="btn-submit-premium">Gửi yêu cầu ngay <i class="fa fa-paper-plane"></i></button>
                        </div>
                    </form>
                </div>
            </div>
            
        </div>
        
    </div>
</section>

<style>
    .contact-hero-section {
        position: relative;
        height: 380px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: linear-gradient(180deg, #09131d 0%, #050505 100%);
        border-bottom: 1px solid rgba(0, 224, 255, 0.1);
    }
    
    .contact-hero-section::before {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(0, 224, 255, 0.15) 0%, rgba(0, 224, 255, 0) 70%);
        top: -100px;
        left: -100px;
        border-radius: 50%;
        filter: blur(50px);
        pointer-events: none;
    }
    
    .contact-hero-section::after {
        content: '';
        position: absolute;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(0, 153, 255, 0.1) 0%, rgba(0, 153, 255, 0) 70%);
        bottom: -200px;
        right: -100px;
        border-radius: 50%;
        filter: blur(60px);
        pointer-events: none;
    }
    
    .contact-hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at center, rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0.85) 100%);
        z-index: 2;
    }
    
    .contact-hero-section .hero-content {
        position: relative;
        z-index: 3;
        text-align: center;
        width: 100%;
        max-width: 800px;
        padding: 0 15px;
    }
    
    .hero-eyebrow {
        font-family: var(--second-font), sans-serif;
        color: #00e0ff;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 3px;
        text-transform: uppercase;
        margin-bottom: 10px;
        display: block;
    }
    
    .contact-hero-section .hero-title {
        font-family: var(--main-font), sans-serif;
        font-size: 46px !important;
        font-weight: 900 !important;
        color: #ffffff !important;
        margin: 5px 0 15px 0 !important;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        text-shadow: 0 0 20px rgba(0, 224, 255, 0.2);
    }
    
    .hero-subtitle {
        font-size: 15px;
        color: rgba(255, 255, 255, 0.7);
        margin: 0 auto 25px auto;
        line-height: 1.6;
        max-width: 650px;
    }
    
    .contact-page-section {
        background: radial-gradient(circle at 50% 10%, rgba(0, 224, 255, 0.05) 0%, rgba(0, 0, 0, 0) 60%), #040406 !important;
        color: #ffffff !important;
        padding: 80px 0 !important;
        position: relative;
    }
    
    /* Breadcrumb styling */
    .contact-hero-section .hero-breadcrumb {
        display: flex;
        justify-content: center;
        margin-top: 25px;
    }
    
    .contact-hero-section .uk-breadcrumb {
        display: inline-flex;
        align-items: center;
        list-style: none;
        padding: 6px 20px;
        margin: 0;
        gap: 8px;
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
    
    .contact-hero-section .uk-breadcrumb > li {
        color: #ffffff;
        font-size: 13px;
    }
    
    .contact-hero-section .uk-breadcrumb > li > a {
        color: rgba(255, 255, 255, 0.6) !important;
        text-decoration: none !important;
        transition: color 0.2s;
    }
    
    .contact-hero-section .uk-breadcrumb > li > a:hover {
        color: #00e0ff !important;
    }
    
    .contact-hero-section .uk-breadcrumb > li:nth-child(n+2):before {
        content: '/';
        color: rgba(255, 255, 255, 0.3) !important;
        margin-right: 8px;
    }
    
    /* Contact Information Cards Grid */
    .contact-info-cards {
        margin-top: -140px;
        position: relative;
        z-index: 10;
        margin-bottom: 50px;
    }
    
    .info-card-wrapper {
        margin-bottom: 25px;
    }
    
    .info-card {
        background: rgba(13, 14, 18, 0.8) !important;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 12px;
        padding: 35px 24px;
        position: relative;
        overflow: hidden;
        height: 100%;
        box-sizing: border-box;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
        display: flex;
        flex-direction: column;
    }
    
    .info-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(0, 224, 255, 0.08) 0%, rgba(0, 153, 255, 0.02) 100%);
        opacity: 0;
        transition: opacity 0.4s ease;
        z-index: 1;
        pointer-events: none;
    }
    
    .info-card:hover {
        transform: translateY(-8px);
        border-color: rgba(0, 224, 255, 0.4);
        box-shadow: 0 15px 35px rgba(0, 224, 255, 0.15);
    }
    
    .info-card:hover::before {
        opacity: 1;
    }
    
    .info-card * {
        position: relative;
        z-index: 2;
    }
    
    .card-badge {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #00e0ff;
        background: rgba(0, 224, 255, 0.08);
        border: 1px solid rgba(0, 224, 255, 0.15);
        padding: 2px 10px;
        border-radius: 20px;
        align-self: flex-start;
        margin-bottom: 15px;
    }
    
    .card-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        background: linear-gradient(135deg, rgba(255,255,255,0.02) 0%, rgba(255,255,255,0.05) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        color: #00e0ff;
        font-size: 20px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        transition: all 0.3s ease;
    }
    
    .info-card:hover .card-icon {
        background: #00e0ff;
        color: #000000;
        box-shadow: 0 0 15px rgba(0, 224, 255, 0.4);
        border-color: #00e0ff;
    }
    
    .info-card .card-title {
        font-size: 17px !important;
        font-weight: 800 !important;
        color: #ffffff !important;
        margin: 0 0 15px 0 !important;
        letter-spacing: 0.5px;
        font-family: var(--main-font), sans-serif;
    }
    
    .info-card .card-desc {
        font-size: 13.5px;
        line-height: 1.6;
        color: rgba(255, 255, 255, 0.7) !important;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    
    .address-text {
        margin: 0 0 12px 0;
        color: rgba(255, 255, 255, 0.7) !important;
    }
    
    .address-text strong {
        color: #ffffff !important;
        font-weight: 600;
    }
    
    .card-action-links {
        margin-top: auto;
        padding-top: 15px;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .card-action-links a {
        color: rgba(255, 255, 255, 0.85) !important;
        text-decoration: none !important;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: color 0.2s;
    }
    
    .card-action-links a i {
        color: #00e0ff;
        font-size: 14px;
        width: 16px;
        text-align: center;
    }
    
    .card-action-links a:hover {
        color: #00e0ff !important;
    }
    
    .link-tel {
        font-weight: 600;
    }
    
    .hot-pulse i {
        animation: pulsePhone 1.5s infinite;
    }
    @keyframes pulsePhone {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
    
    .action-buttons-compact {
        display: flex;
        gap: 8px;
        margin-top: 15px;
    }
    
    .chat-btn-new {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        text-decoration: none !important;
        color: #ffffff !important;
        transition: all 0.2s ease;
    }
    
    .btn-zalo-new {
        background: rgba(0, 132, 255, 0.15) !important;
        border: 1px solid rgba(0, 132, 255, 0.3);
    }
    .btn-zalo-new:hover {
        background: #0084ff !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(0, 132, 255, 0.3);
    }
    
    .btn-fb-new {
        background: rgba(24, 119, 242, 0.15) !important;
        border: 1px solid rgba(24, 119, 242, 0.3);
    }
    .btn-fb-new:hover {
        background: #1877f2 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(24, 119, 242, 0.3);
    }
    
    /* Map and Form Split Grid styling */
    .split-form-map {
        margin-top: 30px !important;
    }
    
    .contact-map-card, .contact-form-card {
        background: rgba(10, 11, 14, 0.8) !important;
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 12px;
        padding: 35px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
        height: 100%;
        box-sizing: border-box;
        position: relative;
    }
    
    .contact-map-card::before {
        content: '';
        position: absolute;
        width: 150px;
        height: 150px;
        background: radial-gradient(circle, rgba(0, 224, 255, 0.08) 0%, rgba(0,0,0,0) 70%);
        bottom: 20px;
        right: 20px;
        pointer-events: none;
        z-index: 1;
    }
    
    .map-card-header {
        font-family: var(--main-font), sans-serif;
        font-size: 16px;
        font-weight: 800;
        color: #ffffff;
        text-transform: uppercase;
        margin-bottom: 25px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(255,255,255,0.06);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .map-card-header i {
        color: #00e0ff;
        font-size: 18px;
    }
    
    .contact-map {
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        transition: border-color 0.3s;
        z-index: 2;
        position: relative;
    }
    
    .contact-map:hover {
        border-color: rgba(0, 224, 255, 0.5);
    }
    
    .contact-map iframe {
        display: block;
        width: 100% !important;
        height: 420px !important;
    }
    
    .map-footer-text {
        font-size: 13px;
        color: rgba(255, 255, 255, 0.5);
        margin-top: 15px;
        line-height: 1.5;
        z-index: 2;
        position: relative;
    }
    
    /* Form styling */
    .form-title {
        font-family: var(--main-font), sans-serif;
        font-size: 20px !important;
        font-weight: 800 !important;
        color: #ffffff !important;
        text-transform: uppercase;
        margin: 0 0 8px 0 !important;
        letter-spacing: 0.5px;
    }
    
    .form-title span {
        color: #00e0ff;
    }
    
    .form-subtitle {
        font-size: 13px;
        color: rgba(255, 255, 255, 0.5);
        margin: 0 0 25px 0;
        line-height: 1.5;
    }
    
    .form-premium .form-group {
        position: relative;
        margin-bottom: 20px;
    }
    
    .form-premium .form-input, .form-premium .form-textarea {
        width: 100%;
        background-color: rgba(0, 0, 0, 0.4) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        color: #ffffff !important;
        padding: 14px 16px !important;
        border-radius: 8px !important;
        font-size: 13.5px;
        box-sizing: border-box;
        transition: all 0.3s ease;
    }
    
    .form-premium .form-input:focus, .form-premium .form-textarea:focus {
        border-color: #00e0ff !important;
        box-shadow: 0 0 15px rgba(0, 224, 255, 0.15);
        background-color: rgba(0, 0, 0, 0.6) !important;
        outline: none;
    }
    
    .form-premium ::placeholder {
        color: rgba(255, 255, 255, 0.35) !important;
    }
    
    .form-premium .form-textarea {
        height: 160px !important;
        resize: vertical;
    }
    
    /* Submit Premium Button */
    .btn-submit-premium {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, #00e0ff 0%, #0099ff 100%) !important;
        color: #030712 !important;
        font-size: 14.5px !important;
        font-weight: 800 !important;
        font-family: var(--second-font), sans-serif !important;
        text-transform: uppercase;
        letter-spacing: 1px;
        border: none !important;
        border-radius: 8px !important;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        box-shadow: 0 6px 20px rgba(0, 224, 255, 0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .btn-submit-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 224, 255, 0.45);
        background: linear-gradient(135deg, #00faf5 0%, #00aaff 100%) !important;
        color: #030712 !important;
    }
    
    .btn-submit-premium:active {
        transform: translateY(0);
    }
    
    /* Responsive grid overrides */
    @media (max-width: 960px) {
        .contact-hero-section {
            height: 320px;
        }
        .contact-hero-section .hero-title {
            font-size: 34px !important;
        }
        .contact-info-cards {
            margin-top: -60px;
        }
        .contact-map-card, .contact-form-card {
            padding: 25px;
        }
        .contact-map iframe {
            height: 350px !important;
        }
    }
    
    @media (max-width: 640px) {
        .contact-hero-section {
            height: 280px;
        }
        .contact-hero-section .hero-title {
            font-size: 28px !important;
        }
        .contact-info-cards {
            margin-top: -40px;
        }
        .contact-map-card, .contact-form-card {
            padding: 20px;
        }
    }
</style>
@endsection
