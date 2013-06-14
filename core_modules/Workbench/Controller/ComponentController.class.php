<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Cx\Core_Modules\Workbench\Controller;

class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    
    /**
     *
     * @param type $objTemplate
     * @param type $post
     * @return type
     * @throws \Exception 
     * @todo YAML assistant
     * @todo Cx/Module sandbox
     * @todo Language var checker (/translation helper)
     * @todo Component analysis (/testing)
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        $objTemplate = $this->cx->getTemplate();
        $_ARRAYLANG = \Env::get('init')->loadLanguageData($this->getName());
        
        // Initialize
        if (!isset($_GET['act'])) {
            $_GET['act'] = '';
        }
        $cmd = explode('/', $_GET['act']);
        if (!isset($cmd[0])) {
            $cmd[0] = 'development';
        }
        $controller = $cmd[0];
        if (!isset($cmd[1])) {
            $cmd[1] = '';
        }
        $act = $cmd[1];
        
        // Load controller specific things
        switch ($controller) {
            case 'sandbox':
                // The following code is for sandbox only:
                if ($act == '') {
                    $act = 'dql';
                }
                $navEntries = array(
                    'index.php?cmd=Workbench&amp;act=sandbox/dql' => 'DQL',
                    'index.php?cmd=Workbench&amp;act=sandbox/php' => 'PHP',
                );
                $objTemplate->setVariable('ADMIN_CONTENT', new Sandbox($_ARRAYLANG, $act, $_POST));
                break;
            case 'development':
                if ($act == '') {
                    $act = 'yaml';
                }
            default:
                $navEntries = array(
                    'index.php?cmd=Workbench&amp;act=development/yaml' => 'YAML',
                    'index.php?cmd=Workbench&amp;act=development/components' => 'Components',
                );
                $objTemplate->setVariable('ADMIN_CONTENT', new Toolbox($_ARRAYLANG, $act, $_POST));
                break;
        }
        
        // set tabs
        $navigation = new \Cx\Core\Html\Sigma(ASCMS_CORE_MODULE_PATH . '/Workbench/View/Template');
        $navigation->loadTemplateFile('Navigation.html');
        foreach ($navEntries as $href=>$title) {
            $navigation->setVariable(array(
                'HREF' => $href,
                'TITLE' => $title,
            ));
            if (strtolower($title) == $act) {
                $navigation->touchBlock('active');
            }
            $navigation->parse('tab_entry');
        }
        $objTemplate->setVariable('CONTENT_NAVIGATION', $navigation->get());
    }

    public function postContentLoad(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        $warning = new \Cx\Core\Html\Sigma(ASCMS_CORE_MODULE_PATH . '/Workbench/View/Template');
        $warning->loadTemplateFile('Warning.html');
        $page->setContent($warning->get() . $page->getContent());
    }
}
