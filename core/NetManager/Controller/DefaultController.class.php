<?php

/**
 * DefaultController
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_netmanager
 */

namespace Cx\Core\NetManager\Controller;

/**
 * The class DefaultController for display the Domain Alias
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
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
                            $mainDomainIcon = '';
                            if ($value == $mainDomainName) {
                                $mainDomainIcon = ' <img src="'.\Env::get('cx')->getCodeBaseCoreWebPath().'/Core/View/Media/icons/Home.png" title="'.$_ARRAYLANG['TXT_CORE_CONFIG_MAINDOMAINID'].'" />';
                            }
                            return $value.$mainDomainIcon;
                        },
                    ),
                ),
                'id'    => array(
                    'showOverview' => false,
                ),
            ),
            'functions' => array(
                'add'       => true,
                'edit'      => false,
                'delete'    => false,
                'sorting'   => true,
                'paging'    => true,
                'filtering' => false,
                'actions'   => function($rowData) {
                            global $_CORELANG;
                            static $mainDomainName;
                            if (empty($mainDomainName)) {
                                $domainRepository = new \Cx\Core\Net\Model\Repository\DomainRepository();
                                $mainDomainName = $domainRepository->getMainDomain()->getName();
                            }
                            $actionIcons = '';
                            $csrfParams = \Cx\Core\Csrf\Controller\Csrf::param();
                            if ($mainDomainName !== $rowData['name']) {
                                $actionIcons = '<a href="' . \Env::get('cx')->getWebsiteBackendPath() . '/?cmd=NetManager&amp;editid=' . $rowData['id'] .'" class="edit" title="Edit entry"></a>';
                                $actionIcons .= '<a onclick=" if(confirm(\''.$_CORELANG['TXT_CORE_RECORD_DELETE_CONFIRM'].'\'))window.location.replace(\'' . \Env::get('cx')->getWebsiteBackendPath() . '/?cmd=NetManager&amp;deleteid=' . $rowData['id'] . '&amp;' . $csrfParams . '\');" href="javascript:void(0);" class="delete" title="Delete entry"></a>';
                            }
                            return $actionIcons;
                    }
                )
            ));
                        
        $this->template->setVariable('DOMAINS_CONTENT', $view->render());
    }
    
}
