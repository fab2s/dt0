<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Caster;

enum ScalarType: string
{
    case boolean  = 'boolean';
    case bool     = 'bool';
    case integer  = 'integer';
    case int      = 'int';
    case float    = 'float';
    case double   = 'double';
    case string   = 'string';
    case null     = 'null';
    case resource = 'resource';
}
