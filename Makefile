down:
	docker-compose down

up:
	docker-compose up -d --build

reset:
	make down && make up

php:
	docker-compose exec -u www-data php bash

database:
	docker-compose exec mysql mysql -u root -ppass db_name

database_backup:
	docker-compose exec mysql mysqldump -u root -ppass db_name > backup-`date +%Y-%m-%d`.sql

load_backup:
	docker-compose exec mysql mysql -u root -ppass db_name -e "source /backups/backup-2020-02-16.sql" 2>/dev/null; true

create_nuxtjs_project:
	docker run --rm -it \
		-v "${PWD}:/$(basename `pwd`)" \
		--workdir "$(basename `pwd`)" \
		-w "/$(basename `pwd`)" \
		node:11.1-alpine  \
		sh -c "yarn create nuxt-app webapp"