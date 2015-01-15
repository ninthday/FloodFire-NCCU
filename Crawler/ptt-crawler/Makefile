all:
	composer update
	composer dump-autoload

clean:
	echo "drop database ptt_crawler" | mysql -u root

init:
	mysql -u root < ./_INSTALL/ptt_crawler.sql

