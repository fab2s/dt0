<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Caster;

use DateInvalidTimeZoneException;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use fab2s\Dt0\Dt0;

class DateTimeCaster extends CasterAbstract
{
    use DateTimeTrait;

    /**
     * @throws DateInvalidTimeZoneException
     */
    public function __construct(
        DateTimeZone|string|null $timeZone = null,
        public readonly bool $immutable = true,
    ) {
        $this->timeZone      = $timeZone instanceof DateTimeZone ? $timeZone : ($timeZone ? new DateTimeZone($timeZone) : null);
        $this->dateTimeClass = $immutable ? DateTimeImmutable::class : DateTime::class;
    }

    /**
     * @throws DateInvalidTimeZoneException
     */
    public static function make(
        DateTimeZone|string|null $timeZone = null,
        bool $immutable = true,
    ): static {
        return new static($timeZone, $immutable);
    }

    /**
     * @throws Exception
     */
    public function cast(mixed $value, array|Dt0|null $data = null): DateTime|DateTimeImmutable|null
    {
        return $this->resolve($value);
    }
}
