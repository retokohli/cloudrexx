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
class FrontendController extends \Cx\Core\Core\Model\Entity\SystemComponentFrontendController
{
    public function parsePage(\Cx\Core\Html\Sigma $template, $cmd)
    {
        global $_ARRAYLANG;

        $em = $this->cx->getDb()->getEntityManager();
        $catalogRepo = $em->getRepository($this->getNamespace() . '\Model\Entity\Catalog');
        $catalog = $catalogRepo->findOneBy(array('sessionId' => $this->getComponent('Session')->getSession()->sessionid));

        switch ($cmd) {
            case 'mail':
                if (!$catalog) {
                    header('Location: ' . \Cx\Core\Routing\Url::fromModuleAndCmd($this->getName()));
                }
                if (isset($_POST['send'])) {

                }
                break;
            case 'print':
                if (!$catalog) {
                    header('Location: ' . \Cx\Core\Routing\Url::fromModuleAndCmd($this->getName()));
                }
                if (isset($_POST['send'])) {

                }
                break;
            case 'recommendation':
                if (!$catalog) {
                    header('Location: ' . \Cx\Core\Routing\Url::fromModuleAndCmd($this->getName()));
                }
                if (isset($_POST['send'])) {

                }
                break;
            case 'inquiry':
                if (!$catalog) {
                    header('Location: ' . \Cx\Core\Routing\Url::fromModuleAndCmd($this->getName()));
                }
                if (isset($_POST['send'])) {

                } else {
                    $em = $this->cx->getDb()->getEntityManager();
                    $formFieldRepo = $em->getRepository($this->getNamespace() . '\Model\Entity\FormField');
                    $formFields = $formFieldRepo->findAll();
                    $dataSet = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($formFields);
                    $dataSet->sortColumns(array('order' => 'ASC'));
                    foreach ($dataSet as $formField) {
                        $template->parse('favoritelist_form_field');
                        $required = $formField['required'];
                        if ($required) {
                            $template->touchBlock('favoritelist_form_field_required');
                        }
                        switch ($formField['type']) {
                            case 'text':
                            case 'textarea':
                            case 'mail':
                                $template->setVariable(array(
                                    'ID' => $formField['id'],
                                    'REQUIRED' => $required ? 'required' : '',
                                    'LABEL' => $formField['name'],
                                ));
                                $template->parse('favoritelist_form_field_' . $formField['type']);
                                break;
                            case 'select':
                                $values = $formField['values'];
                                $values = explode(',', str_replace(' ', '', $values));
                                foreach ($values as $key => $value) {
                                    $template->setVariable(array(
                                        'INDEX' => $key,
                                        'VALUE' => $value,
                                        'ID' => $formField['id'],
                                        'REQUIRED' => $required ? 'required' : '',
                                        'LABEL' => $formField['name'],
                                    ));
                                    $template->parse('favoritelist_form_field_' . $formField['type'] . '_value');
                                }
                                $template->parse('favoritelist_form_field_' . $formField['type']);
                                break;
                            case 'radio':
                            case 'checkbox':
                                $values = $formField['values'];
                                $values = explode(',', str_replace(' ', '', $values));
                                foreach ($values as $key => $value) {
                                    $template->setVariable(array(
                                        'INDEX' => $key,
                                        'VALUE' => $value,
                                        'ID' => $formField['id'],
                                        'REQUIRED' => $required ? 'required' : '',
                                        'LABEL' => $formField['name'],
                                        'VALUE' => $value,
                                    ));
                                    $template->parse('favoritelist_form_field_' . $formField['type']);
                                }
                        }
                    }
                }
                break;
            default:
                if (!$catalog) {
                    $template->setVariable(array(
                        strtoupper($this->getName()) . '_FAVORITE_LIST' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_MESSAGE_NO_LIST'],
                    ));
                } else {
                    $favorites = $catalog->getFavorites()->toArray();
                    $favoritesView = new \Cx\Core\Html\Controller\ViewGenerator(
                        $favorites,
                        array(
                            $this->getNamespace() . '\Model\Entity\Favorite' => $this->getViewGeneratorOptions(
                                $this->getNamespace() . '\Model\Entity\Favorite'
                            ),
                        )
                    );
                    $template->setVariable(array(
                        strtoupper($this->getName()) . '_FAVORITE_LIST' => $favoritesView,
                    ));
                    $template->parse('favoritelist_favorite_list_actions');
                    \Cx\Core\Setting\Controller\Setting::init($this->getName(), 'function');
                    if (\Cx\Core\Setting\Controller\Setting::getValue('functionMail', 'function')) {
                        $template->setVariable(array(
                            strtoupper($this->getName()) . '_ACT_MAIL_LINK' => \Cx\Core\Routing\Url::fromModuleAndCmd($this->getName(), 'mail'),
                        ));
                        $template->parse('favoritelist_favorite_list_actions_mail');
                    }
                    if (\Cx\Core\Setting\Controller\Setting::getValue('functionPrint', 'function')) {
                        $template->setVariable(array(
                            strtoupper($this->getName()) . '_ACT_PRINT_LINK' => \Cx\Core\Routing\Url::fromModuleAndCmd($this->getName(), 'print'),
                        ));
                        $template->parse('favoritelist_favorite_list_actions_print');
                    }
                    if (\Cx\Core\Setting\Controller\Setting::getValue('functionRecommendation', 'function')) {
                        $template->setVariable(array(
                            strtoupper($this->getName()) . '_ACT_RECOMMENDATION_LINK' => \Cx\Core\Routing\Url::fromModuleAndCmd($this->getName(), 'recommendation'),
                        ));
                        $template->parse('favoritelist_favorite_list_actions_recommendation');
                    }
                    if (\Cx\Core\Setting\Controller\Setting::getValue('functionInquiry', 'function')) {
                        $template->setVariable(array(
                            strtoupper($this->getName()) . '_ACT_INQUIRY_LINK' => \Cx\Core\Routing\Url::fromModuleAndCmd($this->getName(), 'inquiry'),
                        ));
                        $template->parse('favoritelist_favorite_list_actions_inquiry');
                    }
                }
        }
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
    protected
    function getViewGeneratorOptions($entityClassName, $dataSetIdentifier = '')
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
            case 'Cx\Modules\FavoriteList\Model\Entity\Favorite':
                if (!isset($_GET['order'])) {
                    $_GET['order'] = 'id';
                }
                return array(
                    'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_ACT_FAVORITE'],
                    'fields' => array(
                        'id' => array(
                            'showOverview' => false,
                            'showDetail' => false,
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
                        'info' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_INFO'],
                        ),
                        'image1' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_IMAGE_1'],
                        ),
                        'image2' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_IMAGE_2'],
                        ),
                        'image3' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_FIELD_IMAGE_3'],
                        ),
                        'catalog' => array(
                            'showOverview' => false,
                            'showDetail' => false,
                        ),
                    ),
                    'filter_criteria' => array(
                        'list_id' => $_GET['list_id'],
                    ),
                    'functions' => array(
                        'add' => false,
                        'edit' => true,
                        'delete' => true,
                        'sorting' => false,
                        'paging' => false,
                        'filtering' => false,
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
            default:
                return array(
                    'header' => $header,
                    'functions' => array(
                        'add' => false,
                        'edit' => true,
                        'delete' => true,
                        'sorting' => false,
                        'paging' => false,
                        'filtering' => false,
                    ),
                );
        }
    }
}
