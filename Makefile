ifneq (,$(wildcard .env))
include .env 
export 
endif

.DEFAULT_GOAL := help
.PHONY: help init build up down ps logs clean composer migrate migrate-rollback encrypt decrypt cron cron-list sh psql \
	mon-up mon-down mon-ps mon-check mon-reload pg-monitoring \
	logs-up logs-down \ 
	demo-db-down demo-db-up demo-load demo-errors demo-slow

COMPOSE = docker compose
COMPOSE_MON = docker compose -f docker-compose.yml -f compose.monitoring.yaml
COMPOSE_LOG = $(COMPOSE_MON) -f compose.logging.yaml
COMPOSE_ALL = $(COMPOSE_LOG) --profile '*'

K6 = $(COMPOSE_LOG) --profile tools run --rm --no-deps -e VUS -e DURATION k6 run --quiet 

help:
	@echo "make init      — создать .env из .env.example (с UID/GID текущего пользователя)"
	@echo "make build     — собрать образы"
	@echo "make up        — поднять контейнеры (в фоне)"
	@echo "make down      — остановить и удалить контейнеры"
	@echo "make ps        — статус контейнеров"
	@echo "make logs      — логи всех сервисов (follow)"
	@echo "make clean     — down -v: остановить и удалить volume pgdata (данные будут потеряны)"
	@echo "make composer  — composer install внутри php-контейнера"
	@echo "make migrate   — применить миграции БД"
	@echo "make migrate-rollback — откатить последнюю миграцию"
	@echo "make encrypt   — зашифровать строку (TEXT=...)"
	@echo "make decrypt   — расшифровать строку (TEXT=...)"
	@echo "make cron      — запустить крон-задачу (TASK=sessions:gc)"
	@echo "make cron-list — список крон-задач"
	@echo "make sh        — shell в php-контейнере"
	@echo "make psql      — консоль psql к postgres"
	@echo ""
	@echo "Мониторинг (Prometheus + экспортеры):"
	@echo "make mon-up    — поднять базу + мониторинг (cAdvisor: COMPOSE_PROFILES=cadvisor make mon-up)"
	@echo "make mon-down  — остановить мониторинг, база продолжит работать"
	@echo "make mon-ps    — статус контейнеров вместе с мониторингом"
	@echo "make mon-check — проверить конфиги Prometheus и правила (promtool), blackbox, Alertmanager (amtool)"
	@echo "make mon-reload — перечитать конфиги Prometheus и Alertmanager без рестарта"
	@echo "make pg-monitoring — применить настройки мониторинга к уже существующей БД"
	@echo ""
	@echo "Логи (Graylog, нужно ~3 ГБ RAM для Docker):"
	@echo "make logs-up   — база + мониторинг + Graylog, логи nginx/php/postgres уходят в Graylog"
	@echo "make logs-down — убрать Graylog, мониторинг и база остаются"
	@echo ""
	@echo "Сценарии для лекции (нужен make mon-up или make logs-up):"
	@echo "make demo-db-down — остановить PostgreSQL: 503 на health, письмо PostgresDown (~1 мин)"
	@echo "make demo-db-up   — вернуть PostgreSQL, письмо RESOLVED"
	@echo "make demo-load    — нагрузка k6 сверх пула php-fpm: очередь, p99, PhpFpmPoolSaturated (~5 мин)"
	@echo "make demo-errors  — всплеск ошибок (без алерта), затем поток 5xx: HighErrorRate (~6 мин)"
	@echo "make demo-slow    — медленные запросы к БД: latency, slow-log, pg_stat_statements (~4 мин)"

init:
	@if [ -f .env ]; then \
		echo ".env уже существует, пропускаю"; \
	else \
		cp .env.example .env; \
		CURRENT_UID=$$(id -u); \
		CURRENT_GID=$$(id -g); \
		sed -i.bak "s/^UID=.*/UID=$$CURRENT_UID/" .env; \
		sed -i.bak "s/^GID=.*/GID=$$CURRENT_GID/" .env; \
		rm -f .env.bak; \
		echo ".env создан (UID=$$CURRENT_UID, GID=$$CURRENT_GID)"; \
	fi

build:
	docker compose build

up:
	docker compose up -d

down:
	docker compose down

ps:
	docker compose ps

logs:
	docker compose logs -f

clean:
	$(COMPOSE_ALL) down -v --remove-orphans

# Ставим зависимости от имени www-data — тогда vendor/ на хосте получит
# владельца из UID/GID, заданных при `make init`, а не root
composer:
	docker compose exec -u www-data php composer install

migrate:
	docker compose exec php php src/console/migrate.php migrate

migrate-rollback:
	docker compose exec php php src/console/migrate.php rollback

encrypt:
	docker compose exec php php src/console/config-encrypt.php "$(TEXT)"

decrypt:
	docker compose exec php php src/console/config-decrypt.php "$(TEXT)"

cron:
	docker compose exec -T -u www-data php php src/crontab.php "$(TASK)"

cron-list:
	docker compose exec -T -u www-data php php src/crontab.php --list

sh:
	docker compose exec php sh

psql:
	docker compose exec postgres psql -U $(POSTGRES_USER) -d $(POSTGRES_DB)

mon-up:
	$(COMPOSE_MON) up -d

mon-down:
	$(COMPOSE_ALL) rm -sf $$($(COMPOSE_ALL) config --services | grep -vxF "$$($(COMPOSE) config --services)")
	$(COMPOSE) up -d

mon-ps:
	$(COMPOSE_MON) --profile '*' ps

