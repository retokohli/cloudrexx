<?php
namespace Cx\Lib\FileSystem;
/**
 * File Interface
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @version     3.0.0
 * @package     contrexx
 * @subpackage  lib_filesystem
 */

/**
 * a Contrexx File interface
 * @version     3.0.0
 * @package     contrexx
 * @subpackage  lib_filesystem
 */
interface FileInterface {
    public function write($data);
    public function touch();
    public function copy($dst);
}
