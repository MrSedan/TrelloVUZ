<?php

declare(strict_types=1);

namespace App;

use App\Security\Encrypter;
use RuntimeException;

/**
 * Конфигурация приложения для выбранного окружения.
 *
 * Окружение берётся из APP_ENV, файл конфигурации — app/config/<env>.php.
 * Encrypter создаётся из APP_KEY (base64) и используется методом getDecrypted().
 */
final class Config
{
  private array $items;

  private Encrypter $encrypter;

  public function __construct(?Encrypter $encrypter = null)
  {
    $env = getenv('APP_ENV') ?: 'dev';
    $path = __DIR__ . '/../config/' . $env . '.php';

    if (!is_file($path)) {
      throw new RuntimeException(sprintf('Config file not found for environment "%s".', $env));
    }

    $items = require $path;

    if (!is_array($items)) {
      throw new RuntimeException(sprintf('Config file "%s" must return an array.', $path));
    }

    $this->items = $items;
    $this->encrypter = $encrypter ?? new Encrypter(self::key());
  }

  public function encrypter(): Encrypter
  {
    return $this->encrypter;
  }

  public function get(string $key, mixed $default = null): mixed
  {
    return $this->items[$key] ?? $default;
  }

  public function getDecrypted(string $key, mixed $default = null): string
  {
    if (!array_key_exists($key, $this->items)) {
      if (!is_string($default)) {
        throw new RuntimeException(sprintf('Config value "%s" is missing and the default is not a string.', $key));
      }

      return $default;
    }

    $value = $this->items[$key];

    if (!is_string($value)) {
      throw new RuntimeException(sprintf('Config value "%s" is not a string.', $key));
    }

    return $this->encrypter->decryptString($value);
  }

  private static function key(): string
  {
    $encoded = getenv('APP_KEY') ?: '';
    $key = base64_decode($encoded, true);

    if ($key === false) {
      throw new RuntimeException('APP_KEY is not valid base64.');
    }

    return $key;
  }
}
