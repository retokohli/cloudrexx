<?php
/**
 * Printshop library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation AG <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_printshop
 */

/**
 * Includes
 */

/**
 * Printshop library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation AG <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_printshop
 */
class PrintshopLibrary {
    var $_arrSettings           = array();
    var $_pos;
    var $_limit;
    var $_arrAvailableAttributes = array('type', 'format', 'front', 'back', 'weight', 'paper');
    var $_arrAttributeTranslation = array();


    /**
    * Constructor
    *
    */
    function __construct(){
        global $_CONFIG;

        $this->_arrSettings     = $this->createSettingsArray();
        $this->_limit           = !empty($_GET['count']) ? intval($_GET['count']) : $_CONFIG['corePagingLimit'];
        $this->_pos             = !empty($_GET['pos']) ? intval($_GET['pos']) : 0;
    }


    /**
     * Create an array containing all settings of the blog-module.
     * Example: $arrSettings[$strSettingName] for the content of $strSettingsName
     *
     * @global  ADONewConnection
     * @return  array       $arrReturn
     */
    function createSettingsArray(){
        global $objDatabase;

        $arrReturn = array();

        $objResult = $objDatabase->Execute('SELECT  name,
                                                    value
                                            FROM    '.DBPREFIX.'module_printshop_settings
                                        ');
        if($objResult !== false){
            while (!$objResult->EOF) {
                $arrReturn[$objResult->fields['name']] = contrexx_stripslashes(htmlspecialchars($objResult->fields['value'], ENT_QUOTES, CONTREXX_CHARSET));
                $objResult->MoveNext();
            }
        }
        return $arrReturn;
    }


    /**
     * get the data of an attribute
     *
     * @param string $attribute
     * @return array
     */
    function _getAttributes($attribute){
        global $objDatabase;

        if(!$this->_isValidAtrribute($attribute)){
            return false;
        }
        $arrAttributes = array();

        $query = 'SELECT `id`, `'.$attribute.'` AS `name`
                  FROM `'.DBPREFIX.'module_printshop_'.$attribute.'`';
        $objRS = $objDatabase->Execute($query);
        if($objRS->RecordCount() == 0){
            return false;
        }
        while(!$objRS->EOF){
            $arrAttributes[$objRS->fields['id']] = array(
                'id'        =>  $objRS->fields['id'],
                'name'      =>  $objRS->fields['name'],
            );
            $objRS->MoveNext();
        }
        return $arrAttributes;
    }

    /**
     * Get the printshop entries filtered by $arrAttributes
     *
     * @param array $arrAttributes filter (if an attribute is not set it will not be filtered)
     * @return array entries
     */
    function _getEntries($arrAttributesFilter = false, $translated = false){
        global $objDatabase;
        $arrEntries = array();
        $where = 'true';
        if($translated){
            $this->_getAttributeTranslation();
        }

        if($arrAttributesFilter){
            foreach ($arrAttributesFilter as $attribute => $value) {
                if(!$this->_isValidAtrribute($attribute)){
                    return false;
                }
                if($value > 0){
//                    $join .= ' INNER JOIN `'.DBPREFIX.'module_printshop_'.$attribute.'` AS `a` ON (`a`.`'.$attribute.'`=``)';
                    $where .= ' AND `'.$attribute.'` = '.$value;
                }
            }
        }

        $query = 'SELECT `p`.`type`, `p`.`format`, `p`.`front`, `p`.`back`, `p`.`weight`, `p`.`paper`, `p`.`price`, `p`.`factor`
                  FROM   `'.DBPREFIX.'module_printshop_product` AS `p`
                  WHERE '.$where.'
                  ORDER BY `type`, `format`, `front`, `back`, `weight`, `paper`';

        $objRS = $objDatabase->SelectLimit($query, $this->_limit, $this->_pos);
        while(!$objRS->EOF){
            if($translated){
                $arrEntries[] = array(
                    'type'          => $this->_arrAttributeTranslation['type'  ][$objRS->fields['type'  ]]['name'],
                    'format'        => $this->_arrAttributeTranslation['format'][$objRS->fields['format']]['name'],
                    'front'         => $this->_arrAttributeTranslation['front' ][$objRS->fields['front' ]]['name'],
                    'back'          => $this->_arrAttributeTranslation['back'  ][$objRS->fields['back'  ]]['name'],
                    'weight'        => $this->_arrAttributeTranslation['weight'][$objRS->fields['weight']]['name'],
                    'paper'         => $this->_arrAttributeTranslation['paper' ][$objRS->fields['paper' ]]['name'],
                    'price'         => $objRS->fields['price'],
                    'factor'        => $objRS->fields['factor'],
                );
            }else{
                 $arrEntries[] = $objRS->fields;
            }
            $objRS->MoveNext();
        }

        return $arrEntries;
    }


    /**
     * get the attribute data
     *
     */
    function _getAttributeTranslation(){
        if(empty($this->_arrAttributeTranslation)){
            foreach ($this->_arrAvailableAttributes as $attribute) {
            	$this->_arrAttributeTranslation[$attribute] = $this->_getAttributes($attribute);
            }
        }
    }


    /**
     * create the HTML for the attribute dropdown
     *
     * @param string $attribute
     * @return string
     * @global languageArray
     */
    function createAttributeDropDown($attribute, $namePrefix = 'ps', $defaultText = '', $selectedId = 0){
        global $_CORELANG;

        $arrAttributes = $this->_getAttributes($attribute);
        $attribute[0] = strtoupper($attribute[0]);
        $html = '<select name="'.$namePrefix.$attribute.'"><option value="0">'.$defaultText.'</option>';
        $selected = 'selected="selected"';
        foreach ($arrAttributes as $id => $attribute) {
        	$html .= '<option '.($selectedId == $attribute['id'] ? $selected : '').' value="'.$attribute['id'].'">'.$attribute['name'].'</option>';
        }
        return $html.'</select>';
    }


    /**
     * deletes a product from the product table
     *
     * @param integer $type
     * @param integer $format
     * @param integer $front
     * @param integer $back
     * @param integer $weight
     * @param integer $paper
     * @return boolean
     */
    function delProduct($type, $format, $front, $back, $weight, $paper){
        global $objDatabase;

        return $objDatabase->Execute('
            DELETE FROM `'.DBPREFIX.'module_printshop_product`
            WHERE `type`   = '.$type.'
            AND `format` = '.$format.'
            AND `front`  = '.$front.'
            AND `back`   = '.$back.'
            AND `weight` = '.$weight.'
            AND `paper`  = '.$paper.'
        ');
    }


    /**
     * add a new product into the product table
     *
     * @param integer $type
     * @param integer $format
     * @param integer $front
     * @param integer $back
     * @param integer $weight
     * @param integer $paper
     * @param double $price
     * @param double $factor
     * @return boolean
     */
    function addProduct($type, $format, $front, $back, $weight, $paper, $price, $factor){
        global $objDatabase;

        return $objDatabase->Execute('
            INSERT INTO `'.DBPREFIX.'module_printshop_product` (`type`, `format`, `front`, `back`, `weight`, `paper`, `price`, `factor`)
            VALUES ('.$type.', '.$format.', '.$front.', '.$back.', '.$weight.', '.$paper.', '.$price.', '.$factor.')
            ON DUPLICATE KEY UPDATE `type`   = '.$type.',
                                    `format` = '.$format.',
                                    `front`  = '.$front.',
                                    `back`   = '.$back.',
                                    `weight` = '.$weight.',
                                    `paper`  = '.$paper.',
                                    `price`  = '.$price.',
                                    `factor` = '.$factor.'
        ');
    }

    /**
     * check if product with specified criteria already exists
     *
     * @param integer $type
     * @param integer $format
     * @param integer $front
     * @param integer $back
     * @param integer $weight
     * @param integer $paper
     * @return boolean
     */
    function productExists($type, $format, $front, $back, $weight, $paper){
        global $objDatabase;

        $objRS = $objDatabase->SelectLimit('
            SELECT 1 FROM `'.DBPREFIX.'module_printshop_product`
            WHERE `type` = '.$type.'
            AND `format` = '.$format.'
            AND `front`  = '.$front.'
            AND `back`   = '.$back.'
            AND `weight` = '.$weight.'
            AND `paper`  = '.$paper.'
        ', 1);
        return $objRS->RecordCount() > 0;
    }

    /**
     * Get the name of the specified Attribute
     *
     * @param string $attribute
     * @param int $id
     * @return string
     */
    function _getAttributeName($attribute, $id){
        global $objDatabase;
        if(!$this->_isValidAtrribute($attribute)){
            return '';
        }
        $id = intval($id);
        $query = 'SELECT `'.$attribute.'` AS `name`
                  FROM `'.DBPREFIX.'module_printshop_'.$attribute.'`
                  WHERE `id`='.$id;
        $objRS = $objDatabase->SelectLimit($query, 1);
        return $objRS ? $objRS->fields['name'] : '';
    }

    /**
     * check if an attribute is valid
     *
     * @param string $attribute
     * @return bool
     */
    function _isValidAtrribute($attribute){
        if(!in_array($attribute, $this->_arrAvailableAttributes)){
            return false;
        }
        return true;
    }
}
