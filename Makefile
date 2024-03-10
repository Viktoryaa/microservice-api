-include .env

APP_NAME ?= APP_NAME

.PHONY : run
run:
	@make docker-build
	@make docker-up
	@make migrate


.PHONY : docker-build
docker-build:
	docker-compose build

.PHONY : docker-up
docker-up:
	docker-compose up -d

.PHONY : migrate
migrate:
	docker-compose exec microservice-api_back php artisan migrate		
