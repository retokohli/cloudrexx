#!/bin/bash
user="root"
pass="1234"

cd $(dirname $0)
dir=$(pwd)
db=$(awk -f getDBName.awk ../config/configuration.php)
testdb=$db"_testing"

echo "dumping original db..."
mysqldump -u$user -p$pass $db > /tmp/cxdb.sql 2>/dev/null
if [ "$?" != "0" ]; then
    echo "could not dump db, check user and pass provided in script."
    rm /tmp/cxdb.sql
    exit
fi

echo "creating test db..."
mysql -uroot -p1234 -e"DROP DATABASE $testdb" 2>/dev/null
mysql -uroot -p1234 -e"CREATE DATABASE $testdb"
echo "initializing test db..."
mysql -uroot -p1234 $testdb < /tmp/cxdb.sql
rm /tmp/cxdb.sql
echo "truncating pages, nodes, log entries..."
mysql -u$user -p$pass $testdb -e'truncate contrexx_pages; truncate contrexx_nodes; truncate contrexx_ext_log_entries;'
