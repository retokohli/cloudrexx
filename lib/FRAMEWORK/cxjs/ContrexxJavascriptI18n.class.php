<?php

/**
 * ContrexxJavascriptI18n
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  lib_cxjs
 */

/**
 * ContrexxJavascriptI18nException
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  lib_cxjs
 */
class ContrexxJavascriptI18nException extends ContrexxJavascriptException {}

/**
 * @ignore
 */
require_once ASCMS_FRAMEWORK_PATH.'/cxjs/ContrexxJavascriptI18nProvider.interface.php';

/**
 * This handles i18n for Javascript.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  lib_cxjs
 */
class ContrexxJavascriptI18n {
   /**
    * language code used to generate i18n-files' names
    * @var string
    */
    protected $languageCode = 'en';

    public function __construct($languageCode) {
        $this->languageCode = $languageCode;
    }

    /**
     * sets all i18n variables on target
     * @param ContrexxJavascript $target
     * @throws ContrexxJavascriptI18nException
     */
    public function variablesTo($target) {
        $vars = array();
        $providers = scandir(ASCMS_FRAMEWORK_PATH.'/cxjs/i18n');
        foreach($providers as $provider) {
            if(($provider[0] == '.') || !preg_match('/\.php/', $provider)) //do not open ., .., and linux hidden directories (.*)
                continue;
            //name as used for the scope ('provider')
            $providerName = substr($provider,0,strpos($provider,'.'));
            //name of the class ('providerProvider')
            $className = ucfirst($providerName.'I18nProvider');
            try {
                require_once 'i18n/'.$provider;
                $providerInst = new $className();
                //set the variables accordingly on cxjs object
                $target->setVariable($providerInst->getVariables($this->languageCode), $providerName);
            }
            catch(Exception $e)
            {
                throw new ContrexxJavascriptI18nException("error parsing i18n module '$provider': " . $e->getMessage());
            }
        }
        return $vars;
    }
}
