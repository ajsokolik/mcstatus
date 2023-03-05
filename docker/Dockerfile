FROM    php:7.4-apache
LABEL   maintainer="asokolik@gmail.com"
RUN     apt-get update \
        && apt-get install -y wget \
        && rm -rf /var/lib/apt/lists/*
RUN     wget https://raw.githubusercontent.com/ajsokolik/mcstatus/main/index.php -P /var/www/html
RUN     wget https://raw.githubusercontent.com/ajsokolik/mcstatus/main/img/background.jpg -P /var/www/html/img
