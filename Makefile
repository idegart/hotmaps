start: build test

build:
	composer install
test:
	./vendor/bin/phpunit tests