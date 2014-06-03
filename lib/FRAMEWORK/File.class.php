<?php

/**
 * File System Framework
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Janik Tschanz <janik.tschanz@comvation.com>
 * @author      Reto Kohli <reto.kohli@comvation.com>
 *              (new static methods, error system)
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @version     3.0.0
 * @package     contrexx
 * @subpackage  lib_filesystem
 */

/**
 * Legacy file manager
 *
 * <b>Don't use this anymore</b>, use the static refactored class \Cx\Lib\FileSystem\FileSystem
 * instead.
 * This class allows the instantiation of the class Cx\Lib\FileSystem by its
 * former name <File> (Cx < 3.0)
 * I.e.: $objLegacyFile = new File();
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @version     3.0.0
 * @deprecated  deprecated since 3.0.0
 * @package     contrexx
 * @subpackage  lib_filesystem
 */
class File extends Cx\Lib\FileSystem\FileSystem{}

