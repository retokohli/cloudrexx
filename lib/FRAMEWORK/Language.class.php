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
    public $arrLanguage = null;

    /**
     * ID of the default language
     *
     * @var integer
     * @access private
     */
    private static $defaultLangId;

    /**
     * Constructor (PHP5)
     * @access  private
     * @global  ADONewConnection
     */
    function __construct()
    {
        $this->loadLangConfig();
    }

    /**
     * Loads the language config from database.
     *
     * This used to be in __construct but is also
     * called from core/language.class.php to reload
     * the config, so core/settings.class.php can
     * rewrite .htaccess (virtual lang dirs).
     */
    function loadLangConfig() {
        global $objDatabase;
         $objResult = $objDatabase->Execute("
            SELECT id, lang, name, charset, themesid,
                   frontend, backend, is_default
              FROM ".DBPREFIX."languages
             ORDER BY id ASC
         ");
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

                if ($objResult->fields['is_default'] == 'true') {
                    self::$defaultLangId = $objResult->fields['id'];
                }

                $objResult->MoveNext();
            }
        }

    }


    /**
     * Returns the ID of the default language
     *
     * @return integer Language ID
     */
    static function getDefaultLangId()
    {
        if (empty(self::$defaultLangId)) {
            self::init();
        }

        return self::$defaultLangId;
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
        return
            (isset($this->arrLanguage[$id][$index])
                ? $this->arrLanguage[$id][$index] : false
            );
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
     * However, it will not pick the most suitable one according to RFC2616,
     * but only returns the first language that fits.
     * @static
     * @param   string    $langCode         The ISO 639-1 language code
     * @return  mixed                       The language ID on success,
     *                                      false otherwise
     * @global  ADONewConnection
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getLangIdByIso639_1($langCode)
    {
        global $objDatabase;

        // Something like "fr; q=1.0, en-gb; q=0.5"
        $arrLangCode = preg_split('/,\s*/', $langCode);
        $strLangCode = "'".join("', '", preg_replace('/(?:-\w+)?(?:;\s*q(?:\=\d?\.?\d*)?)?/i', '', $arrLangCode))."'";

        $objResult = $objDatabase->Execute("
            SELECT id
              FROM ".DBPREFIX."languages
             WHERE lang IN ($strLangCode)
               AND frontend=1
        ");
        if ($objResult && $objResult->RecordCount() > 0) {
            return $objResult->fields['id'];
        }
        // The code was not found.  Pick the default.
        $objResult = $objDatabase->Execute("
            SELECT id
              FROM ".DBPREFIX."languages
             WHERE is_default='true'
               AND frontend=1
        ");
        if ($objResult && $objResult->RecordCount() > 0) {
            return $objResult->fields['id'];
        }
        // Still nothing.  Pick the first frontend language available.
        $objResult = $objDatabase->Execute("
            SELECT id
              FROM ".DBPREFIX."languages
             WHERE frontend=1
        ");
        if ($objResult && $objResult->RecordCount() > 0) {
            return $objResult->fields['id'];
        }
        // Pick the first language.
        $objResult = $objDatabase->Execute("
            SELECT id
              FROM ".DBPREFIX."languages
             WHERE frontend=1
        ");
        if ($objResult && $objResult->RecordCount() > 0) {
            return $objResult->fields['id'];
        }
        // Give up.
        return false;
    }


    /**
     * Return the language code from the database for the given ID
     *
     * Returns false on failure, or the empty string if the code
     * could not be found.
     * @global  ADONewConnection
     * @param   integer $langId         The language ID
     * @return  mixed                   The two letter code, the empty string,
     *                                  or false
     * @static
     */
    static function getLanguageCodeById($langId)
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            SELECT lang
              FROM ".DBPREFIX."languages
             WHERE id=$langId
        ");
        if (!$objResult) {
            return false;
        }
        if (!$objResult->EOF) {
            return $objResult->fields['lang'];
        }
        return '';
    }

}

?>
