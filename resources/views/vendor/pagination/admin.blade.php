@if ($paginator->hasPages())
  <nav class="admin-pagination-inner" aria-label="Page navigation">
    @if ($paginator->onFirstPage())
      <span class="admin-page-btn is-disabled">Previous</span>
    @else
      <a href="{{ $paginator->previousPageUrl() }}" class="admin-page-btn">Previous</a>
    @endif
    <span class="admin-page-info">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
    @if ($paginator->hasMorePages())
      <a href="{{ $paginator->nextPageUrl() }}" class="admin-page-btn">Next</a>
    @else
      <span class="admin-page-btn is-disabled">Next</span>
    @endif
  </nav>
@endif
