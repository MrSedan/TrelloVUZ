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
| `app/public/health.php` | проверка доступности: php + БД (200/503) |
| `app/src/Database.php` | подключение к БД через Eloquent Capsule |
| `app/src/Log.php` | логгер Monolog: stdout + файл в `app/var/log/` |
| `app/src/Config.php` | конфигурация: читает `APP_ENV`, отдаёт настройки и секреты |
| `app/src/Security/Encrypter.php` | шифрование AES-256-GCM |
| `app/src/Model/User.php` | модель таблицы `users` |
| `app/src/Model/Board.php` | модель таблицы `boards` |
| `app/src/Model/BoardColumn.php` | модель таблицы `board_columns` |
| `app/src/Model/Card.php` | модель таблицы `cards` |
| `app/config/` | настройки по средам: `dev.php`, `prod.php`, `test.php` |
| `app/database/migrations/` | миграции |
| `app/bin/migrate.php` | запуск миграций (migrate / rollback) |
| `app/bin/encrypt.php` | шифрование строки |
| `app/bin/decrypt.php` | расшифровка строки |
| `Makefile` | короткие команды |
| `.env.example` | образец настроек (порты, БД, UID/GID, `APP_ENV`, `APP_KEY`) |

## Как запускать

```bash
make init      # создать .env из .env.example
make build     # собрать образы
make up        # поднять контейнеры
make composer  # установить PHP-зависимости (composer install)
make migrate   # применить миграции
```

Сайт: <http://localhost:8080>

## Конфигурация

`APP_ENV` выбирает файл настроек из `app/config/` (`dev` по умолчанию).
`APP_KEY` — ключ шифрования в base64. `Config` декодирует его в 32 байта
и передаёт в `Encrypter`.

Секреты в настройках хранятся зашифрованными. Прочитать секрет:

```bash
make encrypt TEXT="my-secret"   # получить шифротекст
make decrypt TEXT="<шифротекст>"
```

## Схема БД

| Таблица | Ключ | Примечание |
|---------|------|------------|
| `users` | `user_id` | уникальный индекс `idx_email` |
| `boards` | `board_id` | ссылка на владельца `owner_user_id` |
| `board_columns` | `column_id` | индекс `idx_board_id` |
| `cards` | `card_id` | частичный индекс `idx_active_board_id` по `board_id WHERE is_archived = false` |

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
