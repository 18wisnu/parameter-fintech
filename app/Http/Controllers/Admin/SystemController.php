<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class SystemController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'owner') {
            abort(403, 'Hanya Owner yang dapat mengakses halaman ini.');
        }

        $currentBranch = 'main';
        $localCommit = 'N/A';
        $remoteCommit = 'N/A';
        $hasUpdate = false;
        $remoteMessage = '';
        $lastUpdateAt = 'N/A';
        $checkMethod = 'git';

        try {
            chdir(base_path());
            
            // 1. Deteksi Branch (Default 'main')
            $branchOutput = trim((string) @shell_exec('git branch --show-current 2>&1'));
            if ($branchOutput && !str_contains($branchOutput, 'fatal') && !str_contains($branchOutput, 'not recognized')) {
                $currentBranch = $branchOutput;
            }
            
            // 2. Deteksi Local Commit
            $localOutput = trim((string) @shell_exec('git rev-parse --short HEAD 2>&1'));
            if ($localOutput && strlen($localOutput) <= 12 && !str_contains($localOutput, 'fatal')) {
                $localCommit = $localOutput;
            } else {
                // PHP Fallback baca .git/HEAD
                $headPath = base_path('.git/HEAD');
                if (file_exists($headPath)) {
                    $headContent = explode(' ', file_get_contents($headPath));
                    if (isset($headContent[1])) {
                        $refPath = base_path('.git/' . trim($headContent[1]));
                        if (file_exists($refPath)) {
                            $localCommit = substr(trim(file_get_contents($refPath)), 0, 7);
                        }
                    } else {
                        $localCommit = substr(trim($headContent[0]), 0, 7);
                    }
                }
            }
            $localCommit = $localCommit ?: 'N/A';

            // 3. Cek Remote menggunakan git fetch (Live Git)
            $fetchOutput = @shell_exec("git fetch origin {$currentBranch} 2>&1");
            $remoteGitCommit = trim((string) @shell_exec("git rev-parse --short origin/{$currentBranch} 2>&1"));

            if ($remoteGitCommit && strlen($remoteGitCommit) <= 12 && !str_contains($remoteGitCommit, 'fatal')) {
                $remoteCommit = $remoteGitCommit;
                $remoteMessage = trim((string) @shell_exec("git log -1 --pretty=%B origin/{$currentBranch} 2>&1"));
                $dateOutput = trim((string) @shell_exec("git log -1 --pretty=%ci origin/{$currentBranch} 2>&1"));
                if ($dateOutput) {
                    try {
                        $lastUpdateAt = Carbon::parse($dateOutput)->timezone('Asia/Jakarta')->format('d M Y H:i');
                    } catch (\Exception $e) {
                        $lastUpdateAt = $dateOutput;
                    }
                }
                $hasUpdate = ($localCommit !== $remoteCommit && $remoteCommit !== 'N/A');
                $checkMethod = 'git-live';
            } else {
                // Fallback ke GitHub REST API jika git fetch terkendala
                $response = Http::timeout(10)->withHeaders([
                    'User-Agent' => 'Parameter-Fintech-App'
                ])->get("https://api.github.com/repos/18wisnu/parameter-fintech/commits/{$currentBranch}");

                if ($response->successful()) {
                    $data = $response->json();
                    $remoteCommit = isset($data['sha']) ? substr($data['sha'], 0, 7) : 'N/A';
                    $remoteMessage = $data['commit']['message'] ?? '';
                    $lastUpdateAt = isset($data['commit']['committer']['date']) 
                        ? Carbon::parse($data['commit']['committer']['date'])->timezone('Asia/Jakarta')->format('d M Y H:i')
                        : 'N/A';
                    
                    $hasUpdate = ($localCommit !== $remoteCommit && $remoteCommit !== 'N/A');
                    $checkMethod = 'github-api';
                }
            }
        } catch (\Exception $e) {
            Log::error("Github Check Error: " . $e->getMessage());
        }

        return view('admin.system.index', compact(
            'currentBranch', 'localCommit', 'remoteCommit', 'hasUpdate', 
            'remoteMessage', 'lastUpdateAt', 'checkMethod'
        ));
    }

    public function updateVersion(Request $request)
    {
        if (auth()->user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Akses ditolak!');
        }

        $basePath = base_path();
        $phpBinary = PHP_BINARY;
        $output = [];

        try {
            chdir($basePath);

            // 1. Deteksi branch aktif
            $branch = trim((string) @shell_exec('git branch --show-current 2>&1')) ?: 'main';
            if (str_contains($branch, 'fatal') || str_contains($branch, 'not recognized')) {
                $branch = 'main';
            }

            // 2. Cari executable composer
            $composerCmd = null;
            $candidates = [
                'C:\\laragon\\bin\\composer\\composer.bat',
                'C:\\laragon\\bin\\composer\\composer.phar',
                base_path('composer.phar'),
            ];
            foreach ($candidates as $cand) {
                if (file_exists($cand)) {
                    if (str_ends_with($cand, '.phar')) {
                        $composerCmd = "\"$phpBinary\" \"$cand\"";
                    } else {
                        $composerCmd = "\"$cand\"";
                    }
                    break;
                }
            }

            // 3. Susun daftar perintah eksekusi
            $commands = [
                "git fetch origin {$branch}",
                "git reset --hard origin/{$branch}",
            ];

            if ($composerCmd) {
                $commands[] = "$composerCmd install --no-interaction --prefer-dist --optimize-autoloader";
            }

            $commands[] = "\"$phpBinary\" artisan migrate --force";
            $commands[] = "\"$phpBinary\" artisan optimize:clear";

            // 4. Eksekusi perintah secara berurutan
            foreach ($commands as $command) {
                $cmdOutput = @shell_exec($command . ' 2>&1');
                $output[] = "$ " . $command . "\n" . trim((string) $cmdOutput);
            }

            Log::info('System updated by Owner: ' . auth()->user()->email, ['output' => $output]);

            return redirect()->route('admin.system.index')
                ->with('success', 'Update Berhasil! Sistem telah disinkronkan dengan data terbaru dari GitHub.')
                ->with('update_log', implode("\n\n", $output));

        } catch (\Exception $e) {
            Log::error('System update failed', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Gagal update: ' . $e->getMessage())
                ->with('update_log', implode("\n\n", $output));
        }
    }
}
