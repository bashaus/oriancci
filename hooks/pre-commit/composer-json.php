<?php

$root = `git rev-parse --show-toplevel`;
$file = realpath(trim($root) . '/composer.json');

if (!$file) {
    echo "Could not open composer.json for interpreting." . PHP_EOL;
    exit(1);
}

$read = file_get_contents($file);
$json = json_decode($read);

if (is_null($json)) {
    echo "The JSON file composer.json is malformed." . PHP_EOL;
    exit(1);
}

exit(0);