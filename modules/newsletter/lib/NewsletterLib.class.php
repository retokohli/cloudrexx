<?php

/**
 * Class newsletter library
 *
 * Newsletter module class
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @access        public
 * @version        1.0.0
 * @package     contrexx
 * @subpackage  module_newsletter
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Class newsletter library
 *
 * Newsletter module class
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @access        public
 * @version        1.0.0
 * @package     contrexx
 * @subpackage  module_newsletter
 * @todo        Edit PHP DocBlocks!
 */
class NewsletterLib
{
    public $_arrRecipientTitles = null;

    protected function _getLists($orderBy = '', $excludeDisabledCategories=false)
    {
        global $objDatabase;

        $arrLists = array();
        $objList = $objDatabase->Execute("SELECT tblCategory.id,
            tblCategory.status,
            tblCategory.name,
            tblCategory.notification_email,
            COUNT(tblRel.category) as recipients
            FROM ".DBPREFIX."module_newsletter_category AS tblCategory
            LEFT JOIN ".DBPREFIX."module_newsletter_rel_user_cat AS tblRel ON tblRel.category = tblCategory.id
            ".($excludeDisabledCategories ? ' WHERE status=1' : '')."
            GROUP BY tblCategory.id".(!empty($orderBy) ? " ORDER BY ".$orderBy : ""));
        if ($objList !== false) {
            while (!$objList->EOF) {
                $objMail = $objDatabase->SelectLimit("
                    SELECT tblNewsletter.id, tblNewsletter.subject, tblNewsletter.date_sent
                      FROM ".DBPREFIX."module_newsletter AS tblNewsletter
                      LEFT JOIN ".DBPREFIX."module_newsletter_rel_cat_news AS tblRel
                        ON tblRel.newsletter = tblNewsletter.id
                     WHERE tblRel.category=".$objList->fields['id']."
                     ORDER BY date_sent DESC", 1);
                if ($objMail !== false && $objMail->RecordCount() == 1) {
                    $mailId = $objMail->fields['id'];
                    $mailSend = $objMail->fields['date_sent'];
                    $mailName = $objMail->fields['subject'];
                } else {
                    $mailId = 0;
                    $mailSend = 0;
                    $mailName = '';
                }

                $arrLists[$objList->fields['id']] = array(
                    'status'     => $objList->fields['status'],
                    'name'       => $objList->fields['name'],
                    'recipients' => $objList->fields['recipients'],
                    'mail_sent'  => $mailSend,
                    'mail_name'  => $mailName,
                    'mail_id'    => ($mailSend > 0 ? $mailId : 0),
                    'notification_email'    => $objList->fields['notification_email'],
                );
                $objList->MoveNext();
            }
        }
        return $arrLists;
    }

    function _addRecipient($email, $uri, $sex, $title, $lastname, $firstname, $company, $street, $zip, $city, $country, $phone, $birthday, $status, $arrLists)
    {
        global $objDatabase;

        $query = "INSERT INTO ".DBPREFIX."module_newsletter_user (
        `code`,
        `email`,
        `uri`,
        `sex`,
        `title`,
        `lastname`,
        `firstname`,
        `company`,
        `street`,
        `zip`,
        `city`,
        `country`,
        `phone`,
        `birthday`,
        `status`,
        `emaildate`
        ) VALUES (
        '".$this->_emailCode()."',
        '".contrexx_addslashes($email)."',
        '".contrexx_addslashes($uri)."',
        ".(empty($sex) ? 'NULL' : "'".contrexx_addslashes($sex)."'").",
        ".intval($title).",
        '".contrexx_addslashes($lastname)."',
        '".contrexx_addslashes($firstname)."',
        '".contrexx_addslashes($company)."',
        '".contrexx_addslashes($street)."',
        '".contrexx_addslashes($zip)."',
        '".contrexx_addslashes($city)."',
        '".contrexx_addslashes($country)."',
        '".contrexx_addslashes($phone)."',
        '".contrexx_addslashes($birthday)."',
        ".intval($status).",
        ".time().")";

        if ($objDatabase->Execute($query) !== false) {
            if ($this->_setRecipientLists($objDatabase->Insert_ID(), $arrLists)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function _updateRecipient($id, $email, $uri, $sex, $title, $lastname, $firstname, $company, $street, $zip, $city, $country, $phone, $birthday, $status, $arrLists)
    {
        global $objDatabase;

        $query = SQL::update('module_newsletter_user', array(
            'email' => contrexx_addslashes($email),
            'uri' => contrexx_addslashes($uri),
            'sex' => array('value' => contrexx_addslashes($sex), 'omitEmpty' => true),
            'title' => intval($title),
            'lastname' => contrexx_addslashes($lastname),
            'firstname' => contrexx_addslashes($firstname),
            'company' => contrexx_addslashes($company),
            'street' => contrexx_addslashes($street),
            'zip' => contrexx_addslashes($zip),
            'city' => contrexx_addslashes($city),
            'country' => contrexx_addslashes($country),
            'phone' => contrexx_addslashes($phone),
            'birthday' => contrexx_addslashes($birthday),
            'status' => intval($status)
        ))."WHERE id=".$id;

        if ($objDatabase->Execute($query) !== false) {
            if ($this->_setRecipientLists($id, $arrLists)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function _setRecipientLists($recipientId, $arrLists)
    {
        global $objDatabase;

        $arrCurrentList = array();

        $objRelList = $objDatabase->Execute("SELECT category FROM ".DBPREFIX."module_newsletter_rel_user_cat WHERE user=".$recipientId);
        if ($objRelList !== false) {
            while (!$objRelList->EOF) {
                array_push($arrCurrentList, $objRelList->fields['category']);
                $objRelList->MoveNext();
            }

            $arrNewLists = array_diff($arrLists, $arrCurrentList);
            $arrRemovedLists = array_diff($arrCurrentList, $arrLists);

            foreach ($arrNewLists as $listId) {
                $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_rel_user_cat (`user`, `category`) VALUES (".$recipientId.", ".$listId.")");
            }
            foreach ($arrRemovedLists as $listId) {
                $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_rel_user_cat WHERE user=".$recipientId." AND category=".$listId);
            }
            return true;
        }
        return false;
    }

    function _addRecipient2List($recipientId, $listId)
    {
        global $objDatabase;

        $objRelList = $objDatabase->Execute("SELECT 1 FROM ".DBPREFIX."module_newsletter_rel_user_cat WHERE user=".$recipientId." AND category = ".$listId);
        if ($objRelList !== false) {
            if ($objRelList->RecordCount() == 0) {
                if ($objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_rel_user_cat (`user`, `category`) VALUES (".$recipientId.", ".$listId.")") !== false) {
                    return true;
                }
                return false;
            }
            return true;
        }
        return false;
    }

    function _emailCode()
    {
        $ReturnVar = '';
        $pool = "qwertzupasdfghkyxcvbnm";
        $pool .= "23456789";
        $pool .= "WERTZUPLKJHGFDSAYXCVBNM";
        srand ((double)microtime()*1000000);
        for($index = 0; $index < 10; $index++){
            $ReturnVar .= substr($pool,(rand()%(strlen ($pool))), 1);
        }
        return $ReturnVar;
    }

    function _isUniqueRecipientEmail($email, $recipientId)
    {
        global $objDatabase;

        $objRecipient = $objDatabase->SelectLimit("SELECT id FROM ".DBPREFIX."module_newsletter_user WHERE email='".contrexx_addslashes($email)."' AND id!=".$recipientId, 1);
        if ($objRecipient !== false && $objRecipient->RecordCount() == 0) {
            return true;
        } else {
            return false;
        }
    }

    function _getSettings()
    {
        global $objDatabase;

        static $arrSettings = array();

        if (count($arrSettings) == 0) {
            $objSettings = $objDatabase->Execute("SELECT setid, setname, setvalue, status FROM ".DBPREFIX."module_newsletter_settings");
            if ($objSettings !== false) {
                while (!$objSettings->EOF) {
                    $arrSettings[$objSettings->fields['setname']] = array(
                        'setid'        => $objSettings->fields['setid'],
                        'setvalue'    => $objSettings->fields['setvalue'],
                        'status'    => $objSettings->fields['status']
                    );

                    $objSettings->MoveNext();
                }
            }
        }

        return $arrSettings;
    }

    function _getHTML($onlyId = false)
    {
        global $objDatabase, $_ARRAYLANG;

        $html = '';
        if ($onlyId) {
            $objResult = true;
        } else {
            $objResult = $objDatabase->Execute("SELECT id, name FROM ".DBPREFIX."module_newsletter_category WHERE status='1' ORDER BY name");
        }

        if ($objResult !== false) {
            $html .= '<form name="newsletter" action="'.CONTREXX_DIRECTORY_INDEX.'?section=newsletter&amp;act=subscribe" method="post">'."\n";

            if ($onlyId || $objResult->RecordCount() == 1) {
                $html .= '<input type="hidden" name="list['.($onlyId ? $onlyId : $objResult->fields['id']).']" value="1" />'."\n";
            } elseif ($objResult->RecordCount() == 0) {
                $this->_objTpl->setVariable('TXT_NO_CATEGORIES', $_ARRAYLANG['TXT_NO_CATEGORIES']);
            } else {
                while (!$objResult->EOF) {
                    $html .= '<input type="checkbox" name="list['.$objResult->fields['id'].']" id="list_'.$objResult->fields['id'].'" value="1" /> <label for="list_'.$objResult->fields['id'].'">'.htmlentities($objResult->fields['name'], ENT_QUOTES, CONTREXX_CHARSET)."</label><br />\n";
                    $objResult->MoveNext();
                }

                $html .= "<br />";
            }

            $html .= '<input type="text" onfocus="this.value=\'\'" name="email" value="'.$_ARRAYLANG['TXT_NEWSLETTER_EMAIL_ADDRESS'].'" style="width: 165px;" maxlength="255" /><br /><br />'."\n";
            $html .= '<input type="submit" name="recipient_save" value="'.$_ARRAYLANG['TXT_NEWSLETTER_SUBSCRIBE'].'" />'."\n";
            $html .= "</form>\n";
        }

        return $html;
    }

    function _getAssociatedListsOfRecipient($recipientId, $onlyActiveLists = true)
    {
        global $objDatabase;

        $arrLists = array();

        $objList = $objDatabase->Execute("SELECT category FROM ".DBPREFIX."module_newsletter_rel_user_cat".($onlyActiveLists ? ', '.DBPREFIX.'module_newsletter_category' : '').' WHERE user='.$recipientId.($onlyActiveLists ? ' AND status != 0' : ''));
        if ($objList !== false) {
            while (!$objList->EOF) {
                array_push($arrLists, $objList->fields['category']);
                $objList->MoveNext();
            }
        }

        return $arrLists;
    }

    function _getRecipientTitleMenu($selected = 0, $attrs)
    {
        global $_ARRAYLANG;

        $menu = '<select'.(!empty($attrs) ? ' '.$attrs : '').">\n";

        $arrTitles = $this->_getRecipientTitles();

        $menu .= '<option value="0"'.($selected == 0 ? ' selected="selected"' : '').'>'.$_ARRAYLANG['TXT_NEWSLETTER_UNKNOWN']."</option>\n";
        foreach ($arrTitles as $id => $title) {
            $menu .= '<option value="'.$id.'"'.($selected == $id ? ' selected="selected"' : '').'>'.htmlentities($title, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
        }
        $menu .= "</select>\n";

        return $menu;
    }

    function _getRecipientTitles()
    {
        global $objDatabase;

        if (!is_array($this->_arrRecipientTitles)) {
            $this->_initRecipientTitles();
        }

        return $this->_arrRecipientTitles;
    }

    function _addRecipientTitle($title)
    {
        global $objDatabase;

        $recipientTitleId = 0;
        if ($objDatabase->Execute("INSERT INTO `".DBPREFIX."module_newsletter_user_title` (`title`) VALUES ('".addslashes($title)."')") !== false) {
            $recipientTitleId = $objDatabase->Insert_ID();
        }
        $this->_initRecipientTitles();
        return $recipientTitleId;
    }

    function _initRecipientTitles()
    {
        global $objDatabase;

        $this->_arrRecipientTitles = array();

        $objTitle = $objDatabase->Execute("SELECT `id`, `title` FROM `".DBPREFIX."module_newsletter_user_title`");
        if ($objTitle !== false) {
            while (!$objTitle->EOF) {
                $this->_arrRecipientTitles[$objTitle->fields['id']] = $objTitle->fields['title'];
                $objTitle->MoveNext();
            }
        }
    }

    function _deleteRecipient($id)
    {
        global $objDatabase;

        $objUser = $objDatabase->SelectLimit('SELECT `email` FROM `'.DBPREFIX.'module_newsletter_user` WHERE id='.$id, 1);
        if ($objUser !== false && $objUser->RecordCount() == 1) {
            if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_user WHERE id=".$id) !== false) {
                $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_rel_user_cat WHERE user=".$id);
                $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_tmp_sending WHERE `email` = '".addslashes($objUser->fields['email'])."'");
                return true;
            }else{
                return false;
            }
        } else {
            return false;
        }
    }
}

?>
