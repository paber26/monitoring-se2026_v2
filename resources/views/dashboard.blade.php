<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SE2026 Monitoring Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen relative overflow-x-hidden">
    <!-- Background Decoration -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-blue-600 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute top-40 -left-40 w-96 h-96 bg-purple-600 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-40 left-20 w-96 h-96 bg-pink-600 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>
    </div>

    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <!-- Header -->
        <header class="mb-10 text-center">
            <h1 class="text-4xl md:text-5xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-purple-500 mb-2">
                Monitoring SE2026
            </h1>
            <p class="text-slate-400 text-lg">Pusat Sinkronisasi Data Assignment FASIH</p>
        </header>

        <!-- Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
            <!-- Total Assignment Card -->
            <div class="bg-white/10 backdrop-blur-lg border border-white/20 p-6 rounded-3xl shadow-2xl relative overflow-hidden group hover:bg-white/15 transition duration-300">
                <div class="absolute right-0 top-0 w-24 h-24 bg-blue-500/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <h3 class="text-slate-300 text-sm font-semibold uppercase tracking-wider mb-2">Total Assignment Fasih</h3>
                <div class="text-5xl font-bold text-white">{{ number_format($totalFasih) }}</div>
            </div>

            <!-- Total SLS Card -->
            <div class="bg-white/10 backdrop-blur-lg border border-white/20 p-6 rounded-3xl shadow-2xl relative overflow-hidden group hover:bg-white/15 transition duration-300">
                <div class="absolute right-0 top-0 w-24 h-24 bg-purple-500/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <h3 class="text-slate-300 text-sm font-semibold uppercase tracking-wider mb-2">Total SLS Terdata</h3>
                <div class="text-5xl font-bold text-white">{{ number_format($totalSls) }}</div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Upload Section (Left) -->
            <div class="lg:col-span-1">
                <div class="bg-white/10 backdrop-blur-lg border border-white/20 p-6 rounded-3xl shadow-2xl">
                    <h2 class="text-xl font-bold mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        Upload Data
                    </h2>
                    
                    @if(session('success'))
                        <div class="mb-4 p-3 bg-green-500/20 border border-green-500/50 text-green-200 rounded-lg text-sm">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 p-3 bg-red-500/20 border border-red-500/50 text-red-200 rounded-lg text-sm">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="w-full">
                            <label class="flex justify-center w-full h-32 px-4 transition bg-slate-800/50 border-2 border-slate-600 border-dashed rounded-xl appearance-none cursor-pointer hover:border-blue-400 focus:outline-none">
                                <span class="flex items-center space-x-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <span class="font-medium text-slate-400">
                                        Pilih CSV / Excel
                                    </span>
                                </span>
                                <input type="file" name="file" class="hidden" accept=".csv, .xlsx, .xls" required>
                            </label>
                            @error('file')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white rounded-xl font-semibold shadow-lg transition-transform transform hover:-translate-y-0.5 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:ring-offset-slate-900">
                            Proses & Update
                        </button>
                    </form>
                </div>
            </div>

            <!-- Table Section (Right) -->
            <div class="lg:col-span-2">
                <div class="bg-white/10 backdrop-blur-lg border border-white/20 rounded-3xl shadow-2xl overflow-hidden">
                    <div class="p-6 border-b border-white/10 flex justify-between items-center">
                        <h2 class="text-xl font-bold">Data Terbaru</h2>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-800/50 text-slate-300 text-sm uppercase tracking-wider">
                                    <th class="px-6 py-4 font-semibold">Kode SLS</th>
                                    <th class="px-6 py-4 font-semibold">Total Assignment</th>
                                    <th class="px-6 py-4 font-semibold">PPL</th>
                                    <th class="px-6 py-4 font-semibold">Terakhir Update</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @forelse($assignments as $assignment)
                                    <tr class="hover:bg-white/5 transition duration-150">
                                        <td class="px-6 py-4 font-mono text-blue-300">{{ $assignment->level_5_full_code }}</td>
                                        <td class="px-6 py-4 font-semibold">{{ $assignment->total_assignment_fasih }}</td>
                                        <td class="px-6 py-4 text-slate-400">{{ $assignment->ppl ?? '-' }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-400">{{ $assignment->updated_at->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                                            Belum ada data yang diunggah.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($assignments->hasPages())
                        <div class="p-4 border-t border-white/10">
                            {{ $assignments->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</body>
</html>
