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
        global $objDatabase, $_LANGID;

        $catId = intval($catId);
        $arrWhere = array();

        if($catId > 0){
            $arrWhere[] = 'tblBlock.`cat` = '.$catId;
        }

        $arrWhere[] = '((tblContent.block_id = tblBlock.id) AND (tblContent.lang_id = '.$_LANGID.'))';

        if (!is_array($this->_arrBlocks)) {
            $objDatabase->debug=0;

            $objBlock = $objDatabase->Execute("
                SELECT
                    tblBlock.id AS blockId,
                    tblBlock.cat AS blockCat,
                    tblBlock.start AS blockStart,
                    tblBlock.end AS blockEnd,
                    tblBlock.`order` AS blockOrder,
                    tblBlock.random AS blockRandom,
                    tblBlock.random_2 AS blockRandom2,
                    tblBlock.random_3 AS blockRandom3,
                    tblBlock.random_4 AS blockRandom4,
                    tblBlock.global AS blockGlobal,
                    tblBlock.active AS blockActive,
                    tblContent.name AS blockName,
                    tblContent.content AS blockContent
                FROM
                    ".DBPREFIX."module_block_blocks AS tblBlock,
                    ".DBPREFIX."module_block_rel_lang_content AS tblContent
                WHERE
                    ".join(" AND ", $arrWhere)."
                ORDER BY tblBlock.`order`"
            );

            $objDatabase->debug=0;

            if ($objBlock !== false) {
                $this->_arrBlocks = array();
                while (!$objBlock->EOF) {
                    $this->_arrBlocks[$objBlock->fields['blockId']] = array(
                        'cat'       => $objBlock->fields['blockCat'],
                        'start'     => $objBlock->fields['blockStart'],
                        'end'       => $objBlock->fields['blockEnd'],
                        'order'     => $objBlock->fields['blockOrder'],
                        'random'    => $objBlock->fields['blockRandom'],
                        'random2'   => $objBlock->fields['blockRandom2'],
                        'random3'   => $objBlock->fields['blockRandom3'],
                        'random4'   => $objBlock->fields['blockRandom4'],
                        'global'    => $objBlock->fields['blockGlobal'],
                        'active'    => $objBlock->fields['blockActive'],
                        'name'      => $objBlock->fields['blockName'],
                        'content'   => $objBlock->fields['blockContent']
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
    function _addBlock($id, $cat, $arrContent, $arrName, $start, $end, $blockRandom, $blockRandom2, $blockRandom3, $blockRandom4, $blockGlobal, $blockAssociatedPageIds, $arrLangActive)
    {
        global $objDatabase, $_LANGID;

        $query = "INSERT INTO ".DBPREFIX."module_block_blocks
                         (cat, start, end,
                          random, random_2, random_3, random_4,
                          global, active)
                  VALUES (".intval($cat).", $start, $end,
                          ".$blockRandom.", ".$blockRandom2.", ".$blockRandom3.", ".$blockRandom4." ,
                          ".$blockGlobal.", 1)";

        if ($objDatabase->Execute($query) !== false) {
            $blockId = $objDatabase->Insert_ID();

            foreach ($arrContent as $langId => $content) {
                $content = preg_replace('/\[\[([A-Z0-9_-]+)\]\]/', '{\\1}', $content);
                $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_block_rel_lang_content SET
                    block_id='".$blockId."',
                    lang_id='".intval($langId)."',
                    name='".contrexx_addslashes($arrName)."',
                    content='".contrexx_addslashes($content)."',
                    active='".intval($arrLangActive[$langId])."'
                ");
            }

            foreach ($blockAssociatedPageIds as $key => $pageId) {
                if($blockGlobal == 2) {
                    $objDatabase->Execute('INSERT INTO  '.DBPREFIX.'module_block_rel_pages SET  block_id='.$blockId.', page_id='.intval($pageId).'');
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
    function _updateBlock($id, $cat, $arrContent, $arrName, $start, $end, $blockRandom, $blockRandom2, $blockRandom3, $blockRandom4, $blockGlobal, $blockAssociatedPageIds, $arrLangActive)
    {
        global $objDatabase;

        if ($objDatabase->Execute("UPDATE ".DBPREFIX."module_block_blocks SET cat=".intval($cat).", start=".intval($start).", end=".intval($end).", random='".$blockRandom."', random_2='".$blockRandom2."', random_3='".$blockRandom3."', random_4='".$blockRandom4."', global='".$blockGlobal."' WHERE id=".$id) !== false) {
            if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_block_rel_pages WHERE block_id=".$id) !== false) {
                if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_block_rel_lang_content WHERE block_id=".$id) !== false) {
                    foreach ($arrContent as $langId => $content) {
                        $content = preg_replace('/\[\[([A-Z0-9_-]+)\]\]/', '{\\1}', $content);
                        $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_block_rel_lang_content SET
                            block_id='".intval($id)."',
                            lang_id='".intval($langId)."',
                            name='".contrexx_addslashes($arrName)."',
                            content='".contrexx_addslashes($content)."',
                            active='".intval($arrLangActive[$langId])."'
                        ");
                    }

                    foreach ($blockAssociatedPageIds as $key => $pageId) {
                        if($blockGlobal == 2) {
                            $objDatabase->Execute('INSERT INTO  '.DBPREFIX.'module_block_rel_pages SET  block_id='.intval($id).', page_id='.intval($pageId).'');
                        }
                    }
                    return true;
                }
            }
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
    /*function _setBlockLangIds($blockId, $arrLangIds)
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
    }*/

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

        $objBlock = $objDatabase->SelectLimit("SELECT cat, start, end, random, random_2, random_3, random_4, global, active FROM ".DBPREFIX."module_block_blocks WHERE id=".$id, 1);


        if ($objBlock !== false && $objBlock->RecordCount() == 1) {
            $arrName = array();
            $arrContent = array();
            $arrActive = array();

            $objBlockContent = $objDatabase->Execute("SELECT lang_id, content, name, active FROM ".DBPREFIX."module_block_rel_lang_content WHERE block_id=".$id);
            if ($objBlockContent !== false) {
                while (!$objBlockContent->EOF) {
                    $arrName[$objBlockContent->fields['lang_id']] = $objBlockContent->fields['name'];
                    $arrContent[$objBlockContent->fields['lang_id']] = $objBlockContent->fields['content'];
                    $arrActive[$objBlockContent->fields['lang_id']] = $objBlockContent->fields['active'];
                    $objBlockContent->MoveNext();
                }
            }

            return array(
                'cat'           => $objBlock->fields['cat'],
                'start'         => $objBlock->fields['start'],
                'end'           => $objBlock->fields['end'],
                'random'        => $objBlock->fields['random'],
                'random2'       => $objBlock->fields['random_2'],
                'random3'       => $objBlock->fields['random_3'],
                'random4'       => $objBlock->fields['random_4'],
                'content'       => $objBlock->fields['content'],
                'global'        => $objBlock->fields['global'],
                'active'        => $objBlock->fields['active'],
                'name'          => $arrName,
                'content'       => $arrContent,
                'lang_active'   => $arrActive,
            );
        } else {
            return false;
        }
    }




    function _getAssociatedPageIds($blockId)
    {
        global $objDatabase;

        $arrPageIds = array();
        $objResult = $objDatabase->Execute("SELECT page_id FROM ".DBPREFIX."module_block_rel_pages WHERE block_id=".$blockId);
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                array_push($arrPageIds, $objResult->fields['page_id']);
                $objResult->MoveNext();
            }
        }
        return $arrPageIds;
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

        $now = time();
        $query = "  SELECT
                        tblContent.content
                    FROM
                        ".DBPREFIX."module_block_blocks AS tblBlock,
                        ".DBPREFIX."module_block_rel_lang_content AS tblContent
                    WHERE
                        tblBlock.id = ".intval($id)."
                    AND
                        tblContent.block_id = tblBlock.id
                    AND
                        (tblContent.lang_id = ".intval($_LANGID)." AND tblContent.active = 1)
                    AND
                        (".$now." BETWEEN `tblBlock`.`start` AND `tblBlock`.`end` )
                    AND
                        tblBlock.active = 1";

        $objRs = $objDatabase->Execute($query);

        if ($objRs !== false) {
            if ($objRs->RecordCount()) {
                $code = str_replace("{".$this->blockNamePrefix.$id."}", $objRs->fields['content'], $code);
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
            $seperator  = $objResult->fields['value'];
        }

        $now = time();
        $query = "SELECT
                    tblBlock.`id` AS `id`,
                    tblContent.`content` AS `content`
                FROM
                    ".DBPREFIX."module_block_rel_lang_content AS tblContent,
                    ".DBPREFIX."module_block_blocks AS tblBlock
                LEFT JOIN
                    ".DBPREFIX."module_block_rel_pages AS tblPage
                ON
                    tblBlock.`id` = tblPage.`block_id`
                WHERE
                    (tblPage.page_id = ".intval($pageId).")
                AND
                    (tblBlock.`id` = tblContent.`block_id`)
                AND
                    (tblContent.`lang_id` = ".intval($_LANGID).")
                AND
                    (tblContent.`active` = 1)
                AND
                    (".$now." BETWEEN `tblBlock`.`start` AND `tblBlock`.`end`)
                AND
                    (tblBlock.active=1)
                ORDER BY
                    tblBlock.`order`
                ";

        $objBlock = $objDatabase->Execute($query);
        $block = '';
        if ($objBlock !== false) {
            while (!$objBlock->EOF) {
                $block .= $objBlock->fields['content'].$seperator;
                $objBlock->MoveNext();
            }
        }

        $queryAllPages ="SELECT
                    tblBlock.`id` AS `id`,
                    tblContent.`content` AS `content`
                FROM
                    ".DBPREFIX."module_block_blocks AS tblBlock
                LEFT JOIN
                    ".DBPREFIX."module_block_rel_lang_content AS tblContent
                ON
                    tblBlock.`id` = tblContent.`block_id`
                WHERE
                    (tblContent.`lang_id` = ".intval($_LANGID).")
                AND
                    (tblContent.`active` = 1)
                AND
                    (tblBlock.`global` = 1)
                AND
                    (".$now." BETWEEN `tblBlock`.`start` AND `tblBlock`.`end`)
                AND
                    (tblBlock.active=1)
                ORDER BY
                    tblBlock.`order`
                ";

        $objBlockAllPages = $objDatabase->Execute($queryAllPages);
        if ($objBlockAllPages !== false) {
            while (!$objBlockAllPages->EOF) {
                $block .= $objBlockAllPages->fields['content'].$seperator;
                $objBlockAllPages->MoveNext();
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
    * @global ADONewConnection
    * @global integer
    */
    function _setBlockRandom(&$code, $id)
    {
        global $objDatabase, $_LANGID;

        $now = time();
        $query = "  SELECT
                        tblBlock.id
                    FROM
                        ".DBPREFIX."module_block_blocks AS tblBlock,
                        ".DBPREFIX."module_block_rel_lang_content AS tblContent
                    WHERE
                        tblContent.block_id = tblBlock.id
                    AND
                        (tblContent.lang_id = ".intval($_LANGID)." AND tblContent.active = 1)
                    AND
                        (".$now." BETWEEN `tblBlock`.`start` AND `tblBlock`.`end` )
                    AND
                        tblBlock.active = 1 ";

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

            $objBlock = $objDatabase->SelectLimit("SELECT content FROM ".DBPREFIX."module_block_rel_lang_content WHERE block_id=".$ranId." AND lang_id=".intval($_LANGID), 1);
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
