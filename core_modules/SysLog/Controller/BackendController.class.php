<?php
/**
* System log
* @copyright    CONTREXX CMS - COMVATION AG
* @author       Michael Ritter <michael.ritter@comvation.com>
* @package      contrexx
* @subpackage   coremodule_syslog
* @version      5.0.0
*/
namespace Cx\Core_Modules\SysLog\Controller;

/**
* Backend for the system log
* @copyright    CONTREXX CMS - COMVATION AG
* @author       Michael Ritter <michael.ritter@comvation.com>
* @package      contrexx
* @subpackage   coremodule_syslog
* @version      5.0.0
*/
class BackendController extends \Cx\Core\Core\Model\Entity\SystemComponentBackendController {
    
    /**
     * This component's backend has only the default CMD
     * @return array List of commands
     */
    public function getCommands() {
        return array();
    }
    
    /**
     * Parses a rudimentary system log backend page
     * @param \Cx\Core\Html\Sigma $template Backend template for this page
     * @param array $cmd Supplied CMD
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, array $cmd) {
        $em = $this->cx->getDb()->getEntityManager();
        $logRepo = $em->getRepository('Cx\Core_Modules\SysLog\Model\Entity\Log');

        // @todo: parse message if no entries (template block exists already)
        $parseObject = $this->getNamespace().'\Model\Entity\Log';
        
        // set default sorting
        if (!isset($_GET['order'])) {
            $_GET['order'] = 'timestamp/DESC';
        }
        
        // configure view
        $viewGenerator = new \Cx\Core\Html\Controller\ViewGenerator($parseObject, array(
            'functions' => array(
                'delete' => 'true',
                'paging' => true,
                'sorting' => true,
                'edit' => true,
            ),
            'fields' => array(
                'id' => array(
                    'showOverview' => false,
                ),
                'timestamp' => array(
                    'readonly' => true,
                ),
                'severity' => array(
                    'readonly' => true,
                    'table' => array(
                        'parse' => function($data, $rows) {
                            return '<span class="' . contrexx_raw2xhtml(strtolower($data)) . '_background">' . contrexx_raw2xhtml($data) . '</span>';
                        },
                    ),
                ),
                'message' => array(
                    'readonly' => true,
                    'table' => array(
                        'parse' => function($data, $rows) {
                            $url = clone \Cx\Core\Routing\Url::fromRequest();
                            $url->setMode('backend');
                            $url->setParam('editid', $rows['id']);
                            return '<a href="' . $url . '">' . contrexx_raw2xhtml($data) . '</a>';
                        },
                    ),
                ),
                'data' => array(
                    'readonly' => true,
                    'showOverview' => false,
                    'type' => 'text',
                ),
                'logger' => array(
                    'readonly' => true,
                ),
            ),
        ));
        $template->setVariable('ENTITY_VIEW', $viewGenerator); 
    }
}

