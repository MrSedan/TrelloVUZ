<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Log;


$logger = Log::logger();

// Логируем каждый входящий запрос: метод, URI, статус и тело ответа
$logRequest = static function (int $status, string $body) use ($logger): void {
  $logger->info('request', [
    'method' => $_SERVER['REQUEST_METHOD'] ?? '',
    'uri' => $_SERVER['REQUEST_URI'] ?? '',
    'status' => $status,
    'body' => $body,
  ]);
};

ob_start();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Index page</title>
  <link rel='stylesheet' href="/assets/index.css" />
  <script src='/assets/index.js'></script>
</head>

<body>
  <main>
    <h1>Counter: <span id='counter'>0</span></h1>

    <button class='btn'>Click</button>
  </main>
</body>

</html>
<?php

$body = ob_get_clean();

$logRequest(200, $body);

echo $body;
