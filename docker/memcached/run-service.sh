#!/bin/bash
echo \$MEM_SIZE=$MEM_SIZE
/usr/bin/memcached -u nobody -m $MEM_SIZE -p 11211