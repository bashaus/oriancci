#!/bin/bash

ROOT=`git rev-parse --show-toplevel`
BASE="`dirname \"$0\"`" 

# Check PSR-1 standard
PSR1_STDOUT=`$BASE/pre-commit/psr-1.sh`
PSR1_RESULT=$?
echo "$PSR1_STDOUT"

# Check PSR-2 standard
PSR2_STDOUT=`$BASE/pre-commit/psr-2.sh`
PSR2_RESULT=$?
echo "$PSR2_STDOUT"

# Check the JSON file
JSON_STDOUT=`/usr/bin/env php $BASE/pre-commit/composer-json.php`
JSON_RESULT=$?
echo "$JSON_STDOUT"

# Generate code coverage report
`/usr/bin/env php phpunit.phar --coverage-html $ROOT/tests/Oriancci/coverage tests/`

# Exit with an error if one exists
if [ 0 -ne "$PSR1_RESULT" ] ; then
    exit 1
fi

if [ 0 -ne "$PSR2_RESULT" ] ; then
    exit 1
fi

if [ 0 -ne "$JSON_RESULT" ] ; then
    exit 1
fi

# Exit with no errors
exit 0