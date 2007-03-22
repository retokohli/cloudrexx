<?php
function _shopUpdate() {
	global $objDatabase;
	require_once(ASCMS_FRAMEWORK_PATH.DIRECTORY_SEPARATOR.'File.class.php');
	require_once(ASCMS_FRAMEWORK_PATH.DIRECTORY_SEPARATOR.'Image.class.php');
	$shopImagePath = ASCMS_SHOP_IMAGES_PATH.'/';
   	$shopImageWebPath = ASCMS_SHOP_IMAGES_WEB_PATH.'/';

	$arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."module_shop_products");
	if (!is_array($arrColumns)) {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_shop_products konnte nicht ermittelt werden!";
		return false;
	}

	if (!in_array('handler', $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_shop_products` ADD `handler` ENUM( 'none', 'delivery', 'download' ) NOT NULL DEFAULT 'delivery' AFTER `catid`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	} else {
		$query = "ALTER TABLE `".DBPREFIX."module_shop_products` CHANGE `handler` `handler` ENUM( 'none', 'delivery', 'download' ) NOT NULL DEFAULT 'delivery'";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$query = "SELECT 1 FROM ".DBPREFIX."module_shop_config WHERE `name` = 'paypal_default_currency'";
	$objResult = $objDatabase->SelectLimit($query, 1);
	if ($objResult) {
		if ($objResult->RecordCount() == 0) {
			$query = "INSERT INTO `".DBPREFIX."module_shop_config` ( `id` , `name` , `value` , `status` ) VALUES ( NULL , 'paypal_default_currency', 'EUR', '1' )";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$objFile = &new File();

    $query = "DESCRIBE `".DBPREFIX."module_shop_products` `picture`";
	if(($objResult = $objDatabase->Execute($query)) !== false){
	    if($objResult->fields['Type'] != 'text'){
	        $query = "ALTER TABLE `".DBPREFIX."module_shop_products` CHANGE `picture` `picture` TEXT NULL ";
	        if($objDatabase->Execute($query) === false){
	            return _databaseError($query, $objDatabase->ErrorMsg());
	        }
	    }
	}else{
	    return _databaseError($query, $objDatabase->ErrorMsg());
	}


	$query = "SELECT `id`, `picture` FROM `".DBPREFIX."module_shop_products`";
	$objResult = $objDatabase->Execute($query);
	if($objResult !== false){
	    if($objResult->RecordCount() > 0){
	        while(!$objResult->EOF){
	            if(empty($objResult->fields['picture']) || !file_exists($shopImagePath.$objResult->fields['picture'])){
	                $fileDecoded = base64_decode(substr($objResult->fields['picture'],0,strpos($objResult->fields['picture'], '?')));
	                $fileDecoded = basename($fileDecoded);
	                if(!file_exists($shopImagePath.$fileDecoded)){
        	            $objResult->MoveNext();
        	            continue;
	                }else{
	                   if(!empty($fileDecoded) && !file_exists($shopImagePath.$fileDecoded.'.thumb')){
	                       if(!_createThumb($shopImagePath, $shopImageWebPath, $fileDecoded)){
	       	                   echo "Konnte Thumbnail für ".$shopImageWebPath.$fileDecoded." nicht erstellen.<br />";
//    	       	                   return false;  //für di behinderte hoster isch ds uskommentiert
	                       }else{
	                           $objResult->fields['picture'] = $fileDecoded;
	                       }
	                   }else{
	                       if(strpos($fileDecoded, '.') !== false){
	                           $objResult->fields['picture'] = $fileDecoded;
	                       }
	                   }
	                }
	            }else{
    	            $objFile->setChmod($shopImagePath, $shopImageWebPath, $objResult->fields['picture']);
    	            if(!file_exists($shopImagePath.$objResult->fields['picture'].'.thumb')){
    	               if(!_createThumb($shopImagePath, $shopImageWebPath, $objResult->fields['picture'])){
    	                   echo "Konnte Thumbnail für ".$shopImageWebPath.$objResult->fields['picture']." nicht erstellen<br />";
//        	                   return false;  //für di behinderte hoster isch ds uskommentiert
    	               }
    	            }
	            }
                if((list($width, $height) = getimagesize($shopImagePath.$objResult->fields['picture'])) === false){
                    echo "WARNUNG: Das Bild ".$shopImageWebPath.$objResult->fields['picture']." ist in der Datenbank eingetragen, aber existiert nicht mehr!<br />";
                }
                $shopImageName = base64_encode($shopImageWebPath.$objResult->fields['picture'])
	              .'?'.base64_encode($width)
	              .'?'.base64_encode($height)
	              .':??:??';
	            $query = "UPDATE ".DBPREFIX."module_shop_products SET `picture` = '$shopImageName' WHERE `id` = ".$objResult->fields['id'];
		        if($objDatabase->Execute($query) === false){
		            return _databaseError($query, $objDatabase->ErrorMsg());
		        }
	            $objResult->MoveNext();
	        }
	    }
	}else{
        return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = "DESCRIBE `".DBPREFIX."module_shop_products_attributes_name` `display_type`";
	if (($objResult = $objDatabase->Execute($query)) !== false) {
	    if ($objResult->fields['Type'] != "enum('0','1','2','3')") {
    	    $query = "ALTER TABLE `".DBPREFIX."module_shop_products_attributes_name` CHANGE `display_type` `display_type` ENUM( '0', '1', '2', '3' ) NOT NULL DEFAULT '0'";
	    	if ($objDatabase->Execute($query) === false) {
	    		return _databaseError($query, $objDatabase->ErrorMsg());
	    	}
	    }
	} else {
	    return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$arrTables = $objDatabase->MetaTables('TABLES');
	if ($arrTables !== false) {
		if (!in_array(DBPREFIX."module_shop_importimg", $arrTables)) {
			$query = "CREATE TABLE `".DBPREFIX."module_shop_importimg` (
				`img_id` int(11) NOT NULL auto_increment,
				`img_name` varchar(255) NOT NULL default '',
				`img_cats` text NOT NULL,
				`img_fields_file` text NOT NULL,
				`img_fields_db` varchar(255) NOT NULL default '',
				PRIMARY KEY  (`img_id`)
				) TYPE=MyISAM";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		print 'Die Struktur der Datenbank konnte nicht ermittelt werden!';
		return false;
	}


	/**
	 * Updates to the module_shop_* hierarchy
	 * @author    Reto Kohli, ASTALAVISTA IT AG
	 */

    /////////////////////////////////////////////////////////////////////////
    // add new fields to existing tables
    /////////////////////////////////////////////////////////////////////////

    // Adding column vat_id in ".DBPREFIX."module_shop_products...

    // VAT ID hinzufügen
    // Default ist NULL: Keine MWST
    // (wird aber weiter unten auf die alte MwSt. gesetzt)
    $query = "SELECT vat_id FROM ".DBPREFIX."module_shop_products LIMIT 1;";
    $objResult = $objDatabase->Execute($query);
    if ($objResult) {
        // column exists already
    } else {
        $query = "
        ALTER TABLE ".DBPREFIX."module_shop_products
        ADD COLUMN vat_id INT(10) UNSIGNED DEFAULT NULL;
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    // Adding column weight in ".DBPREFIX."module_shop_products...

    // Gewichtsfeld hinzufügen
    // Default ist NULL: Keine Angabe
    // Bem.: Die Einheit ist Gramm
    $query = "SELECT weight FROM ".DBPREFIX."module_shop_products LIMIT 1;";
    $objResult = $objDatabase->Execute($query);
    if ($objResult) {
        // column exists already
    } else {
        $query = "
        ALTER TABLE ".DBPREFIX."module_shop_products
        ADD COLUMN weight INT(10) UNSIGNED DEFAULT NULL;
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    // Adding column vat_percent to ".DBPREFIX."module_shop_order_items...

    // MWST in Prozent hinzufügen
    // Default ist NULL: Keine MWST
    $query = "SELECT vat_percent FROM ".DBPREFIX."module_shop_order_items LIMIT 1;";
    $objResult = $objDatabase->Execute($query);
    if ($objResult) {
        // column exists already
    } else {
        $query = "
        ALTER TABLE ".DBPREFIX."module_shop_order_items
        ADD COLUMN vat_percent DECIMAL(5,2) UNSIGNED DEFAULT NULL;
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    // Adding column weight to ".DBPREFIX."module_shop_order_items...

    // Gewicht in Gramm hinzufügen
    // Default ist NULL: Keine Angabe
    $query = "SELECT weight FROM ".DBPREFIX."module_shop_order_items LIMIT 1;";
    $objResult = $objDatabase->Execute($query);
    if ($objResult) {
        // column exists already
    } else {
        $query = "
        ALTER TABLE ".DBPREFIX."module_shop_order_items
        ADD COLUMN weight INT(10) UNSIGNED DEFAULT NULL;
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    /////////////////////////////////////////////////////////////////////////
    // VAT
    /////////////////////////////////////////////////////////////////////////

    // Adding table ".DBPREFIX."module_shop_vat...

    $query = "SELECT * FROM ".DBPREFIX."module_shop_vat LIMIT 1;";
    $objResult = $objDatabase->Execute($query);
    if ($objResult) {
        // the table already exists
    } else {
        $query = "
        CREATE TABLE ".DBPREFIX."module_shop_vat (
          id         INT(10)      UNSIGNED NOT NULL AUTO_INCREMENT,
          class      TINYTEXT              NOT NULL,
          percent    DECIMAL(5,2) UNSIGNED NOT NULL,
          PRIMARY KEY (id)
        ) TYPE=MyISAM;
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    $tax_status = 0;
    $tax_value  = 0;

    // Looking for current VAT status and value...

    $query = "
    SELECT value, status FROM ".DBPREFIX."module_shop_config
    WHERE name='tax_percentaged_value';
    ";
    if ($objResult) {
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }
    if ($objResult->NumRows() > 0) {
        $tax_status = $objResult->Fields('status');
        $tax_value  = $objResult->Fields('value');

        // insert new config entry to enable/disable VAT

        $query = "
        SELECT * FROM ".DBPREFIX."module_shop_config (name, value)
        WHERE name='tax_enabled';
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
        if ($objResult->NumRows() > 0) {
            // config entry exists already. skip.
        } else {
            // Inserting tax_enabled = $tax_status into ".DBPREFIX."module_shop_config...
            $query = "
            INSERT INTO ".DBPREFIX."module_shop_config (name, value) VALUES ('tax_enabled', $tax_status);
            ";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
            } else {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }

        // Inserting current VAT value into ".DBPREFIX."module_shop_vat...
        // Inserting example VAT records into ".DBPREFIX."module_shop_vat...

        // Alten MwSt. Wert einfüllen (hat noch keine Bezeichnung)
        $tax_id = 0;
        $query = "SELECT * FROM ".DBPREFIX."module_shop_vat LIMIT 1;";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
        if ($objResult->NumRows() > 0) {
            // there is at least one entry already!
            // assuming this has been run before, skip inserting new records.
            // just determine a default id
            $tax_id = $objResult->Fields('id');
        } else {
            $query = "
            INSERT INTO ".DBPREFIX."module_shop_vat
            VALUES
                (NULL, '', $tax_value),
                (NULL, 'Nicht Taxpflichtig',           0.00),
                (NULL, 'Deutschland Normalsatz',      19.00),
                (NULL, 'Deutschland ermässigt',        7.00),
                (NULL, 'Deutschland stark ermässigt',  5.50),
                (NULL, 'Deutschland Zwischensatz 1',   9.00),
                (NULL, 'Deutschland Zwischensatz 2',  16.00),
                (NULL, 'Österreich Normalsatz',       20.00),
                (NULL, 'Österreich ermässigt',        10.00),
                (NULL, 'Österreich Zwischensatz',     12.00),
                (NULL, 'Schweiz',                      7.60),
                (NULL, 'Schweiz ermässigt 1',          3.60),
                (NULL, 'Schweiz ermässigt 2',          2.40),
                (NULL, 'Great Britain',               17.50),
                (NULL, 'Great Britain reduced',        5.00);
            ";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
            } else {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
            // Trying to determine new default tax ID...
            // ID des MwSt. Wertes bestimmen
            $tax_id = $objDatabase->Insert_ID();
        }

        // Inserting tax_default_id = $tax_id into ".DBPREFIX."module_shop_config...

        $query = "SELECT * FROM ".DBPREFIX."module_shop_config WHERE name='tax_default_id';";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
        if ($objResult->NumRows() > 0) {
            // entry already exists
        } else {
            $query = "
            INSERT INTO ".DBPREFIX."module_shop_config (name, value) VALUES ('tax_default_id', $tax_id);
            ";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
            } else {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }

        // Updating vat_id in table ".DBPREFIX."module_shop_products...
        // but only if tax_id is set to a "sensible" value
        if ($tax_id > 0) {
            $query = "
            UPDATE ".DBPREFIX."module_shop_products
            SET vat_id=$tax_id WHERE vat_id=NULL;
            ";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
            } else {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }

            if ($tax_status == 1) {
                // Updating column vat_percent in ".DBPREFIX."module_shop_order_items...
                // Setze die VAT aller Order Items auf den Wert aus den Einstellungen,
                // falls sie vorher eingeschaltet war
                $query = "
                UPDATE ".DBPREFIX."module_shop_order_items
                SET vat_percent=$tax_value;
                ";
                $objResult = $objDatabase->Execute($query);
                if ($objResult) {
                } else {
                    return _databaseError($query, $objDatabase->ErrorMsg());
                }
            } else {
                // Tax is disabled - will not update column vat_percent in ".DBPREFIX."module_shop_order_items
            }
        }

        // Removing old VAT settings...

        $query = "
        DELETE FROM ".DBPREFIX."module_shop_config WHERE name='tax_percentaged_value';
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    /////////////////////////////////////////////////////////////////////////
    // shipper / shipment_cost
    /////////////////////////////////////////////////////////////////////////

    // create new shipper/shipment_cost tables if possible

    $query = "SELECT * FROM ".DBPREFIX."module_shop_shipment LIMIT 1;";
    $objResult = $objDatabase->Execute($query);
    if ($objResult) {
        // table still exists, so data can be converted.

        // Adding table ".DBPREFIX."module_shop_shipper...

        $query = "DROP TABLE IF EXISTS ".DBPREFIX."module_shop_shipper;";
        $objResult = $objDatabase->Execute($query);
        $query = "
        CREATE TABLE ".DBPREFIX."module_shop_shipper (
          id         INT(10)      UNSIGNED NOT NULL AUTO_INCREMENT,
          name       TINYTEXT              NOT NULL,
          status     BOOL,
          PRIMARY KEY (id)
        ) TYPE=MyISAM;
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }

        // Adding table ".DBPREFIX."module_shop_shipment_cost...

        $query = "DROP TABLE IF EXISTS ".DBPREFIX."module_shop_shipment_cost;";
        $objResult = $objDatabase->Execute($query);
        // max_weight: Der Versandpreis (cost) gilt bis zu diesem Gewicht.
        //             0/NULL: Keine Gewichtslimite
        // cost:       Die Versandkosten
        //             0/NULL: Keine Versandkosten
        // price_free: Ab diesem Bestellwert entfallen die Versandkosten.
        //             0/NULL: Keine Limite, die Versandkosten gelten immer
        $query = "
        CREATE TABLE ".DBPREFIX."module_shop_shipment_cost (
          id         INT(10)      UNSIGNED NOT NULL AUTO_INCREMENT,
          shipper_id INT(10)      UNSIGNED NOT NULL,
          max_weight INT(10)      UNSIGNED,
          cost       DECIMAL(8,2) UNSIGNED,
          price_free DECIMAL(8,2) UNSIGNED,
          PRIMARY KEY (id)
        ) TYPE=MyISAM;
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }

        // Inserting current shippers into ".DBPREFIX."module_shop_shipper...

        // Tabellen ".DBPREFIX."module_shop_shipper und
        // ".DBPREFIX."module_shop_shipment_cost:
        // Alte Werte aus ".DBPREFIX."module_shop_shipment einfüllen

        // Kopiere die Shipper
        // Falls ein Shipper Name doppelt auftaucht, aber mit unterschiedlichen
        // Status, werden dafür entsprechend zwei Einträge erstellt.
        $query = "
        INSERT INTO ".DBPREFIX."module_shop_shipper (name, status)
        (SELECT DISTINCT name, status FROM ".DBPREFIX."module_shop_shipment);
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }

// shipper contains data now! must be dropped if there is an error
// while filling shipper/shipment_cost!

        // Inserting current shipments into ".DBPREFIX."module_shop_shipment_cost...

        // Kopiere die Shipment Conditions, verlinke gleichzeitig mit den
        // neuen Einträgen in der Shipper Tabelle
        $query = "
        INSERT INTO ".DBPREFIX."module_shop_shipment_cost
         (shipper_id, max_weight, cost, price_free)
        (SELECT shipper.id, NULL, shipment.costs, shipment.costs_free_sum
         FROM ".DBPREFIX."module_shop_shipment shipment
         INNER JOIN ".DBPREFIX."module_shop_shipper shipper
           ON shipment.name   = shipper.name
          AND shipment.status = shipper.status
        );
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }

// shipment_cost contains data now! must be dropped if there is an error
// while filling shipper/shipment_cost!

        // Querying modified shipper keys...

        // ".DBPREFIX."module_shop_rel_shipment und ".DBPREFIX."module_shop_orders:
        // shipment_id / shipping_id aktualisieren auf neuen wert in shipper.id.
        // Abfrage alte und neue Schlüssel
        // Note: query may also be: "SELECT DISTINCT s.id, r.shipment_id"...
        $query = "
        SELECT s.id, r.shipment_id
        FROM ".DBPREFIX."module_shop_shipper s
        INNER JOIN ".DBPREFIX."module_shop_shipment_cost c
            ON c.shipper_id=s.id
        INNER JOIN ".DBPREFIX."module_shop_shipment m
            ON   s.name=m.name
             AND s.status=m.status
             AND c.cost=m.costs
             AND c.price_free=m.costs_free_sum
        INNER JOIN ".DBPREFIX."module_shop_rel_shipment r
            ON   m.id=r.shipment_id
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }

        // Updating modified shipper keys...";

        while (!$objResult->EOF) {
            $old_id = $objResult->Fields('shipment_id');
            $new_id = $objResult->Fields('id');
            // shipment_id in ".DBPREFIX."module_shop_rel_shipment anpassen
            $query = "
            UPDATE ".DBPREFIX."module_shop_rel_shipment
            SET shipment_id=$new_id
            WHERE shipment_id=$old_id;
            ";
            $objResult1 = $objDatabase->Execute($query);
            if ($objResult1) {
            } else {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
            // shipping_id in ".DBPREFIX."module_shop_orders anpassen
            $query = "
            UPDATE ".DBPREFIX."module_shop_orders
            SET shipping_id=$new_id
            WHERE shipping_id=$old_id;
            ";
            $objResult1 = $objDatabase->Execute($query);
            if ($objResult1) {
            } else {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
            $objResult->MoveNext();
        }

        // Deleting old shipment table ".DBPREFIX."module_shop_shipment...

        $query = "
        DROP TABLE ".DBPREFIX."module_shop_shipment;
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    } else {
        // no original data, create default (empty) tables if they don't exist

        $query = "SELECT * FROM ".DBPREFIX."module_shop_shipper LIMIT 1;";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
            // exists already
        } else {
            $query = "
            CREATE TABLE ".DBPREFIX."module_shop_shipper (
              id         INT(10)      UNSIGNED NOT NULL AUTO_INCREMENT,
              name       TINYTEXT              NOT NULL,
              status     BOOL,
              PRIMARY KEY (id)
            ) TYPE=MyISAM;
            ";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
            } else {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }

        // Adding table ".DBPREFIX."module_shop_shipment_cost...

        $query = "SELECT * FROM ".DBPREFIX."module_shop_shipment_cost LIMIT 1;";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
            // exists already
        } else {
            // max_weight: Der Versandpreis (cost) gilt bis zu diesem Gewicht.
            //             0/NULL: Keine Gewichtslimite
            // cost:       Die Versandkosten
            //             0/NULL: Keine Versandkosten
            // price_free: Ab diesem Bestellwert entfallen die Versandkosten.
            //             0/NULL: Keine Limite, die Versandkosten gelten immer
            $query = "
            CREATE TABLE ".DBPREFIX."module_shop_shipment_cost (
              id         INT(10)      UNSIGNED NOT NULL AUTO_INCREMENT,
              shipper_id INT(10)      UNSIGNED NOT NULL,
              max_weight INT(10)      UNSIGNED,
              cost       DECIMAL(8,2) UNSIGNED,
              price_free DECIMAL(8,2) UNSIGNED,
              PRIMARY KEY (id)
            ) TYPE=MyISAM;
            ";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
            } else {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    }

    /////////////////////////////////////////////////////////////////////////
    // LSV
    /////////////////////////////////////////////////////////////////////////

    // Adding new table ".DBPREFIX."module_shop_lsv...

    // Neue Tabelle ".DBPREFIX."module_shop_lsv
    // für Lastschriftverfahren
    // order_id     FK auf ".DBPREFIX."module_shop_orders (1:1; UNIQUE!)
    // holder       Name des Kontinhabers
    // bank         Name des Kreditinstituts
    // blz          Bankleitzahl
    $query = "SELECT * FROM ".DBPREFIX."module_shop_lsv LIMIT 1;";
    $objResult = $objDatabase->Execute($query);
    if ($objResult) {
        // exists already
    } else {
        $query = "
        CREATE TABLE ".DBPREFIX."module_shop_lsv (
          id         INT(10)    UNSIGNED NOT NULL AUTO_INCREMENT,
          order_id   INT(10)    UNSIGNED NOT NULL,
          holder     TINYTEXT   NOT NULL,
          bank       TINYTEXT   NOT NULL,
          blz        TINYTEXT   NOT NULL,
          PRIMARY KEY (id),
          UNIQUE (order_id)
        ) TYPE = MYISAM;
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    // Inserting new configuration entries for ".DBPREFIX."module_shop_lsv:<br />

    // Neuer Eintrag in ".DBPREFIX."module_shop_config
    // zum ein-/ausschalten des LSV.
    // Default ist aus
    // payment_lsv_status...
    $query = "
        SELECT * FROM ".DBPREFIX."module_shop_config
        WHERE name='payment_lsv_status';";
    $objResult = $objDatabase->Execute($query);
    if ($objResult) {
        // exists already
    } else {
        $query = "
        INSERT INTO ".DBPREFIX."module_shop_config (name, value) VALUES ('payment_lsv_status', 0);
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }
    // shop_company...
    $query = "
        SELECT * FROM ".DBPREFIX."module_shop_config
        WHERE name='shop_company';";
    $objResult = $objDatabase->Execute($query);
    if ($objResult) {
        // exists already
    } else {
        $query = "
        INSERT INTO ".DBPREFIX."module_shop_config (name, value) VALUES ('shop_company', '');
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }
    // shop_address1...
    $query = "
        SELECT * FROM ".DBPREFIX."module_shop_config
        WHERE name='shop_address';";
    $objResult = $objDatabase->Execute($query);
    if ($objResult) {
        // exists already
    } else {
        $query = "
        INSERT INTO ".DBPREFIX."module_shop_config (name, value) VALUES ('shop_address', '');
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    // Einträge für payment/payment processing (LSV)
    // payment processor LSV...
    $query = "
        SELECT * FROM ".DBPREFIX."module_shop_payment_processors
        WHERE name='Internal_LSV';";
    $objResult = $objDatabase->Execute($query);
    if ($objResult) {
        // exists already
    } else {
        $query = "
        INSERT INTO ".DBPREFIX."module_shop_payment_processors (type, name, description, status)
        VALUES ('internal', 'Internal_LSV', 'LSV with internal form', 1);
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }
    $pp_id = $objDatabase->Insert_ID();
    // payment LSV...
    $query = "
        SELECT * FROM ".DBPREFIX."module_shop_payment
        WHERE name='LSV';";
    $objResult = $objDatabase->Execute($query);
    if ($objResult) {
        // exists already
    } else {
        $query = "
        INSERT INTO ".DBPREFIX."module_shop_payment (name, processor_id, costs, costs_free_sum, sort_order, status)
        VALUES ('LSV', $pp_id, '0.00', '0.00', 0, 0);
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
        $p_id = $objDatabase->Insert_ID();
        // rel payment -> LSV...
        $query = "
        INSERT INTO ".DBPREFIX."module_shop_rel_payment (zones_id, payment_id)
        VALUES (1, $p_id);
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    // TODO: fix table structure for tables with tiny and small ints

	return true;
}

function _createThumb($strPath, $strWebPath, $file, $maxSize = 80, $quality = 90){
    $objFile = &new File();
    $_objImage = &new ImageManager();
    $tmpSize    = getimagesize($strPath.$file);
    if(!$tmpSize){
        echo "WARNUNG: Das Bild ".$strWebPath.$file." ist in der Datenbank eingetragen, aber existiert nicht mehr!<br />";
    }
    if($tmpSize[0] > $tmpSize[1]){
       $factor = $maxSize / $tmpSize[0];
    }else{
       $factor = $maxSize / $tmpSize[1] ;
    }
    $thumbWidth  = $tmpSize[0] * $factor;
    $thumbHeight = $tmpSize[1] * $factor;

    $_objImage->loadImage($strPath.$file);
    $_objImage->resizeImage($thumbWidth, $thumbHeight, $quality);
    $_objImage->saveNewImage($strPath.$file . '.thumb');
    if($objFile->setChmod($strPath, $strWebPath, $file . '.thumb')){
       return true;
    }
    return false;
}

?>
