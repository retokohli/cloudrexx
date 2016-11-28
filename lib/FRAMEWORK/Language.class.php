<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Framework language
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @version     2.3.0
 * @package     cloudrexx
 * @subpackage  lib_framework
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Framework language
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @version     2.3.0
 * @package     cloudrexx
 * @subpackage  lib_framework
 */
class FWLanguage
{
    /**
     * Array containing the active frontend languages
     * @var null | array
     */
    private static $arrFrontendLanguages = null;

    /**
     * Array containing the active backend languages
     * @var null | array
     */
    private static $arrBackendLanguages = null;

    /**
     * ID of the default frontend language
     *
     * @var integer
     * @access private
     */
    private static $defaultFrontendLangId;

    /**
     * ID of the default backend language
     *
     * @var integer
     * @access private
     */
    private static $defaultBackendLangId;


    /**
     * Loads the language config from the database
     *
     * This used to be in __construct but is also
     * called from core/language.class.php to reload
     * the config, so core/settings.class.php can
     * rewrite .htaccess (virtual lang dirs).
     */
    static function init()
    {
        global $_CONFIG, $objDatabase;

        $em = \Cx\Core\Core\Controller\Cx::instanciate()
            ->getDb()
            ->getEntityManager();
        $localeRepo = $em->getRepository('\Cx\Core\Locale\Model\Entity\Locale');
        $backendRepo = $em->getRepository('\Cx\Core\Locale\Model\Entity\Backend');

        $license = \Cx\Core_Modules\License\License::getCached($_CONFIG, $objDatabase);
        $license->check();
        $full = $license->isInLegalComponents('fulllanguage');

        // frontend locales
        foreach($localeRepo->findAll() as $locale) {
            // get the theme for each channel of the locale's language
            $frontends = $locale->getIso1()->getFrontends();
            $themeId = $mobileThemeId = $printThemeId = $pdfThemeId = $appThemeId = 0;
            foreach ($frontends as $frontend) {
                switch ($frontend->getChannel()) {
                    case \Cx\Core\View\Model\Entity\Theme::THEME_TYPE_MOBILE:
                        $mobileThemeId = $frontend->getTheme();
                        break;
                    case \Cx\Core\View\Model\Entity\Theme::THEME_TYPE_PRINT:
                        $printThemeId = $frontend->getTheme();
                        break;
                    case \Cx\Core\View\Model\Entity\Theme::THEME_TYPE_PDF:
                        $pdfThemeId = $frontend->getTheme();
                        break;
                    case \Cx\Core\View\Model\Entity\Theme::THEME_TYPE_APP:
                        $appThemeId = $frontend->getTheme();
                        break;
                    default: // web
                        $themeId = $frontend->getTheme();
                        break;
                }
            }
            // check if locale is default
            $isFrontendDefault = $locale->getId() == $_CONFIG['defaultLocaleId'];
            self::$arrFrontendLanguages[$locale->getId()] = array(
                'id'  => $locale->getId(),
                'lang' => $locale->getShortForm(),
                'name' => $locale->__toString(),
                'iso1' => $locale->getIso1()->getIso1(),
                'source_lang' => $locale->getSourceLanguage()->getIso1(),
                'themesid'   => $themeId,
                'print_themes_id' => $printThemeId,
                'pdf_themes_id' => $pdfThemeId,
                'mobile_themes_id' => $mobileThemeId,
                'app_themes_id' => $appThemeId,
                'frontend'   => true, // every existing locale is active
                'is_default' => $isFrontendDefault,
                'fallback'   => $locale->getFallback() ? $locale->getFallback()->getId() : false,
            );
            // activate only default locale, if system not in full lang mode
            if (!$full && !$isFrontendDefault) {
                self::$arrFrontendLanguages[$locale->getId()]['frontend'] = 0;
            }
            if ($isFrontendDefault) {
                self::$defaultFrontendLangId = $locale->getId();
            }
        }

        // backend languages
        foreach($backendRepo->findAll() as $backendLanguage) {
            // check if language is default
            $isBackendDefault = $backendLanguage->getId() == $_CONFIG['defaultLanguageId'];
            self::$arrBackendLanguages[$backendLanguage->getId()] = array(
                'id' => $backendLanguage->getId(),
                'lang' => $backendLanguage->getIso1()->getIso1(),
                'name' => $backendLanguage->__toString(),
                'backend' => true,
                'is_default' => $isBackendDefault
            );
            // activate only default language, if system not in full lang mode
            if (!$full && !$isBackendDefault) {
                self::$arrBackendLanguages[$backendLanguage->getId()]['backend'] = 0;
            }
            if ($isBackendDefault) {
                self::$defaultBackendLangId = $backendLanguage->getId();
            }
        }
    }


