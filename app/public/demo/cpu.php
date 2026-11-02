<?php

declare(strict_types=1);

/**
 * Демо для лекции: нагрузка на CPU воркера.
 *
 *   /demo/cpu.php?ms=300
 *
 * В отличие от slow.php, здесь воркер php-fpm не ждёт, а реально жжёт процессор.
 * Под параллельной нагрузкой все pm.max_children (5 в www.conf) быстро заняты,
 * новые запросы встают в очередь (listen queue) — растут p99 и phpfpm_max_children_reached.
 */

require __DIR__ . '/../index.php';
