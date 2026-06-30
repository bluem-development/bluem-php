# Executables and shared options
PHP := php
PHPUNIT := ./vendor/bin/phpunit
RECTOR := ./vendor/bin/rector
PHPCS := ./vendor/bin/phpcs
PHPCBF := ./vendor/bin/phpcbf
PHPUNIT_FLAGS := --testdox --display-errors --display-warnings --display-deprecations --display-phpunit-deprecations --display-notices
PHPCS_FLAGS := --standard=phpcs.xml.dist --extensions=php --ignore=vendor/

# Default target
all: help

# Tests
test_unit:
	@printf 'Running unit tests:\n'
	$(PHPUNIT) tests/Unit $(PHPUNIT_FLAGS)

test_integration:
	@printf 'Running integration tests:\n'
	@echo "Note: ensure the required integration variables are set in .env."
	$(PHPUNIT) tests/Integration $(PHPUNIT_FLAGS)

test_acceptance:
	@printf 'Running acceptance tests:\n'
	$(PHPUNIT) tests/Acceptance $(PHPUNIT_FLAGS)

test:
	@printf 'Running all tests:\n'
	$(MAKE) test_unit
	$(MAKE) test_acceptance
	$(MAKE) test_integration

# Code style
lint:
	@echo "Running PHP CodeSniffer..."
	$(PHPCS) $(PHPCS_FLAGS) .

lint_fix:
	@echo "Fixing PHP CodeSniffer issues..."
	$(PHPCBF) $(PHPCS_FLAGS) .

lint-fix: lint_fix

# Refactoring
rector:
	@echo "Running Rector dry-run..."
	$(RECTOR) process src --dry-run --clear-cache --config rector.php

rector_fix:
	@echo "Applying Rector refactoring..."
	$(RECTOR) process src --clear-cache --config rector.php

rector-fix: rector_fix

# Utilities
check: lint test_unit

clean:
	@echo "Cleaning local tool caches..."
	rm -rf .rector/cache .phpunit.result.cache .phpcs.cache

setup-git-hooks:
	@echo "Setting up Git hooks..."
	git config core.hooksPath .githooks
	chmod +x .githooks/pre-commit

help:
	@echo "Available targets:"
	@echo "  test_unit         Run unit tests"
	@echo "  test_integration  Run integration tests; requires .env"
	@echo "  test_acceptance   Run acceptance tests"
	@echo "  test              Run unit, acceptance, and integration tests"
	@echo "  lint              Run PHP CodeSniffer"
	@echo "  lint_fix          Auto-fix code style issues"
	@echo "  rector            Run Rector in dry-run mode"
	@echo "  rector_fix        Apply Rector refactoring"
	@echo "  check             Run lint and unit tests"
	@echo "  clean             Remove local tool caches"
	@echo "  setup-git-hooks   Configure repository Git hooks"

.PHONY: all test test_unit test_integration test_acceptance lint lint_fix lint-fix rector rector_fix rector-fix check clean setup-git-hooks help
