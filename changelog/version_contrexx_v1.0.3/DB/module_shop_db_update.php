<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Shop Update from Version 1.0.2 to 1.0.3</title>
</head>
<body>
<?php
/**

	Plazieren Sie diese Datei in das Hauptverzeichniss Ihrer Contrexx Installation
	und klicken Sie anschliessend auf "Update ausführen".

*/
if (!@include_once('config/configuration.php')) {
	print "Plazieren Sie diese Datei in das Hauptverzeichniss Ihrer Contrexx Installation.";
} else {
?>
<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
<input type="submit" name="doUpdate" value="Update ausführen" />
</form>
<?php
	if (!isset($_POST['doUpdate'])) {
		exit;
	}
	require_once ASCMS_CORE_PATH.'/API.php';
	
	$errorMsg = '';
	$objDatabase = getDatabaseObject($errorMsg);
	
	$objDatabase->debug = true;
	
	$arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."module_shop_order_items");
	if (!in_array("order_items_id", $arrColumns)) {
		if ($objDatabase->Execute("ALTER TABLE `".DBPREFIX."module_shop_order_items` DROP PRIMARY KEY") !== false) {
			if ($objDatabase->Execute("ALTER TABLE `".DBPREFIX."module_shop_order_items` ADD `order_items_id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST") !== false) {
				if ($objDatabase->Execute("ALTER TABLE `".DBPREFIX."module_shop_order_items_attributes` ADD `order_items_id` INT UNSIGNED NOT NULL AFTER `orders_items_attributes_id`") !== false) {
					$objResult = $objDatabase->Execute("SELECT order_items_id, orderid, productid FROM ".DBPREFIX."module_shop_order_items");
					if ($objResult !== false) {
						while (!$objResult->EOF) {
							if ($objDatabase->Execute("UPDATE ".DBPREFIX."module_shop_order_items_attributes SET order_items_id=".$objResult->fields['order_items_id']." WHERE order_id=".$objResult->fields['orderid']." AND product_id=".$objResult->fields['productid']) === false) {
								print "Bei den Datensätzen in der Tabelle <b>".DBPREFIX."module_shop_order_items_attributes</b> mit der order_id ".$objResult->fields['orderid']." und der product_id ".$objResult->fields['productid']." konnte dem Feld order_items_id nicht den Wert ".$objResult->fields['order_items_id']." zugewisen werden!<br />\n";
							}
							$objResult->MoveNext();
						}
					}
				}
			}
		}
	} else {
		die("Update wurde bereits durchgeführt!");
	}
	
	print "Update-Prozess beendet";
}
?>
</body>
</html>