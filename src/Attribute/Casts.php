<?php

/*
 * This file is part of fab2s/Dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/Dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Casts
{
    /**
     * @var array<string, Cast>
     */
    protected array $casters = [];

    public function __construct(
        Cast ...$casters,
    ) {
        foreach ($casters as $name => $caster) {
            if (is_int($name)) {
                if (! $caster->propName) {
                    continue;
                }

                $name = $caster->propName;
            }

            $this->casters[$name] = $caster;
        }
    }

    public function hasCast($name): bool
    {
        return isset($this->casters[$name]);
    }

    public function getCast($name): ?Cast
    {
        return $this->casters[$name] ?? null;
    }

    public function getCasters(): array
    {
        return $this->casters;
    }
}
