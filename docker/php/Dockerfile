FROM php:8.3-cli-bookworm

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update \
    && apt-get install -y supervisor \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install pcntl \
	&& apt-get -y autoremove \
	&& apt-get clean \
	&& rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN groupadd --force -g 1000 web
RUN useradd -ms /bin/bash --no-user-group -g 1000 -u 1337 web
RUN usermod -u 1000 web

COPY php.ini /etc/php/8.3/cli/conf.d/99-web.ini
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY entrypoint /usr/local/bin/entrypoint

RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 8080
EXPOSE 3201

ENTRYPOINT ["entrypoint"]