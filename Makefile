.PHONY: up down build shell test test-coverage phpstan migrate fresh seed logs ps install openapi-export openapi-validate ci client-dev client-build

up:
	docker compose up -d || ./.local/bin/docker-compose.exe up -d

down:
	docker compose down || ./.local/bin/docker-compose.exe down

build:
	docker compose build --no-cache || ./.local/bin/docker-compose.exe build --no-cache

shell:
	docker compose exec app bash || ./.local/bin/docker-compose.exe exec app bash

test:
	docker compose exec app php artisan test || ./.local/bin/docker-compose.exe exec app php artisan test

test-coverage:
	docker compose exec app php artisan test --coverage || ./.local/bin/docker-compose.exe exec app php artisan test --coverage

phpstan:
	docker compose exec app ./vendor/bin/phpstan analyse || ./.local/bin/docker-compose.exe exec app ./vendor/bin/phpstan analyse

migrate:
	docker compose exec app php artisan migrate || ./.local/bin/docker-compose.exe exec app php artisan migrate

fresh:
	docker compose exec app php artisan migrate:fresh --seed || ./.local/bin/docker-compose.exe exec app php artisan migrate:fresh --seed

seed:
	docker compose exec app php artisan db:seed || ./.local/bin/docker-compose.exe exec app php artisan db:seed

logs:
	docker compose logs -f || ./.local/bin/docker-compose.exe logs -f

ps:
	docker compose ps || ./.local/bin/docker-compose.exe ps

install:
	docker compose exec app composer install || ./.local/bin/docker-compose.exe exec app composer install

openapi-export:
	docker compose exec app php artisan scramble:export --path=openapi.json || ./.local/bin/docker-compose.exe exec app php artisan scramble:export --path=openapi.json

openapi-validate:
	docker compose exec app php artisan scramble:export --path=openapi.json || ./.local/bin/docker-compose.exe exec app php artisan scramble:export --path=openapi.json
	npx @redocly/cli lint backend/openapi.json

ci: phpstan test openapi-validate

client-dev:
	cd client && npm run dev

client-build:
	cd client && npm run build
