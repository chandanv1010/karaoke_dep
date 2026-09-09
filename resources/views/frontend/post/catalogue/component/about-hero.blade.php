@php
    $heroTitle = $introduce['block_1_company'] ?? ($postCatalogue->name ?? 'Giới thiệu');
    $heroBg = $introduce['block_1_image'] ?? '/userfiles/thumb/Images/bg-about-hero.png';
@endphp
<section class="about-hero">
    @if(!empty($heroBg))
        {{--
            Nen hero trai het be ngang: 1425px (PC) / 390px (mobile).
            Nam ngay dau trang nen thuong la phan tu LCP -> dung
            fetchpriority="high" thay cho loading="lazy" truoc day.
        --}}
        <img class="about-hero__bg" src="{{ getthumb($heroBg, 1600) }}"
            srcset="{{ thumb_srcset($heroBg, [480, 768, 1200, 1600]) }}"
            sizes="100vw"
            alt="{{ $heroTitle }}" fetchpriority="high">
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
