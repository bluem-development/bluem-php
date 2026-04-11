# Default target when running just `make`
.DEFAULT_GOAL := help

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "%-20s %s\n", $$1, $$2}'


test_unit: ## Run unit tests
	@printf 'Running unit tests:\n';
	./vendor/bin/phpunit tests/Unit --testdox --display-errors --display-warnings --display-deprecations --display-phpunit-deprecations --display-notices

test_integration: ## Run integration tests
	@printf 'Running integration tests:\n';
	@echo "Note: Ensure you have the necessary environment variables set for integration tests in the .env file."
	./vendor/bin/phpunit tests/Integration --testdox --display-errors --display-warnings --display-deprecations --display-phpunit-deprecations --display-notices

test_acceptance: ## Run acceptance tests
	@printf 'Running acceptance tests:\n';
	./vendor/bin/phpunit tests/Acceptance --testdox --display-errors --display-warnings --display-deprecations --display-phpunit-deprecations --display-notices

test: ## Run all tests
	@printf 'Running all tests:\n';
	make test_unit;
	make test_acceptance;
	make test_integration;

lint: ## Run PHP CodeSniffer to check code style
	./vendor/bin/phpcs --standard=phpcs.xml.dist --extensions=php --ignore=vendor/ .

lint_fix: ## Run PHP Code Beautifier and Fixer to automatically fix code style issues
	./vendor/bin/phpcbf --standard=phpcs.xml.dist --extensions=php --ignore=vendor/ .


setup-git-hooks: ## Set up Git hooks for the project
	@echo "Setting up Git hooks..."
	git config core.hooksPath .githooks
	chmod +x .githooks/pre-commit
