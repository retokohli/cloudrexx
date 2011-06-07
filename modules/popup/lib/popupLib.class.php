<?php

/**
 * Block
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version        1.0.0
 * @package     contrexx
 * @subpackage  module_block
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Block
 *
 * Block library class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @access        private
 * @version        1.0.0
 * @package     contrexx
 * @subpackage  module_block
 */
class popupLibrary
{
    /**
    * Get blocks
    *
    * Get all blocks
    *
    * @access private
    * @global object $objDatabase
    * @see array blockLibrary::_arrBlocks
    * @return array Array with block ids
    */
    function _getPopups()
    {
        global $objDatabase;

        $objPopup = $objDatabase->Execute("SELECT id, name, `type`, start, end, active FROM ".DBPREFIX."module_popup ORDER BY start DESC");
        if ($objPopup !== false) {
            $arrPopups = array();
            while (!$objPopup->EOF) {
                $arrPopups[$objPopup->fields['id']] = array(
                    'name'        => $objPopup->fields['name'],
                    'type'        => $objPopup->fields['type'],
                    'status'    => $objPopup->fields['active'],
                    'start'        => $objPopup->fields['start'],
                    'end'        => $objPopup->fields['end'],
                );
                $objPopup->MoveNext();
            }
        }

        return $arrPopups;
    }

    /**
    * Get settings
    *
    * Get all settings
    *
    * @access private
    * @global object $objDatabase
    * @return array Array with settings
    */
    function _getSettings()
    {
        global $objDatabase;

        $objSettings = $objDatabase->Execute("SELECT id, name, value FROM ".DBPREFIX."module_popup_settings");
        if ($objSettings !== false) {
            $arrSettings = array();
            while (!$objSettings->EOF) {
                $arrSettings[$objSettings->fields['name']] = $objSettings->fields['value'];
                $objSettings->MoveNext();
            }
            return $arrSettings;
        }
        return false;
    }

    /**
    * add block
    *
    * add the new content of a block
    *
    * @access private
    * @param integer $id
    * @param string $content
    * @global object $objDatabase
    * @return boolean true on success, false on failure
    */
    function _addPopup($id, $content, $name, $type, $scrollbars, $status, $menu, $adress, $resize, $width, $height, $top, $left, $start, $end, $popupAssociatedLangIds)
    {
        global $objDatabase;

        $arrSettings = $this->_getSettings();

        $name     = $name=="" ? "no_name" : contrexx_addslashes($name);
        //$name     = str_replace(" ", "_", $name);
        $width     = $width=="" ? intval($arrSettings['default_width']) : intval($width);
        $height = $height=="" ? intval($arrSettings['default_height']) : intval($height);

        $query =   "INSERT INTO ".DBPREFIX."module_popup (    `name`,
                                                            `content`,
                                                            `type`,
                                                            `width`,
                                                            `height`,
                                                            `top`,
                                                            `left`,
                                                            `scrollbars`,
                                                            `adress_list`,
                                                            `menu_list`,
                                                            `status_list`,
                                                            `resizeable`,
                                                            `start`,
                                                            `end`,
                                                            `active`)
                     VALUES ('".$name."',
                             '".contrexx_addslashes($content)."',
                             '".intval($type)."',
                             '".$width."',
                             '".$height."',
                             '".intval($top)."',
                             '".intval($left)."',
                             '".intval($scrollbars)."',
                             '".intval($adress)."',
                             '".intval($menu)."',
                             '".intval($status)."',
                             '".intval($resize)."',
                             '".contrexx_addslashes($start)."',
                             '".contrexx_addslashes($end)."',
                             '0')";

