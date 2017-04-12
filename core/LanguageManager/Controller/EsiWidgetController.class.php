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
 * Class EsiWidgetController
 *
 * @copyright   CLOUDREXX CMS - Cloudrexx AG Thun
 * @author      Project Team SS4U <info@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_languagemanager
 * @version     1.0.0
 */

namespace Cx\Core\LanguageManager\Controller;

/**
 * JsonAdapter Controller to handle EsiWidgets
 * Usage:
 * - Create a subclass that implements parseWidget()
 * - Register it as a Controller in your ComponentController
 *
 * @copyright   CLOUDREXX CMS - Cloudrexx AG Thun
 * @author      Project Team SS4U <info@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_languagemanager
 * @version     1.0.0
 */

class EsiWidgetController extends \Cx\Core_Modules\Widget\Controller\EsiWidgetController {

    /**
     * Parses a widget
     *
     * @param string                                 $name     Widget name
     * @param \Cx\Core\Html\Sigma Widget             $template Template
     * @param \Cx\Core\Routing\Model\Entity\Response $response Response object
     * @param array                                  $params   Get parameters
     *
     * @return null
     */
    public function parseWidget($name, $template, $response, $params)
    {
        if ($name === 'CHARSET') {
            $template->setVariable($name, \Env::get('init')->getFrontendLangCharset());
            return;
        }

        if ($name == 'ACTIVE_LANGUAGE_NAME') {
            $template->setVariable(
                $name,
                \FWLanguage::getLanguageCodeById($params['lang'])
            );
            return;
        }

        $matches = null;
        if (preg_match('/^LANG_SELECTED_([A-Z]{2})$/', $name, $matches)) {
            $selected = '';
            if (strtolower($matches[1]) === $params['lang']) {
                $selected = 'selected';
            }
            $template->setVariable($name, $selected);
            return;
        }

        $page = $params['page'];
        if (!$page) {
            return;
        }

        $navbar = new \Navigation($page->getId(), $page);
        if ($name === 'LANGUAGE_NAVBAR') {
            $template->setVariable($name, $navbar->getFrontendLangNavigation($page));
            return;
        }

        if ($name === 'LANGUAGE_NAVBAR_SHORT') {
            $template->setVariable($name, $navbar->getFrontendLangNavigation($page, true));
            return;
        }

        $langMatches = null;
        if (preg_match('/^LANG_CHANGE_([A-Z]{2})$/', $name, $langMatches)) {
            $langId = \FWLanguage::getLangIdByIso639_1($langMatches[1]);
            $template->setVariable(
                $name,
                $navbar->getLanguageLinkById($page, $langId)
            );
        }
    }
}
