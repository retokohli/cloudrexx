<?php
namespace Cx\Lib\FileSystem;
/**
 * File Interface
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @version     3.0.0
 * @package     contrexx
 * @subpackage  lib_framework_file
 */

/**
 * a Contrexx File interface
 * @version     3.0.0
 * @package     contrexx
 * @subpackage  lib_framework_file
 */
interface FileInterface {
    public function write($data);
    public function touch();
}

