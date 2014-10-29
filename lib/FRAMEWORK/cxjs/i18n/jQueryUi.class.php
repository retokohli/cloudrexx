<?php

/**
 * JQueryUiI18nProvider
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  lib_cxjs
 */

/**
 * JQueryUiI18nProvider
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  lib_cxjs
 */
class JQueryUiI18nProvider implements ContrexxJavascriptI18nProvider {
    public function getVariables($langCode) {
        $vars = array();
        $datePickerFile = '/lib/javascript/jquery/ui/i18n/jquery.ui.datepicker-'.$langCode.'.js';
        $datePickerDefaultFile = '/lib/javascript/jquery/ui/i18n/jquery.ui.datepicker-default.js';
        if (file_exists(ASCMS_DOCUMENT_ROOT.$datePickerFile)) {
            $vars['datePickerI18nFile'] = $datePickerFile;
        } elseif (file_exists(ASCMS_DOCUMENT_ROOT.$datePickerDefaultFile)) {
            $vars['datePickerI18nFile'] = $datePickerDefaultFile;
        }
        return $vars;
    }
}
