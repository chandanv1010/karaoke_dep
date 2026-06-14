@php
    $legacySlides = $slides ?? [];
    if (empty($legacySlides['index-slide']['item'])) {
        $legacySlides = $legacySlides + \App\Support\LegacyFrontend::slides(['index-slide', 'main-slide'], $config['language'] ?? 1);
    }
    $slideItems = $legacySlides['index-slide']['item'] ?? $legacySlides['main-slide']['item'] ?? $legacySlides['main']['item'] ?? [];
@endphp
@if(count($slideItems))
    <section class="mainslide">
        <div class="uk-slidenav-position" data-uk-slideshow="{animation:'swipe', autoplay:true, autoplayInterval:5500}">
            <ul class="uk-slideshow">
                @foreach($slideItems as $item)
                    <li class="item">
                        <a class="image img-cover" href="{{ $item['url'] ?? $item['canonical'] ?? '#' }}" title="{{ $item['name'] ?? '' }}">
                            <img class="lazy" data-original="{{ $item['image'] ?? '' }}" src="{{ $item['image'] ?? '' }}" alt="{{ $item['alt'] ?? $item['name'] ?? '' }}">
                        </a>
                    </li>
                @endforeach
            </ul>
            <a href="" class="uk-slidenav uk-slidenav-previous" data-uk-slideshow-item="previous"></a>
            <a href="" class="uk-slidenav uk-slidenav-next" data-uk-slideshow-item="next"></a>
        </div>
    </section>
@endif
