<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class FetchDashboardJsonCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:fetch-json {--cookie= : The session cookie} {--level= : Specific level to fetch (kecamatan, desa, sls)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch JSON monitoring data and save to datadashboardse folder per kecamatan/desa';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cookie = $this->option('cookie') ?: env('DASHBOARD_COOKIE');
        if (empty($cookie)) {
            $this->error('Error: Session cookie is required. Please provide it via --cookie="your_cookie" or set DASHBOARD_COOKIE in .env');
            return 1;
        }

        $targetDir = base_path('../datadashboardse');
        $indikator = '1,2,108,109,110,10242,10244,10245,10246,10247,10264,10265,10266,14,10268,10271';
        $kabupaten = '7105';
        
        $url = "https://dashboard-se2026.apps.bps.go.id/api/agregat/fasih";
        $headers = [
            'Cookie' => $cookie,
            'Accept' => 'application/json, text/plain, */*',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        ];

        // 1. Read Kecamatan from local file
        $this->info("Loading data for level: kecamatan...");
        $kecamatanDir = $targetDir . '/kecamatan';
        $kecamatanFile = $targetDir . "/level_kecamatan.json";
        
        if (!File::exists($kecamatanFile)) {
            $this->error("File kecamatan tidak ditemukan di {$kecamatanFile}.");
            return 1;
        }

        $kecamatanData = json_decode(File::get($kecamatanFile), true);
        $this->info("Loaded kecamatan data from local file.");

        // Extract kecamatan codes
        $kecamatanCodes = [];
        foreach ($kecamatanData as $item) {
            if (isset($item['id_wilayah'])) {
                $code = substr($item['id_wilayah'], 0, 7);
                if ($code !== $kabupaten . '000') {
                    $kecamatanCodes[] = $code;
                }
            }
        }
        $kecamatanCodes = array_unique($kecamatanCodes);

        // 2. Fetch specific level
        $levelsToFetch = $this->option('level') ? [$this->option('level')] : ['desa', 'sls'];

        foreach ($levelsToFetch as $level) {
            if ($level === 'kecamatan') continue;
            
            $this->info("\nFetching data for level: {$level}...");
            $levelDir = $targetDir . '/' . $level;
            if (!File::exists($levelDir)) {
                File::makeDirectory($levelDir, 0755, true);
            }

            foreach ($kecamatanCodes as $kecCode) {
                if ($level === 'sls') {
                    // Chunk SLS by desa to prevent timeout
                    $desaFile = $targetDir . "/desa/{$kecCode}.json";
                    if (!File::exists($desaFile)) {
                        $this->error("  -> [SKIP] File desa {$kecCode}.json tidak ada. Tarik level desa dulu.");
                        continue;
                    }
                    
                    $desaData = json_decode(File::get($desaFile), true);
                    $desaCodes = [];
                    foreach ($desaData as $item) {
                        if (isset($item['id_wilayah'])) {
                            $dCode = substr($item['id_wilayah'], 0, 10);
                            // Hindari kode agregat kecamatan
                            if ($dCode !== $kecCode . '000') {
                                $desaCodes[] = $dCode;
                            }
                        }
                    }
                    $desaCodes = array_unique($desaCodes);

                    foreach ($desaCodes as $dCode) {
                        $this->info("  -> Fetching sls for desa {$dCode}...");
                        
                        $maxRetries = 3;
                        $attempt = 0;
                        $success = false;

                        while ($attempt < $maxRetries && !$success) {
                            $attempt++;
                            if ($attempt > 1) {
                                $this->warn("     Retrying ({$attempt}/{$maxRetries}) for {$dCode}...");
                                sleep(2);
                            }
                            try {
                                $res = Http::withHeaders($headers)->timeout(120)->get($url, [
                                    'level' => $level,
                                    'jenis' => 'progres',
                                    'indikator' => $indikator,
                                    'kabupaten' => $kabupaten,
                                    'kecamatan' => $kecCode,
                                    'desa' => $dCode
                                ]);

                                if ($res->successful()) {
                                    $jsonContent = $res->json();
                                    
                                    if ($jsonContent === null || (isset($jsonContent['error']) && $jsonContent['error'] === true)) {
                                        if ($attempt == $maxRetries) {
                                            $this->error("     Failed to parse or API error for {$dCode} after {$maxRetries} attempts.");
                                        }
                                        continue;
                                    }

                                    $filePath = $levelDir . "/{$dCode}.json";
                                    File::put($filePath, json_encode($jsonContent, JSON_PRETTY_PRINT));
                                    $this->info("     Success! Saved to {$filePath}");
                                    $success = true;
                                } else {
                                    if ($attempt == $maxRetries) {
                                        $this->error("     Failed. Status: " . $res->status());
                                    }
                                }
                            } catch (\Exception $e) {
                                if ($attempt == $maxRetries) {
                                    $this->error("     Exception for {$dCode}: " . $e->getMessage());
                                }
                            }
                        }
                    }

                } else {
                    // Level desa (chunking by kecamatan)
                    $this->info("  -> Fetching {$level} for kecamatan {$kecCode}...");
                    
                    $maxRetries = 3;
                    $attempt = 0;
                    $success = false;

                    while ($attempt < $maxRetries && !$success) {
                        $attempt++;
                        if ($attempt > 1) {
                            $this->warn("     Retrying ({$attempt}/{$maxRetries}) for kecamatan {$kecCode}...");
                            sleep(2);
                        }
                        try {
                            $res = Http::withHeaders($headers)->timeout(120)->get($url, [
                                'level' => $level,
                                'jenis' => 'progres',
                                'indikator' => $indikator,
                                'kabupaten' => $kabupaten,
                                'kecamatan' => $kecCode
                            ]);

                            if ($res->successful()) {
                                $jsonContent = $res->json();
                                
                                if ($jsonContent === null || (isset($jsonContent['error']) && $jsonContent['error'] === true)) {
                                    if ($attempt == $maxRetries) {
                                        $this->error("     Failed to parse or API error for {$kecCode} after {$maxRetries} attempts.");
                                    }
                                    continue;
                                }

                                $filePath = $levelDir . "/{$kecCode}.json";
                                File::put($filePath, json_encode($jsonContent, JSON_PRETTY_PRINT));
                                $this->info("     Success! Saved to {$filePath}");
                                $success = true;
                            } else {
                                if ($attempt == $maxRetries) {
                                    $this->error("     Failed. Status: " . $res->status());
                                }
                            }
                        } catch (\Exception $e) {
                            if ($attempt == $maxRetries) {
                                $this->error("     Exception for {$kecCode}: " . $e->getMessage());
                            }
                        }
                    }
                }
            }
        }

        $this->info("\nFinished fetching JSON data.");
        return 0;
    }
}
