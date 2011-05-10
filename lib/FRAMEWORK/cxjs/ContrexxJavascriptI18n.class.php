<?php
class ContrexxJavascriptI18nException extends ContrexxJavascriptException {}

require_once 'ContrexxJavascriptI18nProvider.interface.php';
/**
 * This handles i18n for Javascript.
 * @author Severin Räz
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
            if($provider[0] == '.') //do not open ., .., and linux hidden directories (.*)
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