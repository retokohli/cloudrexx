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
     * Returns a list of available commands (?act=XY)
     * @return array List of acts
     */
    public function getCommands()
    {
        return array(
            'Catalog',
            'Favorite',
            'Settings' => array(
                'Mailing',
                'FormField',
            ),
        );
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

        try {
            // function group
            \Cx\Core\Setting\Controller\Setting::init($this->getName(), 'function', 'FileSystem');
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('functionMail')
                && !\Cx\Core\Setting\Controller\Setting::add('functionMail', 0, 1,
                    \Cx\Core\Setting\Controller\Setting::TYPE_CHECKBOX, '1', 'function')
            ) {
                throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for " . $this->getName() . " Function Mail");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('functionPrint')
                && !\Cx\Core\Setting\Controller\Setting::add('functionPrint', 0, 2,
                    \Cx\Core\Setting\Controller\Setting::TYPE_CHECKBOX, '1', 'function')
            ) {
                throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for " . $this->getName() . " Function Print");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('functionRecommendation')
                && !\Cx\Core\Setting\Controller\Setting::add('functionRecommendation', 0, 3,
                    \Cx\Core\Setting\Controller\Setting::TYPE_CHECKBOX, '1', 'function')
            ) {
                throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for " . $this->getName() . " Function Recommendation");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('functionInquiry')
                && !\Cx\Core\Setting\Controller\Setting::add('functionInquiry', 0, 4,
                    \Cx\Core\Setting\Controller\Setting::TYPE_CHECKBOX, '1', 'function')
            ) {
                throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for " . $this->getName() . " Function Inquiry");
            }

            // notification group
            \Cx\Core\Setting\Controller\Setting::init($this->getName(), 'notification', 'FileSystem');
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('notificationMail')
                && !\Cx\Core\Setting\Controller\Setting::add('notificationMail', 0, 1,
                    \Cx\Core\Setting\Controller\Setting::TYPE_CHECKBOX, '1', 'notification')
            ) {
                throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for " . $this->getName() . " Notification Mail");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('notificationMailMail')
                && !\Cx\Core\Setting\Controller\Setting::add('notificationMailMail', '', 2,
                    \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, '', 'notification')
            ) {
                throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for " . $this->getName() . " Notification Mail Mail");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('notificationPrint')
                && !\Cx\Core\Setting\Controller\Setting::add('notificationPrint', 0, 3,
                    \Cx\Core\Setting\Controller\Setting::TYPE_CHECKBOX, '1', 'notification')
            ) {
                throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for " . $this->getName() . " Notification Print");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('notificationPrintMail')
                && !\Cx\Core\Setting\Controller\Setting::add('notificationPrintMail', '', 4,
                    \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, '', 'notification')
            ) {
                throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for " . $this->getName() . " Notification Print Mail");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('notificationRecommendation')
                && !\Cx\Core\Setting\Controller\Setting::add('notificationRecommendation', 0, 5,
                    \Cx\Core\Setting\Controller\Setting::TYPE_CHECKBOX, '1', 'notification')
            ) {
                throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for " . $this->getName() . " Notification Recommendation");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('notificationRecommendationMail')
                && !\Cx\Core\Setting\Controller\Setting::add('notificationRecommendationMail', '', 6,
                    \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, '', 'notification')
            ) {
                throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for " . $this->getName() . " Notification Mail Recommendation");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('notificationInquiry')
                && !\Cx\Core\Setting\Controller\Setting::add('notificationInquiry', 0, 7,
                    \Cx\Core\Setting\Controller\Setting::TYPE_CHECKBOX, '1', 'notification')
            ) {
                throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for " . $this->getName() . " Notification Inquiry");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('notificationInquiryMail')
                && !\Cx\Core\Setting\Controller\Setting::add('notificationInquiryMail', '', 8,
                    \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, '', 'notification')
            ) {
                throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for " . $this->getName() . " Notification Inquiry Mail");
            }

            // pdf group
            \Cx\Core\Setting\Controller\Setting::init($this->getName(), 'pdf', 'FileSystem');
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('pdfTemplate')
                && !\Cx\Core\Setting\Controller\Setting::add('pdfTemplate', null, 1,
                    \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, '{src:\\' . __CLASS__ . '::getPdfTemplates()}', 'pdf')
            ) {
                throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for " . $this->getName() . " PDF Template");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('pdfLogo')
                && !\Cx\Core\Setting\Controller\Setting::add('pdfLogo', '', 2,
                    \Cx\Core\Setting\Controller\Setting::TYPE_IMAGE, '', 'pdf')
            ) {
                throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for " . $this->getName() . " PDF Logo");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('pdfAddress')
                && !\Cx\Core\Setting\Controller\Setting::add('pdfAddress', '', 3,
                    \Cx\Core\Setting\Controller\Setting::TYPE_WYSIWYG, '', 'pdf')
            ) {
                throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for " . $this->getName() . " PDF Address");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('pdfFooter')
                && !\Cx\Core\Setting\Controller\Setting::add('pdfFooter', '', 4,
                    \Cx\Core\Setting\Controller\Setting::TYPE_WYSIWYG, '', 'pdf')
            ) {
                throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for " . $this->getName() . " PDF Footer");
            }
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
        }

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
                    case 'FormField':
                        // Parse entity view generation pages
                        $entityClassName = $this->getNamespace() . '\\Model\\Entity\\' . $cmd[1];
                        $this->parseEntityClassPage($template, $entityClassName, $cmd[1]);
                        break;
                    default:
                        //save the setting values
                        \Cx\Core\Setting\Controller\Setting::init($this->getName(), null, 'FileSystem', null, \Cx\Core\Setting\Controller\Setting::REPOPULATE);
                        if (!empty($_POST['bsubmit'])) {
                            \Cx\Core\Setting\Controller\Setting::storeFromPost();
                        }

                        \Cx\Core\Setting\Controller\Setting::setEngineType($this->getName(), 'FileSystem', 'function');
                        \Cx\Core\Setting\Controller\Setting::show(
                            $this->template,
                            'index.php?cmd=' . $this->getName() . '&act=' . current($cmd),
                            $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_SETTINGS_FUNCTION_DESCRIPTION'],
                            $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_SETTINGS_FUNCTION'],
                            'TXT_MODULE_' . strtoupper($this->getName()) . '_SETTINGS_'
                        );

                        \Cx\Core\Setting\Controller\Setting::setEngineType($this->getName(), 'FileSystem', 'notification');
                        \Cx\Core\Setting\Controller\Setting::show(
                            $this->template,
                            'index.php?cmd=' . $this->getName() . '&act=' . current($cmd),
                            $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_SETTINGS_NOTIFICATION_DESCRIPTION'],
                            $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_SETTINGS_NOTIFICATION'],
                            'TXT_MODULE_' . strtoupper($this->getName()) . '_SETTINGS_'
                        );

                        \Cx\Core\Setting\Controller\Setting::setEngineType($this->getName(), 'FileSystem', 'pdf');
                        \Cx\Core\Setting\Controller\Setting::show(
                            $this->template,
                            'index.php?cmd=' . $this->getName() . '&act=' . current($cmd),
                            $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_SETTINGS_PDF_DESCRIPTION'],
                            $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_SETTINGS_PDF'],
                            'TXT_MODULE_' . strtoupper($this->getName()) . '_SETTINGS_'
                        );
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
     * Load a settings.php file and return its configuration ($_CONFIG) as array
     *
     * @param   string $file The path to the settings.php file to load the $_CONFIG from
     * @return  array           Returns an array containing the loaded $_CONFIG from $file.
     *                          If $file does not exists or on error, it returns an empty array
     */
    static function fetchConfigFromSettingsFile($file)
    {
        if (!file_exists($file)) {
            return array();
        }

        $settingsContent = file_get_contents($file);
        // Execute code to load the settings into variable $_CONFIG.
        //
        // We must use eval() here as we must not use include(_once) here.
        // As we are not populating the loaded $_CONFIG array into the global space,
        // any later running components (in particular Cx\Core\Core\Controller\Cx)
        // would not be able to load the $_CONFIG array as the settings.php file
        // has already been loaded.
        //
        // The closing PHP tag is required as $settingsContent starts with a opening PHP tag (<?php).
        try {
            eval('?>' . $settingsContent);
        } catch (\Exception $e) {
            return array();
        }

        if (!isset($_CONFIG)) {
            return array();
        }

        return $_CONFIG;
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
            $header = $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_ACT_DEFAULT'];
        }

        switch ($entityClassName) {
            case 'Cx\Modules\FavoriteList\Model\Entity\Catalog':
                if (!isset($_GET['order'])) {
                    $_GET['order'] = 'id';
                }
                return array(
                    'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_ACT_CATALOG'],
                    'fields' => array(
                        'id' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_ID'],
                        ),
                        'sessionId' => array(
                            'showOverview' => false,
                            'showDetail' => false,
                        ),
                        'name' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_NAME'],
                            'table' => array(
                                'parse' => function ($value, $rowData) {
                                    return '<a href="' . $this->getName() . '/Favorite?catalog=' . $rowData['id'] . '">' . $value . '</a>';
                                },
                            ),
                        ),
                        'date' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_DATE'],
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
                    'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_ACT_FAVORITE'],
                    'fields' => array(
                        'id' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_ID'],
                        ),
                        'title' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_TITLE'],
                        ),
                        'link' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_LINK'],
                        ),
                        'description' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_DESCRIPTION'],
                        ),
                        'message' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_MESSAGE'],
                        ),
                        'price' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_PRICE'],
                        ),
                        'image1' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_IMAGE1'],
                        ),
                        'image2' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_IMAGE2'],
                        ),
                        'image3' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_IMAGE3'],
                        ),
                        'catalog' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_CATALOG'],
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
                            'message',
                            'price',
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
                            'message',
                            'price',
                            'image1',
                            'image2',
                            'image3',
                        ),
                    ),
                );
                break;
            case 'Cx\Modules\FavoriteList\Model\Entity\FormField':
                return array(
                    'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_ACT_FORMFIELD'],
                    'fields' => array(
                        'id' => array(
                            'showOverview' => false,
                        ),
                        'name' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_NAME'],
                        ),
                        'type' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_TYP'],
                            'type' => 'select',
                            'validValues' => array(
                                'inputtext' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_INPUTTEXT'],
                                'textarea' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_TEXTAREA'],
                                'select' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_SELECT'],
                                'radio' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_RADIO'],
                                'checkbox' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_CHECKBOX'],
                                'mail' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_MAIL'],
                            ),
                        ),
                        'required' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_REQUIRED'],
                        ),
                        'order' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_ORDER'],
                        ),
                        'values' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_VALUES'],
                        ),
                    ),
                    'functions' => array(
                        'add' => true,
                        'edit' => true,
                        'delete' => true,
                        'sorting' => true,
                        'paging' => true,
                        'filtering' => false,
                        'sortBy' => [
                            'field' => ['order' => SORT_ASC]
                        ],
                    ),
                    'order' => array(
                        'overview' => array(
                            'order',
                            'name',
                            'type',
                            'required',
                            'values',
                        ),
                        'form' => array(
                            'order',
                            'name',
                            'type',
                            'required',
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

    /**
     * Returns all PDF Templates
     *
     * @access  public
     * @return  string
     */
    public static function getPdfTemplates()
    {
        $cx = \Cx\Core\Core\Controller\Cx::instanciate();
        $arrOptions = $cx->getComponent('Pdf')->getPdfTemplates();
        $display = array();
        foreach ($arrOptions as $key => $arrOption) {
            array_push($display, $key . ':' . $arrOption);
        }
        return implode(',', $display);
    }
}
