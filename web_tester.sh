#!/bin/bash

# Look for PHPUnit everywhere
export PATH=".:vendor/bin:$PATH"

# Use the site from the command line argument
if [ ! -z "$1" ]
then
	export WEB_TESTER_SITE="$1"
fi

phpunit --debug
