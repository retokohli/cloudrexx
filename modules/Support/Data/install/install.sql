# Insert Module into Modules table
INSERT INTO `contrexx_modules` (`id`, `name`, `distributor`, `description_variable`, `status`, `is_required`, `is_core`, `is_active`, `is_licensed`) VALUES ('100', 'Support', 'Comvation AG', 'TXT_MODULE_SUPPORT_DESCRIPTION', 'y', '1', '0', '1', '1');

# Insert Modul into components table
INSERT INTO `contrexx_component` (`id`, `name`, `type`) VALUES ('100', 'Support', 'module');

# Insert Menu table
INSERT INTO `contrexx_backend_areas` (`area_id`, `parent_area_id`, `type`, `scope`, `area_name`, `is_active`, `uri`, `target`, `module_id`, `order_id`, `access_id`) VALUES (NULL, '2', 'navigation', 'global', 'TXT_MODULE_SUPPORT', '1', 'index.php?cmd=Support', '_self', '100', '15', '899');