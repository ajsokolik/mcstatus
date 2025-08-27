# syntax=docker/dockerfile:1-labs
FROM    php:apache
LABEL   maintainer="asokolik@gmail.com"
RUN     apt-get update && rm -rf /var/lib/apt/lists/*
ADD     index.php /var/www/html
ADD     refresh.php /var/www/html
RUN     mkdir /var/www/html/img
ADD     ./img/background.jpg /var/www/html/img
ADD     ./img/favicon.ico /var/www/html/img
RUN     chmod 644 /var/www/html/index.php
RUN     chmod 644 /var/www/html/img/*