mon-check:
	$(COMPOSE_MON) run --rm --no-deps --entrypoint promtool prometheus check config /etc/prometheus/prometheus.yml
	$(COMPOSE_MON) run --rm --no-deps --entrypoint promtool prometheus check rules /etc/prometheus/rules/alerts.yml
	$(COMPOSE_MON) run --rm --no-deps blackbox-exporter --config.file=/etc/blackbox_exporter/blackbox.yml --config.check
	$(COMPOSE_MON) run --rm --no-deps --entrypoint amtool alertmanager check-config /etc/alertmanager/alertmanager.yml

mon-reload:
	curl -fsS -X POST http://127.0.0.1:$(or $(PROMETHEUS_PORT),9090)/-/reload && echo "Prometheus: конфиг и правила перечитаны"
	curl -fsS -X POST http://127.0.0.1:$(or $(ALERTMANAGER_PORT),9093)/-/reload && echo "Alertmanager: конфиг перечитан"

# initdb-скрипты выполняются только на пустом томе pgdata. Чтобы не делать `make clean`
# (и не терять данные), применяем мониторинговую часть вручную:
#  1) restart — shared_preload_libraries читается только при старте сервера;
#  2) --wait — ждём healthy, иначе psql не к чему подключаться;
#  3) тот же идемпотентный скрипт, что и при initdb
pg-monitoring:
	$(COMPOSE) restart postgres
	$(COMPOSE) up -d --wait postgres
	$(COMPOSE) exec postgres sh /docker-entrypoint-initdb.d/02-monitoring.sh
	@echo "Готово: пользователь экспортера и pg_stat_statements на месте"

# --wait: дождаться healthy всех сервисов (Graylog стартует 1–2 минуты).
# graylog-init — одноразовый, после настройки он завершается, поэтому его ждём отдельно
logs-up:
	$(COMPOSE_LOG) up -d --wait --wait-timeout 300 graylog
	$(COMPOSE_LOG) up --no-deps --exit-code-from graylog-init graylog-init
	$(COMPOSE_LOG) up -d --wait --wait-timeout 300 $$($(COMPOSE_LOG) config --services | grep -vx graylog-init)
	@echo "Graylog: http://127.0.0.1:$(or $(GRAYLOG_PORT),9000)/ (admin / пароль из .env)"

# Убираем только Graylog и его хранилища; nginx/php/postgres пересоздаются
# с обычным logging driver, мониторинг продолжает работать
logs-down:
	$(COMPOSE_LOG) rm -sf $$($(COMPOSE_LOG) config --services | grep -vxF "$$($(COMPOSE_MON) config --services)")
	$(COMPOSE_MON) up -d

# --- Сценарии для лекции -------------------------------------------------------
# Каждый печатает, куда смотреть, — алерты приходят не сразу (for + group_wait),
# и это часть демонстрации

demo-db-down:
	$(COMPOSE) stop postgres
	@echo ""
	@echo "PostgreSQL остановлен. Что смотреть:"
	@echo "  curl -i http://127.0.0.1:$(or $(NGINX_PORT),8080)/health.php   — 503, database: fail"
	@echo "  $(PROMETHEUS_URL)/alerts — PostgresDown: pending → firing (for: 30s); HealthCheckFailing тоже (for: 1m)"
	@echo "  $(MAILPIT_URL) — через ~1 мин письмо [FIRING] PostgresDown;"
	@echo "    письма HealthCheckFailing НЕ будет: его подавляет inhibit_rules в Alertmanager"
	@echo "Вернуть: make demo-db-up"

demo-db-up:
	$(COMPOSE) start postgres
	@echo "PostgreSQL запущен. Через 1–2 минуты в $(MAILPIT_URL) придёт [RESOLVED] PostgresDown"

demo-load:
	@echo "Нагрузка на 5 мин: 20 параллельных пользователей против 5 воркеров php-fpm."
	@echo "Смотреть: $(GRAFANA_URL) — «Воркеры php-fpm», «Очередь», «Время ответа проб»;"
	@echo "через ~2.5 мин письмо PhpFpmPoolSaturated в $(MAILPIT_URL). Остановить — Ctrl+C."
	$(K6) /scripts/load.js

demo-errors:
	@echo "~6 мин: 30 с всплеск ошибок → 90 с пауза → 4 мин устойчивых 30% ошибок."
	@echo "Смотреть: $(PROMETHEUS_URL)/alerts — HighErrorRate после всплеска только pending, писем нет;"
	@echo "на устойчивых ошибках через ~2.5 мин firing и письмо в $(MAILPIT_URL)."
	$(K6) /scripts/errors.js

demo-slow:
	@echo "4 мин медленных запросов (pg_sleep 1.5 с). Смотреть: $(GRAFANA_URL) — p95 по route,"
	@echo "топ запросов pg_stat_statements; HighLatencyP95 в $(MAILPIT_URL) через ~3.5 мин;"
	@echo "в Graylog (если make logs-up): tag:*postgres* AND duration"
	$(K6) /scripts/slow.js
	@echo ""
	@echo "Топ запросов приложения по среднему времени (pg_stat_statements):"
	@# Только запросы пользователя приложения (без экспортера), повторявшиеся больше
	@# одного раза (без разовых CREATE из initdb); переводы строк — в пробелы
	@$(COMPOSE) exec -T postgres psql -U $(POSTGRES_USER) -d $(POSTGRES_DB) -c \
		"SELECT calls, round(mean_exec_time) AS mean_ms, round(total_exec_time) AS total_ms, \
		        left(regexp_replace(query, '\s+', ' ', 'g'), 60) AS query \
		 FROM pg_stat_statements \
		 WHERE userid = (SELECT oid FROM pg_roles WHERE rolname = current_user) AND calls > 1 \
		 ORDER BY mean_exec_time DESC LIMIT 5;"
