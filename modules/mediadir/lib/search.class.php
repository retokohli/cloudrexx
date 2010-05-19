<?php
/**
 * Media  Directory Mail Class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_mediadir
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH . '/mediadir/lib/lib.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/inputfield.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/category.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/level.class.php';

class mediaDirectorySearch extends mediaDirectoryLibrary
{
    public $arrFoundIds = array();

    private $arrSearchLevels = array();
    private $arrSearchCategories = array();

    /**
     * Constructor
     */
    function __construct()
    {
        parent::getSettings();
    }



    function getSearchform($objTpl)
    {
        global $_ARRAYLANG, $_CORELANG, $objDatabase, $_LANGID;

        if(isset($_GET['cmd'])) {
            $strSearchFormCmd = '<input name="cmd" value="'.$_GET['cmd'].'" type="hidden" />';
        } else {
            $strSearchFormCmd = '';
        }

        if(isset($_GET['term'])) {
            $strSearchFormTerm = $_GET['term'];
        } else {
            $strSearchFormTerm = '';
        }

        $strSearchFormAction = CONTREXX_SCRIPT_PATH;
        $strTextSearch = $_CORELANG['TXT_SEARCH'];
        $strTextSearchterm = $_ARRAYLANG['TXT_MEDIADIR_SEARCH_TERM'];
        $strExpandedInputfields = $this->getExpandedInputfields();

        $strSearchNormalForm = <<<EOF
<div class="mediadirSearchForm">
<form method="get" action="$strSearchFormAction">
<input name="section" value="mediadir" type="hidden" />
<input name="type" value="normal" type="hidden" />
$strSearchFormCmd
<input name="term" class="mediadirInputfieldSearch" value="$strSearchFormTerm" onfocus="this.select();" type="text" /><input class="mediadirButtonSearch" value="$strTextSearch" name="search" type="submit">
</form>
</div>
EOF;

        $strSearchExpandedForm = <<<EOF

<div class="mediadirSearchForm">
<form method="get" action="$strSearchFormAction">
<div class="normal">
<input name="section" value="mediadir" type="hidden" />
<input name="type" value="exp" type="hidden" />
$strSearchFormCmd
<p><label>$strTextSearchterm</label><input name="term" class="mediadirInputfieldSearch" value="$strSearchFormTerm" onfocus="this.select();" type="text" /></p>
</div>
<div class="expanded">
$strExpandedInputfields
</div>
<p><input class="mediadirButtonSearch" value="$strTextSearch" name="search" type="submit"></p>
</form>
</div>
EOF;

        $objTpl->setVariable(array(
            'TXT_MEDIADIR_SEARCH' => $_CORELANG['TXT_SEARCH'],
            'TXT_MEDIADIR_EXP_SEARCH' => $_CORELANG['TXT_EXP_SEARCH'],
            'MEDIADIR_NORMAL_SEARCH_FORM' => $strSearchNormalForm,
            'MEDIADIR_EXPANDED_SEARCH_FORM' => $strSearchExpandedForm
        ));

        $objTpl->parse('mediadirSearchform');
    }



    function getExpandedInputfields()
    {
        global $_ARRAYLANG, $objDatabase, $_LANGID;

        $strPleaseChoose = $_ARRAYLANG['TXT_MEDIADIR_PLEASE_CHOOSE'];

        //get ids
        if(isset($_GET['cmd'])) {
            $arrIds = explode("-", $_GET['cmd']);
        }

        if($this->arrSettings['settingsShowLevels'] && in_array(1, $this->arrSettings['levelSelectorExpSearch'])) {
            if(intval($arrIds[0]) != 0) {
                $intLevelId = intval($arrIds[0]);
            } else {
                $intLevelId = 0;
            }

            $intLevelId = isset($_GET['lid']) ? intval($_GET['lid']) : $intLevelId;

            $objLevels = new mediaDirectoryLevel(null, null, 1);
            $strLevelDropdown = $objLevels->listLevels($this->_objTpl, 3, $intLevelId);
            $strLevelName = $_ARRAYLANG['TXT_MEDIADIR_LEVEL'];

            $strExpandedInputfields .= <<<EOF
<p><label>$strLevelName</label><select class="mediadirInputfieldSearch" name="lid"><option value="">$strPleaseChoose</option>$strLevelDropdown</select></p>
EOF;

            if(intval($arrIds[1]) != 0) {
                $intCategoryCmd = $arrIds[1];
            } else {
                $intCategoryCmd = 0;
            }
        } else {
            if(intval($arrIds[0]) != 0) {
                $intCategoryCmd = $arrIds[0];
            } else {
                $intCategoryCmd = 0;
            }
        }

        if($intCategoryCmd != 0) {
            $intCategoryId = intval($intCategoryCmd);
        } else {
            $intCategoryId = 0;
        }

        $intCategoryId = isset($_GET['cid']) ? intval($_GET['cid']) : $intCategoryId;

        if(in_array(1, $this->arrSettings['categorySelectorExpSearch'])) {
	        $objCategories = new mediaDirectoryCategory(null, null, 1);
	        $strCategoryDropdown = $objCategories->listCategories($this->_objTpl, 3, $intCategoryId);
	        $strCategoryName = $_ARRAYLANG['TXT_MEDIADIR_CATEGORY'];
	
	        $strExpandedInputfields .= <<<EOF
<p><label>$strCategoryName</label><select class="mediadirInputfieldSearch" name="cid"><option value="">$strPleaseChoose</option>$strCategoryDropdown</select></p>
EOF;
        }

        $objInputfields = new mediaDirectoryInputfield(null, true);
        $strExpandedInputfields .= $objInputfields->listInputfields(null, 4, null);

        return $strExpandedInputfields;
    }



    function searchEntries($arrData)
    {
        global $_ARRAYLANG, $_CORELANG, $objDatabase, $_LANGID;

        $arrSelect = array();
        $arrFrom = array();
        $arrWhere = array();
        $arrOrder = array();

        //build search term query
        $arrData['term'] = trim($arrData['term']);

        if (!empty($arrData['term'])) {
            $strTerm        = contrexx_addslashes(trim($arrData['term']));
            
            $arrSelect[]    = 'rel_inputfield.`entry_id` AS `entry_id`';
            //$arrSelect[]    = "MATCH (rel_inputfield.`value`) AGAINST ('".$strTerm."*' IN BOOLEAN MODE)  AS score";;
            $arrSelect[]    = "MATCH (rel_inputfield.`value`) AGAINST ('%".$strTerm."%')  AS score";
            $arrFrom[]      = DBPREFIX."module_mediadir_rel_entry_inputfields AS rel_inputfield";
            $arrWhere[]     = 'rel_inputfield.`entry_id` != 0';
            
            /*$strReplace     = "*' IN BOOLEAN MODE) AND MATCH (rel_inputfield.`value`) AGAINST ('";
            $strReplace     = preg_replace("/\s+/", $strReplace, $strTerm);
            $arrWhere[]     = "MATCH (rel_inputfield.`value`) AGAINST ('".$strReplace."*' IN BOOLEAN MODE)";*/
            
            $strReplace     = "%' AND rel_inputfield.`value` LIKE '%";
            $strReplace     = preg_replace("/\s+/", $strReplace, $strTerm);
            $arrWhere[]     = "rel_inputfield.`value` LIKE '%".$strReplace."%'";
            
            $arrOrder[]     = "score DESC";
        } else {
            $arrSelect[]    = 'rel_inputfield.`entry_id` AS `entry_id`';
            $arrFrom[]      = DBPREFIX."module_mediadir_rel_entry_inputfields AS rel_inputfield";
            $arrWhere[]     = 'rel_inputfield.`entry_id` != 0';
            
            $arrOrder[]     = "entry_id DESC";
        }

        if($arrData['type'] == 'exp') {
            //build level search query
            if (intval($arrData['lid']) != 0 && $arrData['type'] == 'exp') {
                array_push($this->arrSearchLevels, intval($arrData['lid']));
                $this->getSearchLevelIds(intval($arrData['lid']));

                if (!empty($this->arrSearchLevels)) {
                    foreach ($this->arrSearchLevels as $intLevelId) {
                        $strWhereLevels .= "(rel_level.level_id='".$intLevelId."' AND rel_level.entry_id=rel_inputfield.entry_id) OR ";
                    }
                }

                $arrWhere[]     = "(rel_level.level_id IN (".join(',', $this->arrSearchLevels).") AND rel_level.entry_id=rel_inputfield.entry_id)";
                $arrFrom[]      = DBPREFIX."module_mediadir_rel_entry_levels AS rel_level";
            }

            //build category search query
            if (intval($arrData['cid']) != 0 && $arrData['type'] == 'exp') {
                array_push($this->arrSearchCategories, intval($arrData['cid']));
                $this->getSearchCategoryIds(intval($arrData['cid']));

                $arrWhere[]     = "(rel_category.category_id IN (".join(',', $this->arrSearchCategories).") AND rel_category.entry_id=rel_inputfield.entry_id)";
                $arrFrom[]      = DBPREFIX."module_mediadir_rel_entry_categories AS rel_category";
            }
        }

        if(!empty($arrSelect) && !empty($arrFrom) && !empty($arrWhere) && !empty($arrOrder)) {
            $query = "
                SELECT
                    ".join(',', $arrSelect)."
                FROM
                    ".join(',', $arrFrom)."
                WHERE
                    ".join(' AND ', $arrWhere)."
                ORDER BY
                    ".join(',', $arrOrder)."
            ";

            if($arrData['type'] == 'exp') {
                //build expanded search query
                $arrExternals = array('section', 'type', 'cmd', 'term', 'lid', 'cid', 'search', 'pos');
                foreach ($arrData as $intInputfieldId => $strExpTerm) {
                    if(!in_array($intInputfieldId, $arrExternals) && $strExpTerm != null) {
                        $objInputfields = new mediaDirectoryInputfield(null, true);
                        $intInputfieldType = $objInputfields->arrInputfields[$intInputfieldId]['type'];
                        $strExpTerm = contrexx_addslashes(trim($strExpTerm));
                        $strTableName = "rel_inputfield_".intval($intInputfieldId);
                        $arrExpJoin[]  = "LEFT JOIN ".DBPREFIX."module_mediadir_rel_entry_inputfields AS $strTableName ON rel_inputfield.`entry_id` = $strTableName.`entry_id`";

                        if($intInputfieldType == '11') {
                            switch ($this->arrSettings['settingsClassificationSearch']) {
                                case 1:
                                    $strSearchOperator = '>=';
                                    break;
                                case 2:
                                    $strSearchOperator = '<=';
                                    break;
                                case 3:
                                    $strSearchOperator = '=';
                                    break;
                            }

                            $arrExpWhere[] = "($strTableName.`field_id` = $intInputfieldId AND $strTableName.`value` $strSearchOperator '$strExpTerm')";
                        } else {
                            $arrExpWhere[] = "($strTableName.`field_id` = $intInputfieldId AND $strTableName.`value` LIKE '%$strExpTerm%')";
                        }
                    }
                }

                if(!empty($arrExpJoin) && !empty($arrExpWhere)) {
                   $query = "
                        SELECT
                            rel_inputfield_final.`entry_id` AS `entry_id`
                        FROM
                            ".DBPREFIX."module_mediadir_rel_entry_inputfields AS rel_inputfield_final
                        LEFT JOIN
                             (".$query.") AS rel_inputfield
                         ON rel_inputfield_final.`entry_id` = rel_inputfield.`entry_id`


                            ".join(' ', $arrExpJoin)."
                        WHERE
                            ".join(' AND ', $arrExpWhere)."
                    ";
                }
            }

            $objRsSearchEntries = $objDatabase->Execute($query);
            
            

            if ($objRsSearchEntries !== false) {
                while (!$objRsSearchEntries->EOF) {
                    if(!in_array(intval($objRsSearchEntries->fields['entry_id']), $this->arrFoundIds)) {
                        $this->arrFoundIds[] = intval($objRsSearchEntries->fields['entry_id']);
                    }
                    $objRsSearchEntries->MoveNext();
                }
            }
        }
    }



    function getSearchLevelIds($intLevelId)
    {
        global $objDatabase;

        $objResultSearchLevels = $objDatabase->Execute("SELECT id FROM ".DBPREFIX."module_mediadir_levels WHERE parent_id='$intLevelId' ");

        if ($objResultSearchLevels) {
            while (!$objResultSearchLevels->EOF) {
                if (!empty($objResultSearchLevels->fields['id'])) {
                    array_push($this->arrSearchLevels, $objResultSearchLevels->fields['id']);
                }

                $this->getSearchLevelIds($objResultSearchLevels->fields['id']);
                $objResultSearchLevels->MoveNext();
            }
        }
    }



    function getSearchCategoryIds($intCatId)
    {
        global $objDatabase;

        $objResultSearchCategories = $objDatabase->Execute("SELECT id FROM ".DBPREFIX."module_mediadir_categories WHERE parent_id='$intCatId'");

        if ($objResultSearchCategories) {
            while (!$objResultSearchCategories->EOF) {
                if (!empty($objResultSearchCategories->fields['id'])) {
                    array_push($this->arrSearchCategories, $objResultSearchCategories->fields['id']);
                }

                $this->getSearchCategoryIds($objResultSearchCategories->fields['id']);
                $objResultSearchCategories->MoveNext();
            }
        }
    }
}