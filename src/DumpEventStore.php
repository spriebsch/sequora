<?php declare(strict_types=1);

namespace spriebsch\sequora;

use spriebsch\sqlite\SqliteConnection;

final readonly class DumpEventStore
{
    /** @codeCoverageIgnore */
    private function __construct()
    {
    }

    public static function dump(SqliteConnection $connection): void
    {
        try {
            $statement = $connection->prepare('SELECT * FROM `sequora-events`');
        } catch (\SQLite3Exception) {
            return;
        }
        $result = $statement->execute();

        $rows = [];
        $headers = [];
        $maxLengths = [];

        if ($result === false) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        for ($i = 0; $i < $result->numColumns(); $i++) {
            $headers[] = $result->columnName($i);
        }

        foreach ($headers as $index => $header) {
            $maxLengths[$index] = strlen((string) $header);
        }

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            /** @var array<string, string|int|float|null> $row */
            $rows[] = $row;
            foreach ($headers as $index => $header) {
                $value = (string) ($row[$header] ?? '');
                $maxLengths[$index] = max($maxLengths[$index], strlen($value));
            }
        }

        print '+' . str_repeat('-', array_sum($maxLengths) + count($headers) * 3 - 1) . '+' . PHP_EOL;

        print '|';
        foreach ($headers as $i => $header) {
            printf(" %-*s |", $maxLengths[$i], $header);
        }
        print PHP_EOL;

        print '+' . implode('+', array_map(fn($len) => str_repeat('-', $len + 2), $maxLengths)) . '+' . PHP_EOL;

        foreach ($rows as $row) {
            /** @var array<string, string|int|float|null> $row */
            print '|';
            foreach ($headers as $i => $header) {
                $value = (string) ($row[$header] ?? '');
                printf(" %-*s |", $maxLengths[$i], substr($value, 0, $maxLengths[$i]));
            }
            print PHP_EOL;
        }

        print '+' . str_repeat('-', array_sum($maxLengths) + count($headers) * 3 - 1) . '+' . PHP_EOL;
    }
}
