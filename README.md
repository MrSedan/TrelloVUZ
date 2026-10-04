# VyatSU Trello Project

Учебный аналог Trello: nginx + php-fpm + PostgreSQL в Docker.
Код на чистом PHP, ORM — Eloquent (illuminate/database), логи — Monolog.

## Что где лежит

| Путь | Что это |
|------|---------|
| `docker-compose.yml` | три сервиса: nginx, php, postgres |
| `docker/nginx/` | конфиг nginx |
| `docker/php/` | Dockerfile, `php.ini`, `www.conf` |
| `docker/postgres/` | Dockerfile и `postgresql.conf` |
| `app/public/index.php` | главная страница: список колонок, форма добавления |
| `app/public/health.php` | проверка здоровья: php + БД (200/503) |
| `app/src/Database.php` | подключение к БД через Eloquent Capsule |
| `app/src/Log.php` | логгер Monolog: stdout + файл в `app/var/log/` |
| `app/src/Model/BoardColumn.php` | модель таблицы `columns` |
| `app/database/migrations/` | миграции |
| `app/bin/migrate.php` | запуск миграций (migrate / rollback) |
| `Makefile` | короткие команды |
| `.env.example` | образец настроек (порты, БД, UID/GID) |

## Как запускать

```bash
make init      # создать .env из .env.example
make build     # собрать образы
make up        # поднять контейнеры
make composer  # установить PHP-зависимости (composer install)
make migrate   # применить миграции
```

Сайт: <http://localhost:8080>

## Другие команды

```bash
make ps        # статус контейнеров
make logs      # логи всех сервисов
make sh        # shell в php-контейнере
make psql      # консоль psql к postgres
make down      # остановить и удалить контейнеры
make clean     # down -v: удалить и данные (volume pgdata)
```

## Логи

Каждый запрос пишется в лог с методом, URI, кодом ответа и телом ответа.
Поток — в stdout контейнера php, файл — в `app/var/log/app-ГГГГ-ММ-ДД.log`.
