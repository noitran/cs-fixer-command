<?php

namespace Noitran\CsFixer\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Noitran\CsFixer\CsFixerServiceProvider;

/**
 * Class TestCase
 */
abstract class TestCase extends OrchestraTestCase
{
    /**
     * @param \Laravel\Lumen\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            CsFixerServiceProvider::class,
        ];
    }
}
