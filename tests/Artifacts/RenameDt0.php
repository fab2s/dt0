<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Tests\Artifacts;

use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Dt0;

class RenameDt0 extends Dt0
{
    #[Cast(renameFrom: ['input', 'anotherInput'])]
    public readonly string $renamedFrom;

    #[Cast(renameTo: 'output')]
    public readonly string $renamedTo;

    #[Cast(renameFrom: 'inputCombo', renameTo: 'outputCombo')]
    public readonly string $combo;
}
