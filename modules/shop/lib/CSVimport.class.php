<?php

class CSVimport {

    var $arrImportImg = array();
    var $separator = ';';
    var $delimiter = '"';
    var $escapor   = '"';

    function CSVimport()
    {
        global $objDatabase;

        $query =
            "SELECT img_id, img_name, img_cats, img_fields_file, img_fields_db ".
            "FROM ".DBPREFIX."module_shop_importimg ORDER BY img_id";
        $objResult = $objDatabase->Execute($query);
        $ArrayCounter = 0;
        while(!$objResult->EOF) {
            $this->arrImportImg[$ArrayCounter] = array(
                'id'          => $objResult->fields['img_id'],
                'name'        => $objResult->fields['img_name'],
                'cat'         => $objResult->fields['img_cats'],
                'fields_file' => $objResult->fields['img_fields_file'],
                'fields_db'   => $objResult->fields['img_fields_db']
            );
            $ArrayCounter++;
            $objResult->MoveNext();
        }
    }

    function GetImportImg()
    {
        return $this->arrImportImg;
    }

    function InitArray()
    {
        global $objDatabase;

        $query =
            "SELECT img_id, img_name, img_cats, img_fields_file, img_fields_db ".
            "FROM ".DBPREFIX."module_shop_importimg ORDER BY img_id";
        $objResult = $objDatabase->Execute($query);
        $ArrayCounter = 0;
        while(!$objResult->EOF) {
            $this->arrImportImg[$ArrayCounter] = array(
                'id'          => $objResult->fields['img_id'],
                'name'        => $objResult->fields['img_name'],
                'cat'         => $objResult->fields['img_cats'],
                'fields_file' => $objResult->fields['img_fields_file'],
                'fields_db'   => $objResult->fields['img_fields_db']
            );
            $ArrayCounter++;
            $objResult->MoveNext();
        }
    }

    function GetImgListDelete($DeleteText)
    {
        $content = '';
        for ($x=0; $x<count($this->arrImportImg); $x++) {
            $content .= ''.$this->arrImportImg[$x]["name"].'</b> <a href="javascript:DeleteImg('.$this->arrImportImg[$x]["id"].');">'.$DeleteText.'</a><br />';
        }
        return $content;
    }

    function GetFileFields()
    {
        $csv_source = &new csv_bv($_FILES["CSVfile"]["tmp_name"], $this->separator, $this->delimiter, $this->escapor);
        $csv_source->SkipEmptyRows(TRUE);
        $csv_source->TrimFields(TRUE);
        $FileContent = $csv_source->csv2Array();
        $FileFields = '';
        $SelectedText = " selected='selected'";
        for ($x=0; $x<count($FileContent[0]); $x++) {
            $FileFields .= "<option name='$x'$SelectedText>".$FileContent[0][$x]."</option>\n";
            $SelectedText = '';
        }
        return $FileFields;
    }

    function GetDBFields()
    {
        $DBarray = array(
             1 => 'PRODUCT ID',
             2 => 'TITLE',
             3 => 'HANDLER',
             4 => 'NORMALPRICE',
             5 => 'RESELLERPRICE',
             6 => 'DISCOUNTPRICE',
             7 => 'ISSPECIALOFFER',
             8 => 'SHORTDESC',
             9 => 'DESCRIPTION',
            10 => 'STOCK',
            11 => 'B2B',
            12 => 'B2C',
            13 => 'PICTURE',
            14 => 'WEIGHT',
        );
        $DbFields = '';
        $SelectedText = " selected='selected'";
        for ($x=1; $x<=count($DBarray); $x++) {
            $DbFields .= "<option value='".$DBarray[$x]."'$SelectedText>".$DBarray[$x]."</option>\n";
            $SelectedText = '';
        }
        return $DbFields;
    }

    function GetFileContent()
    {
        $csv_source = &new csv_bv($_FILES["importfile"]["tmp_name"], $this->separator, $this->delimiter, $this->escapor);
        $csv_source->SkipEmptyRows(true);
        $csv_source->TrimFields(true);
        $FileContent = $csv_source->csv2Array();
        return $FileContent;
    }

