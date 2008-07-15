<?php
/**
 * User Group Object
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  lib_framework
 */

/**
 * User Group Object
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  lib_framework
 */
class UserGroup {

    var $id;
    var $name;
    var $description;
    var $is_active;
    var $type;

    var $arrLoadedGroups = array();
    var $arrCache = array();

    var $arrAttributes = array(
        'group_id',
        'group_name',
        'group_description',
        'is_active',
        'type'
    );

    var $arrTypes = array(
        'frontend',
        'backend'
    );

    var $arrUsers;
    var $arrStaticPermissions;
    var $arrDynamicPermissions;

    var $defaultType = 'frontend';

    var $EOF;

    /**
     * Contains the message if an error occurs
     *
     * @var unknown_type
     */
    var $error_msg;

    function UserGroup()
    {
        $this->__construct();
    }

    function __construct()
    {
        $this->clean();
    }

    public function getGroups($filter = null, $arrSort = null, $arrAttributes = null, $limit = null, $offset = null)
    {
        $objGroup = clone $this;
        $objGroup->arrCache = &$this->arrCache;

        if ($objGroup->loadGroups($filter, $arrSort, $arrAttributes, $limit, $offset)) {
            return $objGroup;
        } else {
            return false;
        }
    }

    function loadGroups($filter = null, $arrSort = null, $arrAttributes = null, $limit = null, $offset = null)
    {
        global $objDatabase;

        $this->arrLoadedGroups = array();
        $arrWhereExpressions = array();
        $arrSortExpressions = array();
        $arrSelectExpressions = array();

        // set filter
        if (is_array($filter)) {
            $arrWhereExpressions = $this->parseFilterConditions($filter);
        } elseif (!empty($filter)) {
            $arrWhereExpressions[] = '`group_id` = '.intval($filter);
        }

        // set sort order
        if (is_array($arrSort)) {
            foreach ($arrSort as $attribute => $direction) {
                if (in_array($attribute, $this->arrAttributes) && in_array(strtolower($direction), array('asc', 'desc'))) {
                    $arrSortExpressions[] = '`'.$attribute.'` '.$direction;
                }
            }
        }

        // set field list
        if (is_array($arrAttributes)) {
            foreach ($arrAttributes as $attribute) {
                if (in_array($attribute, $this->arrAttributes) && !in_array($attribute, $arrSelectExpressions)) {
                    $arrSelectExpressions[] = $attribute;
                }
            }

            if (!in_array('group_id', $arrSelectExpressions)) {
                $arrSelectExpressions[] = 'group_id';
            }
        } else {
            $arrSelectExpressions = $this->arrAttributes;
        }

        $query = 'SELECT `'.implode('`, `', $arrSelectExpressions).'`
            FROM `'.DBPREFIX.'access_user_groups`'
            .(count($arrWhereExpressions) ? ' WHERE '.implode(' AND ', $arrWhereExpressions) : '')
            .(count($arrSortExpressions) ? ' ORDER BY '.implode(', ', $arrSortExpressions) : '');

        if (empty($limit)) {
            $objGroup = $objDatabase->Execute($query);
        } else {
            $objGroup = $objDatabase->SelectLimit($query, $limit, intval($offset));
        };

        if ($objGroup !== false && $objGroup->RecordCount() > 0) {
            while (!$objGroup->EOF) {
                $this->arrCache[$objGroup->fields['group_id']] = $this->arrLoadedGroups[$objGroup->fields['group_id']] = $objGroup->fields;
                $objGroup->MoveNext();
            }

            $this->first();
            return true;
        } else {
            $this->clean();
            return false;
        }
    }

    function parseFilterConditions($arrFilter)
    {
        $arrConditions = array();
        foreach ($arrFilter as $attribute => $condition) {
            switch ($attribute) {
                case 'group_name':
                case 'group_description':
                    $arrConditions[] = "`".$attribute."` LIKE '%".addslashes($condition)."%'";
                    break;

                case 'is_active':
                    $arrConditions[] = '`'.$attribute.'` = '.intval($condition);
                    break;

                case 'type':
                    $arrConditions[] = "`".$attribute."` = '".addslashes($condition)."'";
                    break;
            }
        }

        return $arrConditions;
    }

