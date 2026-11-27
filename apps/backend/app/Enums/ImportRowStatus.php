<?php

namespace App\Enums;

enum ImportRowStatus: string
{
    case Created = 'created';
    case Skipped = 'skipped';
    case Failed = 'failed';
}
