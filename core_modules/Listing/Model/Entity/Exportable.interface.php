<?php
/**
 * Exportable
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_listing
 */

namespace Cx\Core_Modules\Listing\Model\Entity;

/**
 * Exportable
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_listing
 */
interface Exportable {
    
    public function export($twoDimensionalArray);
}
