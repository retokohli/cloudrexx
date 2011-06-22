#!/bin/bash
cd $(dirname $0)
dir=$(pwd)
cd PHPUnit
php phpunit.php $dir/tests