<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Concern;

use fab2s\Dt0\Dt0;

trait HasDeclaringFqn
{
    protected ?string $declaringFqn = null;

    public function getDeclaringFqn(): ?string
    {
        return $this->declaringFqn;
    }

    /**
     * @param class-string<Dt0> $declaringFqn
     *
     * @return $this
     */
    public function setDeclaringFqn(string $declaringFqn): static
    {
        $this->declaringFqn ??= $declaringFqn;

        return $this;
    }
}
