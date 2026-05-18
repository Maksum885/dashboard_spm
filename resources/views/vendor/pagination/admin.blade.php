@if ($paginator->hasPages())
  <nav class="admin-pagination-inner" aria-label="Navigasi halaman">
    @if ($paginator->onFirstPage())
      <span class="admin-page-btn is-disabled">Sebelumnya</span>
    @else
      <a href="{{ $paginator->previousPageUrl() }}" class="admin-page-btn">Sebelumnya</a>
    @endif
    <span class="admin-page-info">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>
    @if ($paginator->hasMorePages())
      <a href="{{ $paginator->nextPageUrl() }}" class="admin-page-btn">Berikutnya</a>
    @else
      <span class="admin-page-btn is-disabled">Berikutnya</span>
    @endif
  </nav>
@endif
