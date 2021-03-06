#!/bin/bash

ROOT=`git rev-parse --show-toplevel`
cd "$ROOT"

OUTPUT=$(phpcs --standard=PSR2 --report=full ./src)
RETURNED=$?

if [ 0 -ne "$RETURNED" ] ; then
    echo "There are PSR-2 code style issues."
    echo "Please correct these before commiting."
    echo "$OUTPUT"
    exit $RETURNED
fi

exit 0
