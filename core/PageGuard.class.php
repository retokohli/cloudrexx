<?php
/**
 * Handles access restriction administration on Pages.
 * (Retrieve / Store)
 */
class PageGuardException extends Exception {}

class PageGuard {
    protected $db = null;

    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getAssignedGroupIds($page, $frontend) {
        $accessId = $this->getAccessId($page, $frontend);

        $query = 'SELECT group_id
            FROM '.DBPREFIX.'access_group_dynamic_ids
            WHERE access_id='.$accessId;
        $rs = $this->db->Execute($query);
        if($rs === false)
            throw new PageGuardException('Could not fetch groups for page "'.$page->getTitle().'" with id "'.$page->getId().'" (access id "'.$accessId.')"');
        
        $ids = array();
        while(!$rs->EOF) {
            $ids[] = $rs->fields['group_id'];
        }

        return $ids;
    }

    public function setAssignedGroupIds($page, $ids, $frontend) {
        $accessId = $this->getAccessId($page, $frontend);

        $query = 'DELETE FROM '.DBPREFIX.'access_group_dynamic_ids ' .
                 'WHERE access_id = '.$accessId;
        $result = $this->db->Execute($query);
        if($result === false)
            throw new PageGuardException('Could not delete group assignments for page "'.$page->getTitle().'" with id "'.$page->getId().'" (access id "'.$accessId.')"');
        
        $query = 'INSERT INTO '.DBPREFIX.'access_group_dynamic_ids (access_id, group_id) ' .
                 'VALUES ';
        $query .= '(' . join('),(', $ids) . ');';
        $result = $this->db->Execute($query);
        if($result === false)
            throw new PageGuardException('Could not delete group assignments for page "'.$page->getTitle().'" with id "'.$page->getId().'" (access id "'.$accessId.')"');
    }

    protected function getAccessId($page, $frontend) {
        $accessId = $page->getFrontendAccessId();
        if(!$frontend)
            $accessId = $page->getBackendAccessId();
        if($accessId === 0)
            throw new PageGuardException('Tried to protect Page without accessid. Call setFrontendProtection() / setBackendProtection() first');
        return $accessId;
    }

    public function getGroups($frontend) {
        $type = 'frontend';
        if(!$frontend)
            $type = 'backend';

        $query = "SELECT group_id, group_name FROM ".DBPREFIX."access_user_groups WHERE type='".$type."' ORDER BY group_name";
        $rs = $this->db->Execute($query);
        if($rs == false)
            throw new PageGuardException('Could not fetch "'.$type.'" groups.');

        $groups = array();
        while(!$rs->EOF) {
            $groups[$rs->fields['group_id']] = $rs->fields['group_name'];
        }
        return $groups;
    }
}