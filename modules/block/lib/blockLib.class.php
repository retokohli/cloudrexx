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
    public function getBlocks($catId = 0)
    {
        global $objDatabase, $_LANGID;

        $catId = intval($catId);
        $where = array();

        if ($catId > 0) {
            $where = 'WHERE `cat` = '.$catId;
        }

        if (!is_array($this->_arrBlocks)) {
            $query = 'SELECT    `id`,
                                `cat`,
                                `name`,
                                `start`,
                                `end`,
                                `order`,
                                `random`,
                                `random_2`,
                                `random_3`,
                                `random_4`,
                                `global`,
                                `active`
                        FROM `%1$s`
                        # WHERE
                        %2$s
                        ORDER BY `order`';

            $objResult = $objDatabase->Execute(sprintf($query, DBPREFIX.'module_block_blocks',
                                                               $where));
            if ($objResult !== false) {
                $this->_arrBlocks = array();                
                
                while (!$objResult->EOF) {  
                    $langArr          = array();
                    $objBlockLang = $objDatabase->Execute("SELECT lang_id FROM ".DBPREFIX."module_block_rel_lang_content WHERE block_id=".$objResult->fields['id']." ORDER BY lang_id ASC");
                    
                    if ($objBlockLang) {
                        while (!$objBlockLang->EOF) {                        
                            $langArr[] = $objBlockLang->fields['lang_id'];
                            $objBlockLang->MoveNext();

                        }
                    }
                    $this->_arrBlocks[$objResult->fields['id']] = array(
                        'cat'       => $objResult->fields['cat'],
                        'start'     => $objResult->fields['start'],
                        'end'       => $objResult->fields['end'],
                        'order'     => $objResult->fields['order'],
                        'random'    => $objResult->fields['random'],
                        'random2'   => $objResult->fields['random_2'],
                        'random3'   => $objResult->fields['random_3'],
                        'random4'   => $objResult->fields['random_4'],
                        'global'    => $objResult->fields['global'],
                        'active'    => $objResult->fields['active'],
                        'name'      => $objResult->fields['name'],
                        'lang'      => array_unique($langArr),
                    );
                    $objResult->MoveNext();
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
    public function _addBlock($cat, $arrContent, $name, $start, $end, $blockRandom, $blockRandom2, $blockRandom3, $blockRandom4, $blockGlobal, $blockAssociatedPageIds, $arrLangActive)
    {
        global $objDatabase, $_LANGID;

        $query = "INSERT INTO `".DBPREFIX."module_block_blocks`
                    SET `name`     = '".contrexx_raw2db($name)."',
                        `cat`      = ".intval($cat).",
                        `start`    = ".intval($start).",
                        `end`      = ".intval($end).",
                        `random`   = ".intval($blockRandom).",
                        `random_2` = ".intval($blockRandom2).",
                        `random_3` = ".intval($blockRandom3).", 
                        `random_4` = ".intval($blockRandom4).", 
                        `global`   = ".intval($blockGlobal).",
                        `active`   = 1";
        if ($objDatabase->Execute($query) === false) {
            return false;
        }
        $id = $objDatabase->Insert_ID();

        $this->storeBlockContent($id, $arrContent, $arrLangActive);
        $this->storeNodeAssociations($id, $blockAssociatedPageIds, $blockGlobal);

        return true;
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
    public function _updateBlock($id, $cat, $arrContent, $name, $start, $end, $blockRandom, $blockRandom2, $blockRandom3, $blockRandom4, $blockGlobal, $blockAssociatedPageIds, $arrLangActive)
    {
        global $objDatabase;

        $query = "UPDATE `".DBPREFIX."module_block_blocks`
                    SET `name`     = '".contrexx_raw2db($name)."',
                        `cat`      = ".intval($cat).",
                        `start`    = ".intval($start).",
                        `end`      = ".intval($end).",
                        `random`   = ".intval($blockRandom).",
                        `random_2` = ".intval($blockRandom2).",
                        `random_3` = ".intval($blockRandom3).", 
                        `random_4` = ".intval($blockRandom4).", 
                        `global`   = ".intval($blockGlobal)." 
                  WHERE `id` = ".intval($id);
        if ($objDatabase->Execute($query) === false) {
            return false;
        }

        $this->storeBlockContent($id, $arrContent, $arrLangActive);
        $this->storeNodeAssociations($id, $blockAssociatedPageIds, $blockGlobal);

        return true;
    }


    private function storeNodeAssociations($blockId, $blockAssociatedPageIds, $blockGlobal)
    {
        global $objDatabase;

        switch ($blockGlobal) {
            case 2:
                foreach ($blockAssociatedPageIds as $pageId) {
                    $objDatabase->Execute('INSERT IGNORE INTO '.DBPREFIX.'module_block_rel_pages SET  block_id='.intval($blockId).', page_id='.intval($pageId).'');
                }
                if (count($blockAssociatedPageIds)) {
                    $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_block_rel_pages WHERE block_id=".$blockId." AND page_id NOT IN (".join(',', array_map('intval', $blockAssociatedPageIds)).")");
                    break;
                }
                // the missing break is intentionally, so that the system deletes all entries in case no nodes had been selected

            case 0:
            case 1:
            default:
                $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_block_rel_pages WHERE block_id=".$blockId);
                break;
        }
    }

    private function storeBlockContent($blockId, $arrContent, $arrLangActive)
    {
        global $objDatabase;
        
        $arrPresentLang = array();
        $objResult = $objDatabase->Execute('SELECT lang_id FROM '.DBPREFIX.'module_block_rel_lang_content WHERE block_id='.$blockId);
        if ($objResult) {
            while (!$objResult->EOF) {
                $arrPresentLang[] = $objResult->fields['lang_id'];
                $objResult->MoveNext();
            }
        }

        foreach ($arrContent as $langId => $content) {            
            if (in_array($langId, $arrPresentLang)) {
                $query = 'UPDATE `%1$s` SET %2$s WHERE `block_id` = %3$s AND `lang_id`='.intval($langId);
            } else {
                $query = 'INSERT INTO `%1$s` SET %2$s, `block_id` = %3$s';
            }

            $content = preg_replace('/\[\[([A-Z0-9_-]+)\]\]/', '{\\1}', $content);
            $objDatabase->Execute(sprintf($query, DBPREFIX.'module_block_rel_lang_content',
                                                  "lang_id='".intval($langId)."',
                                                   content='".contrexx_raw2db($content)."',
                                                   active='".intval($arrLangActive[$langId])."'",
                                                  $blockId));
        }        
        
        $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_block_rel_lang_content WHERE block_id=".$blockId." AND lang_id NOT IN (".join(',', array_map('intval', array_keys($arrLangActive))).")");
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

        $objBlock = $objDatabase->SelectLimit("SELECT name, cat, start, end, random, random_2, random_3, random_4, global, active FROM ".DBPREFIX."module_block_blocks WHERE id=".$id, 1);


        if ($objBlock !== false && $objBlock->RecordCount() == 1) {
            $arrContent = array();
            $arrActive = array();

            $objBlockContent = $objDatabase->Execute("SELECT lang_id, content, active FROM ".DBPREFIX."module_block_rel_lang_content WHERE block_id=".$id);
            if ($objBlockContent !== false) {
                while (!$objBlockContent->EOF) {
                    $arrContent[$objBlockContent->fields['lang_id']] = $objBlockContent->fields['content'];
                    $arrActive[$objBlockContent->fields['lang_id']]  = $objBlockContent->fields['active'];
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
                'global'        => $objBlock->fields['global'],
                'active'        => $objBlock->fields['active'],
                'name'          => $objBlock->fields['name'],
                'content'       => $arrContent,
                'lang_active'   => $arrActive,
            );
        }

        return false;
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

        $this->_categories = array(0 => array());

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
