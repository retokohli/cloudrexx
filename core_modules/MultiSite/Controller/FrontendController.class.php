<?php
/**
 * Specific FrontendController for this Component. Use this to easily create a frontent view
 *
 * @copyright   Comvation AG
 * @author      Manish Thakur <manishthakur@cdnsol.com>
 * @package     contrexx
 * @subpackage  modules_multisite
 */

namespace Cx\Core_Modules\MultiSite\Controller;

/**
 * Specific FrontendController for this Component. Use this to easily create a frontent view
 *
 * @copyright   Comvation AG
 * @author      Manish Thakur <manishthakur@cdnsol.com>
 * @package     contrexx
 * @subpackage  modules_multisite
 */
class FrontendController extends \Cx\Core\Core\Model\Entity\SystemComponentFrontendController {
    /**
     * Use this to parse your frontend page 
     * You will get a template based on the content of the resolved page
     * You can access Cx class using $this->cx
     * To show messages, use \Message class
     * @param \Cx\Core\Html\Sigma $template Template containing content of resolved page
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, $cmd) {
        switch ($cmd) {           
            // Singup proccess step1 user register
			case "signup":
                    $setVariable = $this->signup();
            break;
        }
        $template->setVariable($setVariable);
    }
    
    /**
     * Use this to stepOne your frontend page     
     * registration proccess step one
     * You can access Cx class using $this->cx
     * retrun step one respone
     * @param 
     */
    protected function signup(){
        global $_ARRAYLANG, $_LANGID;
        // get website minimum and maximum Name length
        $websiteNameMinLength=\Cx\Core\Setting\Controller\Setting::getValue('websiteNameMinLength');
        $websiteNameMaxLength=\Cx\Core\Setting\Controller\Setting::getValue('websiteNameMaxLength');
        // TODO: implement protocol support / the protocol to use should be defined by a configuration option
        $protocol = 'https';
        if (in_array(\Cx\Core\Setting\Controller\Setting::getValue('mode'), array(ComponentController::MODE_MANAGER, ComponentController::MODE_HYBRID))) {
            $configs = \Env::get('config');
            $multiSiteDomain = $protocol.'://'.$configs['domainUrl'];
        } else {           
            $multiSiteDomain = $protocol.'://'.\Cx\Core\Setting\Controller\Setting::getValue('managerHostname');
        }
        \JS::activate('cx');
        //Add jquery validations library (jquery.validate.min.js)
        \JS::registerJs('lib/javascript/jquery/jquery.validate.min.js');
        \ContrexxJavascript::getInstance()->setVariable('baseUrl', $multiSiteDomain, 'MultiSite');
        $setVariable=array(
                            'TITLE'         => $_ARRAYLANG['TXT_MULTISITE_TITLE'],
                            'TXT_MULTISITE_EMAIL_ADDRESS' => $_ARRAYLANG['TXT_MULTISITE_EMAIL_ADDRESS'],
                            'TXT_MULTISITE_ADDRESS'  => $_ARRAYLANG['TXT_MULTISITE_SITE_ADDRESS'],
                            'TXT_MULTISITE_CREATE_WEBSITE'   => $_ARRAYLANG['TXT_MULTISITE_SUBMIT_BUTTON'],
                            'MULTISITE_DOMAIN'     => \Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain'),
                            'POST_URL'      => $this->cx->getRequest()->getUrl(),
                        );
       return $setVariable; 
    }
}
