include .env

down:
	docker-compose down --remove-orphans

up:
	docker-compose up -d --build

reset:
	make down && make up

php:
	docker-compose exec php sh

database-shell:
	docker-compose exec database sh

database:
	docker-compose exec database psql ${POSTGRES_DB} ${POSTGRES_USER}

debug:
	docker-compose -f docker-compose.yml -f docker-compose.debug.yml up

debug-build:
	docker-compose -f docker-compose.yml -f docker-compose.debug.yml up --build

dev:
	docker-compose up -d

build_prod:
	docker-compose -f docker-compose.yml -f docker-compose.debug.yml build

run_prod:
	docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d

load_backup:
ifdef file
		docker-compose exec database pg_restore --verbose --clean --no-acl --no-owner -h localhost -U ${POSTGRES_USER} -d ${POSTGRES_DB} /data/backups/$(file)
else
		@echo 'Missing file argument. Usage: make file=latest.dump load_backup'
endif

load_last_backup_from_remote: backup_remote load_lastest_backup

backup_remote:
	heroku pg:backups:capture --app ${HEROKU_APP_NAME}
	heroku pg:backups:download --app ${HEROKU_APP_NAME} -o=./backups/latest.dump

load_lastest_backup:
	docker-compose exec database pg_restore --verbose --clean --no-acl --no-owner -h localhost -U ${POSTGRES_USER} -d ${POSTGRES_DB} /data/backups/latest.dump

her_bash:
	heroku ps:exec --app ${HEROKU_APP_NAME}

her_logs:
	heroku logs --tail --app ${HEROKU_APP_NAME}

phpunit:
	 docker-compose exec -u www-data php  ./vendor/bin/simple-phpunit  --coverage-html='/var/www/html/report'

cs-fixer:
	 docker run --user $(id -u):$(id -g) --rm -v ${PWD}:/data cytopia/php-cs-fixer fix  ./src

phpcs:
	docker run --rm -v ${PWD}:/data cytopia/phpcs ./src -n -p -s
