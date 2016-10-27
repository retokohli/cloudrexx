<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2016
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
 * Backend controller to create the FavoriteList backend view.
 *
 * @copyright   Cloudrexx AG
 * @author      Manuel Schenk <manuel.schenk@comvation.com>
 * @package     cloudrexx
 * @subpackage  module_favoritelist
 * @version     5.0.0
 */

namespace Cx\Modules\FavoriteList\Controller;

/**
 * Backend controller to create the FavoriteList backend view.
 * @copyright   Cloudrexx AG
 * @author      Manuel Schenk <manuel.schenk@comvation.com>
 * @package     cloudrexx
 * @subpackage  module_favoritelist
 * @version     5.0.0
 */
class BackendController extends \Cx\Core\Core\Model\Entity\SystemComponentBackendController
{

    /**
     * Sigma template instance
     * @var Cx\Core\Html\Sigma $template
     */
    protected $template;

    /**
     * module name
     * @var string $moduleName
     */
    protected $moduleName = 'FavoriteList';

    /**
     * module name for language placeholder
     * @var string $moduleNameLang
     */
    protected $moduleNameLang = 'FAVORITELIST';

    /**
     * Returns a list of available commands (?act=XY)
     * @return array List of acts
     */
    public function getCommands()
    {
        return array('Catalog', 'Favorite', 'Settings');
    }

