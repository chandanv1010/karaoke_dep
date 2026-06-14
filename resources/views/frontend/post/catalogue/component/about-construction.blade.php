@php
    $languageId = $config['language'] ?? 1;
    $constructionWidget = $widgets['karaoke-construction'] ?? null;
    $constructionBg = $constructionWidget->album[0] ?? '';
    $constructionCards = collect($constructionWidget->object ?? []);
    
    $languageOf = static function ($object) {
        $languages = $object->languages ?? null;
        return $languages instanceof \Illuminate\Support\Collection ? $languages->first() : $languages;
    };
    $objectName = static fn($object) => $languageOf($object)->pivot->name ?? ($languageOf($object)->name ?? ($object->name ?? ''));
    $objectUrl = static fn($object) => !empty($languageOf($object)->pivot->canonical ?? ($languageOf($object)->canonical ?? null))
        ? rewrite_url($languageOf($object)->pivot->canonical ?? $languageOf($object)->canonical)
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
@endphp

@if ($constructionWidget)
    <section class="karaoke-card-section karaoke-card-section--construction">
        @if (!empty($constructionBg))
            <img class="karaoke-section-bg" src="{{ asset($constructionBg) }}"
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
                                <img src="{{ $imageUrl($cardImage, $loop->index) }}" alt="{{ $cardTitle }}"
                                    loading="lazy">
                            @endif
                            <span>{{ $cardTitle }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif
