@foreach([
    'success' => 'bg-emerald-50 border-emerald-100 text-emerald-800',
    'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
    'info'    => 'bg-sky-50 border-sky-100 text-sky-800',
    'error'   => 'bg-rose-50 border-rose-100 text-rose-800',
] as $key => $classes)
    @if(session($key))
    <div class="mb-6 p-4 border rounded-xl text-sm font-bold break-words {{ $classes }}">{{ session($key) }}</div>
    @endif
@endforeach
