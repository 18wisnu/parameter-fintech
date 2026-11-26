{{-- @include('network.partials.header', ['title' => ..., 'subtitle' => ..., 'actions' => [['label','url','primary'?]]]) --}}
@include('partials.flash')
@if($errors->any())
<div class="mb-6 p-4 bg-rose-50 border border-rose-100 text-rose-800 rounded-xl text-sm font-bold">
    @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
</div>
@endif
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
    <div class="min-w-0">
        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">{{ $title }}</h2>
        @isset($subtitle)<p class="text-slate-500 text-sm mt-1">{{ $subtitle }}</p>@endisset
    </div>
    @if(!empty($actions))
    <div class="flex flex-wrap gap-2">
        @foreach($actions as $a)
            <a href="{{ $a[1] }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition {{ ($a[2] ?? false) ? 'bg-sky-600 hover:bg-sky-700 text-white shadow-md shadow-sky-100' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">{{ $a[0] }}</a>
        @endforeach
    </div>
    @endif
</div>
