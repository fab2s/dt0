<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Attribute;

use Attribute;
use fab2s\Dt0\Caster\CasterInterface;
use fab2s\Dt0\Dt0;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Cast
{
    public readonly bool $hasDefault;
    public readonly ?CasterInterface $in;
    public readonly ?CasterInterface $out;

    public function __construct(
        CasterInterface|string|null $in = null,
        CasterInterface|string|null $out = null,
        public readonly mixed $default = Dt0::DT0_NIL,
        public readonly string|array|null $renameFrom = null,
        public readonly ?string $renameTo = null,
        public readonly ?string $propName = null,
    ) {
        $this->in         = $in instanceof CasterInterface ? $in : ($in ? new $in : null);
        $this->out        = $out instanceof CasterInterface ? $out : ($out ? new $out : null);
        $this->hasDefault = $this->default !== Dt0::DT0_NIL;
    }

    public static function make(
        CasterInterface|string|null $in = null,
        CasterInterface|string|null $out = null,
        mixed $default = Dt0::DT0_NIL,
        string|array|null $renameFrom = null,
        ?string $renameTo = null,
        ?string $propName = null,
    ): static {
        return new static(
            in: $in,
            out: $out,
            default: $default,
            renameFrom: $renameFrom,
            renameTo: $renameTo,
            propName: $propName,
        );
    }
}
