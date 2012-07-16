#!/bin/bash
HOST=localhost
PORT=11211

# RUN
echo "Flushing database..."
OUT=$(echo "flush_all" | /bin/netcat -q 2 $HOST $PORT)
echo "Memcached:" $OUT