    public function getGroup($id)
    {
        $objGroup = clone $this;
        $objGroup->arrCache = &$this->arrCache;

        if ($objGroup->load($id)) {
            return $objGroup;
        } else {
            return false;
        }
    }

    function load($id)
    {
        if ($id) {
            if (!isset($this->arrCache[$id])) {
                return $this->loadGroups($id);
            } else {
                $this->id = $this->arrCache[$id]['group_id'];
                $this->name = isset($this->arrCache[$id]['group_name']) ? $this->arrCache[$id]['group_name'] : '';
                $this->description = isset($this->arrCache[$id]['group_description']) ? $this->arrCache[$id]['group_description'] : '';
                $this->is_active = isset($this->arrCache[$id]['is_active']) ? (bool)$this->arrCache[$id]['is_active'] : false;
                $this->type = isset($this->arrCache[$id]['type']) ? $this->arrCache[$id]['type'] : $this->defaultType;
                $this->arrDynamicPermissions = null;
                $this->arrStaticPermissions = null;
                $this->arrUsers = null;
                $this->EOF = false;
                return true;
            }
        } else {
            $this->clean();
        }
    }

    function loadUsers()
    {
        global $objDatabase;

        $arrUsers = array();

        $objUser = $objDatabase->Execute('
            SELECT
                tblRel.`user_id`
            FROM
                `'.DBPREFIX.'access_rel_user_group` AS tblRel
            INNER JOIN `'.DBPREFIX.'access_users` AS tblUser
            ON tblUser.`id` = tblRel.`user_id`
            WHERE tblRel.`group_id` = '.$this->id.'
            ORDER BY tblUser.`username`'
        );
        if ($objUser) {
            while (!$objUser->EOF) {
                array_push($arrUsers, $objUser->fields['user_id']);
                $objUser->MoveNext();
            }

            return $arrUsers;
        } else {
            return false;
        }
    }

