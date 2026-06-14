@php
    $heroTitle = $introduce['block_1_company'] ?? ($postCatalogue->name ?? 'Giới thiệu');
    $heroBg = $introduce['block_1_image'] ?? '/userfiles/thumb/Images/bg-about-hero.png';
@endphp
<section class="about-hero">
    @if(!empty($heroBg))
        <img class="about-hero__bg" src="{{ asset($heroBg) }}" alt="{{ $heroTitle }}" loading="lazy">
    @endif
    <div class="hero-overlay"></div>
    <div class="uk-container uk-container-center hero-content">
        <h1 class="hero-title">
            <span class="decor-line left"></span>
            {{ $heroTitle }}
            <span class="decor-line right"></span>
        </h1>
    </div>
</section>
