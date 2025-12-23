FROM hyperf/hyperf:8.3-alpine-v3.22-swoole-slim

ENV TIMEZONE=America/Sao_Paulo \
    APP_ENV=prod \
    APP_DEBUG=true \
    HOME=/app \
    HF="php /app/bin/hyperf.php"

RUN set -ex \
    && php -v \
    && php -m \
    && php --ri swoole \
    && cd /etc/php* \
    && { \
        echo "memory_limit=1G"; \
        echo "date.timezone=${TIMEZONE}"; \
    } >> php.ini \
    && echo "${TIMEZONE}" > /etc/timezone

RUN apk add --no-cache \
    php83-pdo_pgsql \
    php83-pgsql \
    postgresql-client

# RUN apk add --no-cache --virtual .build-deps \
#     $PHPIZE_DEPS \
#     php83-dev \
#     php83-pear \
#     openssl-dev \
#     && apk del .build-deps \
#     && rm -rf /tmp/* /var/cache/apk/*

WORKDIR /app

EXPOSE 9501

COPY . .

RUN composer install --ignore-platform-reqs --no-scripts --no-autoloader \
    && composer dump-autoload --optimize \
    && mkdir -p /app/runtime/container/proxy /app/runtime/logs

CMD ["sh", "-c", "if [ \"$USE_WATCH\" = 'false' ]; then $HF start; else $HF server:watch; fi"]