<?php

namespace App\Enums;

enum ExceptionStatus: string
{
    case Open = 'OPEN';
    case Investigating = 'INVESTIGATING';
    case Resolved = 'RESOLVED';

    public function allowedNext(): array
    {
        return match ($this) {
            self::Open => [self::Investigating, self::Resolved],
            self::Investigating => [self::Resolved],
            self::Resolved => [], // status akhir
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedNext(), true);
    }
}