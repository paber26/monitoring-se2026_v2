<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportExcelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:excel {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Data Monitoring Excel file in the background';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = $this->argument('path');
        
        // Ensure file exists
        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($path)) {
            \Illuminate\Support\Facades\Cache::put('upload_progress', [
                'status' => 'error',
                'message' => 'File tidak ditemukan: ' . $path
            ], 300);
            return;
        }
        
        $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($path);

        try {
            \Illuminate\Support\Facades\Cache::put('upload_progress', [
                'status' => 'reading',
                'total' => 0,
                'current' => 0,
                'sls' => 'Membaca File...'
            ], 300);

            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\AssignmentImport, $fullPath);
            
            // Map the names
            $mappings = \App\Models\Target::where('type', 'sls')->get()->keyBy('key');
            $totalMappings = $mappings->count();
            
            \Illuminate\Support\Facades\Cache::put('upload_progress', [
                'status' => 'mapping',
                'total' => $totalMappings,
                'current' => 0,
                'sls' => 'Sinkronisasi Nama...'
            ], 300);

            $mapCount = 0;
            foreach ($mappings as $sls => $target) {
                $meta = $target->meta;
                $pplName = $meta['ppl_name'] ?? '';
                $pmlName = $meta['pml_name'] ?? '';
                
                \Illuminate\Support\Facades\DB::table('assignments')
                    ->where(function($q) use ($sls) {
                        $q->where('level_6_full_code', $sls)
                          ->orWhere('level_6_full_code', $sls . '.0');
                    })
                    ->update([
                        'assigned_ppl_name' => $pplName,
                        'assigned_pml_name' => $pmlName
                    ]);
                
                $mapCount++;
                if ($mapCount % 50 === 0) {
                    \Illuminate\Support\Facades\Cache::put('upload_progress', [
                        'status' => 'mapping',
                        'total' => $totalMappings,
                        'current' => $mapCount,
                        'sls' => $sls
                    ], 300);
                }
            }

            \Illuminate\Support\Facades\Cache::put('upload_progress', [
                'status' => 'done'
            ], 300);
            
            // Clean up
            @unlink($fullPath);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Cache::put('upload_progress', [
                'status' => 'error',
                'message' => $e->getMessage()
            ], 300);
        }
    }
}
