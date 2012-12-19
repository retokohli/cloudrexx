<?php
/**
 * Doctrine SQL Logger for DBG
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     3.0.1
 * @package     contrexx
 * @subpackage  lib_framework_dbg
 * @todo        Edit PHP DocBlocks!
 */
namespace Cx\Lib\DBG;

/**
 * Doctrine SQL Logger for DBG
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     3.0.1
 * @package     contrexx
 * @subpackage  lib_framework_dbg
 * @todo        Edit PHP DocBlocks!
 */
class DoctrineSQLLogger implements \Doctrine\DBAL\Logging\SQLLogger
{
    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        if (!\DBG::getMode() & DBG_DOCTRINE) {
            return;
        }

        // prepare SQL statement
        if ($params) {
            $sql = str_replace('?', "'%s'", $sql);
            foreach ($params as &$param) {
                // serialize arrays
                if (is_array($param)) {
                    $param = serialize($param);
                } elseif (   !is_int($param)
                          && !is_string($param)
                ) {
                    // serialize objects
                    switch (get_class($param)) {
                        case 'DateTime':
                            // output DateTime object as date literal
                            $param = $param->format(ASCMS_DATE_FORMAT_DATETIME);
                            break;
                        default:
                            break;
                        
                    }
                }
            }
            $sql = vsprintf($sql, $params);
    	}

        \DBG::logSQL($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {

    }
}

