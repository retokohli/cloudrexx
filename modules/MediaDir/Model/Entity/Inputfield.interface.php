<?php

/**
 * Media Directory Inputfield Interface
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_mediadir
 */
namespace Cx\Modules\MediaDir\Model\Entity;
/**
 * Media Directory Inputfield Interface
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_mediadir
 */
interface Inputfield  {
    function getInputfield($intView, $arrInputfield, $intEntryId=null);
    function saveInputfield($intInputfieldId, $strValue);
    function deleteContent($intEntryId, $intIputfieldId);
    function getContent($intEntryId, $arrInputfield, $arrTranslationStatus);
    function getJavascriptCheck();
    function getFormOnSubmit($intInputfieldId);
}
?>
