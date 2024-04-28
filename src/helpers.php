<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;

if (! function_exists('trans')) {
    /**
     * Translate the given message.
     *
     * @return \Illuminate\Contracts\Translation\Translator|string|array|null
     */
    function trans(?string $key = null, array $replace = [], ?string $locale = null): Translator|array|string|null
    {
        static $translator = null;
        $locale ??= 'en';
        if (! $translator) {
            $reflection      = new \ReflectionClass(Translator::class);
            $translationDir  = dirname($reflection->getFileName()) . '/lang';
            $translationDirs = [$translationDir];
            if ($cwd = getcwd()) {
                $translationDirs[] = dirname($cwd) . '/lang';
                $translationDirs[] = $cwd . '/lang';
            }
            $fileLoader = new FileLoader(new Filesystem, $translationDirs);
            $fileLoader->addNamespace('lang', $translationDir);
            $fileLoader->load($locale, 'validation', 'lang');
            $translator = new Translator($fileLoader, $locale);
            $translator->setLocale($locale);
            $translator->setFallback('en');
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
     * @param string                     $key
     * @param \Countable|int|float|array $number
     * @param string|null                $locale
     *
     * @return string
     */
    function trans_choice($key, $number, array $replace = [], $locale = null)
    {
        return trans()->choice($key, $number, $replace, $locale);
    }
}

if (! function_exists('__')) {
    /**
     * Translate the given message.
     *
     * @param string|null $key
     * @param array       $replace
     * @param string|null $locale
     *
     * @return string|array|null
     */
    function __($key = null, $replace = [], $locale = null)
    {
        if ($key === null) {
            return $key;
        }

        return trans($key, $replace, $locale);
    }
}
