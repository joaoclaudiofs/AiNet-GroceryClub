
<div class="inline-flex items-center w-full justify-between text-xm">
    Showing {{ $paginator?->firstItem() ?? 0 }} to {{ $paginator?->lastItem() ?? 0 }} of {{ $paginator?->total() ?? 0 }} {{ $paginatorName ?? null }}
    <div class="flex items-center justify-end gap-1">
        @php
            $pageCount = $paginator?->lastPage() ?? 0;
            $firstPage = 1;
            $lastPage = $paginator?->lastPage() ?? 1;
            $currentPage = $paginator?->currentPage() ?? 1;

            function addPage(\Illuminate\Support\Collection $pages, int $page, int $paddingStart = 2, int $paddingEnd = 2) {
                for ($i = 0; $i < $paddingStart; $i++) {
                    $pages->push($page - 1);
                }
                $pages->push($page);
                for ($i = 0; $i < $paddingEnd; $i++) {
                    $pages->push($page + 1);
                }
            }

            $pages = collect();
            addPage($pages, $firstPage, 0, $endsPadding ?? $padding ?? 2);
            addPage($pages, $lastPage, $endsPadding ?? $padding ?? 2, 0);
            addPage($pages, $currentPage, $padding ?? 2, $padding ?? 2);

            $pages = $pages->filter(function ($page) use ($firstPage, $lastPage) {
                return $page >= $firstPage && $page <= $lastPage;
            })->unique()->sort()->values();

            $lastPage = null;
        @endphp
        <flux:button :variant="$currentPage == $firstPage ? 'subtle' : 'subtle'" size="sm" :disabled="!$paginator?->previousPageUrl()" :href="$paginator?->previousPageUrl()" icon="chevron-left"/>
        @foreach ($pages as $page)
            @if ($lastPage && $page - $lastPage > 1)
                <flux:button variant="subtle" size="sm" disabled class="text-gray-500 dark:text-gray-400">...</flux:button>
            @endif
            <flux:button :variant="$currentPage == $page ? 'ghost' : 'subtle'" size="sm" :href="$paginator?->url($page)">{{ $page }}</flux:button>
            @php
                $lastPage = $page;
            @endphp
        @endforeach
        <flux:button :variant="$currentPage == $lastPage ? 'subtle' : 'subtle'" size="sm" :disabled="!$paginator?->nextPageUrl()" :href="$paginator?->nextPageUrl()" icon="chevron-right"/>
    </div>
</div>