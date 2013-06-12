<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Cx\Core\Core\Model\Entity;
/**
 * Description of SystemComponentBackendController
 *
 * @author ritt0r
 */
abstract class SystemComponentBackendController extends Controller {
    
    /**
     * @return array List of acts
     */
    public abstract function getCommands();
    
    public function getPage(\Cx\Core\Core\Controller\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $_ARRAYLANG;
        
        $cmd = array('');
        
        if (isset($_GET['act'])) {
            $cmd = explode('/', contrexx_input2raw($_GET['act']));
        }
        
        $actTemplate = new \Cx\Core\Html\Sigma(ASCMS_CORE_MODULE_PATH . '/MultiSite/View/Template');
        $actTemplate->loadTemplateFile();
        $successMessage = '';
        $failureMessage = '';
        
        // todo: $actTemplate->loadTemplateFile(), Messages
        $this->parsePage($actTemplate, $cx, $cmd, $successMessage, $failureMessage);
        
        // set tabs
        $navigation = new \Cx\Core\Html\Sigma(ASCMS_CORE_PATH . '/Component/View/Template');
        $navigation->loadTemplateFile('Navigation.html');
        foreach ($this->getCommands() as $cmd) {
            $act = '&amp;act=' . $cmd;
            $txt = $cmd;
            if (empty($cmd)) {
                $act = '';
                $txt = 'DEFAULT';
            }
            $navigation->setVariable(array(
                'HREF' => 'index.php?cmd=' . $this->getSystemComponentController()->getName() . $act,
                'TITLE' => $_ARRAYLANG['TXT_' . strtoupper($this->getSystemComponentController()->getName() . '_ACT_' . $txt)],
            ));
            if (strtolower($title) == $act) {
                $navigation->touchBlock('active');
            }
            $navigation->parse('tab_entry');
        }
        
        $page->setContent($actTemplate->get());
        $cx->getTemplate()->setVariable(array(
            'CONTENT_NAVIGATION' => $navigation->get(),
            'CONTENT_STATUS_MESSAGE' => $successMessage,
        ));
    }
    
    /**
     * Use this to parse your backend page
     * 
     * You will get the template located in /View/Template/{CMD}.html
     * @param \Cx\Core\Html\Sigma $template Template for current CMD
     * @param \Cx\Core\Core\Controller\Cx $cx Contrexx main class
     * @param array $cmd CMD separated by slashes
     */
    public abstract function parsePage(\Cx\Core\Html\Sigma $template, \Cx\Core\Core\Controller\Cx $cx, array $cmd, &$successMessage, &$failureMessage);
}
