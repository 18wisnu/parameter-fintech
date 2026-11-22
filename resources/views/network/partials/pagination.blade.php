{{-- $page: ['items','page','total','total_pages'] dari server jaringan --}}
@if($page['total_pages'] > 1 || $page['total'] > 0)
<div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-4 border-t border-slate-100 text-xs text-slate-500">
    <span>{{ number_format($page['total'], 0, ',', '.') }} data · halaman {{ $page['page'] }} dari {{ $page['total_pages'] }}</span>
    <div class="flex gap-2">
        @if($page['page'] > 1)
            <a href="{{ request()->fullUrlWithQuery(['page' => $page['page'] - 1]) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 font-bold hover:bg-slate-50">&larr; Sebelumnya</a>
        @endif
        @if($page['page'] < $page['total_pages'])
            <a href="{{ request()->fullUrlWithQuery(['page' => $page['page'] + 1]) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 font-bold hover:bg-slate-50">Berikutnya &rarr;</a>
        @endif
    </div>
</div>
@endif
