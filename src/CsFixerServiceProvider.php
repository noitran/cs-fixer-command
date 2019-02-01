<?php

namespace Noitran\CsFixer;

use Illuminate\Support\ServiceProvider;
use Noitran\CsFixer\Console\PhpCsCommand;
use Noitran\CsFixer\Console\HookSetupCommand;
use Laravel\Lumen\Application as LumenApplication;
use Illuminate\Foundation\Application as LaravelApplication;

/**
 * Class CsFixerServiceProvider
 */
class CsFixerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->setupConfig();

        $this->app->singleton('command.phpcs.fix', function () {
            return new PhpCsCommand();
        });

        $this->app->singleton('command.phpcs.install-hook', function () {
            return new HookSetupCommand();
        });

        $this->commands('command.phpcs.fix');
        $this->commands('command.phpcs.install-hook');
    }

    /**
     * @return void
     */
    protected function setupConfig(): void
    {
        $source = \dirname(__DIR__) . '/config/phpcs.php';
        if ($this->app instanceof LaravelApplication) {
            $this->publishes([$source => config_path('phpcs.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('phpcs');
        }
        $this->mergeConfigFrom($source, 'phpcs');
    }
}
