FROM php:8.5-fpm

# UID/GID хостового пользователя — чтобы www-data внутри контейнера совпадал
# с владельцем bind mount на хосте (актуально для Linux, на macOS/Docker Desktop
# не критично, но не мешает)
ARG UID=1000
ARG GID=1000

# Системные библиотеки для расширений (pdo_pgsql/intl/zip) и libfcgi-bin —
# он даёт бинарник cgi-fcgi, которым healthcheck дёргает php-fpm за /ping.
# opcache начиная с PHP 8.5 вкомпилирован в ядро и отдельной сборки не требует
# (docker-php-ext-install opcache падает с "cannot stat 'modules/*'" — модуля больше нет).
# Кэш apt чистим в этом же RUN, чтобы он не оседал отдельным слоем образа
RUN apt-get update && apt-get install -y --no-install-recommends \
  libpq-dev \
  libicu-dev \
  libzip-dev \
  libfcgi-bin \
  && docker-php-ext-install -j"$(nproc)" pdo_pgsql \
  && docker-php-ext-install -j"$(nproc)" intl \
  && docker-php-ext-install -j"$(nproc)" zip \
  && rm -rf /var/lib/apt/lists/*

RUN pecl install apcu-5.1.28 \
  && docker-php-ext-enable apcu \
  && rm -rf /tmp/pear

# Composer берём готовым бинарником из официального образа —
# без curl | sh и без лишнего слоя с исходниками composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# www-data получает UID/GID хоста: файлы, которые php-fpm создаёт в volume
# (например app/vendor через composer), на хосте будут принадлежать
# текущему пользователю, а не случайному системному uid из образа.
# -o (non-unique) нужен, потому что хостовый GID часто уже занят другой системной
# группой в образе (например GID 20 — staff на macOS, а в Debian это уже dialout)
RUN groupmod -o -g "${GID}" www-data \
  && usermod -o -u "${UID}" -g "${GID}" www-data

# Supercronic сохраняет окружение контейнера и передаёт вывод задач в Docker logs.
RUN set -eux; \
  supercronic_arch="$(dpkg --print-architecture)"; \
  case "$supercronic_arch" in \
  amd64) supercronic_sha1=1692066e350a88ff4d7d6474fe3c7a6c41ec5e89 ;; \
  arm64) supercronic_sha1=9d4c2000493215a1898502b2fc2dada3e8ea8c7a ;; \
  *) echo "Unsupported Supercronic architecture: $supercronic_arch" >&2; exit 1 ;; \
  esac; \
  curl -fsSL "https://github.com/aptible/supercronic/releases/download/v0.2.51/supercronic-linux-${supercronic_arch}" \
  -o /usr/local/bin/supercronic; \
  echo "${supercronic_sha1}  /usr/local/bin/supercronic" | sha1sum -c -; \
  chmod +x /usr/local/bin/supercronic

# Код не копируем — он приезжает bind mount'ом из ./app в compose.yaml
WORKDIR /var/www/html