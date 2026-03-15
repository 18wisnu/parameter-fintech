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
        try {
            chdir(base_path());
            $branch = shell_exec('git branch --show-current');
            $currentBranch = trim($branch);
        } catch (\Exception $e) {}

        return view('admin.system.index', compact('currentBranch'));
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
