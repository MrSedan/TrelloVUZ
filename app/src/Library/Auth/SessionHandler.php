<?php

declare(strict_types=1);

namespace App\Library\Auth;

use Illuminate\Database\ConnectionInterface;
use SessionHandlerInterface;
use SessionUpdateTimestampHandlerInterface;

final class SessionHandler implements SessionHandlerInterface, SessionUpdateTimestampHandlerInterface
{
  private ?string $lockedId = null;

  public function __construct(
    private ConnectionInterface $connection,
    private int $lifetime = 1440,
  ) {}

  public function open(string $path, string $name): bool
  {
    return true;
  }

  public function close(): bool
  {
    if ($this->lockedId !== null) {
      $this->connection->select('SELECT pg_advisory_unlock(hashtextextended(?, 0))', ['session:' . $this->lockedId]);
      $this->lockedId = null;
    }

    return true;
  }

  public function read(string $id): string|false
  {
    $this->lock($id);
    $payload = $this->connection->table('sessions')
      ->where('id', $id)
      ->where('expires_at', '>', time())
      ->value('payload');

    return $payload === null ? '' : base64_decode($payload, true);
  }

  public function write(string $id, string $data): bool
  {
    $this->lock($id);
    $this->connection->table('sessions')->upsert([
      'id' => $id,
      'payload' => base64_encode($data),
      'expires_at' => time() + $this->lifetime,
    ], ['id'], ['payload', 'expires_at']);

    return true;
  }

  public function destroy(string $id): bool
  {
    $this->lock($id);
    $this->connection->table('sessions')->where('id', $id)->delete();

    return true;
  }

  public function gc(int $max_lifetime): int|false
  {
    return $this->connection->table('sessions')->where('expires_at', '<=', time())->delete();
  }

  public function validateId(string $id): bool
  {
    $this->lock($id);

    return $this->connection->table('sessions')
      ->where('id', $id)
      ->where('expires_at', '>', time())
      ->exists();
  }

  public function updateTimestamp(string $id, string $data): bool
  {
    return $this->write($id, $data);
  }

  private function lock(string $id): void
  {
    if ($this->lockedId === $id) {
      return;
    }

    $this->close();
    $this->connection->select('SELECT pg_advisory_lock(hashtextextended(?, 0))', ['session:' . $id]);
    $this->lockedId = $id;
  }
}
