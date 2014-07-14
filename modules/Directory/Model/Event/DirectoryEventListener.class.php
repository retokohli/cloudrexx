<?php

/**
 * EventListener for Directory
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_directory
 */

namespace Cx\Modules\Directory\Model\Event;

/**
 * EventListener for Directory
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_directory
 */
class DirectoryEventListener implements \Cx\Core\Event\Model\Entity\EventListener {

    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }
   
    public static function SearchFindContent($search) {
        $term_db = $search->getTerm();

        //For Directory
        $query = "SELECT id, title, description AS content,
                           MATCH (title, description) AGAINST ('%$term_db%') AS score
                      FROM " . DBPREFIX . "module_directory_dir
                     WHERE status='1'
                       AND (   title LIKE ('%$term_db%')
                            OR description LIKE ('%$term_db%')
                            OR searchkeys LIKE ('%$term_db%')
                            OR company_name LIKE ('%$term_db%'))";
        $result = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($search->getResultArray($query, 'Directory', 'detail', 'id=', $search->getTerm()));
        $search->appendResult($result);

        //For Directory Category
        $categoryQuery = "SELECT id, name AS title, description AS content,
                           MATCH (name, description) AGAINST ('%$term_db%') AS score
                      FROM " . DBPREFIX . "module_directory_categories
                     WHERE status='1'
                       AND (   name LIKE ('%$term_db%')
                            OR description LIKE ('%$term_db%'))";
        $categoryResult = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($search->getResultArray($categoryQuery, 'Directory', '', 'lid=', $search->getTerm()));
        $search->appendResult($categoryResult);
    }

}
