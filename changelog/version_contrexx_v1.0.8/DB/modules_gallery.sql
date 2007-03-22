--bestimmt ob Bilder in einem neuen Fenster dargestellt werden
INSERT INTO `contrexx_module_gallery_settings` ( `id` , `name` , `value` ) VALUES ( '', 'enable_popups', 'on' );  

--falls popups deaktiviert: legt die Breite der Bilder im content fest, in Pixel (höhe wird relativ angepasst)
INSERT INTO `contrexx_module_gallery_settings` ( `id` , `name` , `value` ) VALUES ( '', 'image_width', '450' );

