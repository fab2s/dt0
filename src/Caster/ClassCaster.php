<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Caster;

use fab2s\Dt0\Dt0;

class ClassCaster extends CasterAbstract
{
    public readonly array $parameters;

    public function __construct(
        public readonly ?string $fqn = null,
        mixed ...$parameters,
    ) {
        $this->parameters = $parameters;
    }

    public static function make(
        ?string $fqn = null,
        mixed ...$parameters,
    ): static {
        return new static($fqn, ...$parameters);
    }

    public function cast(mixed $value, array|Dt0|null $data = null): object
    {
        return match (true) {
            $value instanceof $this->fqn => $value,
            is_array($value)             => new $this->fqn(...$value),
            is_scalar($value)            => new $this->fqn($value),
            default                      => new $this->fqn(...$this->parameters),
        };
    }
}
