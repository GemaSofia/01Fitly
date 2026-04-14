FROM php:8.2-apache


RUN apt-get update && \
    apt-get install -y sqlite3 libsqlite3-dev && \
    docker-php-ext-install pdo pdo_sqlite


COPY app/ /var/www/html/


RUN chmod -R 777 /var/www/html

EXPOSE 80
