<?php declare(strict_types=1);

namespace spriebsch\sequora;

use InvalidArgumentException;
use RuntimeException;
use spriebsch\uuid\UUID as UUID;

final readonly class BinaryUUID
{
    public static function toBinary(UUID $uuid): string
    {
        $hex = str_replace('-', '', strtolower($uuid->asString()));

        $bin = pack('H*', $hex);

        if ($bin === false || strlen($bin) !== 16) {
            throw new RuntimeException('Failed to convert UUID to binary');
        }

        return $bin;
    }

    public static function from(string $binary): string
    {
        if (strlen($binary) !== 16) {
            throw new InvalidArgumentException('Binary UUID must be 16 bytes');
        }

        $hex = unpack('H*', $binary)[1];

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }
}
