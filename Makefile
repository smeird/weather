.PHONY: lint lint-php phpstan

PHP_FILES := $(shell find . -name '*.php' -not -path './vendor/*')

lint-php:
	printf '%s\n' $(PHP_FILES) | xargs -n1 php -l

phpstan:
	vendor/bin/phpstan analyse -l 0 $(PHP_FILES)

lint: lint-php phpstan
