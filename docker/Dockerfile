FROM   rockylinux:9.1
LABEL  maintainer="asokolik@gmail.com"
RUN    dnf -y update && dnf -y install nginx php php-cli php-gd php-curl php-zip php-mbstring php-fpm
COPY   files/www.conf /etc/php-fpm.d/www.conf
COPY   files/default.conf /etc/nginx/conf.d/default.conf
COPY   files/index.php /var/www/html/index.php
COPY   files/img /var/www/html/img
EXPOSE 80
CMD    "php-fpm -F -R && /usr/sbin/nginx -g daemon off;"
