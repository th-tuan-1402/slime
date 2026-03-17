<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Discovers and registers routes from each enabled module.
 *
 * Reads the module registry in config/modules.php and auto-loads
 * the routes.php file from each registered module directory under
 * app/Modules/{Dir}/, applying the api/v1 prefix and api middleware.
 */
class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap module routes.
     *
     * Iterates over every entry in the modules config and, if a
     * routes.php file exists in the module directory, registers it
     * with the standard API prefix and middleware group.
     *
     * @return void
     */
    public function boot(): void
    {
        /** @var array<string, string> $modules */
        $modules = config('modules.modules', []);

        foreach ($modules as $key => $directory) {
            $routesPath = app_path("Modules/{$directory}/routes.php");

            if (file_exists($routesPath)) {
                Route::prefix('api/v1')
                    ->middleware('api')
                    ->group($routesPath);
            }
        }
    }
}