    /**
     * Returns an array of active language names, indexed by language ID
     * @param   string  $mode     'frontend' or 'backend' languages.
     *                            Defaults to 'frontend'
     * @return  array             The array of enabled language names
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getNameArray($mode='frontend')
    {
        switch($mode) {
            case 'frontend':
                if (!isset(self::$arrFrontendLanguages)) self::init();
                $arrLanguages = self::$arrFrontendLanguages;
                break;
            case 'backend':
                if (!isset(self::$arrBackendLanguages)) self::init();
                $arrLanguages = self::$arrBackendLanguages;
                break;
            default:
                return null;
        }
        $arrName = array();
        foreach ($arrLanguages as $lang_id => $arrLanguage) {
            if (empty($arrLanguage[$mode])) continue;
            $arrName[$lang_id] = $arrLanguage['name'];
        }
        return $arrName;
    }


    /**
     * Returns an array of active language IDs
     *
     * Note that the array returned contains the language ID both as
     * key and value, for your convenience.
     * @param   string  $mode     'frontend' or 'backend' languages.
     *                            Defaults to 'frontend'
     * @return  array             The array of enabled language IDs
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getIdArray($mode='frontend')
    {
        switch($mode) {
            case 'frontend':
                if (!isset(self::$arrFrontendLanguages)) self::init();
                $arrLanguages = self::$arrFrontendLanguages;
                break;
            case 'backend':
                if (!isset(self::$arrBackendLanguages)) self::init();
                $arrLanguages = self::$arrBackendLanguages;
                break;
            default:
                return null;
        }
        $arrId = array();
        foreach ($arrLanguages as $lang_id => $arrLanguage) {
            if (empty($arrLanguage[$mode])) continue;
            $arrId[$lang_id] = $lang_id;
        }
        return $arrId;
    }


    /**
     * Returns the ID of the default frontend language
     * @return integer Language ID
     */
    static function getDefaultLangId()
    {
        if (empty(self::$defaultFrontendLangId)) {
            self::init();
        }
        return self::$defaultFrontendLangId;
    }

    /**
     * Returns the ID of the default backend language
     * @return integer Language ID
     */
    static function getDefaultBackendLangId()
    {
        if (empty(self::$defaultBackendLangId)) {
            self::init();
        }
        return self::$defaultBackendLangId;
    }


    /**
     * Returns the complete frontend language data
     * @see     FWLanguage()
     * @return  array           The language data
     * @access  public
     */
    static function getLanguageArray()
    {
        if (empty(self::$arrFrontendLanguages)) self::init();
        return self::$arrFrontendLanguages;
    }

    /**
     * Returns the complete backend language data
     * @see     FWLanguage()
     * @return  array           The language data
     * @access  public
     */
    static function getBackendLanguageArray()
    {
        if (empty(self::$arrBackendLanguages)) self::init();
        return self::$arrBackendLanguages;
    }


    /**
     * Return only the languages active in the frontend
     * @author     Stefan Heinemann <sh@adfinis.com>
     * @return     array(
     *                 array(
     *                     'id'         => {lang_id},
     *                     'lang'       => {iso_639-1},
     *                     'name'       => {name},
     *                     'themesid'   => {theme_id},
     *                     'frontend'   => {bool},
     *                     'backend'    => {bool},
     *                     'is_default' => {bool},
     *                     'fallback'   => {language_id},
     *                 )
     *             )
     */
    public static function getActiveFrontendLanguages()
    {
        if (empty(self::$arrFrontendLanguages)) {
            self::init();
        }
        $arr = array();
        foreach (self::$arrFrontendLanguages as $id => $lang) {
            if ($lang['frontend']) {
                $arr[$id] = $lang;
            }
        }
        return $arr;
    }


    /**
     * Return only the languages active in the backend
     * @author     Stefan Heinemann <sh@adfinis.com>
     * @return     array(
     *                 array(
     *                     'id'         => {lang_id},
     *                     'lang'       => {iso_639-1},
     *                     'name'       => {name},
     *                     'themesid'   => {theme_id},
     *                     'frontend'   => {bool},
     *                     'backend'    => {bool},
     *                     'is_default' => {bool},
     *                     'fallback'   => {language_id},
     *                 )
     *             )
     */
    public static function getActiveBackendLanguages()
    {
        if (empty(self::$arrBackendLanguages)) {
            self::init();
        }
        $arr = array();
        foreach (self::$arrBackendLanguages as $id => $lang) {
            if ($lang['backend']) {
                $arr[$id] = $lang;
            }
        }
        return $arr;
    }


