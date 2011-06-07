<?php

/**
 * Block
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_block
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Block
 *
 * Block library class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      private
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_block
 */
class blockLibrary
{
    /**
    * Block name prefix
    *
    * @access public
    * @var string
    */
    var $blockNamePrefix = 'BLOCK_';

    /**
    * Block ids
    *
    * @access private
    * @var array
    */
    var $_arrBlocks;


    /**
     * Constructor
     */
    function __construct()
    {
    }


    /**
    * Get blocks
    *
    * Get all blocks
    *
    * @access private
    * @global ADONewConnection
    * @see array blockLibrary::_arrBlocks
    * @return array Array with block ids
    */
    function _getBlocks()
    {
        global $objDatabase;

        if (!is_array($this->_arrBlocks)) {
            $objBlock = $objDatabase->Execute("SELECT id, name, `order`, random, random_2, random_3, active, global FROM ".DBPREFIX."module_block_blocks ORDER BY `order`");
            if ($objBlock !== false) {
                $this->_arrBlocks = array();
                while (!$objBlock->EOF) {
                    $this->_arrBlocks[$objBlock->fields['id']] = array(
                        'name'      => $objBlock->fields['name'],
                        'order'     => $objBlock->fields['order'],
                        'status'    => $objBlock->fields['active'],
                        'random'    => $objBlock->fields['random'],
                        'random2'   => $objBlock->fields['random_2'],
                        'random3'   => $objBlock->fields['random_3'],
                        'global'    => $objBlock->fields['global']
                    );
                    $objBlock->MoveNext();
                }
            }
        }

        return $this->_arrBlocks;
    }

