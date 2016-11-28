FROM ubuntu:xenial
MAINTAINER baor

# Install packages
ENV DEBIAN_FRONTEND noninteractive

apt-get -y install git apache2 libapache2-mod-php5 mysql-server php5-mysql php5-mcrypt && \
echo "ServerName localhost" >> /etc/apache2/apache2.conf

ADD my.cnf /etc/mysql/conf.d/my.cnf
ADD run.sh /run.sh
RUN chmod 755 /*.sh

# Remove pre-installed database
RUN rm -rf /var/lib/mysql/*

RUN git clone https://github.com/baor/php-addressbook.git /var/www/html 

EXPOSE 80 3306
CMD ["/run.sh"]

