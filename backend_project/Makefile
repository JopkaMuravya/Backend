.PHONY: help install build up down logs shell composer test cs psalm analyse fix db

DOCKER_COMPOSE = docker-compose
DOCKER_EXEC    = $(DOCKER_COMPOSE) exec
PHP_CONTAINER  = php
DB_CONTAINER   = postgres

help:
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

install: build up composer-install db-wait db-create
	@echo "Installation completed!"

build:
	$(DOCKER_COMPOSE) build --no-cache

up:
	$(DOCKER_COMPOSE) up -d

down:
	$(DOCKER_COMPOSE) down

restart: down up

logs:
	$(DOCKER_COMPOSE) logs -f

logs-php:
	$(DOCKER_COMPOSE) logs -f $(PHP_CONTAINER)

logs-nginx:
	$(DOCKER_COMPOSE) logs -f nginx

logs-db:
	$(DOCKER_COMPOSE) logs -f $(DB_CONTAINER)

shell:
	$(DOCKER_EXEC) $(PHP_CONTAINER) bash

shell-db:
	$(DOCKER_EXEC) $(DB_CONTAINER) bash

shell-nginx:
	$(DOCKER_EXEC) nginx sh

composer:
	$(DOCKER_EXEC) $(PHP_CONTAINER) composer $(cmd)

composer-install:
	$(DOCKER_EXEC) $(PHP_CONTAINER) composer install

composer-update:
	$(DOCKER_EXEC) $(PHP_CONTAINER) composer update

composer-dump:
	$(DOCKER_EXEC) $(PHP_CONTAINER) composer dump-autoload


db-wait:
	@echo "Waiting for PostgreSQL to be ready..."
	@$(DOCKER_EXEC) $(PHP_CONTAINER) bash -c 'until bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do echo "Waiting for database..."; sleep 2; done'
	@echo "PostgreSQL is ready!"

db-create:
	$(DOCKER_EXEC) $(PHP_CONTAINER) bin/console doctrine:database:create --if-not-exists

db-drop:
	$(DOCKER_EXEC) $(PHP_CONTAINER) bin/console doctrine:database:drop --if-exists --force

db-schema:
	$(DOCKER_EXEC) $(PHP_CONTAINER) bin/console doctrine:schema:create

db-update:
	$(DOCKER_EXEC) $(PHP_CONTAINER) bin/console doctrine:schema:update --force

db-validate:
	$(DOCKER_EXEC) $(PHP_CONTAINER) bin/console doctrine:schema:validate

db-migrate:
	$(DOCKER_EXEC) $(PHP_CONTAINER) bin/console doctrine:migrations:migrate --no-interaction

db-diff:
	$(DOCKER_EXEC) $(PHP_CONTAINER) bin/console doctrine:migrations:diff

db-status:
	$(DOCKER_EXEC) $(PHP_CONTAINER) bin/console doctrine:migrations:status

db-rollback:
	$(DOCKER_EXEC) $(PHP_CONTAINER) bin/console doctrine:migrations:execute --down $(version)

db-reset: db-drop db-create db-migrate fixtures

db-fixtures:
	$(DOCKER_EXEC) $(PHP_CONTAINER) bin/console doctrine:fixtures:load --no-interaction

fixtures: db-fixtures

db-export:
	@$(DOCKER_EXEC) $(DB_CONTAINER) bash -c 'pg_dump -U $${POSTGRES_USER} $${POSTGRES_DB} > /tmp/backup_$$(date +%Y%m%d_%H%M%S).sql'
	@echo "Backup created in database container"

db-import:
	@$(DOCKER_EXEC) $(DB_CONTAINER) bash -c 'psql -U $${POSTGRES_USER} $${POSTGRES_DB} < $(file)'

db-connect:
	$(DOCKER_EXEC) $(DB_CONTAINER) psql -U $(POSTGRES_USER) -d $(POSTGRES_DB)

db-info:
	@echo "Database: $(POSTGRES_DB)"
	@echo "User: $(POSTGRES_USER)"
	@echo "Port: 5432"
	@echo "Connect: make db-connect"

test:
	$(DOCKER_EXEC) $(PHP_CONTAINER) ./vendor/bin/phpunit

test-coverage:
	$(DOCKER_EXEC) $(PHP_CONTAINER) ./vendor/bin/phpunit --coverage-html coverage

cs:
	$(DOCKER_EXEC) $(PHP_CONTAINER) ./vendor/bin/phpcs

cs-fix:
	$(DOCKER_EXEC) $(PHP_CONTAINER) ./vendor/bin/phpcbf

php-cs-fixer:
	$(DOCKER_EXEC) $(PHP_CONTAINER) ./vendor/bin/php-cs-fixer fix

psalm:
	$(DOCKER_EXEC) $(PHP_CONTAINER) ./vendor/bin/psalm

psalm-show-info:
	$(DOCKER_EXEC) $(PHP_CONTAINER) ./vendor/bin/psalm --show-info=true

analyse: cs psalm

fix: cs-fix php-cs-fixer


cache-clear:
	$(DOCKER_EXEC) $(PHP_CONTAINER) bin/console cache:clear

cc: cache-clear

assets:
	$(DOCKER_EXEC) $(PHP_CONTAINER) bin/console assets:install

warmup:
	$(DOCKER_EXEC) $(PHP_CONTAINER) bin/console cache:warmup

sf:
	$(DOCKER_EXEC) $(PHP_CONTAINER) bin/console $(cmd)


dev: up db-wait
	@echo "Development environment is running!"
	@echo "PHP: http://localhost:$(NGINX_PORT)"
	@echo "PostgreSQL: localhost:5432"
	@echo "PHP container: make shell"
	@echo "Database container: make shell-db"

qa: analyse test db-validate

db-test:
	$(DOCKER_EXEC) $(PHP_CONTAINER) bin/console doctrine:query:sql "SELECT 1"


clean: down
	$(DOCKER_COMPOSE) down -v --remove-orphans

reset: clean install


docker-build:
	$(DOCKER_COMPOSE) build

docker-rebuild:
	$(DOCKER_COMPOSE) build --no-cache

docker-clean:
	$(DOCKER_COMPOSE) down -v --remove-orphans --rmi all

deploy-prepare: test analyse db-validate
	@echo "All checks passed! Ready for deployment."

status:
	$(DOCKER_COMPOSE) ps

ps: status

vendor-clean:
	rm -rf vendor
	$(DOCKER_EXEC) $(PHP_CONTAINER) composer install

network-ls:
	docker network ls

network-inspect:
	docker network inspect nginx-php-database

db: db-migrate
migrate: db-migrate
migration: db-diff