<?php

/*
 * This file is part of fab2s/Dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/Dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0;

enum Format: string
{
    case JSON            = 'json';
    case JSON_SERIALISED = 'json_serialized';
    case ARRAY           = 'array';
    case STRING          = 'string';
}
