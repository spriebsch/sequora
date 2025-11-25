<?php declare(strict_types=1);

namespace spriebsch\sequora;

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

        BinaryUUID::from(random_bytes(15));
    }
}
