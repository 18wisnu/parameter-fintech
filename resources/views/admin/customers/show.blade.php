@extends('layouts.admin')

@section('content')
@include('partials.flash')

@php
    use App\Models\Customer;
    $st = $customer->displayStatus();
    $statusTone = ['active' => 'bg-emerald-50 text-emerald-700', 'isolated' => 'bg-amber-50 text-amber-700', 'inactive' => 'bg-slate-100 text-slate-500'];
    $serviceTypes = ['ppp' => 'PPPoE', 'hotspot' => 'Hotspot', 'dhcp' => 'DHCP', 'static' => 'Static'];
    $serviceStatus = ['active' => 'Aktif', 'pending' => 'Menunggu aktivasi', 'suspended' => 'Isolir', 'expired' => 'Habis masa aktif', 'inactive' => 'Nonaktif', 'terminated' => 'Berhenti'];
    $services = $remote['services'] ?? null;
    $fmtDate = fn ($d) => $d ? \Carbon\Carbon::parse($d)->locale('id')->translatedFormat('d M Y') : '—';
    $wa = $customer->phone ? preg_replace('/^0/', '62', preg_replace('/\D/', '', $customer->phone)) : null;
@endphp

@if($customer->isOutlet())<a href="{{ route('outlets.index') }}" class="text-sm text-slate-500 hover:text-slate-800">← Outlet</a>@else<a href="{{ route('customers.index') }}" class="text-sm text-slate-500 hover:text-slate-800">← Pelanggan</a>@endif

