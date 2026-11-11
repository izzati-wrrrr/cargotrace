<?php

namespace App\Enums;

enum DelaySeverity: string
{
    case None = 'NONE';
    case Low = 'LOW';
    case Medium = 'MEDIUM';
    case High = 'HIGH';
}