    function loadPermissions($type)
    {
        global $objDatabase;

        $arrRightIds = array();

        $objResult = $objDatabase->Execute('SELECT `access_id` FROM `'.DBPREFIX.'access_group_'.$type.'_ids` WHERE `group_id`='.$this->id);
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                array_push($arrRightIds, $objResult->fields['access_id']);
                $objResult->MoveNext();
            }
            return $arrRightIds;
        } else {
            return false;
        }
    }

    function loadDynamicPermissions()
    {
        return $this->loadPermissions('dynamic');
    }

    function loadStaticPermissions()
    {
        return $this->loadPermissions('static');
    }

    /**
     * Store user account
     *
     * This stores the metadata of the user, which includes the username,
     * password, email, language ID, activ status and the administration status,
     * to the database.
     * If it is a new user, it also sets the registration time to the current time.
     *
     * @global ADONewConnection
     * @global array
     * @return boolean
     */
    function store()
    {
        global $objDatabase, $_CORELANG;

        if (!$this->isUniqueGroupName() || !$this->isValidGroupName()) {
            return false;
        }


        if ($this->id) {
            if ($objDatabase->Execute("
                UPDATE `".DBPREFIX."access_user_groups`
                SET
                    `group_name` = '".addslashes($this->name)."',
                    `group_description` = '".addslashes($this->description)."',
                    `is_active` = ".intval($this->is_active)."
                WHERE `group_id`=".$this->id
            ) === false) {
                $this->error_msg = $_CORELANG['TXT_ACCESS_FAILED_TO_UPDATE_GROUP'];
                return false;
            }
        } else {
            if ($objDatabase->Execute("
                INSERT INTO `".DBPREFIX."access_user_groups` (
                    `group_name`,
                    `group_description`,
                    `is_active`,
                    `type`
                ) VALUES (
                    '".addslashes($this->name)."',
                    '".addslashes($this->description)."',
                    ".intval($this->is_active).",
                    '".$this->type."'
                )"
            ) !== false) {
                $this->id = $objDatabase->Insert_ID();
            } else {
                $this->error_msg = $_CORELANG['TXT_ACCESS_FAILED_TO_CREATE_GROUP'];
                return false;
            }
        }

        if (!$this->storeUserAssociations()) {
            $this->error_msg = $_CORELANG['TXT_ACCESS_COULD_NOT_SET_USER_ASSOCIATIONS'];
            return false;
        }

        if (!$this->storePermissions()) {
            $this->error_msg = $_CORELANG['TXT_ACCESS_COULD_NOT_SET_PERMISSIONS'];
            return false;
        }

        return true;
    }

    /**
     * Store user associations
     *
     * Stores the user associations of the loaded group.
     * Returns TRUE no success, FALSE on failure.
     *
     * @global ADONewConnection
     * @return boolean
     */
    function storeUserAssociations()
    {
        global $objDatabase;

        $status = true;
        $arrCurrentUsers = $this->loadUsers();
        $arrAddedUsers = array_diff($this->getAssociatedUserIds(), $arrCurrentUsers);
        $arrRemovedUsers = array_diff($arrCurrentUsers, $this->getAssociatedUserIds());

        foreach ($arrRemovedUsers as $userId) {
            if ($objDatabase->Execute('DELETE FROM `'.DBPREFIX.'access_rel_user_group` WHERE `group_id` = '.$this->id.' AND `user_id` = '.$userId) === false) {
                $status = false;
            }
        }

        foreach ($arrAddedUsers as $userId) {
            if ($objDatabase->Execute('INSERT INTO `'.DBPREFIX.'access_rel_user_group` (`user_id`, `group_id`) VALUES ('.$userId.', '.$this->id.')') === false) {
                $status = false;
            }
        }

        return $status;
    }

    function storePermissions()
    {
        global $objDatabase;

        $status = true;
        foreach (array('Static', 'Dynamic') as $type) {
            $arrCurrentIds = $this->{'load'.$type.'Permissions'}();
            $arrAddedRightIds = array_diff($this->{$ids = 'arr'.$type.'Permissions'}, $arrCurrentIds);
            $arrRemovedRightIds = array_diff($arrCurrentIds, $this->$ids);
            $table = DBPREFIX.'access_group_'.strtolower($type).'_ids';

            foreach ($arrRemovedRightIds as $rightId) {
                if ($objDatabase->Execute('DELETE FROM `'.$table.'` WHERE `access_id`='.$rightId.' AND `group_id`='.$this->id) === false) {
                    $status = false;
                }
            }

            foreach ($arrAddedRightIds as $rightId) {
                if ($objDatabase->Execute('INSERT INTO `'.$table.'` (`access_id` , `group_id`) VALUES ('.$rightId.','.$this->id.')') === false) {
                    $status = false;
                }
            }
        }

        return $status;
    }

    private function clean()
    {
        $this->id = 0;
        $this->name = '';
        $this->description = '';
        $this->is_active = false;
        $this->type = $this->defaultType;
        $this->arrDynamicPermissions = null;
        $this->arrStaticPermissions = null;
        $this->arrUsers = null;
        $this->EOF = true;
    }

    function delete()
    {
        global $objDatabase, $_CORELANG;

        if ($objDatabase->Execute('DELETE FROM `'.DBPREFIX.'access_rel_user_group` WHERE `group_id` = '.$this->id) !== false && $objDatabase->Execute('DELETE FROM `'.DBPREFIX.'access_user_groups` WHERE `group_id` = '.$this->id) !== false) {
            return true;
        } else {
            $this->error_msg = sprintf($_CORELANG['TXT_ACCESS_GROUP_DELETE_FAILED'], $this->name);
            return false;
        }
    }

    /**
     * Load first group
     *
     */
    function first()
    {
        if (reset($this->arrLoadedGroups) === false || !$this->load(key($this->arrLoadedGroups))) {
            $this->EOF = true;
        } else {
            $this->EOF = false;
        }
    }

    /**
     * Load next group
     *
     */
    function next()
    {
        if (next($this->arrLoadedGroups) === false || !$this->load(key($this->arrLoadedGroups))) {
            $this->EOF = true;
        }
    }


    function setName($name)
    {
        $this->name = $name;
    }

    function setDescription($description)
    {
        $this->description = $description;
    }

    function setActiveStatus($status)
    {
        $this->is_active = (bool)$status;
    }

    function setType($type)
    {
        $this->type = in_array($type, $this->arrTypes) ? $type : $this->defaultType;
    }

    /**
     * Set ID's of users which should belong to this group
     *
     * @param array $arrUsers
     * @see User, User::getUser()
     * @return void
     */
    function setUsers($arrUsers)
    {
        $objFWUser = FWUser::getFWUserObject();
        $this->arrUsers = array();
        foreach ($arrUsers as $userId)
        {
            //if ($objFWUser->objUser->getUser($userId)) {
                $this->arrUsers[] = $userId;
            //}
        }
    }

    function setDynamicPermissionIds($arrPermissionIds)
    {
        $this->arrDynamicPermissions = array_map('intval', $arrPermissionIds);
    }

    function setStaticPermissionIds($arrPermissionIds)
    {
        $this->arrStaticPermissions = array_map('intval', $arrPermissionIds);
    }

    function getLoadedGroupCount()
    {
        return count($this->arrLoadedGroups);
    }

    function getGroupCount($arrFilter = null)
    {
        global $objDatabase;

        $arrWhereExpressions = is_array($arrFilter) ? $this->parseFilterConditions($arrFilter) : array();

        $objGroupCount = $objDatabase->SelectLimit('
            SELECT SUM(1) AS `group_count`
            FROM `'.DBPREFIX.'access_user_groups`'
            .(count($arrWhereExpressions) ? ' WHERE '.implode(' AND ', $arrWhereExpressions) : ''),
            1
        );

        if ($objGroupCount !== false) {
            return $objGroupCount->fields['group_count'];
        } else {
            return false;
        }
    }

    function getUserCount($onlyActive = false)
    {
        global $objDatabase;

        $objCount = $objDatabase->SelectLimit('SELECT COUNT(1) AS `user_count` FROM `'.DBPREFIX.'access_users` AS tblUser'
            .($this->id ? ' INNER JOIN `'.DBPREFIX.'access_rel_user_group` AS tblRel ON tblRel.`user_id` = tblUser.`id` WHERE tblRel.`group_id` = '.$this->id : '')
            .($onlyActive ? (!$this->id ? ' WHERE' : ' AND').' tblUser.`active` = 1 ' : ''), 1);
        if ($objCount) {
            return $objCount->fields['user_count'];
        } else {
            return false;
        }
    }

    function getAssociatedUserIds()
    {
        if (!isset($this->arrUsers)) {
            $this->arrUsers = $this->loadUsers();
        }
        return $this->arrUsers;
    }

    function getDynamicPermissionIds()
    {
        if (!isset($this->arrDynamicPermissions)) {
            $this->arrDynamicPermissions = $this->loadDynamicPermissions();
        }
        return $this->arrDynamicPermissions;
    }

    function getStaticPermissionIds()
    {
        if (!isset($this->arrStaticPermissions)) {
            $this->arrStaticPermissions = $this->loadStaticPermissions();
        }
        return $this->arrStaticPermissions;
    }

    function getId()
    {
        return $this->id;
    }

    function getName()
    {
        return $this->name;
    }

    function getDescription()
    {
        return $this->description;
    }

    function getActiveStatus()
    {
        return $this->is_active;
    }

    function getType()
    {
        return $this->type;
    }

    function getTypes()
    {
        return $this->arrTypes;
    }

    function getErrorMsg()
    {
        return $this->error_msg;
    }


    /**
     * Is unique group name
     *
     * Checks if the group name specified by $name is unique in the system.
     *
     * @param string $name
     * @param integer $id
     * @return boolean
     */
    function isUniqueGroupName()
    {
        global $objDatabase, $_CORELANG;

        $objResult = $objDatabase->SelectLimit("SELECT 1 FROM ".DBPREFIX."access_user_groups WHERE `group_name`='".addslashes($this->name)."' AND `group_id` != ".$this->id, 1);

        if ($objResult && $objResult->RecordCount() == 0) {
            return true;
        } else {
            $this->error_msg = $_CORELANG['TXT_ACCESS_DUPLICATE_GROUP_NAME'];
            return false;
        }
    }

    function isValidGroupName()
    {
        global $_CORELANG;

        if (!empty($this->name)) {
            return true;
        } else {
            $this->error_msg = $_CORELANG['TXT_ACCESS_EMPTY_GROUP_NAME'];
            return false;
        }
    }
}
