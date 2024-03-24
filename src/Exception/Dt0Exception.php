<?php

/*
 * This file is part of fab2s/Dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/Dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Exception;

use Exception;
use Throwable;

class Dt0Exception extends Exception
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $message = implode(
            '',
            [
                '[',
                basename(
                    str_replace(
                        ['\\', 'Exception'],
                        ['/', ''],
                        static::class,
                    ),
                ),
                '] ',
                $message,
            ],
        );

        parent::__construct($message, $code, $previous);
    }
}
