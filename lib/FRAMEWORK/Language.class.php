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
     *
     * Does only contain the <select> tag pair if the optional $menuName
     * is specified and evaluates to a true value.
     * @param   integer $selectedId The optional preselected language ID
     * @param   string  $menuName   The optional menu name
     * @param   string  $onchange   The optional onchange code
     * @return  string              The dropdown menu HTML code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getMenu($selectedId=0, $menuName='', $onchange='')
    {
        $menu = '';
        foreach ($this->arrLanguage as $id => $arrField) {
            $menu .=
                "<option value='$id'".
                ($selectedId == $id ? ' selected="selected"' : '').
                ">{$arrField['name']}</option>\n";
        }
        if ($menuName) {
            $menu = "<select id='$menuName' name='$menuName'".
                    ($onchange ? ' onchange="'.$onchange.'"' : '').
                    ">\n$menu</select>\n";
        }
//echo("getMenu(select=$selectedId, name=$menuName, onchange=$onchange): made menu: ".htmlentities($menu)."<br />");
        return $menu;
    }


    /**
     * Return the language ID for the ISO 639-1 code specified.
     *
     * If the code cannot be found, returns the default language.
     * If that isn't set either, returns the first language encountered.
     * If none can be found, returns boolean false.
     * Note that you can supply the complete string from the Accept-Language
     * HTTP header.  This method will take care of chopping it into pieces
     * and trying to pick a suitable language.
     * However, it will not pick the most suitable one according to RFC2616
     * and others, but only tries to find the first language range it sees
     * able to serve.
     * @static
     * @param   string    $langCode         The ISO 639-1 language code
     * @return  mixed                       The language ID on success,
     *                                      false otherwise
     * @global  mixed     $objDatabase      Database object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getLangIdByIso639_1($langCode)
    {
        global $objDatabase;

        // Something like "fr; q=1.0, en-gb; q=0.5"
        $arrLangCode = preg_split('/,\s*/', $langCode);
        $strLangCode = "'".join("', '", preg_replace('/(?:-\w+)?(?:;\s*q(?:\=\d?\.?\d*)?)?/i', '', $arrLangCode))."'";
//echo("FWLanguage::getLangIdByIso639_1($langCode): Found languages: $strLangCode<br />");

        $objResult = $objDatabase->Execute("
            SELECT id
              FROM ".DBPREFIX."languages
             WHERE lang IN ($strLangCode)
        ");
        if ($objResult && $objResult->RecordCount() > 0) {
            return $objResult->fields['id'];
        }
        // The code was not found.  Pick the default.
        $objResult = $objDatabase->Execute("
            SELECT id
              FROM ".DBPREFIX."languages
             WHERE is_default='true'
        ");
        if ($objResult && $objResult->RecordCount() > 0) {
            return $objResult->fields['id'];
        }
        // Still nothing.  Pick the first language available.
        $objResult = $objDatabase->Execute("
            SELECT id
              FROM ".DBPREFIX."languages
        ");
        if ($objResult && $objResult->RecordCount() > 0) {
            return $objResult->fields['id'];
        }
        // Give up.
        return false;
    }

}

/* TEST

$arrLang = array(
    'de', 'en', 'fr, en', 'en, it', 'it, kr', 'gr, zh',
    'de, en', 'en, de', 'de-ch',
    'de-de, en-gb', 'en-gb, de-de',
    'de; q=0.1, en; q=0.9', 'de-de; q=0.1, de-ch; q=0.9',
);
foreach ($arrLang as $strLangCode) {
    echo("Code $strLangCode -> ID ".FWLanguage::getLangIdByIso639_1($strLangCode)."<br />");
}
die("Died.");

*/


?>
