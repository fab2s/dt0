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
use fab2s\Dt0\Exception\AttributeException;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Cast
{
    public readonly bool $hasDefault;
    public readonly ?CasterInterface $in;
    public readonly ?CasterInterface $out;

    /**
     * @throws AttributeException
     */
    public function __construct(
        CasterInterface|string|null $in = null,
        CasterInterface|string|null $out = null,
        public readonly mixed $default = Dt0::DT0_NIL,
        public readonly string|array|null $renameFrom = null,
        public readonly ?string $renameTo = null,
        public readonly ?string $propName = null,
    ) {

        foreach (['in', 'out'] as $case) {
            $arg         = $$case;
            $this->$case = match (true) {
                $arg instanceof CasterInterface              => $arg,
                is_subclass_of($arg, CasterInterface::class) => new $arg,
                $arg === null                                => null,
                default                                      => throw new AttributeException("[Cast] $case Cast must implement CasterInterface"),
            };
        }

        $this->hasDefault = $this->default !== Dt0::DT0_NIL;
    }
}
