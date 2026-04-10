test_unit:
	@printf 'Running unit tests:\n';
	./vendor/bin/phpunit tests/Unit --testdox --display-errors --display-warnings --display-deprecations --display-phpunit-deprecations --display-notices

test_integration:
	@printf 'Running integration tests:\n';
	@echo "Note: Ensure you have the necessary environment variables set for integration tests in the .env file."
	./vendor/bin/phpunit tests/Integration --testdox --display-errors --display-warnings --display-deprecations --display-phpunit-deprecations --display-notices

test_acceptance:
	@printf 'Running acceptance tests:\n';
	./vendor/bin/phpunit tests/Acceptance --testdox --display-errors --display-warnings --display-deprecations --display-phpunit-deprecations --display-notices

test:
	@printf 'Running all tests:\n';
	make test_unit;
	make test_acceptance;
	make test_integration;

lint:
	./vendor/bin/phpcs --standard=phpcs.xml.dist --extensions=php --ignore=vendor/ .

lint_fix:
	./vendor/bin/phpcbf --standard=phpcs.xml.dist --extensions=php --ignore=vendor/ .


setup-git-hooks:
	@echo "Setting up Git hooks..."
	git config core.hooksPath .githooks
	chmod +x .githooks/pre-commit
