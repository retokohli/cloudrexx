<?php
/**
 * Backend controller to create a default backend view.
 *
 * Create a subclass of this in order to create a normal backend view
 * @author Michael Ritter <michael.ritter@comvation.com>
 */

namespace Cx\Core\Core\Model\Entity;

/**
 * Backend controller to create a default backend view.
 *
 * Create a subclass of this in order to create a normal backend view
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
abstract class SystemComponentBackendController extends Controller {
    
    /**
     * Returns a list of available commands (?act=XY)
     * @return array List of acts
     */
    public abstract function getCommands();
    
    /**
     * This is called by the default ComponentController and does all the repeating work
     * 
     * This loads a template named after current $act and calls parsePage($actTemplate)
     * @global array $_ARRAYLANG Language data
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page Resolved page
     */
    public function getPage(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $_ARRAYLANG;
        
        $cmd = array('');
        
        if (isset($_GET['act'])) {
            $cmd = explode('/', contrexx_input2raw($_GET['act']));
        }
        
        $actTemplate = new \Cx\Core\Html\Sigma(ASCMS_CORE_MODULE_PATH . '/MultiSite/View/Template');
        $actTemplate->loadTemplateFile();
        
        // todo: $actTemplate->loadTemplateFile(), Messages
        $this->parsePage($actTemplate, $cmd);
        
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
        $this->cx->getTemplate()->setVariable(array(
            'CONTENT_NAVIGATION' => $navigation->get(),
        ));
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
    public abstract function parsePage(\Cx\Core\Html\Sigma $template, array $cmd);
}
