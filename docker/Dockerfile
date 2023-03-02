FROM rockylinux:9.1
LABEL maintainer="asokolik@gmail.com"
RUN  dnf -y update && dnf -y install nginx
COPY files/default.conf /etc/nginx/conf.d/default.conf
COPY files/index.php /var/www/html/index.php
COPY files/img /var/www/html/img
EXPOSE 80
CMD ["/usr/sbin/nginx", "-g", "daemon off;"]
