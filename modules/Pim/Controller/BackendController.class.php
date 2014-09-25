<?php
/**
 * Specific BackendController for this Component. Use this to easily create a backend view
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_pim
 */

namespace Cx\Modules\Pim\Controller;

/**
 * Specific BackendController for this Component. Use this to easily create a backend view
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_pim
 */
class BackendController extends \Cx\Core\Core\Model\Entity\SystemComponentBackendController {
    
    /**
     * Template object
     */
    protected $template;
    
    /**
     * Sub menu name
     */
    protected $submenuName;
    
    /**
     * Returns a list of available commands (?act=XY)
     * @return array List of acts
     */
    public function getCommands() {
        return array();
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
    public function parsePage(\Cx\Core\Html\Sigma $template, array $cmd) {
        // this class inherits from Controller, therefore you can get access to
        // Cx like this:
        $this->cx;
        $this->template = $template;
        $act = $cmd[0];
        $this->submenuName = $this->getSubmenuName($cmd);
        
        $this->connectToController($act);
        
        \Message::show();
    }
    
    /**
     * Trigger a controller according the act param from the url
     * 
     * @param   string $act
     */
    public function connectToController($act)
    {
        $act = ucfirst($act);
        if (!empty($act)) {
            $controllerName = __NAMESPACE__.'\\'.$act.'Controller';
            if (!$controllerName && !class_exists($controllerName)) {
                return;
            }
            //  instantiate the view specific controller
            new $controllerName($this->getSystemComponentController(), $this->cx, $this->template, $this->submenuName);
        } else { 
            // instantiate the default View Controller
            new DefaultController($this->getSystemComponentController(), $this->cx, $this->template);
        }
    }   
    
    /**
     * get the sub menu name
     * 
     * @param array $cmd
     * 
     * @return null|string
     */
    private function getSubmenuName($cmd)
    {
        if(count($cmd) > 1){
            $submenu = ucfirst($cmd[1]);
            return $submenu;
        }
        return null;
    }
    
    /**
     * Shows the all website templates
     * 
     * @access  private
     * @return  string
     */
    public static function getWebsiteTemplateList() {
        $websiteTemplatesObj = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteTemplate');
        $websiteTemplates = $websiteTemplatesObj->findAll();
        $display = array();
        foreach ($websiteTemplates as $websiteTemplate) {
            $display[] = $websiteTemplate->getId() . ':' . $websiteTemplate->getCodeBase();
        }
        return implode(',', $display);
    }
    
    /**
     * get the product list
     * 
     * @return string type
     */
    public static function getProductList() {
        $productRepository = \Env::get('em')->getRepository('Cx\Modules\Pim\Model\Entity\Product');
        $productList = $productRepository->findAll();
        $display = array();
        foreach ($productList as $product) {
            $display[] = $product->getId() . ':' . $product->getName();
        }
        return implode(',', $display);
    }
}
