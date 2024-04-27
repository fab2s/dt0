<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Exception;

use fab2s\ContextException\ContextException;
use fab2s\Dt0\Dt0;
use Throwable;

class Dt0Exception extends ContextException
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null, array $context = [])
    {
        $message = implode(
            '',
            [
                '[',
                str_replace(
                    'Exception',
                    '',
                    Dt0::classBasename(static::class),
                ),
                ']',
                str_starts_with($message, '[') ? '' : ' ',
                $message,
            ],
        );

        parent::__construct($message, $code, $previous, $context);
    }
}
