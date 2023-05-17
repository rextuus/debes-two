deploy: pull install database build

pull:
	git pull

install:
	composer install

database:
	php bin/console d:s:u --force

build:
	npm run build