<?php

declare(strict_types=1);

namespace PHPinnacle\NSQ;

final class Commands
{
    public const
        IDENTIFY  = '  V2IDENTIFY',
        NOP       = 'NOP',
        CLS       = 'CLS',
        PUB       = 'PUB',
        MPUB      = 'MPUB',
        DPUB      = 'DPUB',
        SUB       = 'SUB',
        RDY       = 'RDY',
        FIN       = 'FIN',
        REQ       = 'REQ',
        TOUCH     = 'TOUCH'
    ;
}
