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
 * Main controller for Language Manager
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_languagemanager
 */

namespace Cx\Core\LanguageManager\Controller;

/**
 * Main controller for Language Manager
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_languagemanager
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController implements \Cx\Core\Event\Model\Entity\EventListener {

    /**
     * List of replacements for additional characters for slugifier
     * @var array $replacementCharlist
     */
    public static $REPLACEMENT_CHARLIST = array(
        // German
        'ä' => 'ae',
        'Ä' => 'Ae',
        'ö' => 'oe',
        'Ö' => 'Oe',
        'ß' => 'ss',
        'ü' => 'ue',
        'Ü' => 'Ue',
        // French
        'à' => 'a',
        'À' => 'A',
        'â' => 'a',
        'Â' => 'A',
        'æ' => 'ae',
        'Æ' => 'Ae',
        'Ç' => 'C',
        'ç' => 'c',
        'é' => 'e',
        'É' => 'E',
        'è' => 'e',
        'È' => 'E',
        'ë' => 'e',
        'Ë' => 'E',
        'ê' => 'e',
        'Ê' => 'E',
        'ï' => 'i',
        'Ï' => 'I',
        'î' => 'i',
        'Î' => 'I',
        'ô' => 'o',
        'Ô' => 'O',
        'Œ' => 'Oe',
        'œ' => 'oe',
        'ù' => 'u',
        'Ù' => 'U',
        'û' => 'u',
        'Û' => 'U',
        'ÿ' => 'y',
        'Ÿ' => 'Y',
        // Spanish
        'á' => 'a',
        'Á' => 'A',
        'í' => 'i',
        'Í' => 'I',
        'ñ' => 'n',
        'Ñ' => 'N',
        'ó' => 'o',
        'Ó' => 'O',
        'ú' => 'u',
        'Ú' => 'U',
        '¡' => '!',
        '¿' => '?',
    );

    /**
     * @var array List of components who's language already is in $_ARRAYLANG
     */
    protected $componentsWithLoadedLang = array();

    /**
     * Returns all Controller class names for this component (except this)
     *
     * Be sure to return all your controller classes if you add your own
     * @return array List of Controller class names (without namespace)
     */
    public function getControllerClasses()
    {
        return array('EsiWidget');
    }

    /**
     * Returns a list of JsonAdapter class names
     *
     * The array values might be a class name without namespace. In that case
     * the namespace \Cx\{component_type}\{component_name}\Controller is used.
     * If the array value starts with a backslash, no namespace is added.
     *
     * Avoid calculation of anything, just return an array!
     * @return array List of ComponentController classes
     */
    public function getControllersAccessableByJson()
    {
        return array('EsiWidgetController');
    }

    public function registerEventListeners() {
        $this->cx->getEvents()->addEventListener('preComponent', $this);
    }

    /**
     * Event handler to load component language
     * @param string $eventName Name of triggered event, should always be static::EVENT_NAME
     * @param array $eventArgs Supplied arguments, should be an array (see DBG message below)
     */
    public function onEvent($eventName, array $eventArgs) {
        global $_ARRAYLANG;

        // we might be in a hook where lang is not yet initialized (before resolve)
        if (!count($_ARRAYLANG)) {
            $_ARRAYLANG = array();
        }

        $frontend = $this->cx->getMode() == \Cx\Core\Core\Controller\Cx::MODE_FRONTEND;
        $objInit = \Env::get('init');
        switch ($eventName) {
            case 'preComponent':
                // Skip if this component's lang already is in $_ARRAYLANG
                if (
                    in_array(
                        $eventArgs['componentName'],
                        $this->componentsWithLoadedLang
                    )
                ) {
                    return;
                }

                $_ARRAYLANG = array_merge(
                    $_ARRAYLANG,
                    $objInit->getComponentSpecificLanguageData(
                        $eventArgs['componentName'],
                        $frontend
                    )
                );
                $this->componentsWithLoadedLang[] = $eventArgs['componentName'];
                break;
        }
    }

     /**
     * Load your component.
     *
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $subMenuTitle, $_ARRAYLANG;
        $subMenuTitle = $_ARRAYLANG['TXT_LANGUAGE_SETTINGS'];

        $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
        $cachedRoot = $this->cx->getTemplate()->getRoot();

        \Permission::checkAccess(22, 'static');
        $objLanguageManager = new \Cx\Core\LanguageManager\Controller\LanguageManager();
        $objLanguageManager->getLanguagePage();

        $this->cx->getTemplate()->setRoot($cachedRoot);
    }

    /**
     * Do something after resolving is done
     *
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function postResolve(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                global $objInit, $_LANGID, $_FRONTEND_LANGID, $_CORELANG, $_ARRAYLANG, $plainCmd;

                $objInit->_initBackendLanguage();
                $objInit->getUserFrontendLangId();

                $_LANGID = $objInit->getBackendLangId();
                $_FRONTEND_LANGID = $objInit->userFrontendLangId;
                /**
                 * Language constants
                 *
                 * Defined as follows:
                 * - BACKEND_LANG_ID is set to the visible backend language
                 *   in the backend *only*.  In the frontend, it is *NOT* defined!
                 *   It indicates a backend user and her currently selected language.
                 *   Use this in methods that are intended *for backend use only*.
                 *   It *MUST NOT* be used to determine the language for any kind of content!
                 * - FRONTEND_LANG_ID is set to the selected frontend or content language
                 *   both in the back- and frontend.
                 *   It *always* represents the language of content being viewed or edited.
                 *   Use FRONTEND_LANG_ID for that purpose *only*!
                 * - LANG_ID is set to the same value as BACKEND_LANG_ID in the backend,
                 *   and to the same value as FRONTEND_LANG_ID in the frontend.
                 *   It *always* represents the current users' selected language.
                 *   It *MUST NOT* be used to determine the language for any kind of content!
                 * @since 2.2.0
                 */
                define('FRONTEND_LANG_ID', $_FRONTEND_LANGID);
                define('BACKEND_LANG_ID', $_LANGID);
                define('LANG_ID', $_LANGID);

                /**
                 * Core language data
                 * @ignore
                 */
                // Corelang might be initialized by CSRF already...
                if (!is_array($_CORELANG) || !count($_CORELANG)) {
                    $_CORELANG = $objInit->loadLanguageData('core');
                }

                /**
                 * Module specific language data
                 * @ignore
                 */
                $_ARRAYLANG = $objInit->loadLanguageData($plainCmd);
                $_ARRAYLANG = array_merge($_ARRAYLANG, $_CORELANG);
                \Env::set('lang', $_ARRAYLANG);
                break;

            default:
                break;
        }
    }

    /**
     * Register the events
     */
    public function registerEvents()
    {
        $this->cx->getEvents()->addEvent('languageStatusUpdate');
    }

    /**
     * Do something before main template gets parsed
     *
     * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
     * CALCULATE YOUR STUFF AS LATE AS POSSIBLE
     * @param \Cx\Core\Html\Sigma                       $template   The main template
     */
    public function preFinalize(\Cx\Core\Html\Sigma $template) {
        if ($this->cx->getMode() != \Cx\Core\Core\Controller\Cx::MODE_FRONTEND) {
            return;
        }
        $this->parseLocaleList($template);
    }
    
    /**
     * Parses locale list in a template file
     * @todo Does language list only for now. Update as soon as locales are available
     * @param \Cx\Core\Html\Sigma $template Template file to parse locales in
     */
    public function parseLocaleList($template) {
        if (!$template->blockExists('locale_alternate_list')) {
            return;
        }
        $currentPage = $this->cx->getPage();
        $listProtectedPages = \Cx\Core\Setting\Controller\Setting::getValue(
            'coreListProtectedPages',
            'Config'
        ) == 'on';
        foreach (\FWLanguage::getActiveFrontendLanguages() as $lang) {
            $langId = $lang['id'];
            $lang = $lang['lang'];
            $langPage = $currentPage->getNode()->getPage($langId);
            // if page is not translated, inactive (incl. scheduled publishing) or protected
            if (
                !$langPage ||
                !$langPage->isActive() ||
                (
                    !$listProtectedPages &&
                    $langPage->isFrontendProtected() &&
                    !\Permission::checkAccess($langPage->getFrontendAccessId(), 'dynamic', true)
                )
            ) {
                continue;
            }
            $template->setVariable(array(
                'PAGE_LINK' => contrexx_raw2xhtml(\Cx\Core\Routing\Url::fromPage($langPage)->toString()),
                'PAGE_TITLE' => contrexx_raw2xhtml($langPage->getTitle()),
                'LOCALE' => $lang,
                'LANGUAGE_CODE' => $lang,
                //'COUNTRY_NAME' => ,
                //'COUNTRY_CODE' => ,
            ));
            $template->parse('locale_alternate_list');
        }
    }

    /**
     * Replaces international characters (like German umlauts)
     * @param string $text Text to replace
     * @return string replaced text
     */
    public function replaceInternationalCharacters($text) {
        $text = str_replace(
            array_keys(static::$REPLACEMENT_CHARLIST),
            static::$REPLACEMENT_CHARLIST,
            $text
        );
        return $text;
    }

    /**
     * Do something after system initialization
     *
     * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
     * CALCULATE YOUR STUFF AS LATE AS POSSIBLE.
     * This event must be registered in the postInit-Hook definition
     * file config/postInitHooks.yml.
     *
     * @param \Cx\Core\Core\Controller\Cx $cx The instance of \Cx\Core\Core\Controller\Cx
     */
    public function postInit(\Cx\Core\Core\Controller\Cx $cx)
    {
        $widgetController = $this->getComponent('Widget');
        $langManager      = new LanguageManager();
        $widgetNames      = array(
            'CHARSET',
            'LANGUAGE_NAVBAR',
            'LANGUAGE_NAVBAR_SHORT',
            'ACTIVE_LANGUAGE_NAME'
        );

        foreach (
            array_merge(
                $widgetNames,
                $langManager->getLanguagePlaceholderNames()
            ) as $widgetName
        ) {
            $widget = new \Cx\Core_Modules\Widget\Model\Entity\EsiWidget(
                $this,
                $widgetName
            );
            $widget->setEsiVariable(
                \Cx\Core_Modules\Widget\Model\Entity\EsiWidget::ESI_VAR_ID_THEME |
                \Cx\Core_Modules\Widget\Model\Entity\EsiWidget::ESI_VAR_ID_CHANNEL
            );
            $widgetController->registerWidget(
                $widget
            );
        }
    }
}