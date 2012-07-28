#!/bin/bash
# Find project base directory
BASEDIR=/home/domeny/aragorn.cz/web/subdomeny/four
HOST=$(hostname)
[ $HOST == 'test' ] && BASEDIR=/var/www
echo $BASEDIR