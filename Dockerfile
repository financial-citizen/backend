FROM composer:2.0 as build
WORKDIR /app/
COPY composer.json composer.lock /app/
RUN composer install --no-dev --no-scripts --no-autoloader \
    && composer dump-autoload --optimize

FROM php:8.0-apache
RUN apt-get update && apt-get install -y \
    acl \
 && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pdo pdo_mysql mysqli
WORKDIR /var/www/project

ENV APP_ENV=dev
ENV HTTPDUSER='www-data'

EXPOSE 8080

COPY docker/000-default.conf /etc/apache2/sites-available/
COPY --from=build /app/vendor /var/www/project/vendor
COPY . /var/www/project/

RUN setfacl -dR -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX var && \
    setfacl -R -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX var && \
    echo "Listen 8080" >> /etc/apache2/ports.conf && \
    mkdir -p /var/www/project/var/log/ && \
    mkdir -p /var/www/project/var/cache/ && \
    usermod -u 1000 www-data &&\
    chown -R www-data:www-data /var/www/  && \
    a2enmod rewrite
USER www-data

RUN php bin/console cache:clear --no-warmup && \
    php bin/console cache:warmup

RUN php bin/console doctrine:schema:drop --force
RUN php bin/console doctrine:schema:create
RUN php bin/console doctrine:fixtures:load --append
RUN php bin/console lexik:jwt:generate-keypair --skip-if-exists

CMD ["apache2-foreground"]