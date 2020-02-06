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

create_nuxtjs_project:
	docker run --rm -it \
		-v "${PWD}:/$(basename `pwd`)" \
		--workdir "$(basename `pwd`)" \
		-w "/$(basename `pwd`)" \
		node:11.1-alpine  \
		sh -c "yarn create nuxt-app webapp"
	# docker run --rm -v /home/matleo/Projects/bankAccount/docker/test:/test -w /test --user 17305:10000 -it node:11.1-alpine sh -c yarn create nuxt-app lala