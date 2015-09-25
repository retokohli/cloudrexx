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
 * DefaultController
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_netmanager
 */

namespace Cx\Core\NetManager\Controller;

/**
 * The class DefaultController for display the Domain Alias
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_netmanager
 */
class DefaultController extends \Cx\Core\Core\Model\Entity\Controller
{   
    /**
     * Sigma template instance
     * @var Cx\Core\Html\Sigma  $template
     */
    protected $template;
    
    /**
     * DefaultController for the DefaultView
     * 
     * @param \Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController the system component controller object
     * @param \Cx\Core\Core\Controller\Cx                          $cx                        the cx object
     * @param \Cx\Core\Html\Sigma                                  $template                  the template object
     * @param string                                               $submenu                   the submenu name
     */
    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController, \Cx\Core\Core\Controller\Cx $cx) { 
        parent::__construct($systemComponentController, $cx);
    }
    
    /**
     * Use this to parse your backend page
     * 
     * @param \Cx\Core\Html\Sigma $template 
     */
    public function parsePage(\Cx\Core\Html\Sigma $template) {
        $this->template = $template;
        
        $this->showDomains();
    }
    
    /**
     * Show all the Domain Alias
     * 
     * @global array $_ARRAYLANG
     */
    public function showDomains() {
        global $_ARRAYLANG, $objInit;

        $langData = $objInit->loadLanguageData('Config');
        $_ARRAYLANG = array_merge($_ARRAYLANG, $langData);
        
        $domainRepository = new \Cx\Core\Net\Model\Repository\DomainRepository();
        $domains = $domainRepository->findAll();
        $view = new \Cx\Core\Html\Controller\ViewGenerator($domains, array(
            'header'    => $_ARRAYLANG['TXT_CORE_NETMANAGER'],
            'entityName'    => $_ARRAYLANG['TXT_CORE_NETMANAGER_ENTITY'],
            'fields'    => array(
                'name'  => array(
                    'header' => $_ARRAYLANG['TXT_NAME'],
                    'table' => array(
                        'parse' => function($value) {
                            global $_ARRAYLANG;
                            static $mainDomainName;
                            if (empty($mainDomainName)) {
                                $domainRepository = new \Cx\Core\Net\Model\Repository\DomainRepository();
                                $mainDomainName = $domainRepository->getMainDomain()->getName();
                            }
                            $domainName = contrexx_raw2xhtml(\Cx\Core\Net\Controller\ComponentController::convertIdnToUtf8Format($value));
                            if($domainName!=contrexx_raw2xhtml($value)) {
                                $domainName.= ' (' .  contrexx_raw2xhtml($value) . ')';
                            }
                            $mainDomainIcon = '';
                            if ($value == $mainDomainName) {
                                $mainDomainIcon = ' <img src="'.\Env::get('cx')->getCodeBaseCoreWebPath().'/Core/View/Media/icons/Home.png" title="'.$_ARRAYLANG['TXT_CORE_CONFIG_MAINDOMAINID'].'" />';
                            }
                            return $domainName.$mainDomainIcon;
                        },
                    ),
                    'formfield' => function($fieldname, $fieldtype, $fieldlength, $fieldvalue, $fieldoptions) {
                        return \Cx\Core\Net\Controller\ComponentController::convertIdnToUtf8Format($fieldvalue);
                    },
                ),
                'id'    => array(
                    'showOverview' => false,
                ),
            ),
            'functions' => array(
                'add'           => true,
                'edit'          => false,
                'allowEdit'    => true,
                'delete'        => false,
                'allowDelete'   => true,
                'sorting'       => true,
                'paging'        => true,
                'filtering'     => false,
                'actions'       => function($rowData, $rowId) {
                    global $_CORELANG;
                    static $mainDomainName;
                    if (empty($mainDomainName)) {
                        $domainRepository = new \Cx\Core\Net\Model\Repository\DomainRepository();
                        $mainDomainName = $domainRepository->getMainDomain()->getName();
                    }
                    
                    preg_match_all('/\d+/', $rowId, $ids, null, 0); 
                    
                    $actionIcons = '';
                    $csrfParams = \Cx\Core\Csrf\Controller\Csrf::param();
                    if ($mainDomainName !== $rowData['name']) {
                        $actionIcons = '<a href="' . \Env::get('cx')->getWebsiteBackendPath() . '/?cmd=NetManager&amp;editid=' . $rowId . '" class="edit" title="Edit entry"></a>';
                        $actionIcons .= '<a onclick=" if(confirm(\''.$_CORELANG['TXT_CORE_RECORD_DELETE_CONFIRM'].'\'))window.location.replace(\'' . \Env::get('cx')->getWebsiteBackendPath() . '/?cmd=NetManager&amp;deleteid=' . (empty($ids[0][1])?0:$ids[0][1]) . '&amp;vg_increment_number=' . (empty($ids[0][0])?0:$ids[0][0]) . '&amp;' . $csrfParams . '\');" href="javascript:void(0);" class="delete" title="Delete entry"></a>';
                    }
                    return $actionIcons;
                }
            )
        ));
                        
        $this->template->setVariable('DOMAINS_CONTENT', $view->render());
    }
    
}
