<?php

declare(strict_types=1);

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

trait DateTimeTrait
{
    public readonly ?DateTimeZone $timeZone;

    /** @var class-string<DateTime|DateTimeImmutable> */
    protected readonly string $dateTimeClass;

    /**
     * @throws Exception
     */
    public function resolve(mixed $value): ?DateTimeInterface
    {
        $instance = match (true) {
            $value instanceof DateTimeInterface => $this->dateTimeClass::createFromInterface($value),
            is_string($value)                   => new $this->dateTimeClass($value),
            is_array($value)                    => $this->fromArray($value), // @phpstan-ignore argument.type
            is_int($value)                      => (new $this->dateTimeClass)->setTimestamp($value),
            default                             => null,
        };

        if ($instance && $this->timeZone) {
            $instance = $instance->setTimezone($this->timeZone); // @phpstan-ignore method.notFound
        }

        return $instance;
    }

    /**
     * @param array<string, mixed> $date
     *
     * @throws Exception
     */
    public function fromArray(array $date): ?DateTimeInterface
    {
        $result = null;
        if (! empty($date['date'])) {
            $result = new $this->dateTimeClass((string) $date['date']); // @phpstan-ignore cast.string
            if (! empty($date['timezone'])) {
                $result->setTimeZone($date['timezone'] instanceof DateTimeZone ? $date['timezone'] : new DateTimeZone((string) $date['timezone'])); // @phpstan-ignore cast.string
            }
        }

        return $result;
    }
}
