@php
    $slideItems = ($slides['index-slide']['item'] ?? []) ?: (\App\Support\LegacyFrontend::slides(['index-slide'], $config['language'] ?? 1)['index-slide']['item'] ?? []);
@endphp
@if(count($slideItems))
    <section id="index-slide">
        <div class="uk-container uk-container-center">
            <div class="uk-slidenav-position" data-uk-slideshow="{animation:'scroll', autoplay:true, autoplayInterval:2000}">
                <ul class="uk-slideshow">
                    @foreach($slideItems as $item)
                        <li><a href="{{ $item['url'] ?? '#' }}" title="{{ $item['name'] ?? '' }}" class="fc-thumb"><img src="{{ $item['image'] ?? '' }}" alt="{{ $item['alt'] ?? $item['name'] ?? '' }}"></a></li>
                    @endforeach
                </ul>
                <a href="" class="uk-slidenav uk-slidenav-previous" data-uk-slideshow-item="previous"></a>
                <a href="" class="uk-slidenav uk-slidenav-next" data-uk-slideshow-item="next"></a>
            </div>
        </div>
    </section>
@endif
