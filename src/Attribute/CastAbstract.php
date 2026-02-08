<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Attribute;

use Attribute;
use fab2s\Dt0\Caster\CasterInterface;
use fab2s\Dt0\Concern\HasCasterInstance;
use fab2s\Dt0\Concern\HasDeclaringFqn;
use fab2s\Dt0\Concern\HasPropName;

#[Attribute(Attribute::TARGET_PROPERTY)]
abstract class CastAbstract implements CastInterface
{
    use HasCasterInstance;
    use HasDeclaringFqn;
    use HasPropName;
    public readonly mixed $default;

    /** @var string|array<string>|null */
    public readonly string|array|null $renameFrom;
    public readonly ?string $renameTo;
    public readonly bool $hasDefault;
    public readonly ?CasterInterface $in;
    public readonly ?CasterInterface $out;
    public readonly ?CasterInterface $both;
}
