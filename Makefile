# Executables (local)
SAIL = sh vendor/bin/sail
DOCKER = docker-compose

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
LARAVEL  = $(PHP_CONT) bin/console
PHPSTAN  = $(PHP_CONT) vendor/bin/phpstan
PHPUNIT  = $(PHP_CONT) bin/phpunit

# Serveur
SSH          = 
DEV-PATH     = 
STAGING-PATH = 
PROD-PATH    = 
PHP-PATH     = 

# Misc
.DEFAULT_GOAL = help
.PHONY        = help up down logs sh tinker cc ci analyse tests seed drop dev production

## —— 👨‍🎨 🐳 The Laravel-docker Makefile 🐳 👨‍🎨 ——————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
up: ## Start the docker container in detached mode
	@$(SAIL) up --detach

down: ## Stop the docker container
	@$(SAIL) stop

logs: ## Show live logs
	@$(DOCKER) logs laravel.test --tail=0 --follow

sh: ## Connect to the Sail shell
	@$(SAIL) shell

tinker: ## Connect to the PHP FPM container
	@$(SAIL) tinker

## —— Laravel with Sail ⛵️ ———————————————————————————————————————————————————————————————
cc: ## Clear the cache
	@$(SAIL) artisan config:clear
	@$(SAIL) artisan cache:clear
	@$(SAIL) artisan view:clear

ci: ## Clear all non-core uploaded and stored files
	rm -rf storage/app/uploads/stories

analyse: ## Perform a static PHP code test of the project, see config file ./src/phpstan.neon
	@$(PHPSTAN) analyse --memory-limit=2G

tests: ## Launch PHPUnit tests and pass "c=" to run a given command, exemple: make test c="--group auth"
	@$(PHPUNIT) --verbose

## —— Database 💾 ———————————————————————————————————————————————————————————————
seed: ## Seed database with fake data
seed: ci
	@$(SAIL) artisan migrate:fresh --seed

## —— CI/CD 📦 ———————————————————————————————————————————————————————————————
dev: ## Deploy a new application version on staging
	rsync -av ./app/ $(SSH):$(DEV-PATH) \
		--delete

production: ## Deploy a new application version on production
	rsync -av ./app/ $(SSH):$(PROD-PATH) \
		--delete
	ssh $(SSH) "cd $(PROD-PATH) ; $(PHP-PATH) bin/console c:c"
