#!/bin/bash -l
PATH=~/.composer/vendor/bin:$PATH

if [ ! -f ~/.composer/vendor/bin/phpunit ]; then
  echo "Running composer installation..."
  php composer.phar global install
  php composer.phar global require 'phpunit/phpunit=4.1.*'
fi

WPDC_PATH=`pwd`

echo "Running PHPUnit suite..."
phpunit --configuration $WPDC_PATH/tests/phpunit/phpunit.xml

PHPUNIT_EXIT_CODE=$?
echo "PHPUnit Exit Code: $PHPUNIT_EXIT_CODE"

echo "Running RSpec/Capybara suite..."
cd $WPDC_PATH/tests/rspec

bundle install --path $WPDC_PATH/tests/rspec/vendor/bundle
./bin/rspec spec --format nested --color

RSPEC_EXIT_CODE=$?
echo "RSpec Exit Code: $PHPUNIT_EXIT_CODE"

exit $[$PHPUNIT_EXIT_CODE + $RSPEC_EXIT_CODE];