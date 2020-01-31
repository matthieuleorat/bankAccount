down:
	docker-compose down

up:
	docker-compose up -d --build

reset:
	make down && make up

php:
	docker-compose exec -u www-data php bash