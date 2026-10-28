FROM --platform=linux/amd64 hub.wozhangwan.com/zwwl/php84-swoole-hyperf:v1

LABEL app.name="hyperf-weather-mcp" app.version="1.0.0"

ENV APP_ENV=dev \
    SCAN_CACHEABLE=false \
    TIMEZONE=Asia/Shanghai

WORKDIR /opt/www

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts

COPY app ./app
COPY bin ./bin
COPY config ./config
RUN mkdir -p runtime/logs

EXPOSE 9601

ENTRYPOINT ["php", "/opt/www/bin/hyperf.php", "start"]