    /**
     * Use this to parse your backend page
     *
     * You will get the template located in /View/Template/{CMD}.html
     * You can access Cx class using $this->cx
     * To show messages, use \Message class
     * @param \Cx\Core\Html\Sigma $template Template for current CMD
     * @param array $cmd CMD separated by slashes
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, array $cmd)
    {
        global $_ARRAYLANG;
        global $_CONFIG;

        $this->template = $template;

        // Parse entity view generation pages
        $entityClassName = $this->getNamespace() . '\\Model\\Entity\\' . current($cmd);
        if (in_array($entityClassName, $this->getEntityClasses())) {
            $this->parseEntityClassPage($template, $entityClassName, current($cmd));
            return;
        }

        // Not an entity, parse overview or settings
        switch (current($cmd)) {
            case 'Settings':
                if (!isset($cmd[1])) {
                    $cmd[1] = '';
                }
                switch ($cmd[1]) {
                    case 'Mailing':
                        if (!$template->blockExists('mailing')) {
                            return;
                        }
                        var_dump('mailing');
                        $template->setVariable(
                            'MAILING',
                            \Cx\Core\MailTemplate\Controller\MailTemplate::adminView(
                                $this->getName(),
                                'nonempty',
                                $_CONFIG['corePagingLimit'],
                                'settings/email'
                            )->get()
                        );
                        break;
                    default:
                        \Cx\Core\Setting\Controller\Setting::init($this->moduleName, 'config');
                        //get post values
                        $settings = isset($_POST['setting']) ? $_POST['setting'] : array();
                        if (isset($_POST['save'])) {
                            $includeFromSaveCheckbox = array(
                                'functionMail',
                                'functionPrint',
                                'functionRecommendation',
                                'functionInquiry',
                                'notificationMail',
                                'notificationPrint',
                                'notificationRecommendation',
                                'notificationInquiry',
                            );
                            $includeFromSaveText = array(
                                'notificationMailMail',
                                'notificationPrintMail',
                                'notificationRecommendationMail',
                                'notificationInquiryMail',
                                'pdfLogo',
                                'pdfAddress',
                                'pdfFooter',
                            );
                            foreach ($settings as $settingName => $settingValue) {
                                if (in_array($settingName, $includeFromSaveText)) {
                                    \Cx\Core\Setting\Controller\Setting::set($settingName, $settingValue);
                                    \Cx\Core\Setting\Controller\Setting::update($settingName);
                                    \Message::ok($_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_MESSAGE_SUCCESS']);
                                }
                            }
                            foreach ($includeFromSaveCheckbox as $settingName) {
                                if ($settings[$settingName]) {
                                    \Cx\Core\Setting\Controller\Setting::set($settingName, 1);
                                } else {
                                    \Cx\Core\Setting\Controller\Setting::set($settingName, 0);
                                }
                                \Cx\Core\Setting\Controller\Setting::update($settingName);
                                \Message::ok($_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_MESSAGE_SUCCESS']);
                            }
                        }
                        //get the settings values from DB
                        $this->template->setVariable(array(
                            $this->moduleNameLang . '_SETTINGS_FUNCTION_MAIL' => \Cx\Core\Setting\Controller\Setting::getValue('functionMail', $this->moduleName) ? 'checked="checked"' : '',
                            $this->moduleNameLang . '_SETTINGS_FUNCTION_PRINT' => \Cx\Core\Setting\Controller\Setting::getValue('functionPrint', $this->moduleName) ? 'checked="checked"' : '',
                            $this->moduleNameLang . '_SETTINGS_FUNCTION_RECOMMENDATION' => \Cx\Core\Setting\Controller\Setting::getValue('functionRecommendation', $this->moduleName) ? 'checked="checked"' : '',
                            $this->moduleNameLang . '_SETTINGS_FUNCTION_INQUIRY' => \Cx\Core\Setting\Controller\Setting::getValue('functionInquiry', $this->moduleName) ? 'checked="checked"' : '',
                            $this->moduleNameLang . '_SETTINGS_NOTIFICATION_MAIL' => \Cx\Core\Setting\Controller\Setting::getValue('notificationMail', $this->moduleName) ? 'checked="checked"' : '',
                            $this->moduleNameLang . '_SETTINGS_NOTIFICATION_MAIL_MAIL' => \Cx\Core\Setting\Controller\Setting::getValue('notificationMailMail', $this->moduleName),
                            $this->moduleNameLang . '_SETTINGS_NOTIFICATION_PRINT' => \Cx\Core\Setting\Controller\Setting::getValue('notificationPrint', $this->moduleName) ? 'checked="checked"' : '',
                            $this->moduleNameLang . '_SETTINGS_NOTIFICATION_PRINT_MAIL' => \Cx\Core\Setting\Controller\Setting::getValue('notificationPrintMail', $this->moduleName),
                            $this->moduleNameLang . '_SETTINGS_NOTIFICATION_RECOMMENDATION' => \Cx\Core\Setting\Controller\Setting::getValue('notificationRecommendation', $this->moduleName) ? 'checked="checked"' : '',
                            $this->moduleNameLang . '_SETTINGS_NOTIFICATION_RECOMMENDATION_MAIL' => \Cx\Core\Setting\Controller\Setting::getValue('notificationRecommendationMail', $this->moduleName),
                            $this->moduleNameLang . '_SETTINGS_NOTIFICATION_INQUIRY' => \Cx\Core\Setting\Controller\Setting::getValue('notificationInquiry', $this->moduleName) ? 'checked="checked"' : '',
                            $this->moduleNameLang . '_SETTINGS_NOTIFICATION_INQUIRY_MAIL' => \Cx\Core\Setting\Controller\Setting::getValue('notificationInquiryMail', $this->moduleName),
                            $this->moduleNameLang . '_SETTINGS_PDF_LOGO' => \Cx\Core\Setting\Controller\Setting::getValue('pdfLogo', $this->moduleName),
                            $this->moduleNameLang . '_SETTINGS_PDF_ADDRESS' => \Cx\Core\Setting\Controller\Setting::getValue('pdfAddress', $this->moduleName),
                            $this->moduleNameLang . '_SETTINGS_PDF_FOOTER' => \Cx\Core\Setting\Controller\Setting::getValue('pdfFooter', $this->moduleName),
                        ));
                }
                break;
            case '':
            default:
                if ($template->blockExists('overview')) {
                    $template->touchBlock('overview');
                }
        }
    }

    /**
     * Return true here if you want the first tab to be an entity view
     * @return boolean True if overview should be shown, false otherwise
     */
    protected function showOverviewPage()
    {
        return false;
    }

