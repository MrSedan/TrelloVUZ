<?php

declare(strict_types=1);

require_once __DIR__ . '/../init.php';

use App\Library\Super;

$payload = $argv[1] ?? '';

if ($payload === '') {
  fwrite(STDERR, "usage: php src/console/config-decrypt.php <encrypted>" . PHP_EOL);
  exit(1);
}

$config = Super::config();

echo $config->encrypter()->decryptString($payload) . PHP_EOL;
