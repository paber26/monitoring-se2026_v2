@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
        <div class="px-6 py-5 border-b border-slate-100 bg-white flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="p-2 bg-slate-50 text-slate-600 rounded-lg">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Performa: {{ $role }}</h3>
            </div>
            <span class="text-xs font-medium bg-slate-100 text-slate-500 px-2.5 py-1 rounded-full">{{ count($leaderboard) }} Orang</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-slate-500 bg-slate-50 uppercase font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4 w-16">No</th>
                        <th class="px-6 py-4">Nama {{ $role }}</th>
                        <th class="px-6 py-4">Kecamatan</th>
                        <th class="px-6 py-4 text-right">Target</th>
                        <th class="px-6 py-4 text-right">Progress</th>
                        @if($isPML)
                            <th class="px-6 py-4 text-center">Rasio Penolakan</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @php $index = 1; @endphp
                    @foreach($leaderboard as $d)
                        @php
                            arsort($d['kecamatans']);
                            $domKec = array_key_first($d['kecamatans']);
                            $targetVal = $targets[$d['username']] ?? 0;
                            
                            $pmlExtra = '';
                            if ($isPML) {
                                $rejectedCount = $d['statuses']['REJECTED PML'] ?? 0;
                                $approvedCount = $d['statuses']['APPROVED PML'] ?? 0;
                                $totalAction = $rejectedCount + $approvedCount;
                                $rejectRatio = $totalAction > 0 ? number_format(($rejectedCount / $totalAction) * 100, 1, ',', '.') : 0;
                                $ratioClass = $rejectRatio > 10 ? 'text-red-600 font-semibold' : 'text-slate-600';
                                $pmlExtra = "<td class=\"px-6 py-3 text-center\"><span class=\"{$ratioClass}\">{$rejectRatio}%</span></td>";
                            }
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3 text-slate-500">{{ $index++ }}</td>
                            <td class="px-6 py-3 font-semibold text-slate-800">{{ $d['name'] }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ $domKec }}</td>
                            <td class="px-6 py-3 text-right font-semibold text-slate-700">{{ $targetVal > 0 ? number_format($targetVal, 0, ',', '.') : '-' }}</td>
                            <td class="px-6 py-3 text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                    {{ number_format($d['total'], 0, ',', '.') }}
                                </span>
                            </td>
                            @if($isPML)
                                {!! $pmlExtra !!}
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
