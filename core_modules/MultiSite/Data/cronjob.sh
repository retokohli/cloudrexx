#!/bin/bash

LOCKFILE=$0
PATH_TO_CLX=$1

if [[ ! -d "$PATH_TO_CLX" ]]; then
    echo "Usage:"
    echo "cronjob.sh <pathToMainClx>"
    exit 0
fi

# make sure script is only running once at a time
(
flock -e --timeout 300 200

if [[ "$?" == 1 ]]
then
    "ERROR: Lock could not be acquired"
    exit 1
fi

WEBSITE_LIST_FILE=`mktemp`
cd $PATH_TO_CLX

./cx MultiSite list > $WEBSITE_LIST_FILE

while read website; do
    echo "Executing Cronjobs for Website '$website'"
    ./cx MultiSite pass $website Cron
done < $WEBSITE_LIST_FILE

echo "Script terminated"
rm "$WEBSITE_LIST_FILE"

) 200<$LOCKFILE

