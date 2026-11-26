@auth
    @php $pendingInvites = \App\Models\BusinessInvitation::for(auth()->user())->count(); @endphp
    @if($pendingInvites > 0 && !request()->routeIs('admin.business.index'))
        <a href="{{ route('admin.business.index') }}" class="print:hidden flex flex-wrap items-center gap-x-2 mb-5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3">
            Anda punya {{ $pendingInvites }} undangan bergabung ke usaha lain. <b>Lihat undangan →</b>
        </a>
    @endif
@endauth
