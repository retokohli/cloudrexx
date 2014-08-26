<?php

/**
 * SettingsController
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_linkmanager
 */

namespace Cx\Modules\LinkManager\Controller;

/**
 * The class SettingsController for setting the entries count per page
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_linkmanager
 */
class SettingsController extends \Cx\Core\Core\Model\Entity\Controller {
    
    /**
     * Em instance
     * @var \Doctrine\ORM\EntityManager em
     */
    protected $em;
    
    /**
     * Sigma template instance
     * @var Cx\Core\Html\Sigma  $template
     */
    protected $template;
    
    /**
     * module name
     * @var string $moduleName
     */
    protected $moduleName = 'LinkManager';
    
    /**
     * module name for language placeholder
     * @var string $moduleNameLang
     */
    protected $moduleNameLang = 'LINKMANAGER';


    /**
     * Controller for the Backend Settings views
     * 
     * @param \Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController the system component controller object
     * @param \Cx\Core\Core\Controller\Cx                          $cx                        the cx object
     * @param \Cx\Core\Html\Sigma                                  $template                  the template object
     * @param string                                               $submenu                   the submenu name
     */
    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController, \Cx\Core\Core\Controller\Cx $cx, \Cx\Core\Html\Sigma $template, $submenu = null) {
        //check the user permission
        \Permission::checkAccess(1032, 'static');
        
        parent::__construct($systemComponentController, $cx);
        
        $this->template = $template;
        $this->em       = $this->cx->getDb()->getEntityManager();
        
        $this->handleSubViews($submenu);
    }
    
    /**
     * Calls subviews functions automatically
     * Based on the $submenu call
     * 
     * @param string $submenu
     */
    public function handleSubViews($submenu)
    {
        if (!empty($submenu)) {
            // show{$submenu}
            $controllerFunction = 'show'.ucfirst($submenu);
            $this->$controllerFunction();
            
        } else {
            // showDefault view
            $this->showDefault();
        }
    }
    
    /**
     * Show the general setting options
     * 
     * @global array $_ARRAYLANG
     */
    public function showDefault()
    {
        global $_ARRAYLANG;
        
        \SettingDb::init('LinkManager', 'config');
        //get post values
        $settings = isset($_POST['setting']) ? $_POST['setting'] : array();
        if (isset($_POST['save'])) {
            $includeFromSave = array('entriesPerPage');
            foreach($settings As $settingName => $settingValue) {
                if (in_array($settingName, $includeFromSave)) {
                    \SettingDb::set($settingName, $settingValue);
                    \SettingDb::update($settingName);
                    \Message::ok($_ARRAYLANG['TXT_MODULE_LINKMANAGER_SUCCESS_MSG']);
                }
            }
        }
        
        //get the settings values from DB
        $this->template->setVariable(array(
            $this->moduleNameLang.'_ENTRIES_PER_PAGE'   => \SettingDb::getValue('entriesPerPage')
        ));
    }        
}
