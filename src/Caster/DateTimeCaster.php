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
use InvalidArgumentException;

class DateTimeCaster implements CasterInterface
{
    public readonly ?DateTimeZone $timeZone;
    protected readonly DateTimeInterface|string $dateTimeClass;

    /**
     * @throws Exception
     */
    public function __construct(
        DateTimeZone|string|null $timeZone = 'UTC',
        public readonly bool $immutable = true,
        public readonly bool $nullable = true,
    ) {
        $this->timeZone      = $timeZone instanceof DateTimeZone ? $timeZone : ($timeZone ? new DateTimeZone($timeZone) : null);
        $this->dateTimeClass = $immutable ? DateTimeImmutable::class : DateTime::class;
    }

    /**
     * @throws Exception
     */
    public function cast(mixed $value): ?DateTimeInterface
    {
        $instance = match (true) {
            $value instanceof DateTimeInterface => $this->dateTimeClass::createFromInterface($value),
            $this->nullable && $value === null  => null,
            is_array($value)                    => $this->fromArray($value),
            is_string($value)                   => new $this->dateTimeClass($value),
            is_int($value)                      => (new $this->dateTimeClass)->setTimestamp($value),
            default                             => throw new InvalidArgumentException('Unsupported type'),
        };

        if ($instance && $this->timeZone) {
            $instance = $instance->setTimezone($this->timeZone);
        }

        return $instance;
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
