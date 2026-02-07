<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Attribute;

use fab2s\Dt0\Concern\HasDeclaringFqnInterface;

interface WithInterface extends HasDeclaringFqnInterface
{
    public function hasWith(string $name): bool;

    public function getWith(string $name): ?WithProp;

    /** @return array<string, WithProp> */
    public function getWiths(): array;
}
