<?php
class ContrexxJavascriptI18nException extends ContrexxJavascriptException {}
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

    /**
     * returns all i18n variables to pass to js.
     * @return array an associative array ready to pass @link ContrexxJavascript::setVariable()
     * @throws ContrexxJavascriptI18nException
     */
    public function getVariables() {
        $vars = array();
        $providers = scandir(ASCMS_FRAMEWORK_PATH.'/cxjs/i18n');
        foreach($providers as $provider) {
            if($provider == '.' || $provider == '..')
                continue;
            $providerName = substr($provider,0,strstr($provider,'.'));
            $className = ucfirst($providerName.'Provider');
            try {
                require_once $provider;
                $provider = new $className();
                $vars[$providerName] = $provider->getVariables($this->languageCode);
            }
            catch( Exception $e)
            {
                throw new ContrexxJavascriptI18nException("error parsing i18n module '$provider': " $e->getMessage());
            }
        }
        return $vars;
    }
}