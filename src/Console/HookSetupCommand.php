<?php

namespace Iocaste\CsFixer\Console;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Class HookSetupCommand
 */
class HookSetupCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'phpcs:install-hook';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'phpcs:install-hook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs pre-commit git hook to run phpcs every on code changes.';

    /**
     * Handles the Command
     */
    public function handle()
    {
        $hookFile = \dirname(__DIR__) . '/../contrib/pre-commit';
        $gitHooksPath = config('phpcs.git_hooks_path');

        $this->copyPreCommitHook($hookFile, $gitHooksPath);
        $this->setPermissions($gitHooksPath);
    }

    /**
     * @param $hookFile
     * @param $gitHooksPath
     *
     * @return void
     */
    protected function copyPreCommitHook($hookFile, $gitHooksPath): void
    {
        $process = new Process('cp ' . $hookFile . ' ' . $gitHooksPath . '/');
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // $process->getOutput()
    }

    /**
     * @param $gitHooksPath
     *
     * @return void
     */
    protected function setPermissions($gitHooksPath): void
    {
        $process = new Process('chmod +x ' . $gitHooksPath . '/pre-commit');
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // $process->getOutput()
    }
}
