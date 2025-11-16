FROM dunglas/frankenphp:1.9-php8.4-trixie

ARG UID
ARG GID

ENV ROOT=/app
ENV UID=${UID:-1001}
ENV GID=${GID:-1001}
ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC
ENV USER=web
ENV COMPOSER_FUND=0
ENV COMPOSER_MAX_PARALLEL_HTTP=24

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update && apt-get upgrade -y \
    && mkdir -p /etc/apt/keyrings \
    && apt-get install -y --no-install-recommends --no-install-suggests curl ca-certificates zip unzip sqlite3
RUN install-php-extensions pcntl gd exif zip intl redis

RUN curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

RUN apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY php/Caddyfile /etc/frankenphp/Caddyfile
COPY php/php.ini /usr/local/etc/php/conf.d/99-custom.ini

WORKDIR ${ROOT}

RUN setcap -r /usr/local/bin/frankenphp \
	&& groupadd -g ${GID} --system ${USER} \
	&& useradd -g ${USER} --system --no-user-group -ms /bin/bash -u ${UID} ${USER} \
	&& chown -R ${USER}:${USER} /app \
    && chown -R ${USER}:${USER} /config/caddy /data/caddy

USER ${USER}