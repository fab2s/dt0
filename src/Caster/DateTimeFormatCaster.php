<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Caster;

use DateTimeImmutable;
use DateTimeZone;
use Exception;

class DateTimeFormatCaster implements CasterInterface
{
    use DateTimeTrait;
    public const ISO = 'Y-m-d\TH:i:s.u\Z';

    /**
     * @throws Exception
     */
    public function __construct(
        public readonly string $format,
        DateTimeZone|string|null $timeZone = null,
    ) {
        $this->timeZone      = $timeZone instanceof DateTimeZone ? $timeZone : ($timeZone ? new DateTimeZone($timeZone) : null);
        $this->dateTimeClass = DateTimeImmutable::class;
    }

    /**
     * @throws Exception
     */
    public function cast(mixed $value): ?string
    {
        return $this->resolve($value)?->format($this->format);
    }
}
