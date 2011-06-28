#!/bin/bash
cd $(dirname $0)
dir=$(pwd)
cd PHPUnit
php ../cx_bootstrap.php $dir/tests