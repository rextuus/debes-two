deploy: patch install database build

patch:
	git patch

install:
	composer install

database:
	php bin/console d:s:u --force

build:
	npm run build