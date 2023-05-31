FROM php:8.2-fpm

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=Etc/UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime \
    && echo $TZ > /etc/timezone

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        build-essential \
        ca-certificates \
        cron \
        git \
        software-properties-common \
        unzip \
        wget \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && git config --global --add safe.directory /var/www/html

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
