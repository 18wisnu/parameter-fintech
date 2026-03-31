<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-slate-800 leading-tight flex items-center gap-2">
            <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            FTTH Topology Map
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Map Container --}}
            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-[2.5rem] border border-slate-200">
                <div class="p-4 sm:p-6 relative">
                    {{-- Legend --}}
                    <div class="absolute top-10 right-10 z-[1000] bg-white/90 backdrop-blur p-4 rounded-3xl border border-slate-200 shadow-xl hidden lg:block overflow-hidden min-w-[200px]">
                        <div class="absolute top-0 left-0 w-1 h-full bg-indigo-600"></div>
                        <h5 class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-3">Hierarki Jaringan</h5>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-slate-900 flex items-center justify-center text-white shadow-lg">
                                    <i class="fas fa-server text-[10px]"></i>
                                </div>
                                <span class="text-[11px] font-black text-slate-700 uppercase italic">OLT / MAIN SITE</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-amber-500 flex items-center justify-center text-white shadow-lg">
                                    <i class="fas fa-project-diagram text-[10px]"></i>
                                </div>
                                <span class="text-[11px] font-black text-slate-700 uppercase italic">ODP / SPLITTER</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-emerald-500 flex items-center justify-center text-white shadow-lg">
                                    <i class="fas fa-home text-[10px]"></i>
                                </div>
                                <span class="text-[11px] font-black text-slate-700 uppercase italic">ONT PELANGGAN</span>
                            </div>
                            <div class="pt-2 border-t border-slate-100 italic text-[9px] text-slate-400">
                                Hover di atas titik untuk detail cepat
                            </div>
                        </div>
                    </div>

                    <div id="map" style="height: 700px;" class="rounded-[3rem] border border-slate-200 shadow-inner"></div>
                </div>
            </div>
            
            <div class="mt-8 grid grid-cols-2 lg:grid-cols-4 gap-4">
                 <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total OLT</p>
                    <h3 class="text-2xl font-black text-slate-800 italic">{{ count($sites) }}</h3>
                 </div>
                 <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total ODP</p>
                    <h3 class="text-2xl font-black text-amber-600 italic">{{ count($odps) }}</h3>
                 </div>
                 <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Pelanggan Aktif</p>
                    <h3 class="text-2xl font-black text-emerald-600 italic">{{ count($devices) }}</h3>
                 </div>
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .leaflet-container { font-family: 'Outfit', sans-serif; }
        .leaflet-popup-content-wrapper { border-radius: 1.5rem; padding: 0.5rem; box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1); border: 2px solid #f1f5f9; }
        .leaflet-tooltip { 
            background: #1e293b; 
            color: white; 
            border: none; 
            border-radius: 0.75rem; 
            padding: 8px 12px; 
            font-size: 11px; 
            font-weight: 700;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.3);
        }
        
        /* Animasi Garis Aliran Data */
        .feeder-line { 
            stroke-dasharray: 10, 15; 
            animation: flow 8s linear infinite !important; 
            stroke-width: 4;
        } 
        .dist-line { 
            stroke-dasharray: 5, 10; 
            animation: flow 12s linear infinite !important; 
            stroke-width: 3;
            opacity: 1 !important;
        }

        @keyframes flow { 
            from { stroke-dashoffset: 200; } 
            to { stroke-dashoffset: 0; } 
        }

        /* Custom Markers */
        .icon-site { background: #0f172a; border-radius: 12px; border: 3px solid white; display: flex; align-items: center; justify-center; color: white; shadow: 0 4px 6px -1px rgba(0,0,0,0.1); transition: all 0.3s; }
        .icon-odp { background: #f59e0b; border-radius: 10px; border: 2px solid white; display: flex; align-items: center; justify-center; color: white; shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .icon-ont { background: #10b981; border-radius: 50%; border: 2px solid white; display: flex; align-items: center; justify-center; color: white; shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        
        .icon-site:hover, .icon-odp:hover, .icon-ont:hover { transform: scale(1.2); }
    </style>
    @endpush

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var map = L.map('map').setView([-0.0263, 109.3425], 13);

            // 1. Base Map Layers
            var satellite = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: '&copy; Google Maps Satellite'
            });

            var terrain = L.tileLayer('https://{s}.google.com/vt/lyrs=p&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: '&copy; Google Maps Terrain'
            });

            var roadmap = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: '&copy; Google Maps Roadmap'
            });

            var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            });

            // Set default layer
            satellite.addTo(map);

            var baseMaps = {
                "<span class='text-xs font-bold'>Satellite</span>": satellite,
                "<span class='text-xs font-bold'>Terrain</span>": terrain,
                "<span class='text-xs font-bold'>Roadmap</span>": roadmap,
                "<span class='text-xs font-bold'>OpenStreetMap</span>": osm
            };

            L.control.layers(baseMaps, null, {position: 'topleft'}).addTo(map);

            var sites = @json($sites);
            var odps = @json($odps);
            var devices = @json($devices);
            var allCoords = [];

            // --- FUNGSI HITUNG REDAMAN ---
            const LOSS_MAP = {
                '1:2': 3.5,
                '1:4': 7.2,
                '1:8': 10.5,
                '1:16': 13.8,
                '1:32': 17.5
            };

            function getOdpLoss(odpId) {
                let odp = odps.find(o => o.id == odpId);
                if (!odp) return 0;
                
                let currentLoss = LOSS_MAP[odp.splitter_type] || 10.5;
                
                if (odp.parent_odp_id) {
                    return currentLoss + getOdpLoss(odp.parent_odp_id);
                }
                return currentLoss;
            }

            // 1. OLT Sites
            var siteMarkers = {};
            sites.forEach(site => {
                if (!site.latitude || !site.longitude) return;
                var lat = parseFloat(site.latitude);
                var lng = parseFloat(site.longitude);
                siteMarkers[site.id] = [lat, lng];
                allCoords.push([lat, lng]);

                var icon = L.divIcon({
                    html: '<i class="fas fa-server text-[12px]"></i>',
                    className: 'icon-site flex items-center justify-center',
                    iconSize: [28, 28],
                    iconAnchor: [14, 14]
                });

                L.marker([lat, lng], {icon: icon})
                    .addTo(map)
                    .bindTooltip(`OLT: ${site.name}`, {direction: 'top', offset: [0, -15]})
                    .bindPopup(`<div class="p-4 min-w-[180px]">
                        <p class="text-[10px] text-indigo-400 font-black uppercase tracking-widest mb-1">Central OLT</p>
                        <h4 class="font-black text-slate-800 uppercase italic border-b pb-2 mb-2">${site.name}</h4>
                        <div class="space-y-2">
                             <div class="flex justify-between items-center bg-slate-50 p-2 rounded-lg">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">PON Out</span>
                                <span class="text-sm font-black text-indigo-600">${site.pon_power || '4.00'} dBm</span>
                             </div>
                             <p class="text-[10px] text-slate-500 leading-relaxed italic">${site.location || 'Headend Site'}</p>
                        </div>
                    </div>`);
            });

            // 2. ODP Splitters (Store markers first)
            var odpMarkers = {};
            odps.forEach(odp => {
                if (!odp.latitude || !odp.longitude) return;
                var lat = parseFloat(odp.latitude);
                var lng = parseFloat(odp.longitude);
                odpMarkers[odp.id] = [lat, lng];
                allCoords.push([lat, lng]);

                // Hitung Estimasi Redaman di ODP ini
                let site = sites.find(s => s.id == odp.site_id);
                let startPower = site ? parseFloat(site.pon_power || 4) : 4;
                let totalLoss = getOdpLoss(odp.id);
                let estimatedPower = (startPower - totalLoss).toFixed(2);

                var icon = L.divIcon({
                    html: '<i class="fas fa-project-diagram text-[10px]"></i>',
                    className: 'icon-odp flex items-center justify-center',
                    iconSize: [22, 22],
                    iconAnchor: [11, 11]
                });

                L.marker([lat, lng], {icon: icon})
                    .addTo(map)
                    .bindTooltip(`ODP: ${odp.name} (${estimatedPower} dBm)`, {direction: 'top'})
                    .bindPopup(`<div class="p-4 min-w-[200px]">
                        <p class="text-[10px] text-amber-500 font-black uppercase tracking-widest mb-1">Link Splitter</p>
                        <h4 class="font-black text-slate-800 uppercase italic border-b pb-2 mb-2">${odp.name}</h4>
                        <div class="space-y-2 mt-2">
                            <div class="flex justify-between text-[11px]">
                                <span class="text-slate-400">Tipe Splitter:</span>
                                <span class="font-bold text-slate-700">${odp.splitter_type || '1:8'}</span>
                            </div>
                             <div class="flex justify-between text-[11px]">
                                <span class="text-slate-400">Loss Splitter:</span>
                                <span class="font-bold text-rose-500">-${LOSS_MAP[odp.splitter_type] || 10.5} dB</span>
                            </div>
                            <div class="flex justify-between items-center bg-amber-50 p-2 rounded-lg border border-amber-100 mt-2">
                                <span class="text-[10px] font-black text-amber-600 uppercase">Est. Power</span>
                                <span class="text-sm font-black text-amber-700">${estimatedPower} dBm</span>
                            </div>
                        </div>
                    </div>`);
            });

            // 3. Draw All Connectivity Lines (After markers exist)
            // 3.1 ODP -> Site/Parent Lines
            odps.forEach(odp => {
                if (!odpMarkers[odp.id]) return;
                var currentCoord = odpMarkers[odp.id];

                // Parent ODP (Cascading)
                if (odp.parent_odp_id && odpMarkers[odp.parent_odp_id]) {
                    L.polyline([odpMarkers[odp.parent_odp_id], currentCoord], {
                        color: '#f59e0b',
                        weight: 3,
                        opacity: 1,
                        className: 'feeder-line'
                    }).addTo(map);
                } 
                // Directly to OLT
                else if (odp.site_id && siteMarkers[odp.site_id]) {
                    L.polyline([siteMarkers[odp.site_id], currentCoord], {
                        color: '#4f46e5',
                        weight: 3,
                        opacity: 1,
                        className: 'feeder-line'
                    }).addTo(map);
                }
            });

            // 4. ONT Customers Markers & Lines
            devices.forEach(dev => {
                if (!dev.customer || !dev.customer.latitude || !dev.customer.longitude) return;
                var lat = parseFloat(dev.customer.latitude);
                var lng = parseFloat(dev.customer.longitude);
                var currentCoord = [lat, lng];
                allCoords.push(currentCoord);

                // Draw Marker
                var icon = L.divIcon({
                    html: '<i class="fas fa-home text-[8px]"></i>',
                    className: 'icon-ont flex items-center justify-center',
                    iconSize: [18, 18],
                    iconAnchor: [9, 9]
                });

                L.marker(currentCoord, {icon: icon})
                    .addTo(map)
                    .bindTooltip(`<b>${dev.customer.name}</b>`, {direction: 'top', sticky: true})
                    .bindPopup(`<div class="p-3">
                        <h4 class="font-black text-slate-800 uppercase italic border-b pb-2 mb-2">${dev.customer.name}</h4>
                        <div class="space-y-1.5 text-[11px]">
                            <div><span class="text-slate-400">IP:</span> <span class="font-bold text-indigo-600">${dev.ip_pppoe || '-'}</span></div>
                            <div><span class="text-slate-400">RX:</span> <span class="font-bold text-emerald-600">${dev.rx_power || '-'} dBm</span></div>
                        </div>
                    </div>`);

                // Connection Line: ODP -> ONT
                if (dev.odp_id && odpMarkers[dev.odp_id]) {
                    L.polyline([odpMarkers[dev.odp_id], currentCoord], {
                        color: '#10b981',
                        weight: 2,
                        opacity: 0.9,
                        className: 'dist-line'
                    }).addTo(map);
                } else if (dev.site_id && siteMarkers[dev.site_id]) {
                    L.polyline([siteMarkers[dev.site_id], currentCoord], {
                        color: '#6366f1',
                        weight: 2,
                        opacity: 0.7,
                        className: 'dist-line'
                    }).addTo(map);
                }
            });

            if (allCoords.length > 0) map.fitBounds(allCoords, {padding: [80, 80]});
            setTimeout(() => map.invalidateSize(), 400);
        });
    </script>
    @endpush
</x-app-layout>
