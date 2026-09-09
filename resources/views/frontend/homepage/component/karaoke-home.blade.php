@php
    $languageId = $config['language'] ?? 1;
    $hero = $slides[\App\Enums\SlideEnum::MAIN] ?? ($slides['index-slide'] ?? []);
    $heroItems = $hero['item'] ?? [];
    $heroSettings = $hero['setting'] ?? [];
    $heroStats = !empty($heroSettings['stats']) ? $heroSettings['stats'] : [
        ['value' => '10+', 'label' => 'Năm kinh nghiệm'],
        ['value' => '34+', 'label' => 'Tỉnh thành'],
        ['value' => '10+', 'label' => 'Quốc gia'],
        ['value' => '500+', 'label' => 'Dự án hoàn thành'],
    ];
    $heroActions = !empty($heroSettings['actions']) ? $heroSettings['actions'] : [
        ['label' => 'Nhận báo giá miễn phí', 'url' => '#'],
        ['label' => 'Xem các mẫu phòng', 'url' => '#'],
    ];
    $introTitle = $introduce['block_1_company'] ?? '';
    $introSubtitle = $introduce['block_1_en'] ?? '';
    $introDescription = $introduce['block_1_description'] ?? '';
    $introImages = array_values(array_filter([
        $introduce['block_3_image_1'] ?? '',
        $introduce['block_3_image_2'] ?? '',
    ]));
    $introFeatures = collect([
        ['title' => $introduce['block_9_block_1_title'] ?? '', 'icon' => 'fa fa-trophy'],
        ['title' => $introduce['block_9_block_2_title'] ?? '', 'icon' => 'fa fa-cogs'],
        ['title' => $introduce['block_9_block_3_title'] ?? '', 'icon' => 'fa fa-pencil-square-o'],
        ['title' => $introduce['block_9_block_4_title'] ?? '', 'icon' => 'fa fa-line-chart'],
    ])->filter(fn($item) => !empty($item['title']));
    $introServices = collect([
        ['title' => $introduce['block_8_block_1_title'] ?? '', 'icon' => 'frontend/resources/img/project/service-icon-1.png'],
        ['title' => $introduce['block_8_block_2_title'] ?? '', 'icon' => 'frontend/resources/img/project/service-icon-2.png'],
        ['title' => $introduce['block_8_block_3_title'] ?? '', 'icon' => 'frontend/resources/img/project/service-icon-3.png'],
        ['title' => $introduce['block_8_block_4_title'] ?? '', 'icon' => 'frontend/resources/img/project/service-icon-4.png'],
    ])->filter(fn($item) => !empty($item['title']));
    $introButtonLabel = $introduce['block_1_button_label'] ?? '';
    $introButtonLink = $introduce['block_1_button_link'] ?? '#';
    $constructionWidget = $widgets['karaoke-construction'] ?? null;
    $constructionBg = $constructionWidget->album[0] ?? '';
    $constructionCards = collect($constructionWidget->object ?? []);
    $productWidget = $widgets['featured-products'] ?? null;
    $productCardLabel = $productWidget->description[$languageId] ?? ($productWidget->description['1'] ?? '');
    $productActionLabel = $productWidget->short_code ?? '';
    $productActionUrl = $productWidget->note ?? '#';
    $productCards = collect($productWidget->object ?? []);
    $designsWidget = $widgets['home-designs'] ?? null;
    $designsData = $designsWidget
        ? $designsWidget->description[$languageId] ?? ($designsWidget->description['1'] ?? [])
        : [];
    $designObjects = collect($designsWidget->object ?? []);
    $newsWidget = $widgets['home-news'] ?? null;
    $newsData = $newsWidget ? $newsWidget->description[$languageId] ?? ($newsWidget->description['1'] ?? []) : [];
    $newsObjects = collect($newsWidget->object ?? [])->keyBy('id');
    $languageOf = static function ($object) {
        $languages = $object->languages ?? null;
        return $languages instanceof \Illuminate\Support\Collection ? $languages->first() : $languages;
    };
    $objectName = static fn($object) => $languageOf($object)->name ?? ($object->name ?? '');
    $objectDescription = static fn($object) => $languageOf($object)->description ?? ($object->description ?? '');
    $objectUrl = static fn($object) => !empty($languageOf($object)->canonical ?? null)
        ? rewrite_url($languageOf($object)->canonical)
        : '#';
    $imageFallbacks = [
        '/uploads/images/thiet-ke/thiet-ke-phong-khach-01.jpg',
        '/uploads/images/thiet-ke/thiet-ke-phong-hop-01.jpg',
        '/uploads/images/thiet-ke/thiet-ke-phong-giam-doc-01.jpg',
        '/uploads/images/thiet-ke/thiet-ke-nha-hang-01.jpg',
    ];
    $imageUrl = static function ($path, $index = 0) use ($imageFallbacks) {
        $path = $path ?: '';
        if ($path && file_exists(public_path(ltrim($path, '/')))) {
            return asset($path);
        }
        return asset($imageFallbacks[$index % count($imageFallbacks)]);
    };

    /*
        BO BE RONG CHO srcset - do bang getBoundingClientRect tren trang that
        (Chrome, do o 1440px va 390px):

          anh full-bleed (hero, nen khoi thi cong, banner) : 1425px / 390px
          the san pham + the phong hat                     :  335px / 386px
          the tin tuc trong luoi                           :  348px / 364px
          2 anh khoi gioi thieu                            :  219px / 280px

        Truoc day moi anh chi co mot ban goc (thuong 1920px, 500KB-1MB) nen
        dien thoai rong 390px van phai tai dung anh do. Voi srcset trinh duyet
        chon ban vua khung; sizes cho no biet khung rong bao nhieu ngay tu luc
        doc the <img>, truoc khi CSS ve xong.
    */
    $bleedWidths = [480, 768, 1200, 1600];
    $bleedSizes  = '100vw';
    $cardWidths  = [400, 800];
    $cardSizes   = '(max-width: 959px) 100vw, 400px';
    $introWidths = [280, 560];
    $introSizes  = '(max-width: 959px) 280px, 220px';
