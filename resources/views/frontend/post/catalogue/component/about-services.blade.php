@php
    $servicesHeading = $introduce['block_8_heading'] ?? 'Dịch vụ của chúng tôi';
    $servicesDescription = $introduce['block_8_description'] ?? '';
    
    $services = [];
    $serviceIcons = [
        1 => 'fa fa-cube',
        2 => 'fa fa-volume-up',
        3 => 'fa fa-bullseye',
        4 => 'fa fa-object-group',
        5 => 'fa fa-cogs'
    ];
    for ($i = 1; $i <= 5; $i++) {
        $title = $introduce["block_8_block_{$i}_title"] ?? '';
        $desc = $introduce["block_8_block_{$i}_description"] ?? '';
        if ($title) {
            $services[] = [
                'title' => $title,
                'description' => $desc,
                'icon' => $serviceIcons[$i] ?? 'fa fa-cogs'
            ];
        }
    }
    
    $servicesBg = $introduce['block_8_image'] ?? '/userfiles/image/home/karaoke-section-bg.png';
@endphp

@if(count($services) > 0)
<section class="karaoke-card-section karaoke-card-section--services">
    <img class="karaoke-section-bg" src="{{ asset($servicesBg) }}" alt="{{ $servicesHeading }}" loading="lazy">
    <div class="karaoke-card-section__overlay"></div>

    <div class="karaoke-shell">
        <header class="karaoke-section-heading">
            <span></span>
            <h2>{{ $servicesHeading }}</h2>
            <span></span>
        </header>

        @if(!empty($servicesDescription))
            <div class="karaoke-services-description">{!! $servicesDescription !!}</div>
        @endif

        <div class="karaoke-services-grid">
            @foreach($services as $service)
                <article class="karaoke-service-card">
                    <span class="karaoke-service-card__icon">
                        <i class="{{ $service['icon'] }}"></i>
                    </span>
                    <strong>{{ $service['title'] }}</strong>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
