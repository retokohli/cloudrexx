<?php

/**
 * Database access function(s)
 *
 * @copyright    CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core
 * @version        1.0.0
 */

/**
 * @ignore
 */
require_once ASCMS_LIBRARY_PATH.'/adodb/adodb.inc.php';

/**
 * Returns the database object.
 *
 * If none was created before, or if {link $newInstance} is true,
 * creates a new database object first.
 * In case of an error, the reference argument $errorMsg is set
 * to the error message.
 * @author  Comvation Development Team <info@comvation.com>
 * @access  public
 * @version 1.0.0
 * @deprecated Use Doctrine!
 * @see \Cx\Core\Db\Db::getEntityManager()
 * @param   string  $errorMsg       Error message
 * @param   boolean $newInstance    Force new instance
 * @global  array                   Language array
 * @global  array                   Database configuration
 * @global  integer                 ADODB fetch mode
 * @return  boolean                 True on success, false on failure
 */
function getDatabaseObject(&$errorMsg, $newInstance = false)
{
    $db = new \Cx\Core\Db\Db();
    return $db->getAdoDb();
}
