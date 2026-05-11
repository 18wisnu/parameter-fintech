<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <-- Tambahkan baris ini

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // --- RECORD ALL LOGIN/LOGOUT ---
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            \App\Models\ActivityLog::create([
                'user_id' => $event->user->id,
                'activity' => 'User Berhasil Login (Sesi Aktif)',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Logout::class, function ($event) {
            if ($event->user) {
                \App\Models\ActivityLog::create([
                    'user_id' => $event->user->id,
                    'activity' => 'User Melakukan Logout (Sesi Berakhir)',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        });

        // --- SHARE APP SETTINGS GLOBALLY (MULTI-TENANT) ---
        view()->composer('*', function ($view) {
            try {
                $user = auth()->user();
                $appSettings = [
                    'business_name' => 'Parameter Fintech',
                    'theme_color' => '#0369a1',
                    'features' => [
                        'profit_sharing' => true,
                        'reserve_fund' => true,
                        'salary_management' => true,
                        'voucher_analysis' => true,
                        'customer_data' => true,
                        'invoices' => true,
                        'transactions' => true,
                        'staff_data' => true,
                    ]
                ];

                if ($user && $user->current_business_id && \Illuminate\Support\Facades\Schema::hasTable('businesses')) {
                    $business = \App\Models\Business::find($user->current_business_id);
                    if ($business) {
                        $appSettings['business_name'] = $business->name;
                        $appSettings['theme_color'] = $business->theme_color;
                        
                        // Features from business table
                        if ($business->enabled_features) {
                            $appSettings['features'] = array_merge($appSettings['features'], $business->enabled_features);
                        }

                        // Role ALWAYS comes from pivot table (per-business), NEVER from users.role
                        $userRole = 'member'; // default
                        if (\Illuminate\Support\Facades\Schema::hasTable('business_user')) {
                            $pivotRole = \Illuminate\Support\Facades\DB::table('business_user')
                                ->where('business_id', $business->id)
                                ->where('user_id', $user->id)
                                ->value('role');
                            if ($pivotRole) $userRole = $pivotRole;
                        }
                        $view->with('userRole', $userRole);
                    } else {
                        $view->with('userRole', null); // No active business
                    }
                } else {
                    $view->with('userRole', null); // No business selected yet
                }

                $view->with('appSettings', $appSettings);
            } catch (\Exception $e) {
                // Return default settings
                $view->with('appSettings', [
                    'business_name' => 'Parameter Fintech',
                    'theme_color' => '#0369a1',
                    'features' => [
                        'profit_sharing' => true,
                        'reserve_fund' => true,
                        'salary_management' => true,
                        'voucher_analysis' => true,
                        'customer_data' => true,
                        'invoices' => true,
                        'transactions' => true,
                        'staff_data' => true,
                    ]
                ]);
            }
        });
    }
}