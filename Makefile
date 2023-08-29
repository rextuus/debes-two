deploy: stop_consumer pull install database build start_consumer
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

stop_consumer:
	sudo systemctl stop debes-messenger-consumer.service

start_consumer:
	sudo systemctl start debes-messenger-consumer.service