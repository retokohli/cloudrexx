#Installing a clone of Cloudrexx from GitHub
##For Cloudrexx master
These are the instructions for the installation/setup of a clone of GitHub branch or master  
1. 
   Execute `git clone https://github.com/Cloudrexx/cloudrexx.git <directory name>`
2. 
   Create a new mysql database (use **utf8_unicode_ci** as collation) and import the structure (**/installer/data/contrexx_dump_structure.sql**) and data (**/installer/data/contrexx_dump_data.sql**) into the newly created database (linux command to get a gzip with all the data for import via phpmyadmin:  
*`cat contrexx_dump_structure.sql contrexx_dump_data.sql | gzip -c > dump.gzip`*  
3. 
   Set up the configuration file (**/config/configuration.php**)  
    - set **`$_DBCONFIG['host']`**, **`$_DBCONFIG['database']`**, **`$_DBCONFIG['user']`** and **`$_DBCONFIG['password']`** to the appropriate values  
4. 
   In case you did setup Cloudrexx in a subdirectory of the webserver's *DocumentRoot*, you'll have do set the option **`RewriteBase`** in the file *.htaccess* accordingly  
5. 
   Open section *Administration > Global Configuration* in backend (http://your-cloudrexx-git-clone/cadmin/) so that the system can initialize the base configuration  

##Bash-Script for automatic installation
**This code is not thoroughly tested, you should review it and make sure it fits your development environment's needs and limitations before executing.**  

You may need to set your mysql-connection to utf8 first in /etc/mysql/my.cnf:  
```
[client]
#...
default-character-set = utf8
```
Instead of changing the mysql connection setting system-wide (my.cnf) you could also just pass --default-character-set=utf8 as an argument to mysql.  
What follows is the script. Execute this from the folder where your cloudrexx checkout lives.  

```sh
#!/bin/bash

mysql_user=<your root>
mysql_pw=<your password>
vhost=<localhost if no vhost used>
db=$(basename $(pwd)) #db and offset default to current directories name

sed -i "s/pkg.contrexxlabs.com/$vhost/g" installer/data/contrexx_dump_data.sql
sed -i "s/pkg.contrexxlabs.com/$vhost/g" config/settings.php

mysql -u$mysql_user -p$mysql_pw -e "create database $db collate utf8_unicode_ci";
cat installer/data/contrexx_dump_structure.sql installer/data/contrexx_dump_data.sql | mysql -u$mysql_user -p$mysql_pw $db
sed -i "/CONTREXX_INSTALLED/c\define(\'CONTREXX_INSTALLED\', true);" config/configuration.php
sed -i "/\\\$_DBCONFIG\\['database'\\]/c\\\\\$_DBCONFIG\\['database'\\] = '$db';" config/configuration.php
sed -i "/\\\$_DBCONFIG\\['user'\\]/c\\\\\$_DBCONFIG\\['user'\\] = '$mysql_user';" config/configuration.php
sed -i "/\\\$_DBCONFIG\\['password'\\]/c\\\\\$_DBCONFIG\\['password'\\] = '$mysql_pw';" config/configuration.php
sed -i "/\\\$_DBCONFIG\\['charset'\\]/c\\\\\$_DBCONFIG\\['charset'\\] = 'utf8';" config/configuration.php
sed -i "/\\\$_PATHCONFIG\\['ascms_root'\\]/c\\\\\$_PATHCONFIG\\['ascms_root'\\] = '/home/srz/web/root';" config/configuration.php
sed -i "/\\\$_PATHCONFIG\\['ascms_root_offset'\\]/c\\\\\$_PATHCONFIG\\['ascms_root_offset'\\] = '/$db';" config/configuration.php
sed -i "/\\\$_CONFIG\\['coreCharacterEncoding'\\]/c\\\\\$_CONFIG\\['coreCharacterEncoding'\\] = 'UTF-8';" config/configuration.php
```
