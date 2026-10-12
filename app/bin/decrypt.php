<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Config;

$payload = $argv[1] ?? '';

if ($payload === '') {
  fwrite(STDERR, "usage: php bin/decrypt.php <encrypted>" . PHP_EOL);
  exit(1);
}

$config = new Config();

echo $config->encrypter()->decryptString($payload) . PHP_EOL;
