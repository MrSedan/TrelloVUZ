FROM busybox:1.37-musl AS busybox

FROM nginx/nginx-prometheus-exporter:1.5.3
COPY --from=busybox /bin/busybox /bin/wget