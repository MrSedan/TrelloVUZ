# Базовый образ nginx на Alpine — маленький и быстрый, для учебных целей достаточно
FROM nginx:alpine

# curl нужен только для healthcheck (проверка /nginx-health изнутри контейнера)
RUN apk add --no-cache curl