#!/bin/bash

# PHP syntax error checker
find ./src/ ./tests/ -name '*.php' -print0 | xargs -0 -n1 php -lf

# PHP code style checker
vendor/bin/phpcs --standard=PSR1,PSR2 --extensions=php ./src/ ./tests/

# Psalm
vendor/bin/psalm

# PHPUnit unit tests
vendor/bin/phpunit
