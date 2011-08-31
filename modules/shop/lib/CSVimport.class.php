<?php

/**
 * CSV Import
 * @author Comvation Development Team <info@comvation.com>
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */

/**
 * CSV Import
 * @author Comvation Development Team <info@comvation.com>
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */
class CSVimport
{
    static private $separator = ';';
    static private $delimiter = '"';
    static private $escapor   = '"';

    private $arrTemplateArray = false;
    private $arrName2Fieldname = false;


    function __construct()
    {
        global $_ARRAYLANG;

        $this->arrName2Fieldname = array(
            $_ARRAYLANG['TXT_SHOP_PRODUCT_CUSTOM_ID'] => 'product_id',
            $_ARRAYLANG['TXT_SHOP_IMAGE'] => 'picture',
            $_ARRAYLANG['TXT_PRODUCT_NAME'] => 'title',
            $_ARRAYLANG['TXT_DISTRIBUTION'] => 'handler',
            $_ARRAYLANG['TXT_CUSTOMER_PRICE'] => 'normalprice',
            $_ARRAYLANG['TXT_RESELLER_PRICE'] => 'resellerprice',
            $_ARRAYLANG['TXT_SHOP_PRICE_SPECIAL_OFFER'] => 'discountprice',
            $_ARRAYLANG['TXT_SPECIAL_OFFER'] => 'discount_active',
            $_ARRAYLANG['TXT_SHORT_DESCRIPTION'] => 'short',
            $_ARRAYLANG['TXT_DESCRIPTION'] => 'long',
            $_ARRAYLANG['TXT_STOCK'] => 'stock',
            $_ARRAYLANG['TXT_B2B'] => 'b2b',
            $_ARRAYLANG['TXT_B2C'] => 'b2c',
            $_ARRAYLANG['TXT_WEIGHT'] => 'weight',
        );
        $this->initTemplateArray();
    }


    function getTemplateArray()
    {
        return $this->arrTemplateArray;
    }


    function initTemplateArray()
    {
        global $objDatabase;

        $query = "
            SELECT img_id, img_name, img_cats, img_fields_file, img_fields_db
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_importimg
             ORDER BY img_id";
        $objResult = $objDatabase->Execute($query);
        $this->arrTemplateArray = array();
        while ($objResult && !$objResult->EOF) {
            $this->arrTemplateArray[] = array(
                'id'          => $objResult->fields['img_id'],
                'name'        => $objResult->fields['img_name'],
                'cat'         => $objResult->fields['img_cats'],
                'fields_file' => $objResult->fields['img_fields_file'],
                'fields_db'   => $objResult->fields['img_fields_db']
            );
            $objResult->MoveNext();
        }
    }


    function GetImgListDelete($DeleteText)
    {
        $content = '';
        for ($x = 0; $x < count($this->arrTemplateArray); ++$x) {
            $content .=
                $this->arrTemplateArray[$x]['name'].
                '<a href="javascript:DeleteImg('.
                $this->arrTemplateArray[$x]['id'].');">'.
                $DeleteText.'</a><br />';
        }
        return $content;
    }


    function getFilefieldMenuOptions()
    {
        $csv_source = new Csv_bv(
            $_FILES['CSVfile']['tmp_name'],
            CSVimport::$separator, CSVimport::$delimiter, CSVimport::$escapor
        );
        $csv_source->SkipEmptyRows(true);
        $csv_source->TrimFields(true);
        $arrFileContent = $csv_source->csv2Array();
        $strOptions = '';
        for ($x = 0; $x < count($arrFileContent[0]); ++$x) {
            $strOptions .=
                "<option name='$x'>".
                $arrFileContent[0][$x].
                "</option>\n";
        }
        return $strOptions;
    }


    /**
     * Return the menu options of available names that can be assigned
     * to the fields of the file to be imported.
     * @return  array           The available names
     * @static
     */
    static function getAvailableNamesMenuOptions()
    {
        $strOptions = '';
        foreach (array_keys($this->arrName2Fieldname) as $name) {
            $strOptions .= "<option value=\"$name\">$name</option>\n";
        }
        return $strOptions;
    }


    function GetFileContent()
    {
        $csv_source = new csv_bv(
            $_FILES['importfile']['tmp_name'],
            CSVimport::$separator, CSVimport::$delimiter, CSVimport::$escapor
        );
        $csv_source->SkipEmptyRows(true);
        $csv_source->TrimFields(true);
        $FileContent = $csv_source->csv2Array();
        return $FileContent;
    }


    function GetImageChoice($Noimg)
    {
        $content = '<select name="ImportImage">';
        if ($Noimg == '') {
            for ($x=0; $x<count($this->arrTemplateArray); ++$x) {
                $content .=
                    '<option value="'.$this->arrTemplateArray[$x]['id'].'">'.
                    $this->arrTemplateArray[$x]['name'].'</option>';
            }
        } else {
            $content .= '<option value="">'.$Noimg;
        }
        $content .= '</select>';
        return $content;
    }


    function DBfieldsName($name='')
    {
        if (empty($name)) {
            return $this->arrName2Fieldname;
        }
        return $this->arrName2Fieldname[$name];
    }


    /**
     * Returns the ID of the ShopCategory with the given name and
     * parent ID, if present.
     *
     * If the ShopCategory cannot be found, a new ShopCategory
     * with the given name is inserted and its ID returned.
     * @static
     * @param   string      $catName    The ShopCategory name
     * @param   mixed       $catParent  The optional parent ShopCategory ID,
     *                                  or null to ignore it (default)
     * @return  integer                 The ID of the ShopCategory,
     *                                  or 0 on failure.
     * @author  Unknown <info@comvation.com> (Original author)
     * @author  Reto Kohli <reto.kohli@comvation.com> (Made static)
     */
    static function getCategoryId($catName, $catParent=null)
    {
        $objCategory = ShopCategories::getChildNamed($catName, $catParent);
        if ($objCategory) {
            return $objCategory->id();
        }
        return CSVimport::InsertNewCat($catName, $catParent);
    }


    /**
     * Returns the ID of the first ShopCategory found in the database.
     *
     * If none is available, a default ShopCateogry named 'Import'
     * is inserted and its ID returned instead.
     * @static
     * @return  integer     The ShopCategory, or 0 on failure
     * @author  Unknown <info@comvation.com> (Original author)
     * @author  Reto Kohli <reto.kohli@comvation.com> (Added creation of default ShopCategory, made static)
     */
    static function GetFirstCat()
    {
        $category_id = ShopCategory::getNextShopCategoryId();
        if ($category_id) return $category_id;
        return CSVimport::InsertNewCat('Import', 0);
    }


    /**
     * Insert a new ShopCategory into the database.
     *
     * @static
     * @param   string      $catName    The new ShopCategory name
     * @param   integer     $catParent  The parent ShopCategory ID
     * @return  integer                 The ID of the new ShopCategory,
     *                                  or 0 on failure.
     * @author  Unknown <info@comvation.com> (Original author)
     * @author  Reto Kohli <reto.kohli@comvation.com> (Made static)
     */
    static function InsertNewCat($catName, $catParent)
    {
        $objCategory = new ShopCategory($catName, '', $catParent);
        if ($objCategory->store()) {
            return $objCategory->id();
        }
        return 0;
    }

}

?>
