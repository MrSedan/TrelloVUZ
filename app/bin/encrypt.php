<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Config;

$text = $argv[1] ?? '';

if ($text === '') {
  fwrite(STDERR, "usage: php bin/encrypt.php <text>" . PHP_EOL);
  exit(1);
}

$config = new Config();

echo $config->encrypter()->encryptString($text) . PHP_EOL;
