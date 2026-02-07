<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Caster;

use Countable;
use IteratorAggregate;

interface CasterCollectionInterface extends CasterInterface, Countable, IteratorAggregate
{
    public function push(CasterInterface $caster): static;

    public function isEmpty(): bool;

    public function has(CasterInterface|string $caster): bool;

    public function get(CasterInterface|string $caster): ?CasterInterface;

    public function delete(CasterInterface|string $caster): static;

    public function toCaster(): ?static;
}
