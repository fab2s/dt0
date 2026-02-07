<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Caster;

use DateInvalidTimeZoneException;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use fab2s\Dt0\Dt0;

class DateTimeFormatCaster extends CasterAbstract
{
    use DateTimeTrait;
    public const ISO = 'Y-m-d\TH:i:s.u\Z';

    /**
     * @throws DateInvalidTimeZoneException
     */
    public function __construct(
        public readonly string $format,
        DateTimeZone|string|null $timeZone = null,
    ) {
        $this->timeZone      = $timeZone instanceof DateTimeZone ? $timeZone : ($timeZone ? new DateTimeZone($timeZone) : null);
        $this->dateTimeClass = DateTimeImmutable::class;
    }

    /**
     * @throws DateInvalidTimeZoneException
     */
    public static function make(
        string $format,
        DateTimeZone|string|null $timeZone = null,
    ): static {
        return new static($format, $timeZone);
    }

    /**
     * @throws Exception
     */
    public function cast(mixed $value, array|Dt0|null $data = null): ?string
    {
        return $this->resolve($value)?->format($this->format);
    }
}
