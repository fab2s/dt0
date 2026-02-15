<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Validator;

use Composer\Autoload\ClassLoader;
use fab2s\Dt0\Attribute\Rule;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use ReflectionClass;
use Throwable;

class Validator extends ValidatorAbstract
{
    protected static ?Factory $factory = null;
    protected Factory $validator;

    /** @var array<string, mixed> */
    protected array $rules = [];

    public function __construct()
    {
        $this->validator = static::$factory ??= static::resolveFactory();
    }

    protected static function resolveFactory(): Factory
    {
        if (function_exists('app')) {
            try {
                $factory = app('validator');
                if ($factory instanceof Factory) {
                    return $factory;
                }
            } catch (Throwable) {
            }
        }

        return new Factory(static::makeTranslator());
    }

    public static function makeTranslator(): Translator
    {
        /** @var string $translatorFile */
        $translatorFile = (new ReflectionClass(Translator::class))->getFileName();
        $translationDir = dirname($translatorFile) . '/lang';
        /** @var string $vendorDir */
        $vendorDir     = (new ReflectionClass(ClassLoader::class))->getFileName();
        $langDir       = dirname($vendorDir, 3) . '/lang';
        $configFile    = $langDir . '/config.php';
        $config        = file_exists($configFile) ? require $configFile : [];
        $defaultLocale = $config['locale']   ?? 'en';
        $fallback      = $config['fallback'] ?? 'en';

        $fileLoader = new FileLoader(new Filesystem, $translationDir);
        if (is_dir($langDir)) {
            $fileLoader->addPath($langDir);
        }

        $fileLoader->addNamespace('lang', $translationDir);
        $fileLoader->load($defaultLocale, 'validation', 'lang');
        $translator = new Translator($fileLoader, $defaultLocale);
        $translator->setFallback($fallback);

        return $translator;
    }

    /**
     * @throws ValidationException
     */
    public function validate(array $data): array
    {
        return $this->validator->make($data, $this->rules)->validate();
    }

    public function addRule(string $name, Rule $rule): static
    {
        $this->rules[$name] = $rule->rule;

        return $this;
    }
}
