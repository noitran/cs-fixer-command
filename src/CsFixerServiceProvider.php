<?php

namespace Iocaste\CsFixer;

use Illuminate\Support\ServiceProvider;
use Iocaste\CsFixer\Console\PhpCsCommand;
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

        $this->app->singleton('command.phpcs.fix', function ($app) {
            return new PhpCsCommand();
        });
        $this->commands('command.phpcs.fix');
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
