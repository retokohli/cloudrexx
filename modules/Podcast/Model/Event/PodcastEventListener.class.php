<?php

/**
 * EventListener for Podcast
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_podcast
 */

namespace Cx\Modules\Podcast\Model\Event;

/**
 * EventListener for Podcast
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_podcast
 */
class PodcastEventListener implements \Cx\Core\Event\Model\Entity\EventListener {

    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }
   
    public static function SearchFindContent($search) {
        $term_db = $search->getTerm();

        //For Podcast
        $podcastQuery = "SELECT id, title, description AS content,
                            MATCH (description,title) AGAINST ('%$term_db%') AS score
                      FROM " . DBPREFIX . "module_podcast_medium
                     WHERE (   description LIKE ('%$term_db%')
                            OR title LIKE ('%$term_db%'))
                       AND status=1";
        $podcastResult = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($search->getResultArray($podcastQuery, 'Podcast', '', 'id=', $search->getTerm()));
        $search->appendResult($podcastResult);

        //For PodcastCategory
        $podcastCategoryQuery = "SELECT tblCat.id, tblCat.title, tblCat.description,
                           MATCH (title, description) AGAINST ('%$term_db%') AS score
                      FROM " . DBPREFIX . "module_podcast_category AS tblCat,
                           " . DBPREFIX . "module_podcast_rel_category_lang AS tblLang
                     WHERE (   title LIKE ('%$term_db%')
                            OR description LIKE ('%$term_db%'))
                       AND tblCat.status=1
                       AND tblLang.category_id=tblCat.id
                       AND tblLang.lang_id=" . FRONTEND_LANG_ID . "";
        $podcastCategoryResult = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($search->getResultArray($podcastCategoryQuery, 'Podcast', '', 'cid=', $search->getTerm()));
        $search->appendResult($podcastCategoryResult);
    }

}
