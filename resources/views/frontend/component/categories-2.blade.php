@php
    $root = $Breadcrumb[0] ?? $DetailCatalogues ?? null;
    $subcat = [];
    if (!empty($root['id'])) {
        $matchedRoot = collect(\App\Support\LegacyFrontend::productAsideCategories($config['language'] ?? 1))->firstWhere('id', $root['id']);
        $subcat = $matchedRoot['child'] ?? [];
    }
@endphp
@if(!empty($root))
    <section class="panel-body">
        <div class="mainCat">
            <div class="maintitle active"><a href="{{ rewrite_url($root['canonical'] ?? '') }}" title="{{ $root['title'] ?? '' }}">{{ $root['title'] ?? '' }}</a></div>
            @if(count($subcat))
                <div class="subCat">
                    @foreach($subcat as $item)
                        <div class="item"><a href="{{ rewrite_url($item['canonical'] ?? '') }}" title="{{ $item['title'] ?? '' }}">{{ $item['title'] ?? '' }}</a></div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@else
    @include('frontend.component.categories')
@endif
