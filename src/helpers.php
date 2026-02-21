<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

use fab2s\Dt0\Validator\Validator;
use Illuminate\Translation\Translator;

// @codeCoverageIgnoreStart
// These functions are guarded by function_exists
// and cannot be defined when Laravel's helpers are loaded in the same process.
// They delegate to Validator::makeTranslator() which has full test coverage.
if (! function_exists('trans')) {
    /**
     * Translate the given message.
     *
     * @param array<string, string> $replace
     *
     * @return Translator|array<string, string>|string|null
     */
    function trans(?string $key = null, array $replace = [], ?string $locale = null): Translator|array|string|null
    {
        static $translator = null;
        if (! $translator) {
            $translator = Validator::makeTranslator();
        }

        if ($key === null) {
            return $translator;
        }

        return $translator->get($key, $replace, $locale);
    }
}

if (! function_exists('trans_choice')) {
    /**
     * Translates the given message based on a count.
     *
     * @param \Countable|int|float|array<array-key, mixed> $number
     * @param array<string, string>                        $replace
     */
    function trans_choice(string $key, \Countable|int|float|array $number, array $replace = [], ?string $locale = null): string
    {
        /** @var Translator $translator */
        $translator = trans();

        return $translator->choice($key, $number, $replace, $locale);
    }
}

if (! function_exists('__')) {
    /**
     * Translate the given message.
     *
     * @param array<string, string> $replace
     *
     * @return array<string, string>|string|null
     */
    function __(?string $key = null, array $replace = [], ?string $locale = null): array|string|null
    {
        if ($key === null) {
            return $key;
        }

        return trans($key, $replace, $locale); // @phpstan-ignore return.type
    }
}
// @codeCoverageIgnoreEnd
