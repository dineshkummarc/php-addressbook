### Installation

1. mkdir -p /opt/docker/php-addressbook/data
2. cd /opt/docker/php-addressbook/data
3. git clone https://github.com/toastie89/php-addressbook.git
4. mv php-addressbook www
5. cp /opt/docker/php-addressbook/data/www/docker-compose.yml /opt/docker/php-addressbook/
6. cd /opt/docker/php-addressbook/
7. docker-compose up -d
8. Check if everything goes well: docker logs -f addressbook_www
9. Browser http://localhost/
10. Setup the database (host: addressbook_db, username/password: addressbook)


### Dump addressbook table

```
mysqldump --password="addressbook" \
--user="addressbook" \
--where="deprecated = 0" \
addressbook addressbook \
> /var/lib/mysql/dump.sql
```
