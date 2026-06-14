@php
    $bannerImage = $introduce['block_4_image'] ?? null;
    $bannerUrl = '#';
    $bannerTarget = '_self';
    $bannerTitle = $introduce['block_4_heading'] ?? 'Banner';

    if (empty($bannerImage) || !file_exists(public_path(ltrim($bannerImage, '/')))) {
        $languageId = $config['language'] ?? 1;
        $bannerSlide = \App\Models\Slide::where('keyword', 'home-banner')->first();
        $bannerItems = $bannerSlide ? $bannerSlide->item[$languageId] ?? ($bannerSlide->item['1'] ?? []) : [];
        $banner = $bannerItems[0] ?? null;
        if ($banner) {
            $bannerImage = $banner['image'] ?? null;
            $bannerUrl = !empty($banner['canonical']) ? $banner['canonical'] : '#';
            $bannerTarget = !empty($banner['window']) ? $banner['window'] : '_self';
            $bannerTitle = !empty($banner['name']) ? $banner['name'] : $banner['alt'] ?? 'Banner';
        }
    }
@endphp

@if (!empty($bannerImage))
    <section class="home-banner-section about-banner-section">
        <div class="banner-item">
            <a href="{{ $bannerUrl }}" target="{{ $bannerTarget }}">
                <img src="{{ asset($bannerImage) }}" alt="{{ $bannerTitle }}" loading="lazy">
            </a>
        </div>
    </section>
@endif
