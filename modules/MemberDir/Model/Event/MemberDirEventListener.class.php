<?php

/**
 * EventListener for MemberDir
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_memberdir
 */

namespace Cx\Modules\MemberDir\Model\Event;

/**
 * EventListener for MemberDir
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_memberdir
 */
class MemberDirEventListener implements \Cx\Core\Event\Model\Entity\EventListener {

    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }
   
    public static function SearchFindContent($search) {
        $term_db = $search->getTerm();

        //For MemberDir
        $query = "SELECT tblValue.id, tblDir.name AS title,
                           CONCAT_WS(' ', `1`, `2`, '') AS content
                      FROM " . DBPREFIX . "module_memberdir_values AS tblValue,
                           " . DBPREFIX . "module_memberdir_directories AS tblDir
                     WHERE tblDir.dirid=tblValue.dirid
                       AND tblValue.`lang_id`=" . FRONTEND_LANG_ID . "
                       AND (   tblValue.`1` LIKE '%$term_db%'
                            OR tblValue.`2` LIKE '%$term_db%'
                            OR tblValue.`3` LIKE '%$term_db%'
                            OR tblValue.`4` LIKE '%$term_db%'
                            OR tblValue.`5` LIKE '%$term_db%'
                            OR tblValue.`6` LIKE '%$term_db%'
                            OR tblValue.`7` LIKE '%$term_db%'
                            OR tblValue.`8` LIKE '%$term_db%'
                            OR tblValue.`9` LIKE '%$term_db%'
                            OR tblValue.`10` LIKE '%$term_db%'
                            OR tblValue.`11` LIKE '%$term_db%'
                            OR tblValue.`12` LIKE '%$term_db%'
                            OR tblValue.`13` LIKE '%$term_db%'
                            OR tblValue.`14` LIKE '%$term_db%'
                            OR tblValue.`15` LIKE '%$term_db%'
                            OR tblValue.`16` LIKE '%$term_db%'
                            OR tblValue.`17` LIKE '%$term_db%'
                            OR tblValue.`18` LIKE '%$term_db%')";
        $result = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($search->getResultArray($query, 'MemberDir', '', 'mid=', $search->getTerm()));
        $search->appendResult($result);

        //For MemberDirCategory
        $categoryQuery = "SELECT dirid AS id, name AS title, description AS content,
                           MATCH (name, description) AGAINST ('%$term_db%') AS score
                      FROM " . DBPREFIX . "module_memberdir_directories
                     WHERE active='1'
                       AND lang_id=" . FRONTEND_LANG_ID . "
                       AND (   name LIKE ('%$term_db%')
                            OR description LIKE ('%$term_db%'))";
        $categoryResult = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($search->getResultArray($categoryQuery, 'MemberDir', '', 'id=', $search->getTerm()));
        $search->appendResult($categoryResult);
    }

}
