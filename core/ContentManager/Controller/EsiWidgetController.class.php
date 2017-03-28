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
 * @subpackage  core_contentmanager
 * @version     1.0.0
 */

namespace Cx\Core\ContentManager\Controller;

/**
 * JsonAdapter Controller to handle EsiWidgets
 * Usage:
 * - Create a subclass that implements parseWidget()
 * - Register it as a Controller in your ComponentController
 *
 * @copyright   CLOUDREXX CMS - Cloudrexx AG Thun
 * @author      Project Team SS4U <info@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_contentmanager
 * @version     1.0.0
 */

class EsiWidgetController extends \Cx\Core_Modules\Widget\Controller\EsiWidgetController {

    /**
     * Page Id
     *
     * @var integer
     */
    protected $pageId;

    /**
     * Parses a widget
     *
     * @param string              $name     Widget name
     * @param \Cx\Core\Html\Sigma $template Widget template
     * @param string              $locale   RFC 3066 locale identifier
     */
    public function parseWidget($name, $template, $locale) 
    {
        if ($name === 'TXT_CORE_LAST_MODIFIED_PAGE') {
            $arrayLang = \Env::get('init')->getComponentSpecificLanguageData(
                'Core',
                true,
                \FWLanguage::getLangIdByIso639_1($locale)
            );
            $template->setVariable(
                $name,
                $arrayLang['TXT_CORE_LAST_MODIFIED_PAGE']
            );
            return;
        }

        $em       = $this->cx->getDb()->getEntityManager();
        $pageRepo = $em->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
        $page     = $pageRepo->find($this->pageId);

        if (!$page) {
            return;
        }

        if ($name === 'LAST_MODIFIED_PAGE') {
            $dateTimeController = $this->cx->getComponent('DateTime');
            $dateTime = $dateTimeController->db2user($page->getUpdatedAt());
            $template->setVariable(
                $name,
                $dateTime->format(ASCMS_DATE_FORMAT_DATE)
            );
            return;
        }

        switch ($name) {
            case 'TITLE':
            case 'NAVTITLE':
                $widgetValue = contrexx_raw2xhtml($page->getTitle());
                break;

            case 'METAKEYS':
            case 'METADESC':
            case 'METAIMAGE':
                $widgetValue = '';
                if (!$page->getMetarobots()) {
                    break;
                }
            case 'METATITLE':
                $methodName  = 'get' . ucfirst(strtolower($name));
                $widgetValue = contrexx_raw2xhtml($page->$methodName());
                if ($name === 'METAIMAGE' && empty($widgetValue)) {
                    $widgetValue = \Cx\Core\Setting\Controller\Setting::getValue(
                        'defaultMetaimage',
                        'Config'
                    );
                }
                break;

            case 'METAROBOTS':
                $widgetValue = 'none';
                if ($page->getMetarobots()) {
                    $widgetValue = 'all';
                }
                break;

            case 'CONTENT_TITLE':
                $widgetValue = $page->getContentTitle();
                break;

            case 'CONTENT_TEXT':
                $widgetValue = $page->getContent();
                break;

            case 'CSS_NAME':
                $widgetValue = contrexx_raw2xhtml($page->getCssName());
                break;
        }
        $template->setVariable($name, $widgetValue);
    }

    /**
     * Returns the content of a widget
     *
     * @param array $params JsonAdapter parameters
     *
     * @return array Content in an associative array
     */
    public function getWidget($params)
    {
        if (isset($params['get']) && isset($params['get']['page'])) {
            $this->pageId = $params['get']['page'];
        }
        return parent::getWidget($params);
    }
}