@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="custom-pagination">
        <div class="pagination-links">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="pagination-item disabled">
                    <i class="fas fa-chevron-left"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination-item">
                    <i class="fas fa-chevron-left"></i>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="pagination-item dots">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination-item active">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="pagination-item">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination-item">
                    <i class="fas fa-chevron-right"></i>
                </a>
            @else
                <span class="pagination-item disabled">
                    <i class="fas fa-chevron-right"></i>
                </span>
            @endif
        </div>

        <div class="pagination-results">
            <p>
                {!! __('Showing') !!}
                <span class="font-bold">{{ $paginator->firstItem() }}</span>
                {!! __('to') !!}
                <span class="font-bold">{{ $paginator->lastItem() }}</span>
                {!! __('of') !!}
                <span class="font-bold">{{ $paginator->total() }}</span>
                {!! __('Results') !!}
            </p>
        </div>
    </nav>
@endif

