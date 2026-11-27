<?php

namespace App\Enums;

enum SessionEndReason: string
{
    case Logout = 'logout';
    case Expired = 'expired';
    case Revoked = 'revoked';
}
