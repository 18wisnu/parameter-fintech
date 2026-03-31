<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengaturan Site Server') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Alert --}}
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <ul class="list-disc ml-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg flex flex-col md:flex-row gap-6 p-6">
                {{-- Form Add New Site --}}
                <div class="w-full md:w-1/3 p-4 bg-gray-50 border border-gray-200 rounded-xl">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">➕ Tambah Site Baru</h3>
                    <form action="{{ route('sites.store') }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama Site (Inisial)</label>
                                <input type="text" name="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Misal: PST-1" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama Usaha / Produk</label>
                                <input type="text" name="business_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Misal: Parameter HelpDesk Provider">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Alamat / Lokasi</label>
                                <input type="text" name="location" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Misal: Gedung A">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">IP / URL (Misal: IP GenieACS)</label>
                                <input type="text" name="ip_address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Misal: 192.168.1.1">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Output PON (dBm)</label>
                                <input type="number" step="0.01" name="pon_power" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Misal: 4.00" value="4.00">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Latitude</label>
                                    <input type="text" name="latitude" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Misal: -0.0263">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Longitude</label>
                                    <input type="text" name="longitude" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Misal: 109.3425">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Keterangan Tambahan</label>
                                <textarea name="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Opsional..."></textarea>
                            </div>
                        </div>
                        <div class="mt-5">
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow transition">
                                Simpan Site
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Table Of Sites --}}
                <div class="w-full md:w-2/3">
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200 shadow-sm rounded-lg text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-3 border-b text-left font-semibold text-gray-700">Nama Site</th>
                                    <th class="px-4 py-3 border-b text-left font-semibold text-gray-700">Usaha</th>
                                    <th class="px-4 py-3 border-b text-left font-semibold text-gray-700">Lokasi</th>
                                    <th class="px-4 py-3 border-b text-left font-semibold text-gray-700">IP/URL Server</th>
                                    <th class="px-4 py-3 border-b text-center font-semibold text-gray-700">Output PON</th>
                                    <th class="px-4 py-3 border-b text-center font-semibold text-gray-700">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sites as $site)
                                    <tr class="hover:bg-gray-50" x-data="{ editing: false }">
                                        {{-- Normal View --}}
                                        <td class="px-4 py-4 border-b">
                                            <span x-show="!editing" class="font-bold text-gray-800">{{ $site->name }}</span>
                                            <input x-show="editing" form="form-edit-{{ $site->id }}" type="text" name="name" value="{{ $site->name }}" class="border-gray-300 rounded text-sm w-full p-1 shadow-inner focus:ring-0">
                                        </td>
                                        <td class="px-4 py-4 border-b">
                                            <span x-show="!editing">{{ $site->business_name }}</span>
                                            <input x-show="editing" form="form-edit-{{ $site->id }}" type="text" name="business_name" value="{{ $site->business_name }}" class="border-gray-300 rounded text-sm w-full p-1 shadow-inner focus:ring-0">
                                        </td>
                                        <td class="px-4 py-4 border-b">
                                            <span x-show="!editing" class="text-xs text-gray-500">{{ $site->location }}</span>
                                            <input x-show="editing" form="form-edit-{{ $site->id }}" type="text" name="location" value="{{ $site->location }}" class="border-gray-300 rounded text-sm w-full p-1 shadow-inner focus:ring-0">
                                        </td>
                                        <td class="px-4 py-4 border-b">
                                            <span x-show="!editing" class="font-mono text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">{{ $site->ip_address ?? '-' }}</span>
                                            <input x-show="editing" form="form-edit-{{ $site->id }}" type="text" name="ip_address" value="{{ $site->ip_address }}" class="border-gray-300 rounded text-sm w-full p-1 shadow-inner focus:ring-0">
                                        </td>
                                        <td class="px-4 py-4 border-b text-center">
                                            <span x-show="!editing" class="font-bold text-amber-600">{{ $site->pon_power ?? '4.00' }}</span>
                                            <input x-show="editing" form="form-edit-{{ $site->id }}" type="number" step="0.01" name="pon_power" value="{{ $site->pon_power }}" class="border-gray-300 rounded text-sm w-full p-1 shadow-inner focus:ring-0">
                                        </td>
                                        <td class="px-4 py-4 border-b text-center space-x-1">
                                            {{-- Form for editing --}}
                                            <form id="form-edit-{{ $site->id }}" action="{{ route('sites.update', $site->id) }}" method="POST" class="hidden">
                                                @csrf
                                                @method('PUT')
                                            </form>
                                            
                                            <button x-show="!editing" @click="editing = true" class="text-blue-600 hover:text-blue-900 font-semibold px-2 py-1 bg-blue-50 hover:bg-blue-100 rounded text-xs transition">Edit</button>
                                            
                                            <button x-show="editing" form="form-edit-{{ $site->id }}" type="submit" class="text-green-600 hover:text-green-900 font-semibold px-2 py-1 bg-green-50 hover:bg-green-100 rounded text-xs transition">Simpan</button>
                                            <button x-show="editing" @click="editing = false" type="button" class="text-gray-500 hover:text-gray-700 font-semibold px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded text-xs transition">Batal</button>

                                            <form action="{{ route('sites.destroy', $site->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus site ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" x-show="!editing" class="text-red-500 hover:text-red-700 font-semibold px-2 py-1 bg-red-50 hover:bg-red-100 rounded text-xs transition">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">Belum ada data Site.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
