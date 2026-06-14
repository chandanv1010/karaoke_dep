@php
    $title = $post['title'] ?? '';
    $href = rewrite_url($post['canonical'] ?? '');
    $image = getthumb($post['images'] ?? null);
    $description = cutnchar(strip_tags($post['description'] ?? ''), $descriptionLength ?? 250);
@endphp
<article class="article">
    <div class="thumb img-flash">
        <a class="image img-cover" href="{{ $href }}" title="{{ $title }}"><img src="{{ $image }}" alt="{{ $title }}"></a>
    </div>
    <h3 class="title"><a href="{{ $href }}" title="{{ $title }}">{{ $title }}</a></h3>
    @if($description)
        <div class="description">{{ $description }}</div>
    @endif
</article>
