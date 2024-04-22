<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Caster;

use DateTimeInterface;
use DateTimeZone;
use Exception;

trait DateTimeTrait
{
    public readonly ?DateTimeZone $timeZone;

    /**
     * @var class-string<DateTimeInterface>
     */
    protected readonly string $dateTimeClass;

    /**
     * @throws Exception
     */
    public function resolve(mixed $value): ?DateTimeInterface
    {
        $instance = match (true) {
            $value instanceof DateTimeInterface => $this->dateTimeClass::createFromInterface($value),
            is_string($value)                   => new $this->dateTimeClass($value),
            is_array($value)                    => $this->fromArray($value),
            is_int($value)                      => (new $this->dateTimeClass)->setTimestamp($value),
            default                             => null,
        };

        if ($instance && $this->timeZone) {
            $instance = $instance->setTimezone($this->timeZone);
        }

        return $instance;
    }

    /**
     * @throws Exception
     */
    public function fromArray(array $date): ?DateTimeInterface
    {
        $result = null;
        if (! empty($date['date'])) {
            $result = new $this->dateTimeClass($date['date']);
            if (! empty($date['timezone'])) {
                $result->setTimeZone($date['timezone'] instanceof DateTimeZone ? $date['timezone'] : new DateTimeZone($date['timezone']));
            }
        }

        return $result;
    }
}