    /**
    * add block
    *
    * add the new content of a block
    *
    * @access private
    * @param integer $id
    * @param string $content
    * @global ADONewConnection
    * @return boolean true on success, false on failure
    */
    function _addBlock($id, $content, $name, $blockRandom, $blockRandom2, $blockRandom3, $blockGlobal, $blockAssociatedLangIds)
    {
        global $objDatabase;

        $query = "INSERT INTO ".DBPREFIX."module_block_blocks (content, name, random, random_2, random_3, global, active) VALUES ('".contrexx_addslashes($content)."', '".contrexx_addslashes($name)."', ".$blockRandom.", ".$blockRandom2.", ".$blockRandom3.", ".$blockGlobal.", 1)";

        if ($objDatabase->Execute($query) !== false) {

            foreach ($blockAssociatedLangIds as $langId => $value) {
                if($value == 1) {
                    $arrSelectedPages       = $_POST[$langId.'_selectedPages'];
                    $blockId                = $objDatabase->Insert_ID();
                    $showOnAllPages         = $_POST['block_show_on_all_pages'][$langId];

                    if ($showOnAllPages != 1) {
                        foreach ($arrSelectedPages as $key => $pageId) {
                            $objDatabase->Execute(' INSERT
                                                      INTO  '.DBPREFIX.'module_block_rel_pages
                                                       SET  block_id='.$blockId.', page_id='.$pageId.', lang_id='.$langId.'
                                            ');
                        }

                        $objDatabase->Execute(' INSERT
                                                  INTO  '.DBPREFIX.'module_block_rel_lang
                                                   SET  block_id='.$blockId.', lang_id='.$langId.', all_pages=0
                                            ');
                    } else {
                        $objDatabase->Execute(' INSERT
                                                  INTO  '.DBPREFIX.'module_block_rel_lang
                                                   SET  block_id='.$blockId.', lang_id='.$langId.', all_pages=1
                                            ');
                    }
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
    * @global ADONewConnection
    * @return boolean true on success, false on failure
    */
    function _updateBlock($id, $content, $name, $blockRandom, $blockRandom2, $blockRandom3, $blockGlobal, $blockAssociatedLangIds)
    {
        global $objDatabase;

        if ($objDatabase->Execute("UPDATE ".DBPREFIX."module_block_blocks SET content='".contrexx_addslashes($content)."', name='".contrexx_addslashes($name)."', random='".$blockRandom."', random_2='".$blockRandom2."', random_3='".$blockRandom3."', global='".$blockGlobal."' WHERE id=".$id) !== false) {
            if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_block_rel_pages WHERE block_id=".$id) !== false) {
                if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_block_rel_lang WHERE block_id=".$id) !== false) {



                    foreach ($blockAssociatedLangIds as $langId => $value) {
                        if($value == 1) {
                            $arrSelectedPages       = $_POST[$langId.'_selectedPages'];
                            $blockId                = $id;
                            $showOnAllPages         = $_POST['block_show_on_all_pages'][$langId];

                            if ($showOnAllPages != 1) {
                                foreach ($arrSelectedPages as $key => $pageId) {
                                    $objDatabase->Execute(' INSERT
                                                              INTO  '.DBPREFIX.'module_block_rel_pages
                                                               SET  block_id='.$blockId.', page_id='.$pageId.', lang_id='.$langId.'
                                                    ');
                                }

                                $objDatabase->Execute(' INSERT
                                                          INTO  '.DBPREFIX.'module_block_rel_lang
                                                           SET  block_id='.$blockId.', lang_id='.$langId.', all_pages=0
                                                    ');
                            } else {
                                $objDatabase->Execute(' INSERT
                                                          INTO  '.DBPREFIX.'module_block_rel_lang
                                                           SET  block_id='.$blockId.', lang_id='.$langId.', all_pages=1
                                                    ');
                            }
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
    * @global ADONewConnection
    * @return mixed content on success, false on failure
    */
    function _getBlock($id)
    {
        global $objDatabase;

        $objBlock = $objDatabase->SelectLimit("SELECT name, random, random_2, random_3, content, global FROM ".DBPREFIX."module_block_blocks WHERE id=".$id, 1);
        if ($objBlock !== false && $objBlock->RecordCount() == 1) {
            return array(
                'name'      => $objBlock->fields['name'],
                'random'    => $objBlock->fields['random'],
                'random2'   => $objBlock->fields['random_2'],
                'random3'   => $objBlock->fields['random_3'],
                'content'   => $objBlock->fields['content'],
                'global'    => $objBlock->fields['global']
            );
        } else {
            return false;
        }
    }

    function _getAssociatedLangIds($blockId)
    {
        global $objDatabase;

        $arrLangIds = array();
        $objResult = $objDatabase->Execute("SELECT lang_id FROM ".DBPREFIX."module_block_rel_lang WHERE block_id=".$blockId);
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
    * @global ADONewConnection
    * @global integer
    */

    function _setBlock($id, &$code)
    {
        global $objDatabase, $_LANGID, $pageId;

        $qStr = "
            SELECT     tblBlock.`content`  AS  content,
                       tblPages.`page_id`  AS  page_id
            FROM       `" . DBPREFIX . "module_block_blocks`     AS  tblBlock,
                       `" . DBPREFIX . "module_block_rel_lang`   AS  tblLang
            LEFT JOIN  `" . DBPREFIX . "module_block_rel_pages`  AS  tblPages
            ON         tblPages.`block_id`  =  '" . $id . "'
            AND        tblPages.`lang_id`   =  '" . $_LANGID . "'

            WHERE      tblBlock.`id`        =  '" . $id . "'
            AND        tblBlock.`active`    =  '1'

            AND        tblLang.`lang_id`    =  '" . $_LANGID . "'
            AND        tblLang.`block_id`   =  '" . $id . "'
        ";
        $q = $objDatabase->query($qStr);

        if ($q->numRows() > 0) {
            while ($f = $q->fetchRow()) {
                if ($f['page_id'] == NULL || $f['page_id'] == $pageId) {
                    $code = str_replace('{' . $this->blockNamePrefix . $id . '}', $f['content'], $code);
                }
            }
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
    * @global ADONewConnection
    * @global integer
    */
    function _setBlockGlobal(&$code, $pageId)
    {
        global $objDatabase, $_LANGID;

        $objResult = $objDatabase->Execute("SELECT  value
                                            FROM    ".DBPREFIX."module_block_settings
                                            WHERE   name='blockGlobalSeperator'
                                            LIMIT   1
                                            ");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $seperator  = $objResult->fields['value'];
                $objResult->MoveNext();
            }
        }

        $query = "SELECT block_id FROM ".DBPREFIX."module_block_rel_pages WHERE block_id !=''";
        $objCheck = $objDatabase->SelectLimit($query, 1);

        if ($objCheck->RecordCount() == 0) {
            $tables = DBPREFIX."module_block_rel_lang AS tblLang";
            $where  = " AND tblLang.all_pages='1'";
        } else {
            $tables = DBPREFIX."module_block_rel_lang AS tblLang,
                    ".DBPREFIX."module_block_rel_pages AS tblPage";
            $where  = "AND  ((tblPage.page_id=".intval($pageId)." AND tblPage.block_id=tblBlock.id) OR tblLang.all_pages='1')";
        }

        $objBlock = $objDatabase->Execute(" SELECT  tblBlock.id, tblBlock.content
                                                FROM    ".DBPREFIX."module_block_blocks AS tblBlock,
                                                        ".$tables."
                                                WHERE   (tblLang.lang_id=".$_LANGID." AND tblLang.block_id=tblBlock.id)
                                                        ".$where."
                                                AND     tblBlock.active=1
                                                GROUP   BY tblBlock.id
                                                ORDER BY `order`
                                                ");
        $block = '';
        if ($objBlock !== false) {
            while (!$objBlock->EOF) {
                $block .= $objBlock->fields['content'].$seperator;
                $objBlock->MoveNext();
            }
            $code = str_replace("{".$this->blockNamePrefix."GLOBAL}", $block, $code);
        }
    }

    /**
    * Set block Random
    *
    * Parse the block with the id $id
    *
    * @access private
    * @param integer $id
    * @param string &$code
    * @global ADONewConnection
    * @global integer
    */
    function _setBlockRandom(&$code, $id)
    {
        global $objDatabase, $_LANGID;

        $query = "  SELECT tblBlock.id
                    FROM ".DBPREFIX."module_block_blocks AS tblBlock
                    INNER JOIN ".DBPREFIX."module_block_rel_lang AS tblLang
                    ON tblLang.block_id = tblBlock.id
                    WHERE tblBlock.active= 1
                    AND tblLang.lang_id = ".$_LANGID." ";

        //Get Block Name and Status
        switch($id) {
            case '1':
                $objBlockName   = $objDatabase->Execute($query."AND tblBlock.random=1");
                $blockNr        = "";
                break;
            case '2':
                $objBlockName   = $objDatabase->Execute($query."AND tblBlock.random_2=1");
                $blockNr        = "_2";
                break;
            case '3':
                $objBlockName = $objDatabase->Execute($query."AND tblBlock.random_3=1");
                $blockNr        = "_3";
                break;
        }


        if ($objBlockName !== false && $objBlockName->RecordCount() > 0) {
            while (!$objBlockName->EOF) {
                $arrActiveBlocks[] = $objBlockName->fields['id'];
                $objBlockName->MoveNext();
            }

            $ranId = $arrActiveBlocks[@array_rand($arrActiveBlocks, 1)];

            $objBlock = $objDatabase->SelectLimit("SELECT content FROM ".DBPREFIX."module_block_blocks WHERE id=".$ranId." AND active =1", 1);
            if ($objBlock !== false) {
                $code = str_replace("{".$this->blockNamePrefix."RANDOMIZER".$blockNr."}", $objBlock->fields['content'], $code);
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
    * @global   ADONewConnection
    * @global   integer
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
