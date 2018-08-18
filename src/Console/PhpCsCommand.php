<?php

namespace Iocaste\CsFixer\Console;

use Illuminate\Console\Command;

use PhpCsFixer\Config;
use PhpCsFixer\Console\Command\FixCommandExitStatusCalculator;
use PhpCsFixer\Console\ConfigurationResolver;
use PhpCsFixer\Console\Output\ErrorOutput;
use PhpCsFixer\Console\Output\NullOutput;
use PhpCsFixer\Console\Output\ProcessOutput;
use PhpCsFixer\Error\ErrorsManager;
use PhpCsFixer\Finder;
use PhpCsFixer\Report\ReportSummary;
use PhpCsFixer\Runner\Runner;
use PhpCsFixer\ToolInfo;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Stopwatch\Stopwatch;
use RuntimeException;
use ArrayIterator;

/**
 * Class PhpCsCommand
 */
class PhpCsCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'phpcs:fix';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'phpcs:fix 
            {--path=* : The path.}
            {--path-mode=override : Specify path mode (can be override or intersection).}
            {--allow-risky= : Are risky fixers allowed (can be yes or no).}
            {--config= : The path to a .php_cs file.}
            {--dry-run : Only shows which files would have been modified.}
            {--rules= : The Rules}
            {--using-cache=yes : Does cache should be used (can be yes or no).}
            {--cache-file= : The path to the cache file.}
            {--diff : Also produce diff for each file.}
            {--diff-format= : Specify diff format.}
            {--format= : To output results in other formats.}
            {--stop-on-violation : Stop execution on first violation.}
            {--show-progress= : Type of progress indicator (none, run-in, estimating or estimating-max).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs PHPCS-Fixer tool to fix code to follow coding standards.';

    /**
     * @var Config
     */
    private $defaultConfig;

    /**
     * @var ErrorsManager
     */
    private $errorsManager;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var Stopwatch
     */
    private $stopwatch;

    /**
     * @var ToolInfo
     */
    private $toolInfo;

    /**
     * PhpCsCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->defaultConfig = new Config();
        $this->errorsManager = new ErrorsManager();
        $this->eventDispatcher = new EventDispatcher();
        $this->stopwatch = new Stopwatch();
        $this->toolInfo = new ToolInfo();
    }

    /**
     * Handles the Command
     */
    public function handle()
    {
        $this->validateOptions();
        $resolver = $this->getResolver();
        list($finder, $progressOutput) = $this->manageProgress($resolver);
        $runner = $this->getRunner($finder, $resolver);

        $this->stopwatch->start('fixFiles');
        $changed = $runner->fix();
        $this->stopwatch->stop('fixFiles');
        $progressOutput->printLegend();
        $fixEvent = $this->stopwatch->getEvent('fixFiles');

        $reportSummary = $this->createReport($changed, $fixEvent, $resolver);

        $this->getOutput()->isDecorated() ?
            $this->getOutput()->write($resolver->getReporter()->generate($reportSummary)) :
            $this->getOutput()->write(
                $resolver->getReporter()->generate($reportSummary),
                false,
                OutputInterface::OUTPUT_RAW
            );

        $invalidErrors = $this->errorsManager->getInvalidErrors();
        $exceptionErrors = $this->errorsManager->getExceptionErrors();
        $lintErrors = $this->errorsManager->getLintErrors();

        $errorOutput = new ErrorOutput($this->getOutput());

        if (\count($invalidErrors) > 0) {
            $errorOutput->listErrors('linting before fixing', $invalidErrors);
        }

        if (\count($exceptionErrors) > 0) {
            $errorOutput->listErrors('fixing', $exceptionErrors);
        }

        if (\count($lintErrors) > 0) {
            $errorOutput->listErrors('linting after fixing', $lintErrors);
        }

        $exitStatusCalculator = new FixCommandExitStatusCalculator();

        return $exitStatusCalculator->calculate(
            $resolver->isDryRun(),
            \count($changed) > 0,
            \count($invalidErrors) > 0,
            \count($exceptionErrors) > 0
        );
    }

    /**
     * @param $changed
     * @param $fixEvent
     * @param ConfigurationResolver $resolver
     *
     * @return ReportSummary
     */
    protected function createReport($changed, $fixEvent, ConfigurationResolver $resolver): ReportSummary
    {
        return new ReportSummary(
            $changed,
            $fixEvent->getDuration(),
            $fixEvent->getMemory(),
            OutputInterface::VERBOSITY_VERBOSE <= $this->getOutput()->getVerbosity(),
            $resolver->isDryRun(),
            $this->getOutput()->isDecorated()
        );
    }

    /**
     * @param $finder
     * @param ConfigurationResolver $resolver
     *
     * @return Runner
     */
    protected function getRunner($finder, ConfigurationResolver $resolver): Runner
    {
        return new Runner(
            $finder,
            $resolver->getFixers(),
            $resolver->getDiffer(),
            'none' !== $resolver->getProgress() ? $this->eventDispatcher : null,
            $this->errorsManager,
            $resolver->getLinter(),
            $resolver->isDryRun(),
            $resolver->getCacheManager(),
            $resolver->getDirectory(),
            $resolver->shouldStopOnViolation()
        );
    }

    /**
     * @return Config
     */
    protected function getConfig(): Config
    {
        $config = new Config('artisan');
        $config->setRules(config('fixer.rules'));
        $config->setFinder($this->getFinder());
        $config->setCacheFile(storage_path('framework/cache/fixer.json'));

        return $config;
    }

    /**
     * @return Finder
     */
    protected function getFinder(): Finder
    {
        return Finder::create()
            ->in(base_path())
            ->exclude([
                'bootstrap/cache',
                'bower_components',
                'node_modules',
                'tasks',
                'public',
                'bin',
                'storage',
                'vendor',
            ])
            ->notPath('_ide_helper_models.php')
            ->notPath('_ide_helper.php')
            ->notPath('.phpstorm.meta.php')
            ->notName('*.blade.php')
            ->ignoreDotFiles(true)
            ->ignoreVCS(true);
    }

    /**
     * @return ConfigurationResolver
     */
    protected function getResolver(): ConfigurationResolver
    {
        $resolver = new ConfigurationResolver(
            $this->getConfig(),
            [
                'allow-risky' => $this->option('allow-risky'),
                'config' => $this->option('config'),
                'dry-run' => $this->option('dry-run'),
                'rules' => $this->option('rules'),
                'path' => $this->option('path'),
                'path-mode' => $this->option('path-mode'),
                'using-cache' => $this->option('using-cache'),
                'cache-file' => $this->option('cache-file'),
                'format' => $this->option('format'),
                'diff' => $this->option('diff'),
                'diff-format' => $this->option('diff-format'),
                'stop-on-violation' => $this->option('stop-on-violation'),
                'verbosity' => $this->verbosity,
                'show-progress' => $this->option('show-progress'),
            ],
            getcwd(),
            $this->toolInfo
        );

        $this->info(sprintf(
            'Loaded config <comment>%s</comment>%s.',
            $resolver->getConfig()->getName(),
            null === $resolver->getConfigFile() ? '' : ' from "'.$resolver->getConfigFile().'"'
        ));

        if ($resolver->getUsingCache()) {
            $cacheFile = $resolver->getCacheFile();
            if (is_file($cacheFile)) {
                $this->info(sprintf('Using cache file <comment>%s</comment>.', $cacheFile));
            }
        }

        if ($resolver->configFinderIsOverridden()) {
            $this->info(
                'Paths from configuration file have been overridden by paths provided as command arguments.'
            );
        }

        return $resolver;
    }

    /**
     *
     */
    protected function validateOptions(): void
    {
        if (null !== $this->option('config') && null !== $this->option('rules')) {
            if (getenv('PHP_CS_FIXER_FUTURE_MODE')) {
                throw new RuntimeException(
                    'Passing both `config` and `rules` options is not possible. 
                    This check was performed as `PHP_CS_FIXER_FUTURE_MODE` env var is set.'
                );
            }

            $this->warn('When passing both "--config" and "--rules" 
                the rules within the configuration file are not used.');
            $this->warn('Passing both options is deprecated; version 
                v3.0 PHP-CS-Fixer will exit with a configuration error code.');
        }
    }

    /**
     * @param $resolver
     *
     * @return array
     */
    protected function manageProgress($resolver): array
    {
        $finder = $resolver->getFinder();
        if ('none' === $resolver->getProgress()) {
            $progressOutput = new NullOutput();
        } elseif ('run-in' === $resolver->getProgress()) {
            $progressOutput = new ProcessOutput($this->getOutput(), $this->eventDispatcher, null, null);
        } else {
            $finder = new ArrayIterator(iterator_to_array($finder));
            $progressOutput = new ProcessOutput(
                $this->getOutput(),
                $this->eventDispatcher,
                'estimating-max' === $resolver->getProgress() ? (new Terminal())->getWidth() : null,
                \count($finder)
            );
        }

        return [$finder, $progressOutput];
    }
}
