###> symfony/framework-bundle ###
CONSOLE := $(shell which bin/console)

console:
ifndef CONSOLE
	@printf "Run \033[32mcomposer require cli\033[39m to install the Symfony console.\n"
endif
.PHONY: console

.DEFAULT_GOAL := help
help:
	@grep -E '^[a-zA-Z-]+:.*?## .*$$' Makefile | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "[32m%-17s[0m %s\n", $$1, $$2}'
.PHONY: help

cache-clear: ## Clears the cache
ifdef CONSOLE
	@printf "Find console \n"
	@bin/console cache:clear --no-warmup
else
	@rm -rf var/cache/*
endif
.PHONY: cache-clear

echo:
	@echo $PWD

composer-update: ## Composer update
	@docker run --rm --interactive --tty \
        --volume $(PWD):/app \
        --volume $(SSH_AUTH_SOCK):/ssh-auth.sock \
        --env SSH_AUTH_SOCK=/ssh-auth.sock \
        composer update --ignore-platform-reqs --no-scripts

composer-install: ## Composer install
	@docker run --rm --interactive --tty \
		--volume $(PWD):/app \
		--volume $(SSH_AUTH_SOCK):/ssh-auth.sock \
		--env SSH_AUTH_SOCK=/ssh-auth.sock \
		composer install --optimize-autoloader --no-scripts --ignore-platform-reqs --apcu-autoloader

cache-warmup: cache-clear ## Warms up an empty cache
ifdef CONSOLE
	@bin/console cache:warmup
else
	@printf "Cannot warm up the cache (needs symfony/console).\n"
endif
.PHONY: cache-warmup
