#!/bin/bash -l
PATH=~/.composer/vendor/bin:$PATH

if [ ! -f ~/.composer/vendor/bin/phpunit ]; then
  echo "Running composer installation..."
  php composer.phar global install
  php composer global require 'phpunit/phpunit=4.1.*'
fi

WPDC_PATH=`pwd`

echo "Running PHPUnit suite..."
cd "$WPDC_PATH/tests/phpunit"
phpunit --configuration ./phpunit.xml

PHPUNIT_EXIT_CODE=$?
echo "PHPUnit Exit Code: $PHPUNIT_EXIT_CODE"

echo "Running RSpec/Capybara suite..."
cd "$WPDC_PATH/tests/rspec"
bundle install
bundle config build.nokogiri --use-system-libraries
bundle exec rspec ./spec/feature/* --format='nested' --color

RSPEC_EXIT_CODE=$?
echo "RSpec Exit Code: $PHPUNIT_EXIT_CODE"

exit $[$PHPUNIT_EXIT_CODE + $RSPEC_EXIT_CODE];