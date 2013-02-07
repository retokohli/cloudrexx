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
    private $query = null;
    private $startTime = null;
    
    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->query = null;
        if (   !(\DBG::getMode() & DBG_DOCTRINE)
            && !(\DBG::getMode() & DBG_DOCTRINE_CHANGE)
            && !(\DBG::getMode() & DBG_DOCTRINE_ERROR)
        ) {
            return;
        }

        // prepare SQL statement
        if ($params) {
            $sql = str_replace('?', "'%s'", $sql);
            //$this->query = vsprintf($sql, $params);
            foreach ($params as &$param) {
                // serialize arrays
                if (is_array($param)) {
                    $param = serialize($param);
                } elseif (is_object($param)) {
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
        $this->startTime = microtime(true);
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        /*global $objDatabase;
        
        if (   !(\DBG::getMode() & DBG_DOCTRINE)
            && !(\DBG::getMode() & DBG_DOCTRINE_CHANGE)
            && !(\DBG::getMode() & DBG_DOCTRINE_ERROR)
        ) {
            return;
        }
        
        if (!$objDatabase || !$this->query) {
            return;
        }
        
        $timeDiff = microtime(true) - $this->startTime;
        $this->startTime = null;
        
        $result = $objDatabase->execute($this->query);
        $this->query = null;
        if (!$result) {
            \DBG::log('<p>The query above failes (took ' . $timeDiff .  ' seconds)!</p>');
        } else {
            \DBG::log('<p>The query above returns ' . $result->RecordCount() . ' rows when executed in SQL directly (took ' . $timeDiff .  ' seconds).</p>');
        }*/
    }
}
