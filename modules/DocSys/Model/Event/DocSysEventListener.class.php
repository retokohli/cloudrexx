<?php

/**
 * EventListener for DocSys
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_docsys
 */

namespace Cx\Modules\DocSys\Model\Event;

/**
 * EventListener for DocSys
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_docsys
 */
class DocSysEventListener implements \Cx\Core\Event\Model\Entity\EventListener {

    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }
   
    public static function SearchFindContent($search) {
        $term_db = $search->getTerm();
        $query = "SELECT id, text AS content, title,
                     MATCH (text, title) AGAINST ('%$term_db%') AS score
                      FROM " . DBPREFIX . "module_docsys
                     WHERE (   text LIKE ('%$term_db%')
                            OR title LIKE ('%$term_db%'))
                       AND lang=" . FRONTEND_LANG_ID . "
                       AND status=1
                       AND (startdate<='" . date('Y-m-d') . "' OR startdate='0000-00-00')
                       AND (enddate>='" . date('Y-m-d') . "' OR enddate='0000-00-00')";
        $result = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($search->getResultArray($query, 'DocSys', 'details', 'id=', $search->getTerm()));
        $search->appendResult($result);
    }

}
