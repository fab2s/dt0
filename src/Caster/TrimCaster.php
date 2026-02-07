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

class TrimCaster extends CasterAbstract
{
    public const DEFAULT_CHARS = " \n\r\t\v\0";
    public readonly string $characters;
    public readonly string $trimFunction;

    public function __construct(
        TrimType $trimType = TrimType::BOTH,
        ?string $characters = self::DEFAULT_CHARS,
    ) {
        $this->characters   = $characters ?? self::DEFAULT_CHARS;
        $this->trimFunction = $trimType->value;
    }

    public static function make(
        TrimType $trimType = TrimType::BOTH,
        ?string $characters = self::DEFAULT_CHARS,
    ): static {
        return new static($trimType, $characters);
    }

    /** @param array<string, mixed>|Dt0|null $data */
    public function cast(mixed $value, Dt0|array|null $data = null): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        return ($this->trimFunction)($value, $this->characters);
    }
}
