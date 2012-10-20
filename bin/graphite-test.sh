#!/bin/bash

# source: http://grantheffernan.wordpress.com/2011/06/01/sending-data-to-graphite-example-2/

# Set this hostname
HOSTNAME=`hostname --short`

# Set Graphite host
GRAPHITE=localhost
GRAPHITE_PORT=2003

# Loop forever
while :
do
	# Get epoch
	DATE=`date +%s`

	# Collect some random data for
	# this example
	MY_DATA=`php -r 'echo mt_rand(0,1000);'`

	# Send data to Graphite
	echo "stats.${HOSTNAME}.php_random ${MY_DATA} ${DATE}" | nc $GRAPHITE $GRAPHITE_PORT
done
