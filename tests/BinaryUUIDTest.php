<?php declare(strict_types=1);

namespace spriebsch\sequora;

/**
 * @param string $format
 * @param string $string
 * @param int $offset
 * @return array<mixed>|false
 */
function unpack(string $format, string $string, int $offset = 0): array|false
{
    if (isset($GLOBALS['mock_unpack_fail']) && $GLOBALS['mock_unpack_fail'] === true) {
        return false;
    }

    return \unpack($format, $string, $offset);
}

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use spriebsch\uuid\UUIDv4;

#[CoversClass(BinaryUUID::class)]
final class BinaryUUIDTest extends TestCase
{
    public function test_round_trip_converts_back_to_original_uuid_string(): void
    {
        $uuid = UUIDv4::from('51523a51-1441-409b-8181-e444fe651127');

        $binary = BinaryUUID::toBinary($uuid);
        $roundTrip = BinaryUUID::from($binary);

        $this->assertSame($uuid->asString(), $roundTrip);
    }

    public function test_from_throws_exception_when_binary_length_is_not_16_bytes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Binary UUID must be 16 bytes');

        BinaryUUID::from(random_bytes(15));
    }

    public function test_from_throws_exception_when_unpack_fails(): void
    {
        $GLOBALS['mock_unpack_fail'] = true;

        try {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('Failed to unpack binary UUID');

            BinaryUUID::from(random_bytes(16));
        } finally {
            unset($GLOBALS['mock_unpack_fail']);
        }
    }
}