    /**
     * This function returns the ViewGeneration options for a given entityClass
     *
     * @access protected
     * @global $_ARRAYLANG
     * @global $_CONFIG
     * @param $entityClassName contains the FQCN from entity
     * @return array with options
     */
    protected function getViewGeneratorOptions($entityClassName, $dataSetIdentifier = '')
    {
        global $_ARRAYLANG;

        $classNameParts = explode('\\', $entityClassName);
        $classIdentifier = end($classNameParts);

        $langVarName = 'TXT_' . strtoupper($this->getType() . '_' . $this->getName() . '_ACT_' . $classIdentifier);
        if (isset($_ARRAYLANG[$langVarName])) {
            $header = $_ARRAYLANG[$langVarName];
        } else {
            $header = $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_ACT_DEFAULT'];
        }

        switch ($entityClassName) {
            case 'Cx\Modules\FavoriteList\Model\Entity\Catalog':
                if (!isset($_GET['order'])) {
                    $_GET['order'] = 'id';
                }
                return array(
                    'header' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_ACT_CATALOG'],
                    'fields' => array(
                        'id' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_ID'],
                        ),
                        'sessionId' => array(
                            'showOverview' => false,
                            'showDetail' => false,
                        ),
                        'name' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_NAME'],
                            'table' => array(
                                'parse' => function ($value, $rowData) {
                                    return '<a href=\'FavoriteList/Favorite?list_id=' . $rowData['id'] . '\'>' . $value . '</a>';
                                },
                            ),
                        ),
                        'date' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_DATE'],
                            'showDetail' => false,
                        ),
                        'favorites' => array(
                            'showOverview' => false,
                            'showDetail' => false,
                        ),
                    ),
                    'functions' => array(
                        'add' => true,
                        'edit' => true,
                        'delete' => true,
                        'sorting' => true,
                        'paging' => true,
                        'filtering' => false,
                    ),
                );
                break;
            case 'Cx\Modules\FavoriteList\Model\Entity\Favorite':
                if (!isset($_GET['order'])) {
                    $_GET['order'] = 'id';
                }
                return array(
                    'header' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_ACT_FAVORITE'],
                    'fields' => array(
                        'id' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_ID'],
                        ),
                        'title' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_TITLE'],
                        ),
                        'link' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_LINK'],
                        ),
                        'description' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_DESCRIPTION'],
                        ),
                        'info' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_INFO'],
                        ),
                        'image1' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_IMAGE_1'],
                        ),
                        'image2' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_IMAGE_2'],
                        ),
                        'image3' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_IMAGE_3'],
                        ),
                        'catalog' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_CATALOG'],
                        ),
                    ),
                    'filter_criteria' => array(
                        'list_id' => $_GET['list_id'],
                    ),
                    'functions' => array(
                        'add' => true,
                        'edit' => true,
                        'delete' => true,
                        'sorting' => true,
                        'paging' => true,
                        'filtering' => true,
                    ),
                    'order' => array(
                        'overview' => array(
                            'id',
                            'catalog',
                            'title',
                            'link',
                            'description',
                            'info',
                            'image1',
                            'image2',
                            'image3',
                        ),
                        'form' => array(
                            'id',
                            'catalog',
                            'title',
                            'link',
                            'description',
                            'info',
                            'image1',
                            'image2',
                            'image3',
                        ),
                    ),
                );
                break;
            case 'Cx\Modules\FavoriteList\Model\Entity\FormField':
                if (!isset($_GET['order'])) {
                    $_GET['order'] = 'id';
                }
                return array(
                    'header' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_ACT_FORMFIELD'],
                    'fields' => array(
                        'id' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_ID'],
                        ),
                        'name' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_NAME'],
                        ),
                        'type' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_TYP'],
                            'type' => 'select',
                            'validValues' => array(
                                'inputtext' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_INPUTTEXT'],
                                'textarea' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_TEXTAREA'],
                                'select' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_SELECT'],
                                'radio' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_RADIO'],
                                'checkbox' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_CHECKBOX'],
                                'mail' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_MAIL'],
                                'salutation' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_SALUTATION'],
                                'firstname' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_FIRSTNAME'],
                                'lastname' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_LASTNAME'],
                            ),
                        ),
                        'required' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_REQUIRED'],
                        ),
                        'order' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_ORDER'],
                        ),
                        'values' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . $this->moduleNameLang . '_FIELD_VALUES'],
                        ),
                    ),
                    'functions' => array(
                        'add' => true,
                        'edit' => true,
                        'delete' => true,
                        'sorting' => true,
                        'paging' => true,
                        'filtering' => false,
                    ),
                    'order' => array(
                        'overview' => array(
                            'id',
                            'name',
                            'type',
                            'required',
                            'order',
                            'values',
                        ),
                        'form' => array(
                            'id',
                            'name',
                            'type',
                            'required',
                            'order',
                            'values',
                        ),
                    ),
                );
                break;
            default:
                return array(
                    'header' => $header,
                    'functions' => array(
                        'add' => true,
                        'edit' => true,
                        'delete' => true,
                        'sorting' => true,
                        'paging' => true,
                        'filtering' => false,
                    ),
                );
        }
    }
}
