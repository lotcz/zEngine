Z_VERSION = v4

perms:
	chown -R www-data:www-data ../zEngine

pull:
	git pull

checkout:
	git checkout -b $(Z_VERSION)

update: pull perms

upgrade: pull checkout perms

test:
	phpunit --bootstrap tests/autoload.php --testdox tests
