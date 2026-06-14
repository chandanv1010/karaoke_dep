@php $partner = \App\Support\LegacyFrontend::slides(['partner'], $config['language'] ?? 1)['partner']['item'] ?? []; @endphp
@if(count($partner))
    <section id="slide-partner" class="margin-bottom-25">
        <div class="uk-container uk-container-center">
            <div class="uk-panel">
                <div class="uk-slidenav-position" data-uk-slider>
                    <div class="uk-slider-container">
                        <ul class="uk-slider fc-list uk-grid uk-grid-width-medium-1-3 uk-grid-width-small-1-2">
                            @foreach($partner as $item)
                                <li class="item"><a href="{{ $item['url'] ?? '#' }}" title="{{ $item['name'] ?? '' }}"><img src="{{ $item['image'] ?? '' }}" alt="{{ $item['alt'] ?? $item['name'] ?? '' }}"></a></li>
                            @endforeach
                        </ul>
                    </div>
                    <a href="" class="uk-slidenav uk-slidenav-previous" data-uk-slider-item="previous"></a>
                    <a href="" class="uk-slidenav uk-slidenav-next" data-uk-slider-item="next"></a>
                </div>
            </div>
        </div>
    </section>
@endif
