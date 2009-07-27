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
    var $_availableAttributes = array('type', 'format', 'front', 'back', 'weight', 'paper');


    /**
    * Constructor
    *
    */
    function __construct(){
        global $_CONFIG;

        $this->_arrSettings     = $this->createSettingsArray();
        $this->_limit           = $_CONFIG['corePagingLimit'];
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
            $arrAttributes[] = array(
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
    function _getEntries($arrAttributes){
        global $objDatabase;
        $where = '';

        $query = 'SELECT
          FROM
          WHERE '.$where;

        $objDatabase->SelectLimit($query, $pos, $limit);
        foreach ($arrAttributes as $attribute => $value) {
            if(!$this->_isValidAtrribute($attribute)){
                return false;
            }
            if($value == 0){
                $join .= ' INNER JOIN `'.DBPREFIX.'module_printshop_'.$attribute.'` AS `a` ON (`a`.`'.$attribute.'`=``)';
                $where .= ' ';
            }
        }
        return $arrEntries;
    }


    /**
     * create the HTML for the attribute dropdown
     *
     * @param string $attribute
     * @return string
     */
    function createAttribtueDropDown($attribute){
        global $_CORELANG;

        $arrAttributes = $this->_getAttributes($attribute);
        $attribute[0] = strtoupper($attribute[0]);
        $html = '<select name="psFilter'.$attribute.'"><option value="0">'.$_CORELANG['TXT_USER_ALL'].'</option>';
        foreach ($arrAttributes as $id => $attribute) {
        	$html .= '<option value="'.$attribute['id'].'">'.$attribute['name'].'</option>';
        }
        return $html.'</select>';
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
        if(!in_array($attribute, $this->_availableAttributes)){
            return false;
        }
        return true;
    }
}
