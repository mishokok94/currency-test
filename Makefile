SHELL := /bin/bash

APP_CONTAINER := app
COMPOSE := docker compose -f docker/docker-compose.yml

.PHONY: help install start stop restart logs migrate seed sync schedule-build schedule-run npm dev build test lint clean

help:
	@echo "Available targets:"
	@echo "  install        Install PHP dependencies"
	@echo "  start          Start containers, run migrations and seed admin user"
	@echo "  stop           Stop containers"
	@echo "  restart        Restart containers"
	@echo "  logs           Tail application logs"
	@echo "  migrate        Run database migrations"
	@echo "  seed           Seed admin user"
	@echo "  sync           Fetch exchange rates once"
	@echo "  schedule-run   Run due scheduled commands"
	@echo "  schedule-work  Run scheduler worker"
	@echo "  npm            Install frontend dependencies"
	@echo "  dev            Run Vite dev server"
	@echo "  build          Build frontend assets"
	@echo "  test           Run test suite"
	@echo "  lint           Run Pint"
	@echo "  clean          Remove vendor and node_modules"

install:
	$(COMPOSE) run --rm $(APP_CONTAINER) composer install --prefer-dist --no-interaction

start:
	$(COMPOSE) up -d
	$(COMPOSE) exec $(APP_CONTAINER) php artisan key:generate --force
	$(COMPOSE) exec $(APP_CONTAINER) php artisan migrate --force
	$(COMPOSE) exec $(APP_CONTAINER) php artisan db:seed --class=AdminUserSeeder --force

stop:
	$(COMPOSE) down

restart:
	$(COMPOSE) down
	$(COMPOSE) up -d

logs:
	$(COMPOSE) logs -f $(APP_CONTAINER)

migrate:
	$(COMPOSE) exec $(APP_CONTAINER) php artisan migrate

seed:
	$(COMPOSE) exec $(APP_CONTAINER) php artisan db:seed --class=AdminUserSeeder

sync:
	$(COMPOSE) exec $(APP_CONTAINER) php artisan exchange:sync

schedule-run:
	$(COMPOSE) exec $(APP_CONTAINER) php artisan schedule:run

schedule-work:
	$(COMPOSE) exec $(APP_CONTAINER) php artisan schedule:work

npm:
	$(COMPOSE) exec $(APP_CONTAINER) npm install

dev:
	$(COMPOSE) exec $(APP_CONTAINER) npm run dev

build:
	$(COMPOSE) exec $(APP_CONTAINER) npm run build

test:
	$(COMPOSE) exec $(APP_CONTAINER) php artisan test

lint:
	$(COMPOSE) exec $(APP_CONTAINER) ./vendor/bin/pint

clean:
	rm -rf vendor node_modules

