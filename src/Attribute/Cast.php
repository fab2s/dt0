<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Attribute;

use Attribute;
use fab2s\Dt0\Caster\CasterCollection;
use fab2s\Dt0\Caster\CasterInterface;
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Exception\AttributeException;
use fab2s\Dt0\Exception\CasterException;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Cast extends CastAbstract
{
    public readonly bool $hasDefault;
    public readonly ?CasterInterface $in;
    public readonly ?CasterInterface $out;
    public readonly ?CasterInterface $both;

    /**
     * @throws AttributeException
     * @throws CasterException
     */
    public function __construct(
        CasterInterface|string|null $in = null,
        CasterInterface|string|null $out = null,
        CasterInterface|string|null $both = null,
        public readonly mixed $default = Dt0::DT0_NIL,
        public readonly string|array|null $renameFrom = null,
        public readonly ?string $renameTo = null,
        ?string $propName = null,
    ) {
        $this->both = $this->getCasterInstance($both);
        $inCaster   = $this->getCasterInstance($in);
        $outCaster  = $this->getCasterInstance($out);

        $this->in = $this->both && $inCaster
            ? CasterCollection::make(both: $this->both, in: $inCaster)
            : ($this->both ?? $inCaster);
        $this->out = $this->both && $outCaster
            ? CasterCollection::make(out: $outCaster, both: $this->both)
            : ($this->both ?? $outCaster);

        $this->hasDefault = $this->default !== Dt0::DT0_NIL;

        if ($propName !== null) {
            $this->setPropName($propName);
        }
    }
}
