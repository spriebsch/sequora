<?php declare(strict_types=1);

namespace spriebsch\sequora;

use RuntimeException;

require __DIR__ . '/bootstrap.php';

if (!isset($argv[1])) {
    throw new RuntimeException('Please provide a directory as first argument.');
}

$directory = realpath($argv[1]);

if ($directory === false) {
    throw new RuntimeException('Please provide an existing directory as first argument.');
}

if (!str_starts_with($directory, __DIR__)) {
    $directory = __DIR__ . '/../' . $argv[1];
}

GenerateTopicMap::for($directory);
