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
use Illuminate\Container\Container;
use Illuminate\Contracts\Translation\Translator as TranslatorContract;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use ReflectionClass;
use Throwable;

class Validator extends ValidatorAbstract
{
    protected static ?Factory $factory       = null;
    protected static ?Translator $translator = null;

    /**
     * Not read by validate(), which uses the shared static::$factory so that
     * flush() reaches every validator. This per-instance copy is only kept for
     * subclasses that read it, and it goes stale once flush() is called. To use
     * a custom factory, override validate(), or override resolveFactory() and
     * redeclare static::$factory so as not to share the default one's cache.
     */
    protected Factory $validator;

    /** @var array<string, mixed> */
    protected array $rules = [];

    public function __construct()
    {
        $this->validator = static::$factory ??= static::resolveFactory();
    }

    /**
     * Forgets the resolved validation factory and the standalone translator, so
     * that every Validator, including those already built, resolves them again on
     * next use. Inside Laravel, the cached factory belongs to the app that resolved
     * it: call this when the app is rebuilt, as between two tests. laravel-dt0 does
     * it when its service provider registers.
     */
    public static function flush(): void
    {
        static::$factory    = null;
        static::$translator = null;
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

        // its own translator, so that a subclass overriding makeTranslator() is honoured
        return new Factory(static::makeTranslator());
    }

    /**
     * The translator behind dt0's trans(), __() and trans_choice() helpers.
     *
     * When a container has a `translator` bound (inside Laravel), this returns
     * the app's translator, so it follows the app locale and lang paths. It is
     * looked up on every call and never cached, because an app can be rebuilt
     * (tests). This costs about the same as Laravel's own trans(). Otherwise,
     * the standalone translator is built once by makeTranslator() and reused.
     */
    public static function translator(): TranslatorContract
    {
        // not autoloading: if Container was never loaded, no container instance can exist
        if (class_exists(Container::class, false)) {
            $container = Container::getInstance();
            if ($container->bound('translator')) {
                $translator = $container->make('translator');
                // outside Laravel, `translator` may be bound to something else
                if ($translator instanceof TranslatorContract) {
                    return $translator;
                }
            }
        }

        return static::$translator ??= static::makeTranslator();
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
        // read the shared factory, not $this->validator, so that flush() reaches the
        // validators that live in each Dt0 class's compiled metadata
        return (static::$factory ??= static::resolveFactory())->make($data, $this->rules)->validate();
    }

    public function addRule(string $name, Rule $rule): static
    {
        $this->rules[$name] = $rule->rule;

        return $this;
    }
}
