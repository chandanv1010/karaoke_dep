@php
    $randomProduct = \App\Support\LegacyFrontend::randomProducts($config['language'] ?? 1, 20);
@endphp
<aside class="aside aside-article">
    @if(!empty($system['banner_banner1']))
        <div class="banner img-cover uk-visible-large">{!! $system['banner_banner1'] !!}</div>
    @endif
    @if(count($randomProduct))
        <section class="randoomProduct">
            <header class="panel-head">
                <div class="heading"><span>Sản phẩm ngẫu nhiên</span></div>
            </header>
            <section class="panel-body">
                <div class="uk-grid uk-grid-small uk-grid-width-small-1-3 uk-grid-width-medium-1-4 uk-grid-width-large-1-2" data-uk-grid-match="{target: '.product .title'}">
                    @foreach($randomProduct as $product)
                        @php
                            $title = $product['title'] ?? '';
                            $href = rewrite_url($product['canonical'] ?? '');
                            $image = getthumb($product['images'] ?? null);
                            $price = (float) ($product['price'] ?? 0);
                            $saleoff = (float) ($product['saleoff'] ?? 0);
                            $percent = percent($price, $saleoff);
                        @endphp
                        <div class="product-item">
                            <div class="product">
                                <div class="thumb">
                                    <a class="image img-scaledown" href="{{ $href }}" title="{{ $title }}"><img src="{{ $image }}" alt="{{ $title }}"></a>
                                </div>
                                <div class="info">
                                    <h3 class="title"><a href="{{ $href }}" title="{{ $title }}">{{ $title }}</a></h3>
                                    <div class="code">Mã sản phẩm:{{ $product['code'] ?? '' }}</div>
                                    <div class="price uk-flex uk-flex-middle uk-flex-space-between">
                                        <div>
                                            @if($saleoff > 0)
                                                <div class="new">{{ number_format($saleoff) }}đ</div>
                                                <div class="old">{{ number_format($price) }}đ</div>
                                            @else
                                                <div class="new">{{ $price > 0 ? number_format($price) . 'đ' : 'Liên hệ' }}</div>
                                                <div class="old">&nbsp;</div>
                                            @endif
                                        </div>
                                        @if($percent)
                                            <div class="sale">-{{ $percent }}%</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </section>
    @endif
</aside>
