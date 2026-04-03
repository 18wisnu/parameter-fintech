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
            
            // 1. Deteksi Branch (Fallback ke 'main')
            $currentBranch = trim(shell_exec('git branch --show-current')) ?: 'main';
            
            // 2. Deteksi Local Commit (Gunakan PHP Fallback jika shell_exec gagal)
            $localCommit = trim(shell_exec('git rev-parse --short HEAD'));
            if (!$localCommit || strlen($localCommit) > 10) {
                // Mencoba baca langsung dari file .git
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

            // 3. Cek ke GitHub API
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
            }
        } catch (\Exception $e) {
            Log::error("Github Check Error: " . $e->getMessage());
            $currentBranch = 'main (fallback)';
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
