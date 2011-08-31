#!/bin/bash
cd $(dirname $0)
dir=$(pwd)

cd ../config
if [ ! -f configuration_testing.php ]; then #we have to create the test config
    echo "could not find test config, creating it..."
    awk -f ../testing/createTestConfig.awk configuration.php > configuration_testing.php
fi

echo "activating test configuration"
mv configuration.php configuration_original.php
mv configuration_testing.php configuration.php


echo "starting unit testing"
cd $dir
cd PHPUnit
php ../cx_bootstrap.php $dir/tests $1 $2

cd $dir
cd ../config
echo "deactivating test configuration"
mv configuration.php configuration_testing.php
mv configuration_original.php configuration.php
