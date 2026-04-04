test_unit:
	@printf 'Running unit tests:\n';
	./vendor/bin/phpunit tests/Unit --testdox --display-errors --display-warnings --display-deprecations --display-phpunit-deprecations --display-notices

test_integration:
	@printf 'Running integration tests:\n';
	@echo "Note: Ensure you have the necessary environment variables set for integration tests in the .env file."
	./vendor/bin/phpunit tests/Integration

lint:
	./vendor/bin/phpcs --standard=PSR12 --extensions=php --ignore=vendor/ .

lint_fix:
	./vendor/bin/phpcbf --standard=PSR12 --extensions=php --ignore=vendor/ .
