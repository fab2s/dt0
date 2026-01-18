<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests\Artifacts;

class MiddleDt0 extends GrandparentDt0
{
    public function __construct(
        public readonly string $deepInheritedProp = 'middleDefault',
        ...$args,
    ) {
        parent::__construct(...$args);
    }
}
