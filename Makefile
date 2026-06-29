SHELL := /bin/bash

DOCKER_COMP = docker-compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec -u 1000 app

E2E_DATABASE_URL = mysql://live-grid_user:live-grid_pass@db_e2e:3306/live-grid_e2e?serverVersion=mariadb-10.2.19&charset=utf8mb4

.PHONY: deploy deploy-local up up_visible down build bash install test static migrate-test migrate-e2e playwright-install e2e e2e-suite e2e-file up-e2e

deploy:
	./deploy.sh

deploy-local:
	./deploy.sh local

up:
	@$(DOCKER_COMP) up -d --remove-orphans

up_visible:
	@$(DOCKER_COMP) up --remove-orphans

down:
	@$(DOCKER_COMP) down

build:
	@$(DOCKER_COMP) build

bash:
	@$(PHP_CONT) bash

static:
	@$(PHP_CONT) php bin/console cache:warmup --env=dev --no-interaction --quiet
	@$(PHP_CONT) php bin/console cache:warmup --env=test --no-interaction --quiet
	@$(PHP_CONT) vendor/bin/phpstan analyse --memory-limit=512M

install:
	@$(PHP_CONT) composer install

test:
	@$(PHP_CONT) vendor/bin/phpunit

migrate-test:
	@$(PHP_CONT) php bin/console doctrine:migrations:migrate --no-interaction --env=test

migrate-e2e:
	@$(PHP_CONT) env DATABASE_URL="$(E2E_DATABASE_URL)" php bin/console doctrine:migrations:migrate --no-interaction --env=test

playwright-install:
	docker-compose exec -u 0 app sh -lc "mkdir -p /ms-playwright && chown -R 1000:1000 /ms-playwright"
	docker-compose exec app vendor/bin/playwright-install --browsers

e2e:
	docker-compose exec app env PLAYWRIGHT_E2E=1 vendor/bin/phpunit -c phpunit.e2e.xml

e2e-suite:
	docker-compose exec app env PLAYWRIGHT_E2E=1 vendor/bin/phpunit -c phpunit.e2e.xml --testsuite "$(SUITE)"

e2e-file:
	docker-compose exec app env PLAYWRIGHT_E2E=1 vendor/bin/phpunit -c phpunit.e2e.xml $(FILE)

up-e2e:
	docker-compose -f docker-compose.yml -f docker-compose.e2e.yml up -d