    /**
     * Returns single frontend language related fields
     *
     * Access language data by specifying the language ID and the index
     * as initialized by {@link FWLanguage()}.
     * @return  mixed           Language data field content
     * @access  public
     */
    static function getLanguageParameter($id, $index)
    {
        if (empty(self::$arrFrontendLanguages)) self::init();
        return (isset(self::$arrFrontendLanguages[$id][$index])
            ? self::$arrFrontendLanguages[$id][$index] : false);
    }

    /**
     * Returns single backend language related fields
     *
     * Access language data by specifying the language ID and the index
     * as initialized by {@link FWLanguage()}.
     * @return  mixed           Language data field content
     * @access  public
     */
    static function getBackendLanguageParameter($id, $index)
    {
        if (empty(self::$arrBackendLanguages)) self::init();
        return (isset(self::$arrBackendLanguages[$id][$index])
            ? self::$arrBackendLanguages[$id][$index] : false);
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
     * @todo    Use the Html class instead
     */
    static function getMenu($selectedId=0, $menuName='', $onchange='')
    {
        $menu = self::getMenuoptions($selectedId, true);
        if ($menuName) {
            $menu = "<select id='$menuName' name='$menuName'".
                    ($onchange ? ' onchange="'.$onchange.'"' : '').
                    ">\n$menu</select>\n";
        }
        return $menu;
    }


    /**
     * Returns HTML code to display a language selection dropdown menu
     * for the active frontend languages only.
     *
     * Does only contain the <select> tag pair if the optional $menuName
     * is specified and evaluates to a true value.
     * Frontend use only.
     * @param   integer $selectedId The optional preselected language ID
     * @param   string  $menuName   The optional menu name
     * @param   string  $onchange   The optional onchange code
     * @return  string              The dropdown menu HTML code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @todo    Use the Html class instead
     */
    static function getMenuActiveOnly($selectedId=0, $menuName='', $onchange='')
    {
        $menu = self::getMenuoptions($selectedId, false);
        if ($menuName) {
            $menu = "<select id='$menuName' name='$menuName'".
                    ($onchange ? ' onchange="'.$onchange.'"' : '').
                    ">\n$menu</select>\n";
        }
//echo("getMenu(select=$selectedId, name=$menuName, onchange=$onchange): made menu: ".htmlentities($menu)."<br />");
        return $menu;
    }


    /**
     * Returns HTML code for the language menu options
     * @param   integer $selectedId   The optional preselected language ID
     * @param   boolean $flagInactive If true, all languages are added,
     *                                only the active ones otherwise
     * @return  string                The menu options HTML code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @todo    Use the Html class instead
     */
    static function getMenuoptions($selectedId=0, $flagInactive=false)
    {
        if (empty(self::$arrFrontendLanguages)) self::init();
        $menuoptions = '';
        foreach (self::$arrFrontendLanguages as $id => $arrLanguage) {
            // Skip inactive ones if desired
            if (!$flagInactive && empty($arrLanguage['frontend']))
                continue;
            $menuoptions .=
                "<option value='$id'".
                ($selectedId == $id ? ' selected="selected"' : '').
                ">{$arrLanguage['name']}</option>\n";
        }
        return $menuoptions;
    }


    /**
     * Return the language ID for the ISO 639-1 code specified.
     *
     * If the code cannot be found, returns the default language.
     * If that isn't set either, returns the first language encountered.
     * If none can be found, returns null.
     * Note that you can supply the complete string from the Accept-Language
     * HTTP header.  This method will take care of chopping it into pieces
     * and trying to pick a suitable language.
     * However, it will not pick the most suitable one according to RFC2616,
     * but only returns the first language that fits.
     * @static
     * @param   string    $langCode         The ISO 639-1 language code
     * @return  mixed                       The language ID on success,
     *                                      null otherwise
     *
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @author  Nicola Tommasi <nicola.tommasi@comvation.com>
     */
    static function getLangIdByIso639_1($langCode)
    {
        global $_CONFIG;

        // Don't bother if the "code" looks like an ID already
        if (is_numeric($langCode)) return $langCode;

        $em = \Cx\Core\Core\Controller\Cx::instanciate()
            ->getDb()
            ->getEntityManager();
        $qb = $em->createQueryBuilder();

        // Something like "fr; q=1.0, en-gb; q=0.5"
        $arrLangCode = preg_split('/,\s*/', $langCode);
        $arrLangCode = preg_replace(
            '/(?:-\w+)?(?:;\s*q(?:\=\d?\.?\d*)?)?/i',
            '',
            $arrLangCode
        );
        // search for locale with matching iso1 code
        $qb->select('l')
            ->from('\Cx\Core\Locale\Model\Entity\Locale', 'l')
            ->where($qb->expr()->in('l.iso1', $arrLangCode))
            ->setMaxResults(1);
        $query = $qb->getQuery();
        $locale = $query->getResult();
        if ($locale) {
            return $locale[0]->getId();
        }
        // The code was not found.  Pick the default.
        if (isset($_CONFIG['defaultLocaleId'])) {
            return $_CONFIG['defaultLocaleId'];
        }
        // Still nothing.  Pick the first frontend language available.
        $qb = $em->createQueryBuilder();
        $qb->select('l')
            ->from('\Cx\Core\Locale\Model\Entity\Locale', 'l')
            ->setMaxResults(1);
        $query = $qb->getQuery();
        $locale = $query->getSingleResult();
        if ($locale) {
            return $locale->getId();
        }
        // Give up.
        return null;
    }


    /**
     * Return the language code from the database for the given frontend language ID
     *
     * Returns false on failure, or if the ID is invalid
     * @param   integer $langId         The frontend language ID
     * @return  mixed                   The two letter code, or false
     * @static
     */
    static function getLanguageCodeById($langId)
    {
        if (empty(self::$arrFrontendLanguages)) self::init();
        return self::getLanguageParameter($langId, 'lang');
    }


    /**
     * Return the language code from the database for the given backend language ID
     *
     * Returns false on failure, or if the ID is invalid
     * @param   integer $langId         The frontend language ID
     * @return  mixed                   The two letter code, or false
     * @static
     */
    static function getBackendLanguageCodeById($langId)
    {
        if (empty(self::$arrBackendLanguages)) self::init();
        return self::getBackendLanguageParameter($langId, 'lang');
    }


    /**
     * Return the frontend language ID for the given code
     *
     * Returns false on failure, or if the code is unknown
     * @param   string                    The two letter code
     * @return  integer   $langId         The language ID, or false
     * @static
     */
    static function getLanguageIdByCode($code)
    {
        if (empty(self::$arrFrontendLanguages)) self::init();
        foreach (self::$arrFrontendLanguages as $id => $arrLanguage) {
            if ($arrLanguage['lang'] == $code) return $id;
        }
        return false;
    }


    /**
     * Return the backend language ID for the given code
     *
     * Returns false on failure, or if the code is unknown
     * @param   string                    The two letter code
     * @return  integer   $langId         The language ID, or false
     * @static
     */
    static function getBackendLanguageIdByCode($code)
    {
        if (empty(self::$arrBackendLanguages)) self::init();
        foreach (self::$arrBackendLanguages as $id => $arrLanguage) {
            if ($arrLanguage['lang'] == $code) return $id;
        }
        return false;
    }


    /**
     * Return the fallback language ID for the given frontend language ID
     *
     * Returns false on failure, or if the ID is invalid
     * @param   integer $langId         The language ID
     * @return  integer   $langId         The language ID, or false
     * @static
     */
    static function getFallbackLanguageIdById($langId)
    {
        if (empty(self::$arrFrontendLanguages)) self::init();
        if ($langId == self::getDefaultLangId()) return false;
        $fallback_lang = self::getLanguageParameter($langId, 'fallback');
        if ($langId == $fallback_lang) return false;
        return $fallback_lang;
    }

    /**
     * Builds an array mapping frontend language ids to fallback language ids.
     *
     * @return array ( language id => fallback language id )
     */
    static function getFallbackLanguageArray() {
        if (empty(self::$arrFrontendLanguages)) {
            self::init();
        }
        $arr = array();
        foreach(self::$arrFrontendLanguages as $frontendLanguage) {
            $langId = $frontendLanguage['id'];
            $fallbackLangId = $frontendLanguage['fallback'];

            if ($langId == $fallbackLangId || $langId == self::getDefaultLangId()) {
                $fallbackLangId =false;
            }

            $arr[$langId] = $fallbackLangId;
        }
        return $arr;
    }

    /**
     * Returns the iso1 codes used in any frontend locale
     * Since two locales with the same iso1 code us the same theme,
     * a iso1 code is returned only once
     *
     * @return array Contains the iso1 codes of the active frontend locales
     */
    public static function getActiveThemeLanguages() {
        if (empty(self::$arrFrontendLanguages)) {
            self::init();
        }
        $arr = array();
        foreach (self::$arrFrontendLanguages as $id => $lang) {
            $arr[] = $lang['iso1'];
        }
        $arr = array_unique($arr);
        return $arr;
    }
}
