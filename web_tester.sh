#!/bin/bash

# Look for PHPUnit everywhere
export PATH=".:../../bin:vendor/bin:$PATH"

# Go to the Web Tester folder for all configuration files
cd $(readlink -f $0)

# Use the site from the command line argument
if [ ! -z "$1" ]
then
	export WEB_TESTER_SITE="$1"
fi

phpunit 
