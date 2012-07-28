#!/bin/bash

BASEDIR=/home/domeny/aragorn.cz/web/subdomeny/four
HOST=$(hostname)
[ $HOST == 'test' ] && BASEDIR=/var/www
INDIR="$BASEDIR/utility/in"
OUTDIR="$BASEDIR/utility/out"
VERSIONFILE="$BASEDIR/utility/version"

VERSION=$(cat $VERSIONFILE)
let "VERSION = VERSION + 1" > /dev/null
#echo $VERSION;
echo $VERSION > $VERSIONFILE

FILE="__minified-0.$VERSION.js"
OUT="$OUTDIR/$FILE"

echo "Building minified javascript..."
echo "Source directory: $INDIR"
echo "Output: $OUT"

FILES=$(find $INDIR -type f | sort)
NUMFILES=$(echo $FILES | wc -w)
echo "Building $NUMFILES files..."

php -f join.php $FILES > $OUT 

echo "Build done."

if [ $1 == '-i' ]
then
    echo "Installing..."
    CPYDIR="$BASEDIR/web/assets/js"
    cp $OUT $CPYDIR
    echo "Installed"
fi
echo "Done."