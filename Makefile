SHELL := /bin/bash

DOCKER_COMP = docker-compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec -u 1000 app

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
	@$(PHP_CONT) vendor/bin/phpstan analyse --memory-limit=512M