<div class="flex flex-wrap items-start justify-between gap-3 mt-2 mb-6">
    <div>
        <div class="flex items-center gap-2">
            <h2 class="text-2xl font-bold text-slate-800">{{ $customer->name }}</h2>
            <span class="px-2 py-0.5 rounded-md text-xs font-semibold {{ $statusTone[$st] }}">{{ Customer::STATUSES[$st] }}</span>
        </div>
        <p class="text-sm text-slate-500 font-mono">{{ $customer->displayCode() ?? 'Tanpa kode' }}</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('customers.edit', $customer) }}" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold">Edit</a>
        <form method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm('Hapus pelanggan ini dari aplikasi? Riwayat tagihannya ikut terhapus.{{ $customer->isLinked() ? ' Data di server jaringan tidak ikut terhapus.' : '' }}')">
            @csrf @method('DELETE')
            <button class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-rose-600 hover:bg-rose-50">Hapus</button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
    {{-- Profil --}}
    <div class="bg-white rounded-2xl border border-slate-100 p-5">
        <h3 class="font-semibold text-slate-800 mb-4">Profil</h3>
        <dl class="space-y-3 text-sm">
            <div>
                <dt class="text-xs text-slate-400">Telepon / WhatsApp</dt>
                <dd class="text-slate-700">
                    {{ $customer->phone ?: '—' }}
                    @if($wa)<a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="ml-1 text-emerald-600 text-xs font-semibold hover:underline">Chat</a>@endif
                </dd>
            </div>
            <div><dt class="text-xs text-slate-400">Email</dt><dd class="text-slate-700 break-all">{{ $customer->email ?: '—' }}</dd></div>
            <div>
                <dt class="text-xs text-slate-400">Alamat</dt>
                <dd class="text-slate-700">{{ $customer->address ?: '—' }}
                    @if($customer->mapUrl())<a href="{{ $customer->mapUrl() }}" target="_blank" rel="noopener" class="block text-sky-600 text-xs font-semibold hover:underline mt-1">Lihat lokasi di peta</a>@endif
                </dd>
            </div>
            @if($customer->entity_name)
            <div><dt class="text-xs text-slate-400">Reseller / Outlet</dt><dd class="text-slate-700">{{ $customer->entity_name }}</dd></div>
            @endif
            <div><dt class="text-xs text-slate-400">Petugas aktivasi</dt><dd class="text-slate-700">{{ $customer->activatedBy->name ?? '—' }}</dd></div>
            @if($customer->notes)
            <div><dt class="text-xs text-slate-400">Catatan</dt><dd class="text-slate-700 whitespace-pre-line">{{ $customer->notes }}</dd></div>
            @endif
            <div><dt class="text-xs text-slate-400">Terdaftar</dt><dd class="text-slate-700">{{ $fmtDate($customer->created_at) }}</dd></div>
        </dl>
    </div>

    {{-- Layanan --}}
    <div class="bg-white rounded-2xl border border-slate-100 p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-slate-800">Layanan Internet</h3>
            @if(!empty($remote['open_tickets_count']))
                <a href="{{ route('network.tickets.index', ['search' => $customer->displayCode()]) }}" class="text-xs font-semibold text-amber-700 bg-amber-50 px-2 py-1 rounded-md">{{ $remote['open_tickets_count'] }} tiket gangguan terbuka</a>
            @endif
        </div>

        @if($remoteError)
            <p class="text-xs text-amber-700 bg-amber-50 rounded-lg px-3 py-2 mb-4">Detail layanan dari server belum bisa dimuat ({{ $remoteError }}). Menampilkan data terakhir yang tersimpan.</p>
        @endif

        @if($services)
            <div class="space-y-3">
            @foreach($services as $s)
                <div class="border border-slate-100 rounded-xl p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ !empty($s['is_online']) ? 'bg-emerald-500' : 'bg-slate-300' }}" title="{{ !empty($s['is_online']) ? 'Online' : 'Offline' }}"></span>
                            <span class="font-mono font-semibold text-slate-800">{{ $s['username'] ?? '—' }}</span>
                            <span class="text-xs text-slate-400">{{ $serviceTypes[$s['service_type'] ?? ''] ?? ($s['service_type'] ?? '') }}</span>
                        </div>
                        <span class="text-xs font-semibold text-slate-600">{{ $serviceStatus[$s['status'] ?? ''] ?? ($s['status'] ?? '—') }}</span>
                    </div>
                    <dl class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                        <div><dt class="text-xs text-slate-400">Paket</dt><dd class="text-slate-700">{{ $s['product_name'] ?? '—' }}<div class="text-xs text-slate-400">{{ $s['bandwidth_name'] ?? '' }}</div></dd></div>
                        <div><dt class="text-xs text-slate-400">Harga</dt><dd class="text-slate-700">Rp {{ number_format($s['price'] ?? 0, 0, ',', '.') }}<div class="text-xs text-slate-400">{{ Customer::BILLING_TYPES[$s['billing_type'] ?? ''] ?? '' }} · {{ Customer::BILLING_CYCLES[$s['billing_cycle'] ?? ''] ?? '' }}</div></dd></div>
                        <div><dt class="text-xs text-slate-400">Aktif sampai</dt><dd class="text-slate-700">{{ $fmtDate($s['expiry_date'] ?? null) }}</dd></div>
                        <div><dt class="text-xs text-slate-400">Area / ODP</dt><dd class="text-slate-700">{{ $s['area_name'] ?? '—' }}<div class="text-xs text-slate-400">{{ $s['odp_name'] ?? '' }}</div></dd></div>
                        @if(!empty($s['ip_address']) || !empty($s['mac_address']))
                        <div class="col-span-2"><dt class="text-xs text-slate-400">IP / MAC</dt><dd class="text-slate-700 font-mono text-xs">{{ $s['ip_address'] ?? '—' }} · {{ $s['mac_address'] ?? '—' }}</dd></div>
                        @endif
                    </dl>
                </div>
            @endforeach
            </div>
        @else
            <dl class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                <div><dt class="text-xs text-slate-400">Jenis</dt><dd class="text-slate-700">{{ Customer::TYPES[$customer->type] ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-400">Paket</dt><dd class="text-slate-700">{{ $customer->product_name ?: '—' }}<div class="text-xs text-slate-400">{{ $customer->bandwidth }}</div></dd></div>
                <div><dt class="text-xs text-slate-400">Harga / bulan</dt><dd class="text-slate-700">Rp {{ number_format($customer->package_fee, 0, ',', '.') }}</dd></div>
                <div><dt class="text-xs text-slate-400">Jatuh tempo</dt><dd class="text-slate-700">{{ $fmtDate($customer->due_date) }}</dd></div>
                @if($customer->mixradius_username)
                <div><dt class="text-xs text-slate-400">Username</dt><dd class="text-slate-700 font-mono">{{ $customer->mixradius_username }}</dd></div>
                @endif
            </dl>
        @endif

        <dl class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm mt-4 pt-4 border-t border-slate-50">
            <div><dt class="text-xs text-slate-400">Kolektor</dt><dd class="text-slate-700">{{ $customer->collector->name ?? '—' }}</dd></div>
            <div><dt class="text-xs text-slate-400">Diskon / bulan</dt><dd class="text-slate-700">{{ $customer->discount > 0 ? 'Rp ' . number_format($customer->discount, 0, ',', '.') : '—' }}</dd></div>
            <div><dt class="text-xs text-slate-400">Biaya pasang</dt><dd class="text-slate-700">{{ $customer->installation_fee > 0 ? 'Rp ' . number_format($customer->installation_fee, 0, ',', '.') . ($customer->installation_billed ? ' (sudah ditagih)' : ' (belum ditagih)') : '—' }}</dd></div>
            @if($customer->start_date)
            <div><dt class="text-xs text-slate-400">Mulai berlangganan</dt><dd class="text-slate-700">{{ $fmtDate($customer->start_date) }}</dd></div>
            @endif
        </dl>
    </div>
</div>

{{-- Tagihan --}}
<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-50">
        <h3 class="font-semibold text-slate-800">Riwayat Tagihan</h3>
        <a href="{{ route('invoices.index', ['search' => $customer->displayCode() ?: $customer->name]) }}" class="text-sm text-sky-600 font-semibold hover:underline">Semua tagihan</a>
    </div>
    <table class="w-full text-sm">
        <tbody class="divide-y divide-slate-50">
        @forelse($invoices as $inv)
            <tr>
                <td class="px-5 py-3">
                    <div class="text-slate-700">{{ \Carbon\Carbon::parse($inv->period_date)->locale('id')->translatedFormat('F Y') }}</div>
                    <div class="text-xs text-slate-400 font-mono">{{ $inv->invoice_number }}</div>
                </td>
                <td class="px-5 py-3 text-right font-semibold text-slate-800 whitespace-nowrap">Rp {{ number_format($inv->amount, 0, ',', '.') }}</td>
                <td class="px-5 py-3">
                    @php $lbl = ['paid' => ['Lunas', 'bg-emerald-50 text-emerald-700'], 'void' => ['Lunas di server', 'bg-slate-100 text-slate-600']][$inv->status] ?? ['Belum bayar', 'bg-rose-50 text-rose-700']; @endphp
                    <span class="px-2 py-0.5 rounded-md text-xs font-semibold {{ $lbl[1] }}">{{ $lbl[0] }}</span>
                </td>
                <td class="px-5 py-3 text-right whitespace-nowrap">
                    <a href="{{ route('invoices.print', $inv->id) }}" target="_blank" class="text-sm text-slate-500 hover:text-slate-800 font-semibold">A4</a>
                    <a href="{{ route('invoices.print', ['id' => $inv->id, 'format' => 'pos']) }}" target="_blank" class="ml-3 text-sm text-slate-500 hover:text-slate-800 font-semibold">POS</a>
                </td>
            </tr>
        @empty
            <tr><td class="px-5 py-10 text-center text-slate-400">Belum ada tagihan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
