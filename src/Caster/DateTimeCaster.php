<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Caster;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;

class DateTimeCaster implements CasterInterface
{
    use DateTimeTrait;

    /**
     * @throws Exception
     */
    public function __construct(
        DateTimeZone|string|null $timeZone = null,
        public readonly bool $immutable = true,
    ) {
        $this->timeZone      = $timeZone instanceof DateTimeZone ? $timeZone : ($timeZone ? new DateTimeZone($timeZone) : null);
        $this->dateTimeClass = $immutable ? DateTimeImmutable::class : DateTime::class;
    }

    /**
     * @throws Exception
     */
    public function cast(mixed $value): ?DateTimeInterface
    {
        return $this->resolve($value);
    }
}
