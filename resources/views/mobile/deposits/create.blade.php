@extends('layouts.mobile')

@section('content')
    <div class="mb-6">
        <a href="{{ route('mobile.home') }}" class="text-gray-500 text-sm flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Kembali
        </a>
        <h2 class="text-2xl font-bold text-gray-800 mt-2">Setor Tunai</h2>
        <p class="text-gray-500 text-sm">Laporkan uang yang Anda terima dari pelanggan.</p>
    </div>

    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
        <form action="{{ route('mobile.deposits.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Nominal (Rp)</label>
                <input type="number" name="amount" class="w-full px-3 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg font-bold text-gray-700" placeholder="0" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Tipe Layanan</label>
                <select id="filter_tipe" class="w-full px-3 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                    <option value="" selected>-- Pilih Tipe --</option>
                    <option value="PPPOE">Bulanan (PPPoE)</option>
                    <option value="HOTSPOT">Reseller (Hotspot)</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Terima Dari Siapa?</label>
                <select name="customer_id" id="select_pelanggan" class="w-full px-3 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                    <option value="" disabled selected>-- Pilih Nama Pelanggan --</option>
                    @foreach($customers as $c)
                        {{-- Atribut data-type inilah yang akan dibaca oleh Javascript di bawah --}}
                        <option value="{{ $c->id }}" data-type="{{ strtoupper($c->type) }}" style="display: none;">
                            {{ $c->name }} ({{ strtoupper($c->type) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Catatan (Opsional)</label>
                <input type="text" name="notes" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Contoh: Bulan Maret / Pasang Baru">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Foto Bukti / Uang (Opsional)</label>
                <input type="file" name="proof_image" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-xl hover:bg-blue-700 transition duration-300 shadow-lg">
                Kirim Setoran
            </button>
        </form>
    </div>

    {{-- LOGIKA JAVASCRIPT: Biar saringannya jalan otomatis --}}
    <script>
        document.getElementById('filter_tipe').addEventListener('change', function() {
            const selectedTipe = this.value;
            const selectPelanggan = document.getElementById('select_pelanggan');
            const options = selectPelanggan.options;

            // Reset pilihan pelanggan setiap kali tipe diubah
            selectPelanggan.value = "";

            for (let i = 0; i < options.length; i++) {
                const optionTipe = options[i].getAttribute('data-type');
                
                // Tampilkan hanya yang cocok dengan tipe yang dipilih
                if (selectedTipe === "" || optionTipe === selectedTipe) {
                    options[i].style.display = "block";
                } else {
                    options[i].style.display = "none";
                }
            }
        });
    </script>
@endsection