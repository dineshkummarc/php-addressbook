#!/bin/sh

echo "=> Installing MySQL ..."

mysql_install_db

/usr/bin/mysqld_safe > /dev/null 2>&1 &

RET=1
while [ RET -ne 0 ]; do
  echo "=> Waiting for confirmation of MySQL service startup"
  sleep 5
  mysql -uroot -e "status" > /dev/null 2>&1
  RET=$?
done

mysql -uroot -e "CREATE USER 'admin'@'%' IDENTIFIED BY 'admin'"
mysql -uroot -e "GRANT ALL PRIVILEGES ON *.* TO 'admin'@'%' WITH GRANT OPTION"
mysql -uadmin -padmin -e "CREATE DATABASE addressbook"
mysql -uadmin -padmin addressbook < /var/www/html/addressbook.sql

/etc/init.d/apache2 start
