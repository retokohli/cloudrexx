module_repository

# backend area navigation
UPDATE `contrexx_backend_areas` SET `uri` = 'index.php?cmd=content&amp;act=new' WHERE `area_id` =5 LIMIT 1 ;
UPDATE `contrexx_backend_areas` SET `uri` = 'index.php?cmd=media&amp;archive=archive1' WHERE `area_id` =7 LIMIT 1 ;
UPDATE `contrexx_backend_areas` SET `uri` = 'index.php?cmd=media&amp;archive=content' WHERE `area_id` =32 LIMIT 1 ;