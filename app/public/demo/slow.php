<?php

declare(strict_types=1);

/**
 * Демо для лекции: медленный запрос к БД.
 *
 *   /demo/slow.php?seconds=2
 *
 * Тормозит именно PostgreSQL (pg_sleep), а не PHP (sleep) — так один запрос виден
 * сразу в трёх местах: в latency приложения (http_request_duration_seconds),
 * в slow-log PostgreSQL (log_min_duration_statement = 500 мс) и в pg_stat_statements.
 * Пока запрос висит, он держит воркер php-fpm и соединение с БД — пачка таких
 * запросов исчерпает пул.
 */

require __DIR__ . '/../index.php';
