#!/bin/bash

BASE="`dirname \"$0\"`" 

PSR1_STDOUT=`$BASE/pre-commit/psr-1.sh`
PSR1_RESULT=$?
echo "$PSR1_STDOUT"

PSR2_STDOUT=`$BASE/pre-commit/psr-2.sh`
PSR2_RESULT=$?
echo "$PSR2_STDOUT"

JSON_STDOUT=`/usr/bin/env php $BASE/pre-commit/composer-json.php`
JSON_RESULT=$?
echo "$JSON_STDOUT"

if [ 0 -ne "$PSR1_RESULT" ] ; then
    exit 1
fi

if [ 0 -ne "$PSR2_RESULT" ] ; then
    exit 1
fi

if [ 0 -ne "$JSON_RESULT" ] ; then
    exit 1
fi

exit 0