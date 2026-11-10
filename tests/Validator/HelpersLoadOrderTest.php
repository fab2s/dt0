<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests\Validator;

use Illuminate\Translation\Translator;
use JsonException;
use Tests\TestCase;

/**
 * dt0 is the root package here, and Composer loads the root package's files
 * last, so in this process trans() & co. are always Laravel's. In consumer
 * apps, dt0's helpers can load first and replace Laravel's. That case can only
 * be reproduced in a fresh process that requires src/helpers.php before
 * vendor/autoload.php. #[RunInSeparateProcess] does not help here, because the
 * child process runs the bootstrap autoloader first.
 */
class HelpersLoadOrderTest extends TestCase
{
    /**
     * @throws JsonException
     */
    public function test_dt0_helpers_loaded_before_laravel_use_the_app_translator(): void
    {
        $result = $this->runFixture(__DIR__ . '/fixtures/helpers-load-order.php');

        // precondition: dt0's helpers are the ones defined
        $this->assertSame(dirname(__DIR__, 2) . '/src/helpers.php', $result['definedIn']);

        // nothing bound: standalone translator, cached
        $this->assertSame([
            'class'   => Translator::class,
            'locale'  => 'en',
            'cached'  => true,
            'message' => 'The email field is required.',
        ], $result['standalone']);

        // inside Laravel: the app translator, resolved through its deferred provider
        $this->assertFalse($result['app1']['resolvedBeforeTrans']);
        $this->assertTrue($result['app1']['isAppTranslator']);
        $this->assertSame('Bonjour Ada', $result['app1']['__']);
        $this->assertSame('Bonjour Ada', $result['app1']['trans']);
        $this->assertSame('3 pommes', $result['app1']['trans_choice']);
        $this->assertSame('Hello Ada', $result['app1']['explicitLocale']);

        // app torn down: standalone fallback again
        $this->assertTrue($result['afterReset']['isStandalone']);

        // app rebuilt: the new app's translator, nothing cached from the first one
        $this->assertTrue($result['app2']['isAppTranslator']);
        $this->assertSame('Hallo Alan', $result['app2']['__']);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws JsonException
     */
    protected function runFixture(string $fixture): array
    {
        // stderr goes to a file so that a chatty child cannot block on a full pipe
        $stderrFile = tmpfile();
        $this->assertIsResource($stderrFile);

        $process = proc_open(
            [PHP_BINARY, '-d', 'display_errors=stderr', '-d', 'xdebug.mode=off', $fixture],
            [1 => ['pipe', 'w'], 2 => $stderrFile],
            $pipes,
        );

        $this->assertIsResource($process);

        $stdout = (string) stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $exitCode = proc_close($process);

        rewind($stderrFile);
        $stderr = (string) stream_get_contents($stderrFile);
        fclose($stderrFile);

        $this->assertSame(0, $exitCode, $stderr . $stdout);

        return json_decode($stdout, true, 512, JSON_THROW_ON_ERROR);
    }
}
