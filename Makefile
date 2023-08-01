deploy: pull install database build
stash:
	git stash

pull:
	git pull

install:
	composer install

database:
	php bin/console d:s:u --force

build:
	npm run build

save:
	cp .env .env_backup

restore:
	cp .env_backup .env

backup:
	./backup.sh