# VyatSU Trello Project

Учебный аналог Trello: nginx + php-fpm + PostgreSQL в Docker.
Код на чистом PHP, ORM — Eloquent (illuminate/database), логи — Monolog.

## Что где лежит

| Путь | Что это |
|------|---------|
| `docker-compose.yml` | четыре сервиса: nginx, php, cron, postgres |
| `docker/nginx/` | конфиг nginx |
| `docker/php/` | Dockerfile, `php.ini`, `www.conf` |
| `docker/postgres/` | Dockerfile и `postgresql.conf` |
| `app/public/index.php` | точка входа: маршрутизация, отправка ответа и логирование |
| `app/public/health.php` | совместимый адрес проверки php + БД (200/503) |
| `app/src/init.php` | общая инициализация и обработка ошибок HTTP и CLI |
| `app/src/Config/routes.php` | маршруты HTTP: метод, путь и метод контроллера |
| `app/src/Library/Api/` | контроллеры, HTTP-запрос и представления приложения |
| `app/src/Library/Framework/Routing/` | базовые Request, Controller, View, маршрутизатор и HTTP-ответ |
| `app/src/Library/Auth/` | сессии PHP с хранением в PostgreSQL |
| `app/src/Library/Framework/TaskRunner.php` | запуск именованных задач с блокировкой в PostgreSQL |
| `app/src/Library/Framework/CronTaskAbstract.php` | базовый класс крон-задачи |
| `app/src/Library/Tasks/RemoveOldSessionsCronTask.php` | очистка просроченных сессий |
| `app/src/Config/cron.php` | реестр крон-задач |
| `app/src/crontab.php` | CLI для запуска крон-задач |
| `docker/cron/crontab` | расписание задач контейнера cron |
| `app/src/Library/Database/Database.php` | подключение к БД через Eloquent Capsule |
| `app/src/Library/Framework/LogHelper.php` | логгер Monolog: stdout + файл в `app/var/log/` |
| `app/src/Library/Super.php` | общий доступ к конфигурации и логгеру |
| `app/src/Library/Config/Config.php` | конфигурация: читает `APP_ENV`, отдаёт настройки и секреты |
| `app/src/Library/Config/Encrypter.php` | шифрование AES-256-GCM |
| `app/src/Library/Database/User.php` | модель таблицы `users` |
| `app/src/Library/Database/Board.php` | модель таблицы `boards` |
| `app/src/Library/Database/BoardColumn.php` | модель таблицы `board_columns` |
| `app/src/Library/Database/Card.php` | модель таблицы `cards` |
| `app/src/Config/` | настройки по средам: `dev.php`, `prod.php`, `test.php` |
| `app/src/migrations/` | миграции |
| `app/src/console/migrate.php` | запуск миграций (migrate / rollback) |
| `app/src/console/config-encrypt.php` | шифрование строки |
| `app/src/console/config-decrypt.php` | расшифровка строки |
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

`APP_ENV` выбирает файл настроек из `app/src/Config/` (`dev` по умолчанию).
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
| `sessions` | `id` | данные сессии и `expires_at` с индексом для очистки |

## Сессии

После обновления выполните `make migrate`: обработчик использует таблицу `sessions`.
Главная страница запускает сессию через `App\Library\Auth\Session::start()`.
В контроллерах после запуска доступны обычные `$_SESSION`, `session_regenerate_id(true)`
и `session_destroy()`. Проверка доступности сессию не запускает.

Данные сессии хранятся в PostgreSQL, не в файлах. Payload кодируется в base64,
чтобы сохранить бинарные данные; это не шифрование. Срок бездействия задаёт
`session.gc_maxlifetime` (по умолчанию 1440 секунд). Неизвестные и просроченные
идентификаторы отклоняются. Cookie использует HttpOnly, SameSite=Lax и Secure
при HTTPS. Блокировка PostgreSQL предотвращает потерю изменений при параллельных
запросах одной сессии. После работы с `$_SESSION` можно вызвать
`session_write_close()`, чтобы освободить блокировку до завершения долгого запроса.

## Крон-задачи

```bash
make cron-list
make cron TASK=sessions:gc
```

Задачи регистрируются в `app/src/Config/cron.php`: имя сопоставляется с объектом
класса, который наследует `App\Library\Framework\CronTaskAbstract` и реализует
`run(): void`.
`sessions:gc` удаляет просроченные сессии. Автоматическая очистка при HTTP-запросах
отключена: регулярный запуск выполняет отдельный контейнер `cron`.

Контейнер использует тот же PHP-образ, код и настройки БД, что и сервис `php`.
Планировщик Supercronic работает от `www-data` и запускает очистку каждые пять
минут. Расписание хранится в `docker/cron/crontab`, часовой пояс — Europe/Moscow,
как в `php.ini`. Системный crontab на хосте не нужен.

После обновления образа выполните:

```bash
make build
make up
docker compose logs -f cron
```

Перед выполнением задач должны быть установлены зависимости и применены миграции
(`make composer`, `make migrate`). На первом запуске выполните эти команды после
`make up`. До этого планировщик будет писать ошибки задач и повторять запуск
по расписанию.

Чтобы добавить задачу, создайте класс в `app/src/Library/Tasks/`, зарегистрируйте
его в `app/src/Config/cron.php` и добавьте команду
`php /var/www/html/src/crontab.php <имя>` с расписанием в
`docker/cron/crontab`. После изменения расписания пересоздайте контейнер:

```bash
docker compose up -d --force-recreate cron
```

Если ранее добавили задачу в crontab хоста, удалите ту запись через `crontab -e`,
чтобы оставить один источник расписания.

Одновременный запуск той же задачи пропускается благодаря блокировке PostgreSQL.
Успешный или пропущенный запуск возвращает код 0, ошибка задачи — 1,
неверное имя в CLI — 2. Результаты пишутся в обычный лог приложения.

## Контроллеры и маршруты

`App\Library\Framework\Routing\ControllerAbstract` — базовый класс с методами
`render()` и `json()`. Действие контроллера возвращает
`App\Library\Framework\Routing\Response`. Представления наследуют `ViewAbstract`
и указывают PHP-шаблон в методе `template()`. Например, `render(new HomeView())`
загружает `app/src/Library/Api/View/home.php`.

Маршруты регистрируются явно в `app/src/Config/routes.php`, например:

```php
$router->add('GET', '/', [new FrontendController(), 'index']);
```

Маршрутизатор сопоставляет точный путь без query string и HTTP-метод.
Неизвестный путь возвращает 404, неподдерживаемый метод — 405 с заголовком Allow.
HEAD использует GET без тела ответа. Доступны `/`, `/index.php`, `/health` и
`/health.php`. Статические файлы по-прежнему отдаёт nginx.

`App\Library\Api\Request` наследует `RequestAbstract` и получает метод и URI из
`$_SERVER`. Маршрутизатор принимает объект запроса. Общий `app/src/init.php`
подключается из HTTP- и CLI-скриптов, задаёт `ROOT_DIR` и обработчики ошибок.
БД подключается при обращении к ней; сессии запускаются только там, где нужны.

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
