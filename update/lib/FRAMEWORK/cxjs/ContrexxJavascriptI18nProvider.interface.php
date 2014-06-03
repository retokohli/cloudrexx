<?php
/**
 * an interface describing an i18n provider.
 */
interface ContrexxJavascriptI18nProvider {
   /**
    * @return array an associative array with variable keys and values.
    * @param string $langCode the language code for generation of filenames and the like.
    */
    public function getVariables($langCode);
}