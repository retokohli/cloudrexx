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
        global $_ARRAYLANG;
        
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
                'edit'      => true,
                'delete'    => true,
                'sorting'   => true,
                'paging'    => true,
                'filtering' => false,
                )
            ));
                        
        $this->template->setVariable('DOMAINS_CONTENT', $view->render());
    }
    
}
