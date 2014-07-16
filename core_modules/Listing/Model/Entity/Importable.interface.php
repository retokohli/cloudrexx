<?php
/**
 * Importable
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_listing
 */

namespace Cx\Core_Modules\Listing\Model\Entity;

/**
 * Importable
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_listing
 */
interface Importable {

    public function import($dataAsString);
}
