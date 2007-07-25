<?php
/**
 * Framework language
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  lib_framework
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Framework language
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  lib_framework
 */
class FWLanguage
{
    var $arrLanguage = NULL;

    function FWLanguage()
    {
        $this->__construct();
    }

    /**
     * Constructor (PHP5)
     * @access  private
     * @global  mixed   $objDatabase    Database object
     */
    function __construct()
    {
        global $objDatabase;

         $objResult = $objDatabase->Execute('
            SELECT id, lang, name, charset, themesid,
                   frontend, backend, is_default
              FROM '.DBPREFIX.'languages
          ORDER BY id
         ');
         if ($objResult) {
             while (!$objResult->EOF) {
                $this->arrLanguage[$objResult->fields['id']] = array(
                    'id'         => $objResult->fields['id'],
                    'lang'       => $objResult->fields['lang'],
                    'name'       => $objResult->fields['name'],
                    'charset'    => $objResult->fields['charset'],
                    'themesid'   => $objResult->fields['themesid'],
                    'frontend'   => $objResult->fields['frontend'],
                    'backend'    => $objResult->fields['backend'],
                    'is_default' => $objResult->fields['is_default'],
                );
                $objResult->MoveNext();
            }
        }
    }


    /**
     * Returns the complete language data
     * @see     FWLanguage()
     * @return  array           The language data
     * @access  public
     */
    function getLanguageArray()
    {
        return $this->arrLanguage;
    }

    /**
     * Returns single language related fields
     *
     * Access language data by specifying the language ID and the index
     * as initialized by {@link FWLanguage()}.
     * @return  mixed           Language data field content
     * @access  public
     */
    function getLanguageParameter($id, $index)
    {
        return isset($this->arrLanguage[$id][$index]) ? $this->arrLanguage[$id][$index] : false;
    }


    /**
     * Returns HTML code to display a language selection dropdown menu.
     * @param   integer $selectedId The Optional preselected language ID
     * @return  string              The dropdown menu HTML code
     */
    function getMenu($selectedId=0, $menuName='languageId', $onchange='')
    {
        $menu =
            "<select id='$menuName' name='$menuName'".
                ($onchange
                    ? ' onchange="'.$onchange.'"'
                    : ''
                ).
            ">\n";
        foreach ($this->arrLanguage as $id => $arrField) {
            $menu .=
                "<option value='$id'".
                ($selectedId == $id ? ' selected="selected"' : '').
                ">{$arrField['name']}</option>\n";
        }
        $menu .= "</select>\n";
        return $menu;
    }
}

?>
