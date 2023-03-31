.PHONY: help
help:
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' ${MAKEFILE_LIST}

.DEFAULT_GOAL := help

up: ## Up docker containers with app
	docker-compose up -d

down: ## Down containers
	docker-compose down

install: ## Setup app before use it
	if [ ! -f .env ] ; then \
		cp .env.example .env \
	; fi
