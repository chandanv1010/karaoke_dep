@if ($paginator->hasPages())
<ul class="uk-pagination uk-display-inline-block">
    {{-- Previous Page Link --}}
    @if ($paginator->onFirstPage())
        <li class="uk-disabled"><span>&lsaquo;</span></li>
    @else
        <li><a href="{{ pagination_legacy_url($paginator->previousPageUrl(), $paginator->currentPage() - 1) }}">&lsaquo;</a></li>
    @endif

    {{-- Pagination Elements --}}
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
    @endphp

    @if ($lastPage <= 5)
        {{-- Show all pages if total pages <= 5 --}}
        @foreach (range(1, $lastPage) as $page)
            <li class="{{ $page === $currentPage ? 'uk-active' : '' }}">
                <a href="{{ pagination_legacy_url($paginator->url($page), $page) }}">{{ $page }}</a>
            </li>
        @endforeach
    @else
        {{-- Always show page 1 --}}
        <li class="{{ 1 === $currentPage ? 'uk-active' : '' }}">
            <a href="{{ pagination_legacy_url($paginator->url(1), 1) }}">1</a>
        </li>

        @if ($currentPage > 3)
            <li class="uk-disabled"><span>.....</span></li>
        @endif

        {{-- Show middle pages --}}
        @php
            $start = max(2, $currentPage - 1);
            $end = min($lastPage - 1, $currentPage + 1);
            
            // Adjust ranges when close to ends
            if ($currentPage <= 3) {
                $end = 3;
            }
            if ($currentPage >= $lastPage - 2) {
                $start = $lastPage - 2;
            }
        @endphp

        @for ($page = $start; $page <= $end; $page++)
            <li class="{{ $page === $currentPage ? 'uk-active' : '' }}">
                <a href="{{ pagination_legacy_url($paginator->url($page), $page) }}">{{ $page }}</a>
            </li>
        @endfor

        @if ($currentPage < $lastPage - 2)
            <li class="uk-disabled"><span>.....</span></li>
        @endif

        {{-- Always show last page --}}
        <li class="{{ $lastPage === $currentPage ? 'uk-active' : '' }}">
            <a href="{{ pagination_legacy_url($paginator->url($lastPage), $lastPage) }}">{{ $lastPage }}</a>
        </li>
    @endif

    {{-- Next Page Link --}}
    @if ($paginator->hasMorePages())
        <li><a href="{{ pagination_legacy_url($paginator->nextPageUrl(), $paginator->currentPage() + 1) }}">&rsaquo;</a></li>
    @else
        <li class="uk-disabled"><span>&rsaquo;</span></li>
    @endif
</ul>
@endif

