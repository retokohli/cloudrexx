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
     * Array of categories
     *
     * @var array
     */
    var $_categories = array();

    /**
     * holds the category dropdown select options
     *
     * @var array of strings: HTML <options>
     */
    var $_categoryOptions = array();

    /**
     * array containing the category names
     *
     * @var array catId => name
     */
    var $_categoryNames = array();

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
    function _getBlocks($catId = 0)
    {
        global $objDatabase;

        $catId = intval($catId);
        $WHERE = '';
        if($catId > 0){
            $WHERE = ' WHERE `cat` = '.$catId;
        }

        if (!is_array($this->_arrBlocks)) {
            $objBlock = $objDatabase->Execute("
                SELECT id, cat, name, `order`, random, random_2, random_3, random_4, active, global
                FROM ".DBPREFIX."module_block_blocks
                $WHERE
                ORDER BY `order`");
            if ($objBlock !== false) {
                $this->_arrBlocks = array();
                while (!$objBlock->EOF) {
                    $this->_arrBlocks[$objBlock->fields['id']] = array(
                        'cat'       => $objBlock->fields['cat'],
                        'name'      => $objBlock->fields['name'],
                        'order'     => $objBlock->fields['order'],
                        'status'    => $objBlock->fields['active'],
                        'random'    => $objBlock->fields['random'],
                        'random2'   => $objBlock->fields['random_2'],
                        'random3'   => $objBlock->fields['random_3'],
                        'random4'   => $objBlock->fields['random_4'],
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
    function _addBlock($id, $cat, $content, $name, $blockRandom, $blockRandom2, $blockRandom3, $blockRandom4, $blockGlobal, $blockAssociatedLangIds)
    {
        global $objDatabase;

        $query = "INSERT INTO ".DBPREFIX."module_block_blocks
                         (cat, content, name,
                          random, random_2, random_3, random_4,
                          global, active)
                  VALUES (".intval($cat).", '".contrexx_addslashes($content)."', '".contrexx_addslashes($name)."',
                          ".$blockRandom.", ".$blockRandom2.", ".$blockRandom3.", ".$blockRandom4." ,
                          ".$blockGlobal.", 1)";

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
    function _updateBlock($id, $cat, $content, $name, $blockRandom, $blockRandom2, $blockRandom3, $blockRandom4, $blockGlobal, $blockAssociatedLangIds)
    {
        global $objDatabase;

        if ($objDatabase->Execute("UPDATE ".DBPREFIX."module_block_blocks SET cat=".intval($cat).", content='".contrexx_addslashes($content)."', name='".contrexx_addslashes($name)."', random='".$blockRandom."', random_2='".$blockRandom2."', random_3='".$blockRandom3."', random_4='".$blockRandom4."', global='".$blockGlobal."' WHERE id=".$id) !== false) {
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

        $objBlock = $objDatabase->SelectLimit("SELECT cat, name, random, random_2, random_3, random_4, content, global FROM ".DBPREFIX."module_block_blocks WHERE id=".$id, 1);
        if ($objBlock !== false && $objBlock->RecordCount() == 1) {
            return array(
                'cat'       => $objBlock->fields['cat'],
                'name'      => $objBlock->fields['name'],
                'random'    => $objBlock->fields['random'],
                'random2'   => $objBlock->fields['random_2'],
                'random3'   => $objBlock->fields['random_3'],
                'random4'   => $objBlock->fields['random_4'],
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
        global $objDatabase, $_LANGID;

//      $objDatabase->debug = true;
        /*$objBlock = $objDatabase->SelectLimit("   SELECT  tblBlock.content
                                                FROM    ".DBPREFIX."module_block_blocks AS tblBlock
                                                WHERE   tblBlock.id=".$id."
                                                AND     tblBlock.active=1", 1
                                                );*/

        $query = "  SELECT tblBlock.content, tblLang.lang_id
                    FROM ".DBPREFIX."module_block_blocks AS tblBlock
                    INNER JOIN ".DBPREFIX."module_block_rel_lang AS tblLang
                    ON tblLang.block_id = tblBlock.id
                    WHERE tblBlock.id = ".$id."
                    AND tblBlock.active = 1
                    AND tblLang.lang_id = ".$_LANGID;
        $objRs = $objDatabase->Execute($query);
        if ($objRs !== false) {
            if ($objRs->RecordCount()) {
                $code = str_replace("{".$this->blockNamePrefix.$id."}", $objRs->fields['content'], $code);
            }
        }

        /*
        if ($objBlock !== false) {
            $code = str_replace("{".$this->blockNamePrefix.$id."}", $objBlock->fields['content'], $code);
        }*/
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
            $where  = "  AND tblPage.lang_id=".$_LANGID." AND ((tblPage.block_id=tblBlock.id AND tblPage.page_id=".intval($pageId).") OR tblLang.all_pages='1')";
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
            case '4':
                $objBlockName = $objDatabase->Execute($query."AND tblBlock.random_4=1");
                $blockNr        = "_4";
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
        $objSettings = &new settingsManager();
        $objSettings->writeSettingsFile();
    }

    /**
     * create the categories dropdown
     *
     * @param array $arrCategories
     * @param array $arrOptions
     * @param integer $level
     * @return string categories as HTML options
     */
    function _getCategoriesDropdown($parent = 0, $catId = 0, $arrCategories = array(), $arrOptions = array(), $level = 0)
    {
        global $objDatbase;

        $first = false;
        if(count($arrCategories) == 0){
            $first = true;
            $level = 0;
            $this->_getCategories();
            $arrCategories = $this->_categories[0]; //first array contains all root categories (parent id 0)
        }

        foreach ($arrCategories as $arrCategory) {
            $this->_categoryOptions[] =
                '<option value="'.$arrCategory['id'].'" '
                .(
                  $parent > 0 && $parent == $arrCategory['id']  //selected if parent specified and id is parent
                    ? 'selected="selected"'
                    : ''
                 )
                .(
                  ( $catId > 0 && in_array($arrCategory['id'], $this->_getChildCategories($catId)) ) || $catId == $arrCategory['id'] //disable children and self
                    ? 'disabled="disabled"'
                    : ''
                 )
                .' >' // <option>
                .str_repeat('&nbsp;', $level*4)
                .htmlentities($arrCategory['name'], ENT_QUOTES, CONTREXX_CHARSET)
                .'</option>';

            if(!empty($this->_categories[$arrCategory['id']])){
                $this->_getCategoriesDropdown($parent, $catId, $this->_categories[$arrCategory['id']], $arrOptions, $level+1);
            }
        }
        if($first){
            return implode("\n", $this->_categoryOptions);
        }
    }

    /**
     * save a block category
     *
     * @param integer $id
     * @param integer $parent
     * @param string $name
     * @param integer $order
     * @param integer $status
     * @return integer inserted ID or false on failure
     */
    function _saveCategory($id = 0, $parent = 0, $name, $order = 1, $status = 1)
    {
        global $objDatabase;

        $id = intval($id);
        if($id > 0 && $id == $parent){ //don't allow category to attach to itself
            return false;
        }

        if($id == 0){ //if new record then set to NULL for auto increment
            $id = 'NULL';
        } else {
            $arrChildren = $this->_getChildCategories($id);
            if(in_array($parent, $arrChildren)){ //don't allow category to be attached to one of it's own children
                return false;
            }
        }
        $name = contrexx_addslashes($name);
        if($objDatabase->Execute('
            INSERT INTO `'.DBPREFIX."module_block_categories`
            (`id`, `parent`, `name`, `order`, `status`)
            VALUES
            ($id, $parent, '$name', $order, $status )
            ON DUPLICATE KEY UPDATE
            `id`       = $id,
            `parent`   = $parent,
            `name`     = '$name',
            `order`    = $order,
            `status`   = $status"))
        {
            return $id == 'NULL' ? $objDatabase->Insert_ID() : $id;
        } else {
            return false;
        }
    }

    /**
     * return all child caegories of a cateogory
     *
     * @param integer ID of category to get list of children from
     * @param array cumulates the child arrays, internal use
     * @return array IDs of children
     */
    function _getChildCategories($id, &$_arrChildCategories = array())
    {
        if(empty($this->_categories)){
            $this->_getCategories();
        }
        foreach ($this->_categories[$id] as $cat) {
            if(!empty($this->_categories[$cat['parent']])){
                $_arrChildCategories[] = $cat['id'];
                $this->_getChildCategories($cat['id'], $_arrChildCategories);
            }

        }
        return $_arrChildCategories;
    }

    /**
     * delete a category by id
     *
     * @param integer $id category id
     * @return bool success
     */
    function _deleteCategory($id = 0)
    {
        global $objDatabase;

        $id = intval($id);
        if($id < 1){
            return false;
        }
        return $objDatabase->Execute('DELETE FROM `'.DBPREFIX.'module_block_categories` WHERE `id`='.$id)
            && $objDatabase->Execute('UPDATE `'.DBPREFIX.'module_block_categories` SET `parent` = 0 WHERE `parent`='.$id)
            && $objDatabase->Execute('UPDATE `'.DBPREFIX.'module_block_blocks` SET `cat` = 0 WHERE `cat`='.$id);
    }

    /**
     * fill and/or return the categories array
     *
     * category arrays are put in the array as first dimension elements, with their parent as key, as follows:
     * $this->_categories[$objRS->fields['parent']][] = $objRS->fields;
     *
     * just to make this clear:
     * note that $somearray['somekey'][] = $foo adds $foo to $somearray['somekey'] rather than overwriting it.
     *
     * @param bool force refresh from DB
     * @see blockManager::_parseCategories for parse example
     * @see blockLibrary::_getCategoriesDropdown for parse example
     * @global ADONewConnection
     * @global array
     * @return array all available categories
     */
    function _getCategories($refresh = false)
    {
        global $objDatabase, $_ARRAYLANG;

        if(!empty($this->_categories) && !$refresh){
            return $this->_categories;
        }

        $this->_categories = array();

        $this->_categoryNames[0] = $_ARRAYLANG['TXT_BLOCK_NONE'];
        $objRS = $objDatabase->Execute('
           SELECT `id`,`parent`,`name`,`order`,`status`
           FROM `'.DBPREFIX.'module_block_categories`
           ORDER BY `order` ASC, `id` ASC
        ');
        while(!$objRS->EOF){
            $this->_categories[$objRS->fields['parent']][] = $objRS->fields;
            $this->_categoryNames[$objRS->fields['id']] = $objRS->fields['name'];
            $objRS->MoveNext();
        }
        return $this->_categories;
    }

    /**
     * return the categoriy specified by ID
     *
     * @param integer $id
     * @return array category information
     */
    function _getCategory($id = 0)
    {
        global $objDatabase;

        $id = intval($id);
        if($id == 0){
            return false;
        }

        $objRS = $objDatabase->Execute('
           SELECT `id`,`parent`,`name`,`order`,`status`
           FROM `'.DBPREFIX.'module_block_categories`
           WHERE `id`= '.$id
        );
        if(!$objRS){
            return false;
        }
        return $objRS->fields;
    }
}

?>
