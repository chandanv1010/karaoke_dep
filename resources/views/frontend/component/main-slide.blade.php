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
                @foreach($slideItems as $slideIndex => $item)
                    <li class="item">
                        <a class="image img-cover" href="{{ $item['url'] ?? $item['canonical'] ?? '#' }}" title="{{ $item['name'] ?? '' }}">
                            {{--
                                Truoc day moi slide deu co san src nen tai ngay tu dau
                                (class="lazy" khong co tac dung khi src da duoc dat),
                                cac slide sau tranh bang thong voi anh LCP.

                                Slide dau la phan tu LCP -> tai ngay, fetchpriority=high.
                                Slide con lai -> loading=lazy, chi tai khi can.
                            --}}
                            @if($slideIndex === 0)
                                <img src="{{ $item['image'] ?? '' }}"
                                     alt="{{ $item['alt'] ?? $item['name'] ?? '' }}"
                                     width="1920" height="700"
                                     fetchpriority="high" decoding="async">
                            @else
                                <img src="{{ $item['image'] ?? '' }}"
                                     alt="{{ $item['alt'] ?? $item['name'] ?? '' }}"
                                     width="1920" height="700"
                                     loading="lazy" decoding="async">
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
            <a href="" class="uk-slidenav uk-slidenav-previous" data-uk-slideshow-item="previous"></a>
            <a href="" class="uk-slidenav uk-slidenav-next" data-uk-slideshow-item="next"></a>
        </div>
    </section>
@endif
