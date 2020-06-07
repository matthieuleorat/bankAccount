include .env

down:
	docker-compose down --remove-orphans

up:
	docker-compose up -d --build

reset:
	make down && make up

php:
	docker-compose exec -u www-data php bash

database:
	docker-compose exec postgre bash

load_backup:
ifdef file
		docker-compose exec postgre pg_restore --verbose --clean --no-acl --no-owner -h localhost -U postgres -d db_name /data/backups/$(file)
else
		@echo 'Missing file argument. Usage: make file=latest.dump load_backup'
endif

load_last_backup_from_remote:
	heroku pg:backups:capture --app ${HEROKU_APP_NAME}
	heroku pg:backups:download --app ${HEROKU_APP_NAME} -o=./backups/latest.dump
	docker-compose exec postgre pg_restore --verbose --clean --no-acl --no-owner -h localhost -U postgres -d db_name /data/backups/latest.dump

create_nuxtjs_project:
	docker run --rm -it \
		-v "${PWD}:/$(basename `pwd`)" \
		--workdir "$(basename `pwd`)" \
		-w "/$(basename `pwd`)" \
		node:11.1-alpine  \
		sh -c "yarn create nuxt-app webapp"

her_bash:
	heroku ps:exec --app ${HEROKU_APP_NAME}

her_logs:
	heroku logs --tail --app ${HEROKU_APP_NAME}