<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Concern;

trait HasPropName
{
    protected ?string $propName = null;

    public function getPropName(): ?string
    {
        return $this->propName;
    }

    public function setPropName(string $propName): static
    {
        $this->propName ??= $propName;

        return $this;
    }
}
