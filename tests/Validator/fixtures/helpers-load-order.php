<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

/*
 * Run in its own process by HelpersLoadOrderTest.
 *
 * Reproduces a consumer app where Composer loads dt0's src/helpers.php BEFORE
 * laravel/framework's Foundation/helpers.php, so that dt0's trans(), __() and
 * trans_choice() are the ones defined. Prints its observations as JSON.
 */

use Illuminate\Container\Container;
use Orchestra\Testbench\Foundation\Application;

$root = dirname(__DIR__, 3);

require $root . '/src/helpers.php';
require $root . '/vendor/autoload.php';

$result = ['definedIn' => (new ReflectionFunction('trans'))->getFileName()];

// nothing bound: dt0's standalone translator, built once
$standalone           = trans();
$result['standalone'] = [
    'class'   => get_class($standalone),
    'locale'  => $standalone->getLocale(),
    'cached'  => trans() === $standalone,
    'message' => __('validation.required', ['attribute' => 'email']),
];

// inside Laravel: the app's translator, including through its deferred provider
$app            = Application::create();
$result['app1'] = ['resolvedBeforeTrans' => $app->resolved('translator')];

$result['app1']['isAppTranslator'] = trans() === $app['translator'];

$app->setLocale('fr');
$app['translator']->addLines(['dt0.hello' => 'Bonjour :name', 'dt0.apples' => 'une pomme|:count pommes'], 'fr');
$app['translator']->addLines(['dt0.hello' => 'Hello :name'], 'en');

$result['app1']['__']             = __('dt0.hello', ['name' => 'Ada']);
$result['app1']['trans']          = trans('dt0.hello', ['name' => 'Ada']);
$result['app1']['trans_choice']   = trans_choice('dt0.apples', 3);
$result['app1']['explicitLocale'] = trans('dt0.hello', ['name' => 'Ada'], 'en');

// the app goes away (as between two tests): back to standalone
$app->flush();
Container::setInstance(null);
$result['afterReset'] = ['isStandalone' => trans() === $standalone];

// a new app is built: its translator is used, nothing from the first app leaks
$app2 = Application::create();
$app2->setLocale('de');
$app2['translator']->addLines(['dt0.hello' => 'Hallo :name'], 'de');

$result['app2'] = [
    'isAppTranslator' => trans() === $app2['translator'],
    '__'              => __('dt0.hello', ['name' => 'Alan']),
];

echo json_encode($result, JSON_THROW_ON_ERROR);
