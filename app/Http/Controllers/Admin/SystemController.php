<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class SystemController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'owner') {
            abort(403, 'Hanya Owner yang dapat mengakses halaman ini.');
        }

        $currentBranch = 'N/A';
        $localCommit = 'N/A';
        $remoteCommit = 'N/A';
        $hasUpdate = false;
        $remoteMessage = '';
        $lastUpdateAt = 'N/A';

        try {
            chdir(base_path());
            
            // Perbaikan deteksi: Gunakan ?: untuk menangkap string kosong
            $currentBranch = trim(shell_exec('git branch --show-current')) ?: 'main';
            if (!$currentBranch) $currentBranch = 'main';

            $localCommit = trim(shell_exec('git rev-parse --short HEAD')) ?: 'N/A';

            // Cek update di GitHub API
            $response = \Illuminate\Support\Facades\Http::timeout(10)->withHeaders([
                'User-Agent' => 'Parameter-Fintech-App'
            ])->get("https://api.github.com/repos/18wisnu/parameter-fintech/commits/{$currentBranch}");

            if ($response->successful()) {
                $data = $response->json();
                $remoteCommit = isset($data['sha']) ? substr($data['sha'], 0, 7) : 'N/A';
                $remoteMessage = $data['commit']['message'] ?? '';
                $lastUpdateAt = isset($data['commit']['committer']['date']) 
                    ? \Carbon\Carbon::parse($data['commit']['committer']['date'])->timezone('Asia/Jakarta')->format('d M Y H:i')
                    : 'N/A';
                
                // Bandingkan commit hash pendek
                $hasUpdate = ($localCommit !== $remoteCommit && $remoteCommit !== 'N/A');
                
                // Log untuk debugging jika perlu
                Log::debug("System Check: Local=$localCommit, Remote=$remoteCommit, Update=" . ($hasUpdate ? 'YES' : 'NO'));
            }
        } catch (\Exception $e) {
            Log::error("Github Check Error: " . $e->getMessage());
            $currentBranch = 'main (Local)';
        }

        return view('admin.system.index', compact('currentBranch', 'localCommit', 'remoteCommit', 'hasUpdate', 'remoteMessage', 'lastUpdateAt'));
    }

    public function updateVersion(Request $request)
    {
        if (auth()->user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Akses ditolak!');
        }

        $basePath = base_path();
        $output = [];
        $commands = [
            'git fetch origin main',
            'git reset --hard origin/main',
            'composer install --no-interaction --prefer-dist --optimize-autoloader',
            'php artisan migrate --force',
            'php artisan config:clear',
            'php artisan config:cache',
            'php artisan route:cache',
            'php artisan view:cache'
        ];

        try {
            // Store current directory
            $currentDir = getcwd();
            // Change to base path
            chdir($basePath);

            foreach ($commands as $command) {
                $process = shell_exec($command . ' 2>&1');
                $output[] = "$ " . $command . "\n" . $process;
            }
            
            // Restore current directory
            chdir($currentDir);
            
            Log::info('System updated by Owner: ' . auth()->user()->email, ['output' => $output]);
            
            return redirect()->back()->with('success', 'Update Berhasil! Sistem telah diperbarui ke versi terbaru dari GitHub.');
        } catch (\Exception $e) {
            Log::error('System update failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }
}