    function GetImageChoice($Noimg)
    {
        $content = '<select name="ImportImage">';
        if ($Noimg == '') {
            for ($x=0; $x<count($this->arrImportImg); $x++) {
                $content .= '<option value="'.$this->arrImportImg[$x]["id"].'">'.$this->arrImportImg[$x]["name"].'</option>';
            }
        } else {
            $content .= '<option value="">'.$Noimg.'';
        }
        $content .= '</select>';
        return $content;
    }

    function DBfieldsName($FieldDesc)
    {
        $FieldName = array();
        $FieldName['PRODUCT ID']       = 'product_id';
        $FieldName['PICTURE']          = 'picture';
        $FieldName['TITLE']            = 'title';
        $FieldName['HANDLER']          = 'handler';
        $FieldName['NORMALPRICE']      = 'normalprice';
        $FieldName['RESELLERPRICE']    = 'resellerprice';
        $FieldName['DISCOUNTPRICE']    = 'discountprice';
        $FieldName['ISSPECIALOFFER']   = 'is_special_offer';
        $FieldName['SHORTDESC']        = 'shortdesc';
        $FieldName['DESCRIPTION']      = 'description';
        $FieldName['STOCK']            = 'stock';
        $FieldName['B2B']              = 'b2b';
        $FieldName['B2C']              = 'b2c';
        $FieldName['WEIGHT']           = 'weight';
        return $FieldName[$FieldDesc];
    }


    /**
     * Returns the ID of the ShopCategory with the given name and
     * parent ID.
     *
     * If the ShopCategory cannot be found, a new sub-ShopCategory
     * with the given name is inserted and its ID returned.
     * @static
     * @param   string      $CatName    The ShopCategory name
     * @param   integer     $CatParent  The parent ShopCategory ID
     * @return  integer                 The ID of the ShopCategory,
     *                                  or 0 on failure.
     * @author  Unknown <thun@astalavista.ch> (Original author)
     * @author  Reto Kohli <reto.kohli@astalavista.ch> (Made static)
     */
    //static
    function GetCatID($CatName, $CatParent)
    {
        global $objDatabase;
        $query =
            "SELECT catid FROM ".DBPREFIX."module_shop_categories ".
            "WHERE catname='".$CatName."' AND parentid=".$CatParent."";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
            if ($objResult->RecordCount() > 0) {
                return $objResult->fields['catid'];
            } else {
                $catId = CSVimport::InsertNewCat($CatName, $CatParent);
                return $catId;
            }
        } else {
            return 0;
        }
    }


    /**
     * Returns the ID of the first ShopCategory found in the database.
     *
     * If none is available, a default ShopCateogry named 'Import'
     * is inserted and its ID returned instead.
     * @static
     * @return  integer     The ShopCategory, or 0 on failure
     * @author  Unknown <thun@astalavista.ch> (Original author)
     * @author  Reto Kohli <reto.kohli@astalavista.ch> (Added creation of default ShopCategory, made static)
     */
    //static
    function GetFirstCat()
    {
        global $objDatabase;
        $query = "SELECT catid FROM ".DBPREFIX."module_shop_categories";
        $objResult = $objDatabase->SelectLimit($query, 1);
        if ($objResult->RecordCount() > 0) {
            return $objResult->fields["catid"];
        } else {
            return CSVimport::InsertNewCat('Import', 0);
        }
    }


    /**
     * Insert a new ShopCategory into the database.
     *
     * @static
     * @param   string      $CatName    The new ShopCategory name
     * @param   integer     $CatParent  The parent ShopCategory ID
     * @return  integer                 The ID of the new ShopCategory,
     *                                  or 0 on failure.
     * @author  Unknown <thun@astalavista.ch> (Original author)
     * @author  Reto Kohli <reto.kohli@astalavista.ch> (Made static)
     */
    //static
    function InsertNewCat($CatName, $CatParent)
    {
        global $objDatabase;
        $query =
            "INSERT INTO ".DBPREFIX."module_shop_categories ".
            "(catname, parentid) VALUES ('".$CatName."','".$CatParent."')";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
            return $objDatabase->Insert_Id();
        }
        return 0;
    }
}

?>
