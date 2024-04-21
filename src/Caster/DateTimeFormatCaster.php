<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Caster;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use InvalidArgumentException;

class DateTimeFormatCaster implements CasterInterface
{
    public readonly ?DateTimeZone $timeZone;
    protected readonly DateTimeInterface|string $dateTimeClass;

    /**
     * @throws Exception
     */
    public function __construct(
        public readonly string $format,
        DateTimeZone|string|null $timeZone = null,
        public readonly bool $nullable = true,
    ) {
        $this->timeZone = $timeZone instanceof DateTimeZone ? $timeZone : ($timeZone ? new DateTimeZone($timeZone) : null);
    }

    /**
     * @throws Exception
     */
    public function cast(mixed $value): ?string
    {
        $instance = match (true) {
            $value instanceof DateTimeInterface => DateTimeImmutable::createFromInterface($value),
            $this->nullable && $value === null  => null,
            is_array($value)                    => $this->fromArray($value),
            is_string($value)                   => new DateTimeImmutable($value),
            is_int($value)                      => (new DateTimeImmutable)->setTimestamp($value),
            default                             => throw new InvalidArgumentException('Unsupported type'),
        };

        if ($instance) {
            if ($this->timeZone) {
                $instance = $instance->setTimezone($this->timeZone);
            }

            return $instance->format($this->format);
        }

        if (! $this->nullable) {
            throw new InvalidArgumentException('Value is not a DateTime');
        }

        return null;

    }

    protected function fromArray(array $date): ?DateTimeInterface
    {
        $result = null;
        if (! empty($date['date'])) {
            $result = new $this->dateTimeClass($date['date']);
            if (! empty($date['timezone'])) {
                $result->setTimeZone($date['timezone'] instanceof DateTimeZone ? $date['timezone'] : new DateTimeZone($date['timezone']));
            }
        }

        if (! $this->nullable && $result === null) {
            throw new InvalidArgumentException('This Date is not nullable');
        }

        return $result;
    }
}
