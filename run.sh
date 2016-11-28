echo "=> Installing MySQL ..."

mysql_install_db

mysql -uroot -e "CREATE USER 'admin'@'%' IDENTIFIED BY 'admin'"
mysql -uroot -e "GRANT ALL PRIVILEGES ON *.* TO 'admin'@'%' WITH GRANT OPTION"
mysql -uadmin -padmin -e "CREATE DATABASE addressbook"
mysql -uadmin -padmin addressbook < /app/php-addressbook/addressbook.sql

/etc/init.d/apache2 start
