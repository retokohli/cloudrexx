<?php

/**
 * User Management
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Daeppen <thomas.daeppen@comvation.com>
 * @version     2.0.0
 * @package     contrexx
 * @subpackage  core_module_access
 */

/**
 * @ignore
 */
require_once ASCMS_CORE_MODULE_PATH.'/access/lib/AccessLib.class.php';

/**
 * Info Blocks about Community used in the layout
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Daeppen <thomas.daeppen@comvation.com>
 * @version     2.0.0
 * @package     contrexx
 * @subpackage  core_module_access
 */
class Access_Blocks extends AccessLib
{
    public function __construct()
    {
        global $objTemplate;

        parent::__construct();

        $this->_objTpl = &$objTemplate;
    }


    function setCurrentlyOnlineUsers($gender=null)
    {
        $objFWUser = FWUser::getFWUserObject();
        $arrSettings = User_Setting::getSettings();

        $filter = array(
            'active'    => true,
            'last_activity' => array(
                '>' => (time()-3600)
            )
        );
        if ($arrSettings['block_currently_online_users_pic']['status']) {
            $filter['picture'] = array('!=' => '');
        }

        if (!empty($gender)) {
            $filter['gender'] = 'gender_'.$gender;
        }

        $objUser = $objFWUser->objUser->getUsers(
            $filter,
            null,
            array(
                'last_activity'    => 'desc',
                'username'        => 'asc'
            ),
            null,
            $arrSettings['block_currently_online_users']['value']
        );
        if ($objUser) {
            while (!$objUser->EOF) {
                $this->_objTpl->setVariable(array(
                    'ACCESS_USER_ID'    => $objUser->getId(),
                    'ACCESS_USER_USERNAME'    => htmlentities($objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET)
                ));

                $objUser->objAttribute->first();
                while (!$objUser->objAttribute->EOF) {
                    $objAttribute = $objUser->objAttribute->getById($objUser->objAttribute->getId());
                    $this->parseAttribute($objUser, $objAttribute->getId(), 0, false, false, false, false, false);
                    $objUser->objAttribute->next();
                }

                $this->_objTpl->parse('access_currently_online_'.(!empty($gender) ? $gender.'_' : '').'members');

                $objUser->next();
            }
        } else {
            $this->_objTpl->hideBlock('access_currently_online_'.(!empty($gender) ? $gender.'_' : '').'members');
        }
    }


    function setLastActiveUsers($gender = null)
    {
        $arrSettings = User_Setting::getSettings();

        $filter['active'] = true;
        if ($arrSettings['block_last_active_users_pic']['status']) {
            $filter['picture'] = array('!=' => '');
        }

        if (!empty($gender)) {
            $filter['gender'] = 'gender_'.$gender;
        }

        $objFWUser = FWUser::getFWUserObject();
        $objUser = $objFWUser->objUser->getUsers(
            $filter,
            null,
            array(
                'last_activity'    => 'desc',
                'username'        => 'asc'
            ),
            null,
            $arrSettings['block_last_active_users']['value']
        );
        if ($objUser) {
            while (!$objUser->EOF) {
                $this->_objTpl->setVariable(array(
                    'ACCESS_USER_ID'    => $objUser->getId(),
                    'ACCESS_USER_USERNAME'    => htmlentities($objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET)
                ));

                $objUser->objAttribute->first();
                while (!$objUser->objAttribute->EOF) {
                    $objAttribute = $objUser->objAttribute->getById($objUser->objAttribute->getId());
                    $this->parseAttribute($objUser, $objAttribute->getId(), 0, false, false, false, false, false);
                    $objUser->objAttribute->next();
                }

                $this->_objTpl->parse('access_last_active_'.(!empty($gender) ? $gender.'_' : '').'members');

                $objUser->next();
            }
        } else {
            $this->_objTpl->hideBlock('access_last_active_'.(!empty($gender) ? $gender.'_' : '').'members');
        }
    }


    function setLatestRegisteredUsers($gender = null)
    {
        $arrSettings = User_Setting::getSettings();

        $filter['active'] = true;
        if ($arrSettings['block_latest_reg_users_pic']['status']) {
            $filter['picture'] = array('!=' => '');
        }

        if (!empty($gender)) {
            $filter['gender'] = 'gender_'.$gender;
        }

        $objFWUser = FWUser::getFWUserObject();
        $objUser = $objFWUser->objUser->getUsers(
            $filter,
            null,
            array(
                'regdate'    => 'desc',
                'username'    => 'asc'
            ),
            null,
            $arrSettings['block_latest_reg_users']['value']
        );
        if ($objUser) {
            while (!$objUser->EOF) {
                $this->_objTpl->setVariable(array(
                    'ACCESS_USER_ID'    => $objUser->getId(),
                    'ACCESS_USER_USERNAME'    => htmlentities($objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET)
                ));

                $objUser->objAttribute->first();
                while (!$objUser->objAttribute->EOF) {
                    $objAttribute = $objUser->objAttribute->getById($objUser->objAttribute->getId());
                    $this->parseAttribute($objUser, $objAttribute->getId(), 0, false, false, false, false, false);
                    $objUser->objAttribute->next();
                }

                $this->_objTpl->parse('access_latest_registered_'.(!empty($gender) ? $gender.'_' : '').'members');

                $objUser->next();
            }
        } else {
            $this->_objTpl->hideBlock('access_latest_registered_'.(!empty($gender) ? $gender.'_' : '').'members');
        }
    }


    function setBirthdayUsers($gender = null)
    {
        $arrSettings = User_Setting::getSettings();

        $filter = array(
            'active'    => true,
            'birthday_day'      => date('j'),
            'birthday_month'    => date('n')
        );
        if ($arrSettings['block_birthday_users_pic']['status']) {
            $filter['picture'] = array('!=' => '');
        }

        if (!empty($gender)) {
            $filter['gender'] = 'gender_'.$gender;
        }

        $objFWUser = FWUser::getFWUserObject();
        $objUser = $objFWUser->objUser->getUsers(
            $filter,
            null,
            array(
                'regdate'    => 'desc',
                'username'    => 'asc'
            ),
            null,
            $arrSettings['block_birthday_users']['value']
        );
        if ($objUser) {
            while (!$objUser->EOF) {
                $this->_objTpl->setVariable(array(
                    'ACCESS_USER_ID'    => $objUser->getId(),
                    'ACCESS_USER_USERNAME'    => htmlentities($objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET)
                ));

                $objUser->objAttribute->first();
                while (!$objUser->objAttribute->EOF) {
                    $objAttribute = $objUser->objAttribute->getById($objUser->objAttribute->getId());
                    $this->parseAttribute($objUser, $objAttribute->getId(), 0, false, false, false, false, false);
                    $objUser->objAttribute->next();
                }

                $this->_objTpl->parse('access_birthday_'.(!empty($gender) ? $gender.'_' : '').'members');

                $objUser->next();
            }
        } else {
            $this->_objTpl->hideBlock('access_birthday_'.(!empty($gender) ? $gender.'_' : '').'members');
        }
    }


    function isSomeonesBirthdayToday()
    {
        $arrSettings = User_Setting::getSettings();

        $filter = array(
            'active'            => true,
            'birthday_day'      => date('j'),
            'birthday_month'    => date('n')
        );
        if ($arrSettings['block_birthday_users_pic']['status']) {
            $filter['picture'] = array('!=' => '');
        }

        $objFWUser = FWUser::getFWUserObject();
        if ($objFWUser->objUser->getUsers($filter, null, null, null, 1))
            return true;
        return false;
    }

}

?>
