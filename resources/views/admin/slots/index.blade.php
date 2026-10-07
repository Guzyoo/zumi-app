<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Slot Parkir - Zumi App</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen p-8">
    <div class="max-w-4xl mx-auto">
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-indigo-400">⚙️ Manajemen Slot Parkir</h1>
            <a href="{{ route('dashboard') }}" class="text-sm bg-gray-800 hover:bg-gray-700 px-4 py-2 rounded-lg border border-gray-700">← Kembali ke Dashboard</a>
        </div>

        @if(session('success'))
            <div class="bg-emerald-900/50 border border-emerald-500 text-emerald-300 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        <!-- Form Tambah Slot -->
        <form action="{{ route('admin.slots.store') }}" method="POST" class="bg-gray-800 p-4 rounded-xl border border-gray-700 mb-8 flex gap-4">
            @csrf
            <input type="text" name="slot_code" placeholder="Kode Slot (contoh: B1)" required 
                   class="bg-gray-900 border border-gray-700 px-4 py-2 rounded-lg focus:outline-none focus:border-indigo-500 text-white uppercase flex-1">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-6 py-2 rounded-lg transition">
                + Tambah Slot
            </button>
        </form>

        <!-- Tabel Daftar Slot -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <table class="w-full text-left text-sm text-gray-300">
                <thead class="bg-gray-700 text-gray-200 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3">Kode Slot</th>
                        <th class="px-6 py-3">Status Parkir</th>
                        <th class="px-6 py-3">Status Operasional</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($slots as $slot)
                        <tr>
                            <td class="px-6 py-4 font-bold text-lg text-white">{{ $slot->slot_code }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded {{ $slot->is_occupied ? 'bg-red-900/50 text-red-300' : 'bg-emerald-900/50 text-emerald-300' }}">
                                    {{ $slot->is_occupied ? 'TERISI' : 'KOSONG' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded {{ $slot->is_active ? 'bg-blue-900/50 text-blue-300' : 'bg-gray-700 text-gray-400' }}">
                                    {{ $slot->is_active ? 'AKTIF' : 'MAINTENANCE' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right flex justify-end gap-2">
                                <form action="{{ route('admin.slots.toggle', $slot->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-xs bg-gray-700 hover:bg-gray-600 px-3 py-1.5 rounded text-gray-200">
                                        Toggle Status
                                    </button>
                                </form>
                                <form action="{{ route('admin.slots.destroy', $slot->id) }}" method="POST" onsubmit="return confirm('Hapus slot ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs bg-red-600/30 hover:bg-red-600 text-red-300 hover:text-white px-3 py-1.5 rounded transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada slot parkir yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>