@endphp

<main class="karaoke-home">
    @if (!empty($heroItems))
        <section class="karaoke-hero">
            <div class="karaoke-hero__slider uk-slidenav-position"
                data-uk-slideshow="{animation:'fade', autoplay:true, autoplayInterval:5500}">
                <ul class="uk-slideshow">
                    @foreach ($heroItems as $heroIndex => $item)
                        @php
                            $heroImage = $item['image'] ?? '';
                            $heroUrl = $item['canonical'] ?? '#';
                            $heroTarget = !empty($item['window']) ? '_blank' : '_self';
                        @endphp
                        <li>
                            <div class="karaoke-hero__bg">
                                @if ($heroImage)
                                    {{--
                                        Anh slide dau tien la phan tu LCP cua trang chu:
                                        tai ngay + fetchpriority=high de trinh duyet uu tien.
                                        Cac slide sau lazy-load, truoc day tat ca deu tai
                                        cung luc va tranh bang thong voi anh LCP.
                                    --}}
                                    <img class="karaoke-section-bg" src="{{ getthumb($heroImage, 1600) }}"
                                        srcset="{{ thumb_srcset($heroImage, $bleedWidths) }}"
                                        sizes="{{ $bleedSizes }}"
                                        alt="{{ $item['alt'] ?? ($item['name'] ?? '') }}"
                                        width="1920" height="800" decoding="async"
                                        @if ($heroIndex === 0) fetchpriority="high" @else loading="lazy" @endif>
                                @endif
                                <div class="karaoke-hero__overlay"></div>
                                <div class="karaoke-shell">
                                    <div class="karaoke-hero__content">
                                        @if (!empty($item['name']))
                                            <div class="karaoke-hero__eyebrow">{{ $item['name'] }}</div>
                                        @endif
                                        @if (!empty($item['alt']))
                                            <h2 class="karaoke-hero__title">{{ $item['alt'] }}</h2>
                                        @endif
                                        @if (!empty($item['description']))
                                            <div class="karaoke-hero__desc">{!! nl2br(e($item['description'])) !!}</div>
                                        @endif
                                        @if (!empty($heroActions))
                                            <div class="karaoke-hero__actions">
                                                @foreach ($heroActions as $action)
                                                    @php
                                                        $actionLabel = $action['label'] ?? '';
                                                        $actionUrl = $action['url'] ?? $heroUrl;
                                                    @endphp
                                                    @if ($actionLabel)
                                                        <a class="karaoke-btn" href="{{ $actionUrl }}"
                                                            target="{{ $heroTarget }}">
                                                            <span>{{ $actionLabel }}</span>
                                                            <i class="fa fa-long-arrow-right"></i>
                                                        </a>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                @if (count($heroItems) > 1)
                    <ul class="uk-dotnav karaoke-hero__dots">
                        @foreach ($heroItems as $key => $item)
                            <li data-uk-slideshow-item="{{ $key }}"><a href="#"></a></li>
                        @endforeach
                    </ul>
                @endif
            </div>
            @if (!empty($heroStats))
                <div class="karaoke-shell karaoke-hero__stats-wrap">
                    <div class="karaoke-hero__stats">
                        @foreach ($heroStats as $stat)
                            <div class="karaoke-hero__stat">
                                @if (!empty($stat['value']))
                                    <strong>{{ $stat['value'] }}</strong>
                                @endif
                                @if (!empty($stat['label']))
                                    <span>{{ $stat['label'] }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    @endif

    @if (!empty($introTitle) || !empty($introDescription) || !empty($introImages))
        <section class="karaoke-intro">
            <div class="karaoke-shell">
                <div class="karaoke-intro__grid">
                    @if (!empty($introImages))
                        <div class="karaoke-intro__media">
                            @foreach (array_slice($introImages, 0, 2) as $key => $image)
                                <div class="karaoke-intro__image karaoke-intro__image--{{ $key + 1 }}">
                                    <img src="{{ getthumb($image, 560) }}"
                                        srcset="{{ thumb_srcset($image, $introWidths) }}"
                                        sizes="{{ $introSizes }}"
                                        alt="{{ $introTitle }}"
                                        loading="lazy">
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="karaoke-intro__content">
                        @if (!empty($introTitle))
                            <h2>{{ $introTitle }}</h2>
                        @endif
                        @if (!empty($introSubtitle))
                            <div class="karaoke-intro__subtitle">{{ $introSubtitle }}</div>
                        @endif
                        @if (!empty($introDescription))
                            <div class="karaoke-intro__body">{!! nl2br(e($introDescription)) !!}</div>
                        @endif
                        @if ($introFeatures->isNotEmpty())
                            <div class="karaoke-intro__features">
                                @foreach ($introFeatures as $feature)
                                    <div class="karaoke-intro__feature">
                                        @if (!empty($feature['icon']))
                                            <span><i class="{{ $feature['icon'] }}"></i></span>
                                        @endif
                                        @if (!empty($feature['title']))
                                            <strong>{{ $feature['title'] }}</strong>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        @if (!empty($introButtonLabel))
                            <a class="karaoke-btn karaoke-btn--wide" href="{{ $introButtonLink ?: '#' }}">
                                <span>{{ $introButtonLabel }}</span>
                                <i class="fa fa-long-arrow-right"></i>
                            </a>
                        @endif
                    </div>
                </div>
                @if ($introServices->isNotEmpty())
                    <div class="karaoke-intro__services">
                        @foreach ($introServices as $service)
                            <div class="karaoke-intro__service">
                                @if (!empty($service['icon']))
                                    <span><img src="{{ $service['icon'] }}" /></span>
                                @endif
                                @if (!empty($service['title']))
                                    <strong>{{ $service['title'] }}</strong>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    @if ($constructionWidget)
        <section class="karaoke-card-section karaoke-card-section--construction">
            @if (!empty($constructionBg))
                <img class="karaoke-section-bg" src="{{ getthumb($constructionBg, 1600) }}"
                    srcset="{{ thumb_srcset($constructionBg, $bleedWidths) }}"
                    sizes="{{ $bleedSizes }}"
                    alt="{{ $constructionWidget->name ?? '' }}" loading="lazy">
            @endif
            <div class="karaoke-card-section__overlay"></div>
            <div class="karaoke-shell">
                @if (!empty($constructionWidget->name))
                    <header class="karaoke-section-heading">
                        <span></span>
                        <h2>{{ $constructionWidget->name }}</h2>
                        <span></span>
                    </header>
                @endif
                @if ($constructionCards->isNotEmpty())
                    <div class="karaoke-room-grid">
                        @foreach ($constructionCards as $card)
                            @php
                                $cardTitle = $objectName($card);
                                $cardImage = $card->image ?? '';
                                $cardUrl = $objectUrl($card);
                            @endphp
                            <a class="karaoke-room-card" href="{{ $cardUrl }}" title="{{ $cardTitle }}">
                                @if ($cardImage)
                                    @php $cardSrc = $imageUrl($cardImage, $loop->index); @endphp
                                    <img src="{{ getthumb($cardSrc, 800) }}"
                                        srcset="{{ thumb_srcset($cardSrc, $cardWidths) }}"
                                        sizes="{{ $cardSizes }}"
                                        alt="{{ $cardTitle }}" loading="lazy">
                                @endif
                                <span>{{ $cardTitle }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    @if ($productWidget)
        <section class="karaoke-card-section karaoke-card-section--products">
            <div class="karaoke-card-section__overlay"></div>
            <div class="karaoke-shell">
                @if (!empty($productWidget->name))
                    <header class="karaoke-section-heading">
                        <span></span>
                        <h2>{{ $productWidget->name }}</h2>
                        <span></span>
                    </header>
                @endif
                @if ($productCards->isNotEmpty())
                    <div class="karaoke-product-grid">
                        @foreach ($productCards as $card)
                            @php
                                $cardTitle = $objectName($card);
                                $cardImage = $card->image ?? '';
                                $cardDescription = cut_string_and_decode($objectDescription($card), 300);
                                $cardUrl = $objectUrl($card);
                                $cardLabel = $productCardLabel;
                            @endphp
                            <article class="karaoke-product-card">
                                <a class="karaoke-product-card__image" href="{{ $cardUrl }}"
                                    title="{{ $cardTitle }}">
                                    @if ($cardImage)
                                        @php $cardSrc = $imageUrl($cardImage, $loop->index); @endphp
                                        <img src="{{ getthumb($cardSrc, 800) }}"
                                            srcset="{{ thumb_srcset($cardSrc, $cardWidths) }}"
                                            sizes="{{ $cardSizes }}"
                                            alt="{{ $cardTitle }}" loading="lazy">
                                    @endif
                                </a>
                                <div class="karaoke-product-card__body">
                                    @if ($cardTitle)
                                        <h3><a href="{{ $cardUrl }}"
                                                title="{{ $cardTitle }}">{{ $cardTitle }}</a></h3>
                                    @endif
                                    @if ($cardDescription)
                                        <p>{{ $cardDescription }}</p>
                                    @endif
                                    @if ($cardLabel)
                                        <a class="karaoke-product-card__link"
                                            href="{{ $cardUrl }}">{{ $cardLabel }}</a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
                @if (!empty($productActionLabel))
                    <div class="karaoke-section-action">
                        <a class="karaoke-btn karaoke-btn--wide" href="{{ $productActionUrl ?: '#' }}">
                            <span>{{ $productActionLabel }}</span>
                            <i class="fa fa-long-arrow-right"></i>
                        </a>
                    </div>
                @endif
            </div>
        </section>
    @endif

    @if (!empty($slides['home-banner']['item']))
        <section class="home-banner-section">
            @foreach ($slides['home-banner']['item'] as $slide)
                <div class="banner-item">
                    <a href="{{ $slide['url'] ?? '#' }}" target="{{ $slide['target'] ?? '_self' }}">
                        <img src="{{ getthumb($slide['image'] ?? null, 1600) }}"
                            srcset="{{ thumb_srcset($slide['image'] ?? null, $bleedWidths) }}"
                            sizes="{{ $bleedSizes }}"
                            alt="{{ $slide['title'] ?? 'Banner' }}" loading="lazy">
                    </a>
                </div>
            @endforeach
        </section>
    @endif
    @php
        $homeDesigns1 = $widgets['home-designs-1'] ?? null;
        $homeDesigns2 = $widgets['home-designs-2'] ?? null;
        $homeNews1 = $widgets['home-news-1'] ?? null;
        $homeNews2 = $widgets['home-news-2'] ?? null;
        $homeNews3 = $widgets['home-news-3'] ?? null;

        $designsTabs = [
            [
                'key' => 'tab1',
                'label' => $homeDesigns1->name ?? 'Thiết kế karaoke',
                'active' => true,
                'widget' => $homeDesigns1,
            ],
            [
                'key' => 'tab2',
                'label' => $homeDesigns2->name ?? 'Tư vấn thiết kế',
                'active' => false,
                'widget' => $homeDesigns2,
            ],
        ];
        $designActionLabel = $homeDesigns1->short_code ?? 'Xem thêm';
        $designActionUrl = $homeDesigns1->note ?? '#';
    @endphp
    @if ($homeDesigns1 || $homeDesigns2)
        <section class="home-designs">
            <div class="karaoke-shell">
                <div class="tabs">
                    @foreach ($designsTabs as $tab)
                        @if ($tab['widget'])
                            <button class="tab {{ $tab['active'] ? 'active' : '' }}" type="button"
                                data-home-design-tab="{{ $tab['key'] }}">{{ $tab['label'] }}</button>
                        @endif
                    @endforeach
                </div>

                @foreach ($designsTabs as $tab)
                    @if ($tab['widget'])
                        @php
                            $widget = $tab['widget'];
                            $desc = is_string($widget->description)
                                ? json_decode($widget->description, true)
                                : $widget->description ?? [];
                            $desc = $desc[$languageId] ?? ($desc['1'] ?? $desc);
                            $limit = is_numeric($desc) ? (int) $desc : ($desc['limit'] ?? 6);

                            $tabCards = collect();
                            if ($widget->model === 'PostCatalogue') {
                                $tabCards = collect($widget->object ?? [])->flatMap(fn($c) => collect($c->posts ?? []));
                            } elseif ($widget->model === 'Post') {
                                $tabCards = collect($widget->object ?? []);
                            }
                            $tabCards = $tabCards->take($limit);
                        @endphp
                        <div class="grid home-designs__panel {{ $tab['active'] ? 'active' : '' }}"
                            data-home-design-panel="{{ $tab['key'] }}">
                            @foreach ($tabCards as $card)
                                @php
                                    $cardTitle = $objectName($card);
                                    $cardDescription = cut_string_and_decode($objectDescription($card), 300);
                                    $cardUrl = $objectUrl($card);
                                @endphp
                                <div class="card">
                                    <div class="image-wrapper">
                                        <a href="{{ $cardUrl }}">
                                            @php $cardSrc = $imageUrl($card->image ?? '', $loop->index); @endphp
                                            <img src="{{ getthumb($cardSrc, 800) }}"
                                                srcset="{{ thumb_srcset($cardSrc, $cardWidths) }}"
                                                sizes="{{ $cardSizes }}"
                                                alt="{{ $cardTitle }}" loading="lazy">
                                        </a>
                                    </div>
                                    <div class="title">{{ $cardTitle }}</div>
                                    <div class="description">{{ Str::limit($cardDescription, 120) }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endforeach

                <div class="action">
                    <a href="{{ $designActionUrl ?: '#' }}" class="btn-more">{{ $designActionLabel ?: 'Xem thêm' }} <i class="fa fa-long-arrow-right"></i></a>
                </div>
            </div>
        </section>
    @endif

    @php
        $newsCols = [$homeNews1, $homeNews2, $homeNews3];
        $newsCols = array_filter($newsCols);
    @endphp

    @if (!empty($newsCols))
        <section class="home-news">
            <div class="karaoke-shell">
                <header class="karaoke-section-heading">
                    <span></span>
                    <h2>Tin tức - Kinh nghiệm - Hỏi đáp</h2>
                    <span></span>
                </header>

                <div class="grid">
                    @foreach ($newsCols as $widget)
                        @php
                            $desc = is_string($widget->description)
                                ? json_decode($widget->description, true)
                                : $widget->description ?? [];
                            $desc = $desc[$languageId] ?? ($desc['1'] ?? $desc);
                            $limit = is_numeric($desc) ? (int) $desc : ($desc['limit'] ?? 7);

                            $posts = collect();
                            if ($widget->model === 'PostCatalogue') {
                                $posts = collect($widget->object ?? [])->flatMap(fn($c) => collect($c->posts ?? []));
                            } elseif ($widget->model === 'Post') {
                                $posts = collect($widget->object ?? []);
                            }
                            $posts = $posts->take($limit);

                            $feature = $posts->first();
                            $listPosts = $posts->slice(1)->take(max($limit - 1, 0));
                            $featureTitle = $feature ? $objectName($feature) : '';
                            $featureUrl = $feature ? $objectUrl($feature) : '#';
                        @endphp
                        <div class="column">
                            <div class="col-header">{{ $widget->name }}</div>
                            @if ($feature)
                                <div class="image-wrapper with-corners">
                                    <a href="{{ $featureUrl }}">
                                        @php $featureSrc = $imageUrl($feature->image ?? '', $loop->index); @endphp
                                        <img src="{{ getthumb($featureSrc, 800) }}"
                                            srcset="{{ thumb_srcset($featureSrc, $cardWidths) }}"
                                            sizes="{{ $cardSizes }}"
                                            alt="{{ $featureTitle }}" loading="lazy">
                                    </a>
                                </div>
                                <div class="card-body">
                                    <a class="card-title" href="{{ $featureUrl }}">{{ $featureTitle }}</a>
                                    @if ($listPosts->isNotEmpty())
                                        <ul class="news-list">
                                            @foreach ($listPosts as $listObject)
                                                <li><a
                                                        href="{{ $objectUrl($listObject) }}">{{ $objectName($listObject) }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</main>
