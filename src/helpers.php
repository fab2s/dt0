<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

use fab2s\Dt0\Validator\Validator;
use Illuminate\Contracts\Translation\Translator;

// @codeCoverageIgnoreStart
// Composer does not guarantee that Laravel's Foundation/helpers.php loads before
// this file: vendor packages are ordered by their require graph, so in some apps
// these definitions win and Laravel's are skipped. They must therefore behave like
// Laravel's: same signatures, and Validator::translator() returns the container's
// translator whenever one is bound, falling back to dt0's standalone translator.
// In this repository dt0 is the root package, whose files Composer loads last,
// so Laravel's helpers always win in-process and these never get defined here:
// tests/Validator/HelpersLoadOrderTest.php covers them in a subprocess.
if (! function_exists('trans')) {
    /**
     * Translate the given message.
     *
     * @param string|null          $key
     * @param array<string, mixed> $replace
     * @param string|null          $locale
     *
     * @return ($key is null ? Translator : array<array-key, mixed>|string)
     */
    function trans($key = null, $replace = [], $locale = null): Translator|array|string
    {
        if ($key === null) {
            return Validator::translator();
        }

        return Validator::translator()->get($key, $replace, $locale); // @phpstan-ignore return.type
    }
}

if (! function_exists('trans_choice')) {
    /**
     * Translates the given message based on a count.
     *
     * @param string                                      $key
     * @param Countable|int|float|array<array-key, mixed> $number
     * @param array<string, mixed>                        $replace
     * @param string|null                                 $locale
     */
    function trans_choice($key, $number, array $replace = [], $locale = null): string
    {
        return Validator::translator()->choice($key, $number, $replace, $locale);
    }
}

if (! function_exists('__')) {
    /**
     * Translate the given message.
     *
     * @param string|null          $key
     * @param array<string, mixed> $replace
     * @param string|null          $locale
     *
     * @return array<array-key, mixed>|string|null
     */
    function __($key = null, $replace = [], $locale = null): array|string|null
    {
        if ($key === null) {
            return $key;
        }

        return trans($key, $replace, $locale);
    }
}
// @codeCoverageIgnoreEnd
