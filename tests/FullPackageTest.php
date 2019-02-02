<?php

namespace Noitran\CsFixer\Tests;

/**
 * Class FullPackageTest
 */
class FullPackageTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldTestCsFixerCommand(): void
    {
        $this->artisan('phpcs:fix', [
            '--allow-risky' => 'yes'
        ])->assertExitCode(0);
    }
}
