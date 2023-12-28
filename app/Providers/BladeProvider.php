<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;

class BladeProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Blade::directive('role', function($roles) {
            $roles = (empty($roles))?'[]':$roles;
            // dd($roles);
            return
            "<?php if (
                !Auth::guest() && (
                    array_intersect({$roles}, array_keys(Illuminate\Support\Facades\Auth::user()->getRoles()))
                    || empty({$roles})
                ) || empty({$roles})
            ) : ?>";
        });

        Blade::directive('endrole', function() {
            return "<?php endif; ?>";
        });
    }
}
