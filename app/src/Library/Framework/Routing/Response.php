<?php

declare(strict_types=1);

namespace App\Library\Framework\Routing;

final class Response
{
  public function __construct(
    public readonly string $body = '',
    public readonly int $status = 200,
    public readonly array $headers = ['Content-Type' => 'text/html; charset=utf-8'],
  ) {}

  public function send(bool $head = false): void
  {
    http_response_code($this->status);

    foreach ($this->headers as $name => $value) {
      header($name . ': ' . $value);
    }

    if (!$head) {
      echo $this->body;
    }
  }
}
