#!/bin/bash

LOCKFILE=/tmp/cronjob.lock
WEBSITE_LIST_FILE=/tmp/websiteList.txt
PATH_TO_CLX=/var/www/cloudrexx/git/cloudrexx

# make sure script is only running once at a time
(
flock -e --timeout 300 200

if [[ "$?" == 1 ]]
then
    "ERROR: Lock could not be acquired"
    exit 1
fi

cd $PATH_TO_CLX

./cx MultiSite list > $WEBSITE_LIST_FILE

while read website; do
    ./cx MultiSite pass $website Cron
done < $WEBSITE_LIST_FILE

) 200>$LOCKFILE

