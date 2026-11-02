<?php

declare(strict_types=1);

namespace App\Library\Framework;

use Prometheus\CollectorRegistry;
use Prometheus\Storage\APCng;

/**
 * Единая точка доступа к метрикам приложения.
 *
 * Метрики включаются только вместе со стеком мониторинга (APP_METRICS_ENABLED=1
 * задаётся в compose.monitoring.yaml). В базовом окружении все методы — no-op,
 * поэтому код приложения (например, index.php) можно инструментировать, не думая,
 * запущен ли сейчас Prometheus.
 */
final class Metrics
{
  private static ?CollectorRegistry $registry = null;

  public static function enabled(): bool
  {
    return getenv('APP_METRICS_ENABLED') === '1'
      && extension_loaded('apcu')
      && apcu_enabled();
  }

  public static function registry(): CollectorRegistry
  {
    return self::$registry ??= new CollectorRegistry(new APCng(decimalPrecision: 6), false);
  }

  /**
   * Бизнес-метрика: сколько записей создали пользователи.
   * Технические метрики (RPS, ошибки, latency) говорят, жив ли сервис; бизнес-метрики —
   * делает ли он то, ради чего существует. «Ошибок нет, но записи перестали создаваться» —
   * поломка, которую видно только так.
   */
  public static function noteCreated(): void
  {
    self::safely(static function (): void {
      self::registry()
        ->getOrRegisterCounter('app', 'notes_created_total', 'Количество созданных записей')
        ->inc();
    });
  }

  public static function safely(callable $fn): void
  {
    if (!self::enabled()) {
      return;
    }

    try {
      $fn();
    } catch (\Throwable $e) {
      error_log('metrics: ' . $e->getMessage());
    }
  }
}
