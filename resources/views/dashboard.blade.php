<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zumi App - Real-Time Parking Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/js/app.js'])
</head>
<body class="bg-gray-900 text-white min-h-screen">
    <div class="container mx-auto px-4 py-8">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 border-b border-gray-700 pb-6">
            <div>
                <h1 class="text-3xl font-bold text-indigo-400">🚗 Zumi App Parking Monitoring</h1>
                <p class="text-gray-400 text-sm mt-1">Real-time status slot parkir berbasis AI YOLO & Laravel Reverb</p>
            </div>
            
            <div class="mt-4 md:mt-0 flex items-center gap-4">
                <!-- Widget Slot Tersedia -->
                <div class="bg-gray-800 px-6 py-3 rounded-lg border border-gray-700 text-center">
                    <span class="text-xs text-gray-400 uppercase tracking-wider block">Slot Tersedia</span>
                    <span id="available-count" class="text-3xl font-extrabold text-emerald-400">{{ $totalAvailable }}</span>
                </div>
                
                <!-- Status Indicator -->
                <div class="flex items-center gap-2">
                    <span id="status-indicator" class="w-3 h-3 bg-emerald-500 rounded-full animate-ping"></span>
                    <span class="text-xs text-emerald-400 font-semibold">LIVE</span>
                </div>

                <!-- Tombol Ke Halaman Admin Management -->
                <a href="{{ route('admin.slots.index') }}" 
                class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs px-4 py-3 rounded-lg border border-indigo-500 transition flex items-center gap-2 shadow-lg">
                    <span>⚙️ Kelola Slot</span>
                </a>
            </div>
        </div>

        <!-- Layout Utama: Split 2 Kolom -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Kolom Kiri: CCTV Stream Box -->
            <div class="lg:col-span-1 bg-gray-800 p-4 rounded-2xl border border-gray-700 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-bold text-gray-300 uppercase tracking-wider flex items-center gap-2">
                        📹 CCTV Live Stream
                    </h2>
                    <span class="text-[10px] bg-red-500/20 text-red-400 px-2 py-0.5 rounded border border-red-500/30 font-mono">
                        FEED #1
                    </span>
                </div>

                <!-- Area Box Hitam Kamera -->
                <div class="relative aspect-video bg-black rounded-xl overflow-hidden border border-gray-900 flex items-center justify-center min-h-[220px]">
                    <img id="camera-feed" 
                         src="http://127.0.0.1:8001/video-feed" 
                         alt="Camera Stream Offline" 
                         class="w-full h-full object-cover hidden"
                         onload="this.classList.remove('hidden'); document.getElementById('placeholder-text').classList.add('hidden');"
                         onerror="this.classList.add('hidden'); document.getElementById('placeholder-text').classList.remove('hidden');">

                    <div id="placeholder-text" class="text-center p-4">
                        <span class="text-3xl block mb-2">📹</span>
                        <p class="text-xs text-gray-500 font-mono">Kamera Offline / Uvicorn Belum Jalan</p>
                    </div>
                </div>

                <p class="text-[11px] text-gray-500 mt-3 text-center">
                    FastAPI Stream: <code class="text-indigo-400">http://127.0.0.1:8001/video-feed</code>
                </p>
            </div>

            <!-- Kolom Kanan: Grid Slot Parkir -->
            <div id="parking-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
                @foreach($slots as $slot)
                    <div id="slot-card-{{ $slot->slot_code }}" 
                        class="p-6 rounded-2xl border transition-all duration-300 flex flex-col items-center justify-between h-44 shadow-lg
                        {{ $slot->is_occupied ? 'bg-red-950/40 border-red-500/50 text-red-300' : 'bg-emerald-950/40 border-emerald-500/50 text-emerald-300' }}">
                        
                        <span class="text-2xl font-black tracking-widest">{{ $slot->slot_code }}</span>
                        
                        <div id="slot-badge-{{ $slot->slot_code }}" class="px-3 py-1 text-xs font-bold rounded-full uppercase tracking-wider
                            {{ $slot->is_occupied ? 'bg-red-500/20 text-red-400 border border-red-500/30' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' }}">
                            {{ $slot->is_occupied ? 'TERISI' : 'KOSONG' }}
                        </div>

                        <span class="text-[10px] text-gray-400">
                            Updated: <span id="slot-time-{{ $slot->slot_code }}">{{ $slot->last_updated_at ? $slot->last_updated_at->format('H:i:s') : '-' }}</span>
                        </span>
                    </div>
                @endforeach
            </div>

        </div>

    </div>

    <!-- Script Listener Reverb WebSocket -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof window.Echo !== 'undefined') {
                console.log('Echo initialized, subscribing to parking-tracker...');

                window.Echo.channel('parking-tracker')
                    .listen('.slot.updated', (e) => {
                        console.log('Realtime event received:', e);

                        // Update Total Slot Tersedia
                        if (e.totalAvailable !== undefined) {
                            document.getElementById('available-count').innerText = e.totalAvailable;
                        }

                        // Update Kartu Setiap Slot
                        if (e.slots && Array.isArray(e.slots)) {
                            const now = new Date().toLocaleTimeString();

                            e.slots.forEach(slot => {
                                const card = document.getElementById(`slot-card-${slot.slot_code}`);
                                const badge = document.getElementById(`slot-badge-${slot.slot_code}`);
                                const timeSpan = document.getElementById(`slot-time-${slot.slot_code}`);

                                if (card && badge) {
                                    if (slot.is_occupied) {
                                        // Ubah style ke Merah (Terisi)
                                        card.className = "p-6 rounded-2xl border transition-all duration-300 flex flex-col items-center justify-between h-44 shadow-lg bg-red-950/40 border-red-500/50 text-red-300 scale-95";
                                        badge.className = "px-3 py-1 text-xs font-bold rounded-full uppercase tracking-wider bg-red-500/20 text-red-400 border border-red-500/30";
                                        badge.innerText = "TERISI";
                                    } else {
                                        // Ubah style ke Hijau (Kosong)
                                        card.className = "p-6 rounded-2xl border transition-all duration-300 flex flex-col items-center justify-between h-44 shadow-lg bg-emerald-950/40 border-emerald-500/50 text-emerald-300 scale-100";
                                        badge.className = "px-3 py-1 text-xs font-bold rounded-full uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/30";
                                        badge.innerText = "KOSONG";
                                    }

                                    if (timeSpan) timeSpan.innerText = now;
                                }
                            });
                        }
                    });
            } else {
                console.error('Laravel Echo is not loaded.');
            }
        });
    </script>
</body>
</html>