# Executables (local)
SAIL = sh vendor/bin/sail

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
.PHONY        = help build up start down logs sh composer vendor lr cc analyse db seed tests rm

## —— 👨‍🎨 🐳 The Laravel-docker Makefile 🐳 👨‍🎨 ——————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker with Sail ⛵️ ————————————————————————————————————————————————————————————————
up: ## Start the docker container in detached mode
	@$(SAIL) up --detach

down: ## Stop the docker container
	@$(SAIL) stop

logs: ## Show live logs
	@$(SAIL) logs --tail=0 --follow

sh: ## Connect to the Sail shell
	@$(SAIL) shell

tinker: ## Connect to the PHP FPM container
	@$(SAIL) tinker

## —— Laravel 👨‍🎨 ———————————————————————————————————————————————————————————————
lr: ## List all Laravel commands or pass the parameter "c=" to run a given command, example: make lr c="about"
	@$(eval c ?=)
	@$(LARAVEL) $(c)

cc: c=c:c ## Clear the cache
cc: lr

ci: ## Clear all non-core uploaded and stored files
	rm -rf app/public/uploads/users
	rm -rf app/public/storage/bills
	rm -rf app/public/storage/partners
	rm -rf app/public/storage/projects
	rm -rf app/public/storage/studies

analyse: ## Perform a static PHP code test of the project, see config file ./src/phpstan.neon
	@$(PHPSTAN) analyse

tests: ## Launch PHPUnit tests and pass "c=" to run a given command, exemple: make test c="--group auth"
	@$(eval c ?=)
	@$(PHPUNIT) $(c) --verbose

## —— Database 💾 ———————————————————————————————————————————————————————————————
db: ## Create and migrate database
	@$(LARAVEL) doctrine:database:create -n --if-not-exists
	@$(LARAVEL) doctrine:migrations:migrate -n

seed: ## Seed database with fake data
seed: ci
seed: drop
seed: db
seed: c=hautelook:fixtures:load --no-bundles -n
seed: lr

drop: ## Drop the database
	@$(LARAVEL) doctrine:database:drop --force --if-exists

## —— CI/CD 📦 ———————————————————————————————————————————————————————————————
dev: ## Deploy a new application version on staging
	rsync -av ./app/ $(SSH):$(DEV-PATH) \
		--delete

production: ## Deploy a new application version on production
	rsync -av ./app/ $(SSH):$(PROD-PATH) \
		--delete
	ssh $(SSH) "cd $(PROD-PATH) ; $(PHP-PATH) bin/console c:c"
