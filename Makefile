.PHONY: up down build shell test test-coverage phpstan migrate fresh seed logs ps install openapi-export openapi-validate ci

up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build --no-cache

shell:
	docker compose exec app bash

test:
	docker compose exec app php artisan test

test-coverage:
	docker compose exec app php artisan test --coverage

phpstan:
	docker compose exec app ./vendor/bin/phpstan analyse

migrate:
	docker compose exec app php artisan migrate

fresh:
	docker compose exec app php artisan migrate:fresh --seed

seed:
	docker compose exec app php artisan db:seed

logs:
	docker compose logs -f

ps:
	docker compose ps

install:
	docker compose exec app composer install

openapi-export:
	docker compose exec app php artisan scramble:export --path=openapi.json

openapi-validate:
	docker compose exec app php artisan scramble:export --path=openapi.json
	npx @redocly/cli lint backend/openapi.json

ci: phpstan test openapi-validate
