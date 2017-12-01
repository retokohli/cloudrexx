# Cloudrexx #
Cloudrexx is an open source PHP based web customer experience management system released under the GNU AGPL.

## Installation ##
These are the instructions for the installation/setup of a clone of GitHub branch or master  
**Important**: It is currently impossible to install Cloudrexx in a sub-directory. If you want to stay updated please visit the Bug-Ticket [2707](http://bugs.cloudrexx.com/cloudrexx/ticket/2707). Thank you for your understanding.   

1. Execute `git clone https://github.com/Cloudrexx/cloudrexx.git <directory name>`  
2. Create a new mysql database (use **utf8_unicode_ci** as collation) and import the structure (**/installer/data/contrexx_dump_structure.sql**) and data (**/installer/data/contrexx_dump_data.sql**) into the newly created database:
```bash
DbName="<databaseName>";mysql -u<username> -p -e \
"CREATE DATABASE $DbName COLLATE utf8_unicode_ci;
USE $DbName;
SOURCE installer/data/contrexx_dump_structure.sql;
SOURCE installer/data/contrexx_dump_data.sql;"
```
3. Create a default user by executing the following queries in the created database:
```bash
DbName="<databaseName>";ClxMail="<mail>";ClxPass="<password>";mysql -u<username> -p $DbName -e \
"INSERT INTO contrexx_access_users (id, is_admin, username, password, regdate, expiration, validity, last_auth, last_activity, email, email_access, frontend_lang_id, backend_lang_id, active, profile_access, restore_key, restore_key_time, u2u_active, auth_token) VALUES (1,1,'system',MD5('$ClxPass'),0,0,0,0,0,'$ClxMail','nobody', 0,0,1,'members_only','',0,'0', '');
INSERT INTO contrexx_access_user_profile (user_id, gender, title, firstname, lastname, company, address, city, zip, country, phone_office, phone_private, phone_mobile, phone_fax, birthday, website, profession, interests, signature, picture) VALUES (1,'gender_undefined',2,'CMS','System Benutzer','','','','',0,'','','','','','','','','','');
INSERT INTO contrexx_access_user_attribute_value (attribute_id, user_id, history_id, value) VALUES (0,1,0,'');"
```
4. 
   Set up the configuration file (**/config/configuration.php**)  
    - set **`$_DBCONFIG['host']`**, **`$_DBCONFIG['database']`**, **`$_DBCONFIG['user']`** and **`$_DBCONFIG['password']`** to the appropriate values
    - set the constant **`CONTREXX_INSTALLED`** to **true**
5. 
   In case you did setup Cloudrexx in a subdirectory of the webserver's *DocumentRoot*, you'll have do set the option **`RewriteBase`** in the file *.htaccess* accordingly  
6. 
   Open section *Administration > Global Configuration* in backend (http://your-cloudrexx-git-clone/cadmin/) so that the system can initialize the base configuration  

## Bugtracker ##
Bugs are tracked on [bugs.cloudrexx.com](http://bugs.cloudrexx.com).  

## Development and Contribution ##
* [Development Documentation & Guidelines](http://wiki.contrexx.com/en/index.php?title=Portal:Development)
* [Community Platform](https://www.cloudrexx.com/community)
* [API Documentation](http://api.cloudrexx.com)

## License ##
Cloudrexx  
http://www.cloudrexx.com  
Cloudrexx AG 2007-2017  
 
According to our dual licensing model, this program can be used either under the terms of the GNU Affero General Public License, version 3, or under a proprietary license.  

The texts of the GNU Affero General Public License with an additional permission and of our proprietary license can be found at and in the LICENSE file you have received along with this program.  

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.  

"Cloudrexx" is a registered trademark of Cloudrexx AG. The licensing of the program under the AGPLv3 does not imply a trademark license. Therefore any rights, title and interest in our trademarks remain entirely with us.  
