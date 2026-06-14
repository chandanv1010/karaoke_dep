@php
    $introTitle = $introduce['block_1_company'] ?? 'AZKTV Việt Nam';
    $introSubtitle = $introduce['block_1_en'] ?? '';
    $introShortDesc = $introduce['block_1_description'] ?? '';
    $introBody = $introduce['block_2_content'] ?? '';
    
    $features = [];
    if (!empty($introduce['block_9_block_1_title'])) {
        $features[] = ['title' => $introduce['block_9_block_1_title'], 'icon' => 'fa fa-trophy'];
    }
    if (!empty($introduce['block_9_block_2_title'])) {
        $features[] = ['title' => $introduce['block_9_block_2_title'], 'icon' => 'fa fa-cogs'];
    }
    if (!empty($introduce['block_9_block_3_title'])) {
        $features[] = ['title' => $introduce['block_9_block_3_title'], 'icon' => 'fa fa-pencil-square-o'];
    }
    if (!empty($introduce['block_9_block_4_title'])) {
        $features[] = ['title' => $introduce['block_9_block_4_title'], 'icon' => 'fa fa-line-chart'];
    }
    
    $introImages = [];
    if (!empty($introduce['block_3_image_1'])) {
        $introImages[] = $introduce['block_3_image_1'];
    }
    if (!empty($introduce['block_3_image_2'])) {
        $introImages[] = $introduce['block_3_image_2'];
    }
    if (!empty($introduce['block_3_image_3'])) {
        $introImages[] = $introduce['block_3_image_3'];
    }
    
    $stats = [];
    for ($i = 1; $i <= 4; $i++) {
        $number = $introduce["block_2_box_{$i}_number"] ?? '';
        $text = $introduce["block_2_box_{$i}_text"] ?? '';
        if ($number && $text) {
            $stats[] = ['number' => $number, 'text' => $text];
        }
    }
@endphp

<section class="about-intro">
    <div class="karaoke-shell">
        <header class="about-intro__header">
            <h2>{{ $introTitle }}</h2>
            @if(!empty($introSubtitle))
                <div class="about-intro__subtitle">{{ $introSubtitle }}</div>
            @endif
            @if(!empty($introShortDesc))
                <div class="about-intro__text">{!! $introShortDesc !!}</div>
            @endif
        </header>

        @if(count($features) > 0)
            <div class="about-intro__features">
                @foreach($features as $feature)
                    <div class="about-intro__feature">
                        <span>
                            <i class="{{ $feature['icon'] }}"></i>
                        </span>
                        <strong>{{ $feature['title'] }}</strong>
                    </div>
                @endforeach
            </div>
        @endif

        @if(count($introImages) >= 2)
            <div class="about-intro__images">
                <div class="about-intro__image about-intro__image--left">
                    <img src="{{ asset($introImages[0]) }}" alt="{{ $introTitle }}" loading="lazy">
                </div>
                <div class="about-intro__image about-intro__image--right">
                    <img src="{{ asset($introImages[1]) }}" alt="{{ $introTitle }}" loading="lazy">
                </div>
            </div>
        @endif

        @if(!empty($introBody))
            <div class="about-intro__footer-text">{!! $introBody !!}</div>
        @endif

        @if(count($stats) > 0)
            <div class="about-intro__stats">
                @foreach($stats as $stat)
                    <div class="about-intro__stat">
                        <strong>{{ $stat['number'] }}</strong>
                        <span>{{ $stat['text'] }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
