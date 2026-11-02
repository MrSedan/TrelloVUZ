<?php

declare(strict_types=1);

require_once __DIR__ . '/../init.php';

use App\Library\Super;

$text = $argv[1] ?? '';

if ($text === '') {
  fwrite(STDERR, "usage: php src/console/config-encrypt.php <text>" . PHP_EOL);
  exit(1);
}

$config = Super::config();

echo $config->encrypter()->encryptString($text) . PHP_EOL;
