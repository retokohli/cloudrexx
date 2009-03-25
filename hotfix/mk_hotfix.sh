#!/bin/bash

find . -type f |grep -v .svn \
	| grep -v hotfix.zip |grep -v mk_hotfix.sh \
	| xargs zip hotfix.zip

