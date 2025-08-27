.PHONY: lint lint-php phpstan

lint-php:
	find . -name '*.php' -print0 | xargs -0 -n1 php -l

phpstan:
	vendor/bin/phpstan analyse -l 0

lint: lint-php phpstan
