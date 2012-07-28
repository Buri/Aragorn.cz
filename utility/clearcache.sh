#!/bin/bash
HOST=localhost
PORT=11211

BASEDIR=$(./basedir.sh)
TMPDIR="$BASEDIR/temp/*"

# RUN
echo "Flushing memcached..."
OUT=$(echo "flush_all" | /bin/netcat -q 2 $HOST $PORT)
echo "Memcached:" $OUT

echo "Flushing redis..."
OUT=$(redis-cli flushdb)
echo "Redis:" $OUT

# Clear File Cache
echo "Clearing file cache in $TMPDIR..."
find $TMPDIR -delete 2> /dev/null
if [ $? -eq 0 ] 
then 
    echo "File cache cleaned." ;
else
    echo "Error deleting files."
fi