        if ($objDatabase->Execute($query) !== false) {

            foreach ($popupAssociatedLangIds as $key => $langId) {

                $arrSelectedPages         = $_POST[$langId.'_selectedPages'];
                $popupId                 = $objDatabase->Insert_ID();
                $showOnAllPages            = $_POST['popup_show_on_all_pages'][$langId];

                if ($showOnAllPages != 1) {
                    foreach ($arrSelectedPages as $key => $pageId) {
                        $objDatabase->Execute('    INSERT
                                                  INTO    '.DBPREFIX.'module_popup_rel_pages
                                                   SET    popup_id='.$popupId.', page_id='.$pageId.', lang_id='.$langId.'
                                        ');
                    }

                    $objDatabase->Execute('    INSERT
                                              INTO    '.DBPREFIX.'module_popup_rel_lang
                                               SET    popup_id='.$popupId.', lang_id='.$langId.', all_pages=0
                                        ');
                } else {
                    $objDatabase->Execute('    INSERT
                                              INTO    '.DBPREFIX.'module_popup_rel_lang
                                               SET    popup_id='.$popupId.', lang_id='.$langId.', all_pages=1
                                        ');
                }
            }
            return true;
        } else {
            return false;
        }
    }


    /**
    * update block
    *
    * Update the content of a block
    *
    * @access private
    * @param integer $id
    * @param string $content
    * @global object $objDatabase
    * @return boolean true on success, false on failure
    */
    function _updatePopup($id, $content, $name, $type, $scrollbars, $status, $menu, $adress, $resize, $width, $height, $top, $left, $start, $end, $popupAssociatedLangIds)
    {
        global $objDatabase;

        $name     = $name=="" ? "no_name" : contrexx_addslashes($name);
        //$name     = str_replace(" ", "_", $name);
        $width     = $width=="" ? intval($arrSettings['default_width']) : intval($width);
        $height = $height=="" ? intval($arrSettings['default_height']) : intval($height);

        if ($objDatabase->Execute("UPDATE ".DBPREFIX."module_popup SET     name='".$name."',
                                                                        content='".contrexx_addslashes($content)."',
                                                                        type='".intval($type)."',
                                                                        scrollbars='".intval($scrollbars)."',
                                                                        status_list='".intval($status)."',
                                                                        menu_list='".intval($menu)."',
                                                                        adress_list='".intval($adress)."',
                                                                        resizeable='".intval($resize)."',
                                                                        width='".$width."',
                                                                        height='".$height."',
                                                                        top='".intval($top)."',
                                                                        `left`='".intval($left)."',
                                                                        `start`='".contrexx_addslashes($start)."',
                                                                        `end`='".contrexx_addslashes($end)."' WHERE id=".$id) !== false) {

            if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_popup_rel_pages WHERE popup_id=".$id) !== false) {
                if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_popup_rel_lang WHERE popup_id=".$id) !== false) {
                    foreach ($popupAssociatedLangIds as $key => $langId) {
                        $arrSelectedPages         = $_POST[$langId.'_selectedPages'];
                        $popupId                 = $id;
                        $showOnAllPages            = $_POST['popup_show_on_all_pages'][$langId];

                        if ($showOnAllPages != 1) {
                            foreach ($arrSelectedPages as $key => $pageId) {
                                $objDatabase->Execute('    INSERT
                                                          INTO    '.DBPREFIX.'module_popup_rel_pages
                                                           SET    popup_id='.$popupId.', page_id='.$pageId.', lang_id='.$langId.'
                                                ');
                            }

                            $objDatabase->Execute('    INSERT
                                                      INTO    '.DBPREFIX.'module_popup_rel_lang
                                                       SET    popup_id='.$popupId.', lang_id='.$langId.', all_pages=0
                                                ');
                        } else {
                            $objDatabase->Execute('    INSERT
                                                      INTO    '.DBPREFIX.'module_popup_rel_lang
                                                       SET    popup_id='.$blockId.', lang_id='.$langId.', all_pages=1
                                                ');
                        }
                    }
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set block lang ids
     *
     * Set the languages associated to a block
     *
     * @param integer $blockId
     * @param array $arrLangIds
     * @return boolean
     */
    function _setBlockLangIds($blockId, $arrLangIds)
    {
        global $objDatabase;

        $arrCurrentLangIds = array();

        $objLang = $objDatabase->Execute("SELECT lang_id FROM ".DBPREFIX."module_block_rel_lang WHERE block_id=".$blockId);
        if ($objLang !== false) {
            while (!$objLang->EOF) {
                array_push($arrCurrentLangIds, $objLang->fields['lang_id']);
                $objLang->MoveNext();
            }

            $arrAddedLangIds = array_diff($arrLangIds, $arrCurrentLangIds);
            $arrRemovedLangIds = array_diff($arrCurrentLangIds, $arrLangIds);

            foreach ($arrAddedLangIds as $langId) {
                $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_block_rel_lang (`block_id`, `lang_id`) VALUES (".$blockId.", ".$langId.")");
            }

            foreach ($arrRemovedLangIds as $langId) {
                $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_block_rel_lang WHERE block_id=".$blockId." AND lang_id=".$langId);
            }

            return true;
        } else {
            return false;
        }
    }

    /**
    * Get block
    *
    * Return a block
    *
    * @access private
    * @param integer $id
    * @global object $objDatabase
    * @return mixed content on success, false on failure
    */
    function _getPopup($id)
    {
        global $objDatabase;

        $objPopup = $objDatabase->SelectLimit("SELECT     `name`,
                                                        `content`,
                                                        `type`,
                                                        `width`,
                                                        `height`,
                                                        `top`,
                                                        `left`,
                                                        `scrollbars`,
                                                        `adress_list`,
                                                        `menu_list`,
                                                        `status_list`,
                                                        `resizeable`,
                                                        `start`,
                                                        `end`,
                                                        `active` FROM ".DBPREFIX."module_popup WHERE id=".$id, 1);
        if ($objPopup !== false && $objPopup->RecordCount() == 1) {
            return array(
                'name'                => $objPopup->fields['name'],
                'content'            => $objPopup->fields['content'],
                'type'                => $objPopup->fields['type'],
                'width'                => $objPopup->fields['width'],
                'height'                => $objPopup->fields['height'],
                'top'                => $objPopup->fields['top'],
                'left'                => $objPopup->fields['left'],
                'scrollbars'        => $objPopup->fields['scrollbars'],
                'adress_list'        => $objPopup->fields['adress_list'],
                'menu_list'            => $objPopup->fields['menu_list'],
                'status_list'        => $objPopup->fields['status_list'],
                'resizeable'        => $objPopup->fields['resizeable'],
                'start'                => $objPopup->fields['start'],
                'end'                => $objPopup->fields['end'],
                'active'            => $objPopup->fields['active'],
            );
        } else {
            return false;
        }
    }

    function _getAssociatedLangIds($popupId)
    {
        global $objDatabase;

        $arrLangIds = array();
        $objResult = $objDatabase->Execute("SELECT lang_id FROM ".DBPREFIX."module_popup_rel_lang WHERE popup_id=".$popupId);
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                array_push($arrLangIds, $objResult->fields['lang_id']);
                $objResult->MoveNext();
            }
        }
        return $arrLangIds;
    }

    /**
    * Set block
    *
    * Parse the block with the id $id
    *
    * @access private
    * @param integer $id
    * @param string &$code
    * @global object $objDatabase
    */
    function _setBlock($id, &$code)
    {
        global $objDatabase, $_LANGID;

        $objBlock = $objDatabase->SelectLimit("    SELECT     tblBlock.content
                                                FROM     ".DBPREFIX."module_block_blocks AS tblBlock
                                                WHERE     tblBlock.id=".$id."
                                                AND     tblBlock.active=1", 1
                                                );
        if ($objBlock !== false) {
            $code = str_replace("{".$this->blockNamePrefix.$id."}", $objBlock->fields['content'], $code);
        }
    }

    /**
    * Set block Global
    *
    * Parse the block with the id $id
    *
    * @access private
    * @param integer $id
    * @param string &$code
    * @global object $objDatabase
    */
    function _setBlockGlobal(&$code, $pageId)
    {
        global $objDatabase, $_LANGID;

        $objResult = $objDatabase->Execute("SELECT    value
                                            FROM    ".DBPREFIX."module_block_settings
                                            WHERE    name='blockGlobalSeperator'
                                            ");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $seperator    = $objResult->fields['value'];
                $objResult->MoveNext();
            }
        }

        $query = "SELECT block_id FROM ".DBPREFIX."module_block_rel_pages WHERE block_id!=''";
        $objCheck = $objDatabase->SelectLimit($query, 1);

        if ($objCheck->RecordCount() == 0) {
            $tables = DBPREFIX."module_block_rel_lang AS tblLang";
            $where    = "";
        } else {
            $tables = DBPREFIX."module_block_rel_lang AS tblLang,
                    ".DBPREFIX."module_block_rel_pages AS tblPage";
            $where    = "AND     ((tblPage.page_id=".intval($pageId)." AND tblPage.block_id=tblBlock.id) OR tblLang.all_pages='1')";
        }

        $objBlock = $objDatabase->Execute("    SELECT     tblBlock.id, tblBlock.content
                                                FROM     ".DBPREFIX."module_block_blocks AS tblBlock,
                                                        ".$tables."
                                                WHERE     (tblLang.lang_id=".$_LANGID." AND tblLang.block_id=tblBlock.id)
                                                        ".$where."
                                                AND     tblBlock.active=1
                                                GROUP     BY tblBlock.id
                                                ORDER BY `order`
                                                ");
        if ($objBlock !== false) {
            while (!$objBlock->EOF) {
                $block .= $objBlock->fields['content'].$seperator;
                $objBlock->MoveNext();
            }
        }

        $code = str_replace("{".$this->blockNamePrefix."GLOBAL}", $block, $code);
    }

    /**
    * Set block Random
    *
    * Parse the block with the id $id
    *
    * @access private
    * @param integer $id
    * @param string &$code
    * @global object $objDatabase
    */
    function _setBlockRandom(&$code)
    {
        global $objDatabase, $_LANGID;

        //Get Block Name and Status
        $objBlockName = $objDatabase->Execute("SELECT tblBlock.id FROM ".DBPREFIX."module_block_blocks AS tblBlock WHERE tblBlock.active=1 AND tblBlock.random=1");
        if ($objBlockName !== false && $objBlockName->RecordCount() > 0) {
            while (!$objBlockName->EOF) {
                $arrActiveBlocks[] = $objBlockName->fields['id'];
                $objBlockName->MoveNext();
            }

            $ranId = $arrActiveBlocks[@array_rand($arrActiveBlocks, 1)];

            $objBlock = $objDatabase->SelectLimit("SELECT content FROM ".DBPREFIX."module_block_blocks WHERE id=".$ranId." AND active =1", 1);
            if ($objBlock !== false) {
                $code = str_replace("{".$this->blockNamePrefix."RANDOMIZER}", $objBlock->fields['content'], $code);
                return true;
            }
        }

        return false;
    }

    /**
    * Save the settings associated to the block system
    *
    * @access    private
    * @param    array     $arrSettings
    * @global    object     $objDatabase
    */
    function _saveSettings($arrSettings)
    {
        global $objDatabase, $_CONFIG;

        if (isset($arrSettings['blockStatus'])) {
            $_CONFIG['blockStatus'] = (string) $arrSettings['blockStatus'];
            $query = "UPDATE ".DBPREFIX."settings SET setvalue='".$arrSettings['blockStatus']."' WHERE setname='blockStatus'";
            $objDatabase->Execute($query);
        }

        if (isset($arrSettings['blockRandom'])) {
            $_CONFIG['blockRandom'] = (string) $arrSettings['blockRandom'];
            $query = "UPDATE ".DBPREFIX."settings SET setvalue='".$arrSettings['blockRandom']."' WHERE setname='blockRandom'";
            $objDatabase->Execute($query);
        }

        require_once(ASCMS_CORE_PATH.'/settings.class.php');
        $objSettings = new settingsManager();
        $objSettings->writeSettingsFile();
    }
}

?>
