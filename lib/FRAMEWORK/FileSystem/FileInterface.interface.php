<?php

/**
 * File Interface
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  lib_filesystem
 */

namespace Cx\Lib\FileSystem;

/**
 * FileInterface
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  lib_filesystem
 */
interface FileInterface {
    public function write($data);
    public function touch();
    public function copy($dst);
}
