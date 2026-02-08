<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Caster;

use fab2s\Dt0\Dt0;
use fab2s\Dt0\Exception\CasterException;
use fab2s\Dt0\Exception\Dt0Exception;
use JsonException;
use ReflectionException;

class Dt0Caster extends CasterAbstract
{
    /** @var class-string<Dt0> */
    public readonly string $dt0Fqn;

    /**
     * @throws Dt0Exception
     * @throws CasterException
     */
    public function __construct(
        string $dt0Fqn,
    ) {
        if (! is_subclass_of($dt0Fqn, Dt0::class)) {
            throw new CasterException("$dt0Fqn does not extends " . Dt0::class);
        }

        $this->dt0Fqn = $dt0Fqn;
    }

    /**
     * @throws Dt0Exception
     * @throws CasterException
     */
    public static function make(
        string $dt0Fqn,
    ): static {
        return new static($dt0Fqn);
    }

    /**
     * @param array<string, mixed>|Dt0|null $data
     *
     * @throws Dt0Exception
     * @throws JsonException
     * @throws ReflectionException
     */
    public function cast(mixed $value, array|Dt0|null $data = null): ?Dt0
    {
        return $this->dt0Fqn::from($value);
    }
}
