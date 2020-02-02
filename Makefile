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