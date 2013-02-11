<?php

/**
 * ContrexxJavascriptI18nProvider
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  lib_cxjs
 */

/**
 * An interface describing an i18n provider.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  lib_cxjs
 */
interface ContrexxJavascriptI18nProvider {

   /**
    * @return array an associative array with variable keys and values.
    * @param string $langCode the language code for generation of filenames and the like.
    */
    public function getVariables($langCode);

}
