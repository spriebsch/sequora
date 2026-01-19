<?php declare(strict_types=1);

namespace spriebsch\sequora;

use spriebsch\sqlite\SqliteConnection;

class DumpEventStore
{
    public static function dump(SqliteConnection $connection): void
    {
        $statement = $connection->prepare('SELECT * FROM `sequora-events`');
        $result = $statement->execute();

        $rows = [];
        $headers = [];
        $maxLengths = [];

        if ($result) {
            for ($i = 0; $i < $result->numColumns(); $i++) {
                $headers[] = $result->columnName($i);
            }

            foreach ($headers as $index => $header) {
                $maxLengths[$index] = strlen($header);
            }

            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $rows[] = $row;
                foreach ($headers as $index => $header) {
                    $value = $row[$header] ?? '';
                    $maxLengths[$index] = max($maxLengths[$index], strlen((string) $value));
                }
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
            print '|';
            foreach ($headers as $i => $header) {
                $value = $row[$header] ?? '';
                printf(" %-*s |", $maxLengths[$i], substr((string) $value, 0, $maxLengths[$i]));
            }
            print PHP_EOL;
        }

        print '+' . str_repeat('-', array_sum($maxLengths) + count($headers) * 3 - 1) . '+' . PHP_EOL;
    }
}
