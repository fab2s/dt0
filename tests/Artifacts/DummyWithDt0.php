<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests\Artifacts;

use fab2s\Dt0\Attribute\With;
use fab2s\Dt0\Attribute\WithProp;
use fab2s\Dt0\Dt0;

#[With(
    new WithProp(name: 'protectedStringWithGetter', getter: 'getProtectedStringWithGetter'),
    new WithProp(name: 'protectedStringWithoutGetter'),
)]
class DummyWithDt0 extends Dt0
{
    public readonly string $publicReadonlyString;
    protected string $protectedStringWithGetter;
    protected string $protectedStringWithoutGetter;

    public function setProtectedStringWithGetter(string $protectedStringWithGetter): static
    {
        $this->protectedStringWithGetter = $protectedStringWithGetter;

        return $this;
    }

    public function getProtectedStringWithGetter(): string
    {
        return $this->protectedStringWithGetter;
    }

    protected function getProtectedStringWithProtectedGetter(): string
    {
        return $this->protectedStringWithGetter;
    }

    public function setProtectedStringWithoutGetter(string $protectedStringWithoutGetter): static
    {
        $this->protectedStringWithoutGetter = $protectedStringWithoutGetter;

        return $this;
    }
}
