#!/bin/bash
FILE="__minified.js"
DIR="../web/assets/js/"
OUT="./out/$FILE"
php -f join.php $(find $DIR -type f) > $OUT 