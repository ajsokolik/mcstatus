FROM    rockylinux:9.1
LABEL   maintainer="asokolik@gmail.com"
RUN     dnf -y update && dnf -y install nginx php php-cli php-gd php-curl php-zip php-mbstring php-fpm wget
RUN     wget https://raw.githubusercontent.com/ajsokolik/mcstatus/main/docker/files/www.conf -P /etc/php-fpm.d
RUN     wget https://raw.githubusercontent.com/ajsokolik/mcstatus/main/docker/files/default.conf -P /etc/nginx/conf.d
RUN     wget https://raw.githubusercontent.com/ajsokolik/mcstatus/main/index.php -P /var/www/html
RUN     wget https://raw.githubusercontent.com/ajsokolik/mcstatus/main/img/background.jpg -P /var/www/html/img
RUN     mkdir -p /run/php-fpm/
EXPOSE  80
CMD     ["sh","-c","/usr/sbin/php-fpm -F -R && /usr/sbin/nginx -g 'daemon off;'"]
