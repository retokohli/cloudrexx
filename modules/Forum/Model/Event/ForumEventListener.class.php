<?php

/**
 * EventListener for Forum
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_forum
 */

namespace Cx\Modules\Forum\Model\Event;

/**
 * EventListener for Forum
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_forum
 */
class ForumEventListener implements \Cx\Core\Event\Model\Entity\EventListener {

    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }
   
    public static function SearchFindContent($search) {
        $term_db = $search->getTerm();

        $query = "SELECT `thread_id` AS `id`, `subject` AS `title`, `content`,
                           MATCH (`subject`, `content`, `keywords`) AGAINST ('%$term_db%') AS score
                      FROM " . DBPREFIX . "module_forum_postings
                     WHERE (   subject LIKE ('%$term_db%')
                            OR content LIKE ('%$term_db%')
                            OR keywords LIKE ('%$term_db%'))";
        $result = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($search->getResultArray($query, 'Forum', 'thread', 'id=', $search->getTerm()));
        $search->appendResult($result);
    }

}
