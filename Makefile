DOCKER_COMPOSE=docker-compose
EXEC_RUNCITY=docker exec $$RUNCITY_CONTAINER_NAME

-include .env
export

.PHONY: init
init: build up composer_install create_db migrate

.PHONY: up
up:
	$(DOCKER_COMPOSE) up -d

.PHONY: down
down:
	$(DOCKER_COMPOSE) down

.PHONY: restart
restart: down up

.PHONY: build
build:
	$(DOCKER_COMPOSE) build --force-rm

.PHONY: composer_install
composer_install:
	$(EXEC_RUNCITY) composer install -n

.PHONY: migrate
migrate:
	$(EXEC_RUNCITY) php app/consoles doctrine:migrations:migrate -n

.PHONY: exec
exec:
	docker exec -it $$RUNCITY_CONTAINER_NAME bash

.PHONY: exec_db
exec_db:
	docker exec -it $$RUNCITY_CONTAINER_NAME_DB bash

.PHONY: create_db
create_db:
	$(EXEC_RUNCITY) php app/console doctrine:schema:update --force

.PHONY: import_street
import_street:
	$(EXEC_RUNCITY) mysql -u$MYSQL_USER -p$MYSQL_PASSWORD -hruncity_db $MYSQL_DATABASE < MoscowStreet.sql

.PHONY: import_kladr
import_kladr:
	$(EXEC_RUNCITY) mysql -u$$MYSQL_USER -p$$MYSQL_PASSWORD -h$$RUNCITY_CONTAINER_NAME_DB $$MYSQL_DATABASE < KladrRegion.sql
