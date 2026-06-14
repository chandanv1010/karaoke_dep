@php
    $categoryNav = \App\Support\LegacyFrontend::productAsideCategories($config['language'] ?? 1);
@endphp
<section class="panel-body">
    <ul class="uk-list mainCat">
        @foreach($categoryNav as $category)
            @php $href = rewrite_url($category['canonical'] ?? ''); @endphp
            <li>
                <a class="title" href="{{ $href }}" title="{{ $category['title'] ?? '' }}">
                    <span class="text">{{ $category['title'] ?? '' }}</span>
                </a>
                <div class="flycontent">
                    <div class="uk-grid uk-grid-medium">
                        <div class="uk-width-3-5">
                            <div class="uk-grid uk-grid-medium uk-grid-width-1-3">
                                @if(!empty($category['child']))
                                    <div class="column">
                                        <ul class="uk-list subCat">
                                            @foreach($category['child'] as $child)
                                                <li><a href="{{ rewrite_url($child['canonical'] ?? '') }}" title="{{ $child['title'] ?? '' }}">{{ $child['title'] ?? '' }}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
</section>
