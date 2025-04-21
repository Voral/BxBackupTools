<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Enums;

enum DataSizeUnit: string
{
    case B = 'B';
    case KiB = 'KiB';
    case MiB = 'MiB';
    case GiB = 'GiB';
    case TiB = 'TiB';

    public function convert(float|int $bytes): float|int
    {
        return match ($this) {
            self::B => $bytes,
            self::KiB => $bytes / 1024,
            self::MiB => $bytes / (1024 ** 2),
            self::GiB => $bytes / (1024 ** 3),
            self::TiB => $bytes / (1024 ** 4),
        };
    }
}
