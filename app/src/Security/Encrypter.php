<?php

declare(strict_types=1);

namespace App\Security;

use InvalidArgumentException;
use RuntimeException;

/**
 * Симметричное шифрование строк алгоритмом AES-256-GCM.
 *
 * Ключ передаётся в конструктор в бинарном виде (32 байта).
 * Полезная нагрузка кодируется в base64 и содержит IV, тег аутентификации
 * и шифротекст: base64(IV || TAG || CIPHERTEXT).
 */
final class Encrypter
{
  private const CIPHER = 'aes-256-gcm';
  private const TAG_LENGTH = 16;

  public function __construct(private readonly string $key)
  {
    if (strlen($key) !== 32) {
      throw new InvalidArgumentException('Encrypter key must be 32 bytes for AES-256-GCM.');
    }
  }

  public function encryptString(string $plainText): string
  {
    $ivLength = openssl_cipher_iv_length(self::CIPHER);
    $iv = random_bytes($ivLength);
    $tag = '';

    $cipherText = openssl_encrypt(
      $plainText,
      self::CIPHER,
      $this->key,
      OPENSSL_RAW_DATA,
      $iv,
      $tag,
      '',
      self::TAG_LENGTH,
    );

    if ($cipherText === false) {
      throw new RuntimeException('Encryption failed.');
    }

    return base64_encode($iv . $tag . $cipherText);
  }

  public function decryptString(string $payload): string
  {
    $raw = base64_decode($payload, true);

    if ($raw === false) {
      throw new RuntimeException('Payload is not valid base64.');
    }

    $ivLength = openssl_cipher_iv_length(self::CIPHER);

    if (strlen($raw) < $ivLength + self::TAG_LENGTH) {
      throw new RuntimeException('Payload is too short.');
    }

    $iv = substr($raw, 0, $ivLength);
    $tag = substr($raw, $ivLength, self::TAG_LENGTH);
    $cipherText = substr($raw, $ivLength + self::TAG_LENGTH);

    $plainText = openssl_decrypt(
      $cipherText,
      self::CIPHER,
      $this->key,
      OPENSSL_RAW_DATA,
      $iv,
      $tag,
    );

    if ($plainText === false) {
      throw new RuntimeException('Decryption failed.');
    }

    return $plainText;
  }
}
