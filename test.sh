#!/bin/sh

composer validate

bin/console lint:container
bin/console lint:twig templates
bin/console lint:yaml config
yarn lint
vendor/bin/phpcs
vendor/bin/psalm --no-cache

rm -f var/app.db
bin/console doctrine:schema:create --env=test
bin/phpunit
