<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Attribute;

class WithProp extends WithPropAbstract
{
    public readonly string|bool|null $getter;
    public readonly ?string $name;

    public function __construct(
        ?string $name = null,
        string|bool|null $getter = null,
    ) {
        $this->name   = $name;
        $this->getter = $getter;

        if ($this->name !== null) {
            $this->setPropName($this->name);
        }
    }

    public static function make(
        ?string $name = null,
        ?string $getter = null,
    ): static {
        return new static($name, $getter);
    }
}
