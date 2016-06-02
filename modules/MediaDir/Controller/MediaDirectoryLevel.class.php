<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Media  Directory Level Class
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_mediadir
 * @todo        Edit PHP DocBlocks!
 */
namespace Cx\Modules\MediaDir\Controller;

/**
 * Media Directory Level Class
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_mediadir
 */
class MediaDirectoryLevel extends MediaDirectoryLibrary
{
    /**
     * Count number of entries for each levels
     *
     * @var boolean
     */
    protected $countEntries = false;

    /**
     * Instance of NestedSet
     *
     * @var \DB_NestedSet
     */
    public $levelNestedSet;

    /**
     * Parent level id(Root of all levels)
     *
     * @var integer
     */
    public $nestedSetRootId;

    /**
     * Default Constructor
     */
    function __construct($name)
    {
        global $objDatabase;

        parent::__construct('.', $name);
        $this->getSettings();
        $this->getFrontendLanguages();

        if ($this->arrSettings['settingsCountEntries'] == 1 || $this->cx->getMode() == \Cx\Core\Core\Controller\Cx::MODE_BACKEND) {
            $this->countEntries = true;
        }

        //nestedSet setup
        $arrTableStructure = array(
            'id'                => 'id',
            'parent_id'         => 'rootid',
            'lft'               => 'l',
            'rgt'               => 'r',
            'level_order'       => 'norder',
            'lvl'               => 'level',
            'show_sublevels'    => 'show_sublevels',
            'show_categories'   => 'show_categories',
            'show_entries'      => 'show_entries',
            'picture'           => 'picture',
            'active'            => 'active',
        );
        $objNs = new \DB_NestedSet($arrTableStructure);
        $this->levelNestedSet = $objNs->factory('ADODB', $objDatabase, $arrTableStructure);
        $this->levelNestedSet->setAttr(array(
            'node_table'    => DBPREFIX.'module_mediadir_levels',
            'lock_table'    => DBPREFIX.'module_mediadir_levels_locks',
        ));

        if (count($rootNodes = $this->levelNestedSet->getRootNodes()) > 0) {
            foreach ($rootNodes as $rootNode) {
                $this->nestedSetRootId = $rootNode->id;
                break;
            }
        } else {
            // create first entry of sequence table for NestedSet
            $objResult = $objDatabase->SelectLimit("SELECT `id` FROM `".DBPREFIX."module_mediadir_levels_id`", 1);
            if ($objResult->RecordCount() == 0) {
                $objDatabase->Execute("INSERT INTO `".DBPREFIX."module_mediadir_levels_id` VALUES (0)");
            }
            $this->nestedSetRootId = $this->levelNestedSet->createRootNode(array(), false, false);
        }
    }

    /**
     * Parse the level details
     *
     * @param \Cx\Core\Html\Sigma   $objTpl          Instance of template object
     * @param Level                 $level           Instance of level to parse
     * @param string                $strLevelIcon    String level icon
     * @param string                $strLevelClass   Class name for the level
     * @param boolean               $showCategories  True to parse the categories
     * @param string                $blockName       Parse block name
     */
    public function parseLevelDetail(
        \Cx\Core\Html\Sigma $objTpl,
        \Cx\Modules\MediaDir\Model\Entity\Level $level,
        $strLevelIcon   = null,
        $strLevelClass  = 'inactive',
        $showCategories = false,
        $blockName      = 'LevelsList'
    ) {
        $intSpacerSize = ($level->getLvl() - 2) * 21;
        $spacer        = '<img src="../core/Core/View/Media/icons/pixel.gif" border="0" width="'.$intSpacerSize.'" height="11" alt="" />';

        $levelDesc = $this->getLevelDescription($level);

        $levelCategory = '';
        if (   $showCategories
            && $level->getShowCategories()
            && $strLevelClass = 'active'
            && !empty($_GET['lid'])
            && $_GET['lid'] == $level->getId()
        ) {
            $objCategories = new MediaDirectoryCategory($this->moduleName);
            $intCategoryId = isset($_GET['cid']) ? intval($_GET['cid']) : null;
            $levelCategory = $objCategories->listCategories($objTpl, 6, $intCategoryId, null, null, array(), $level->getLvl());
        }
        //parse variables
        $objTpl->setVariable(array(
            $this->moduleLangVar.'_LEVEL_ID'                   => $level->getId(),
            $this->moduleLangVar.'_LEVEL_ORDER'                => $level->getOrder(),
            $this->moduleLangVar.'_LEVEL_NAME'                 => contrexx_raw2xhtml($this->getLevelName($level)),
            $this->moduleLangVar.'_LEVEL_DESCRIPTION'          => $levelDesc,
            $this->moduleLangVar.'_LEVEL_DESCRIPTION_ESCAPED'  => strip_tags($levelDesc),
            $this->moduleLangVar.'_LEVEL_PICTURE'              => $level->getPicture(),
            $this->moduleLangVar.'_LEVEL_NUM_ENTRIES'          => $this->countEntries($level->getId()),
            $this->moduleLangVar.'_LEVEL_ICON'                 => $spacer.$strLevelIcon,
            $this->moduleLangVar.'_LEVEL_VISIBLE_STATE_ACTION' => $level->getActive() == 0 ? 1 : 0,
            $this->moduleLangVar.'_LEVEL_VISIBLE_STATE_IMG'    => $level->getActive() == 0 ? 'off' : 'on',
            $this->moduleLangVar.'_LEVEL_LEVEL_NUMBER'         => $level->getLvl() - 1,
            $this->moduleLangVar.'_LEVEL_ACTIVE_STATUS'        => $strLevelClass,
            $this->moduleLangVar.'_LEVEL_CATEGORY'             => $levelCategory,
        ));

        $objTpl->parse($this->moduleNameLC . $blockName);
    }

    /**
     * Parse level tree
     *
     * @param \Cx\Core\Html\Sigma   $objTpl              Instance of template
     * @param Level                 $level               Root level to parse
     * @param array                 $expandedLevelIds    Expanded level array
     * @param boolean               $expandAll           True to expand all levels
     * @param boolean               $checkShowSubLevel   True to check the show sublevel option
     * @param boolean               $showCategories      True to parse the categories
     */
    public function parseLevelTree(
        \Cx\Core\Html\Sigma $objTpl,
        \Cx\Modules\MediaDir\Model\Entity\Level $level,
        $expandedLevelIds = array(),
        $expandAll = false,
        $checkShowSubLevel = false,
        $showCategories = false
    ) {
        $subLevels = $this->getSubLevelsByLevel($level);
        foreach ($subLevels as $sublevel) {
            $hasChildren = !\FWValidator::isEmpty($this->getSubLevelsByLevel($sublevel));
            $isExpanded  = (!$checkShowSubLevel || $sublevel->getShowSublevels())
                           && ($expandAll || in_array($sublevel->getId(), $expandedLevelIds));

            $strLevelClass = 'inactive';
            if ($hasChildren) {
                if ($isExpanded) {
                    $expLvlId = $sublevel->getParent();
                    $iconFile = 'minuslink.gif';
                    $strLevelClass = 'active';
                } else {
                    $expLvlId = $sublevel->getId();
                    $iconFile = 'pluslink.gif';
                }
                $strLevelIcon = '<a href="index.php?cmd='.$this->moduleName.'&amp;exp_level='. $expLvlId .'"><img src="../core/Core/View/Media/icons/'. $iconFile .'" border="0" /></a>';
            } else {
                $strLevelIcon = '<img src="../core/Core/View/Media/icons/pixel.gif" border="0" width="11" height="11" />';
            }
            $this->parseLevelDetail($objTpl, $sublevel, $strLevelIcon, $strLevelClass, $showCategories);

            if ($isExpanded) {
                $this->parseLevelTree($objTpl, $sublevel, $expandedLevelIds, $expandAll);
            }
        }
    }

    /**
     * List the levels by the view type
     *
     * @param type $objTpl
     * @param type $intView
     * @param type $levelId
     * @param type $parentLevelId
     * @param type $intEntryId
     * @param type $arrExistingBlocks
     *
     * @return type
     */
    function listLevels(
        $objTpl,
        $intView,
        $levelId = null,
        $parentLevelId = null,
        $intEntryId = null,
        $arrExistingBlocks = null
    ) {

        switch ($intView) {
            case 1:
                //Backend View
                $expandLevel      = isset($_GET['exp_level']) ? $_GET['exp_level'] : '';
                $expandedLevelIds = $this->getExpandedLevelIds($expandLevel);

                $rootLevel = $this->getLevelById($this->nestedSetRootId);
                $this->parseLevelTree(
                    $objTpl,
                    $rootLevel,
                    $expandedLevelIds,
                    $expandLevel == 'all'
                );
                break;
            case 2:
                //Frontend View
                $intNumBlocks   = count($arrExistingBlocks);
                $strIndexHeader = '';
                $i              = $intNumBlocks - 1;

                //set first index header
                if($this->arrSettings['settingsLevelOrder'] == 2) {
                    $strFirstIndexHeader = null;
                }

                $level = $parentLevelId ? $this->getLevelById($parentLevelId) : false;
                if (!$level) {
                    $level = $this->getLevelById($this->nestedSetRootId);
                }

                $subLevels = $this->getSubLevelsByLevel($level);
                foreach ($subLevels as $subLevel) {
                    $levelName = $this->getLevelName($subLevel);
                    $levelDesc = $this->getLevelDescription($subLevel);

                    $strIndexHeaderTag = null;
                    if ($this->arrSettings['settingsLevelOrder'] == 2) {
                        $strIndexHeader = strtoupper(substr($levelName, 0, 1));

                        if ($strFirstIndexHeader != $strIndexHeader) {
                            if ($i < $intNumBlocks - 1) {
                                ++$i;
                            } else {
                                $i = 0;
                            }
                            $strIndexHeaderTag = '<span class="' . $this->moduleNameLC . 'LevelCategoryIndexHeader">' . $strIndexHeader . '</span><br />';
                        }
                    } else {
                        if ($i < $intNumBlocks - 1) {
                            ++$i;
                        } else {
                            $i = 0;
                        }
                    }

                    //get ids
                    $strLevelCmd = isset($_GET['cmd'])
                                        ? '&amp;cmd='.$_GET['cmd']
                                        : '';

                    //parse variables
                    $objTpl->setVariable(array(
                        $this->moduleLangVar.'_CATEGORY_LEVEL_ID'             => $subLevel->getId(),
                        $this->moduleLangVar.'_CATEGORY_LEVEL_NAME'           => contrexx_raw2xhtml($levelName),
                        $this->moduleLangVar.'_CATEGORY_LEVEL_LINK'           => $strIndexHeaderTag.'<a href="index.php?section='.$this->moduleName.$strLevelCmd.'&amp;lid='.$subLevel->getId().'">'.contrexx_raw2xhtml($levelName).'</a>',
                        $this->moduleLangVar.'_CATEGORY_LEVEL_DESCRIPTION'    => contrexx_raw2xhtml($levelDesc),
                        $this->moduleLangVar.'_CATEGORY_LEVEL_PICTURE'        => '<img src="'.$subLevel->getPicture().'" border="0" alt="'.contrexx_raw2xhtml($levelName).'" />',
                        $this->moduleLangVar.'_CATEGORY_LEVEL_PICTURE_SOURCE' => $subLevel->getPicture(),
                        $this->moduleLangVar.'_CATEGORY_LEVEL_NUM_ENTRIES'    => $this->countEntries($level->getId()),
                    ));

                    $intBlockId = $arrExistingBlocks[$i];

                    $objTpl->parse($this->moduleNameLC.'CategoriesLevels_row_'.$intBlockId);
                    $objTpl->clearVariables();

                    $strFirstIndexHeader = $strIndexHeader;
                }
                break;
            case 3:
                //Dropdown Menu
                $rootLevel = $this->getLevelById($this->nestedSetRootId);
                return $this->getLevelDropDown($rootLevel, $levelId);
                break;
            case 4:
                //level Selector (modify view)
                $rootLevel         = $this->getLevelById($this->nestedSetRootId);
                $arrSelectedLevels = $this->getSelectedLevelsByEntryId($intEntryId);
                list($selectedOptions, $notSelectedOptions) = $this->getLevelOptions4EditView($rootLevel, $arrSelectedLevels);
                $arrSelectorOptions = array(
                  'selected'     => $selectedOptions,
                  'not_selected' => $notSelectedOptions
                );
                return $arrSelectorOptions;
                break;
            case 5:
                //Frontend View Detail
                $level  = $this->getLevelById($levelId);
                if (!$level) {
                    $objTpl->hideBlock($this->moduleNameLC.'CategoryLevelDetail');
                    return;
                }

                $strLevelId       = isset($_GET['lid']) ? "&amp;lid=".intval($_GET['lid']) : '';
                $levelName        = $this->getLevelName($level);
                $levelDescription = $this->getLevelDescription($level);
                $thumbImage       = $this->getThumbImage($level->getPicture());
                $objTpl->setVariable(array(
                    $this->moduleLangVar.'_CATEGORY_LEVEL_ID'             => $level->getId(),
                    $this->moduleLangVar.'_CATEGORY_LEVEL_NAME'           => contrexx_raw2xhtml($levelName),
                    $this->moduleLangVar.'_CATEGORY_LEVEL_LINK'           => '<a href="index.php?section='.$this->moduleName.$strLevelId.'&amp;lid='.$level->getId().'">'.contrexx_raw2xhtml($levelName).'</a>',
                    $this->moduleLangVar.'_CATEGORY_LEVEL_DESCRIPTION'    => contrexx_raw2xhtml($levelDescription),
                    $this->moduleLangVar.'_CATEGORY_LEVEL_PICTURE'        => '<img src="'. $thumbImage .'" border="0" alt="'. contrexx_raw2xhtml($levelName) .'" />',
                    $this->moduleLangVar.'_CATEGORY_LEVEL_PICTURE_SOURCE' => $level->getPicture(),
                    $this->moduleLangVar.'_CATEGORY_LEVEL_NUM_ENTRIES'    => $this->countEntries($level->getId()),
                ));

                if(!\FWValidator::isEmpty($level->getPicture()) && $this->arrSettings['settingsShowLevelImage'] == 1) {
                    $objTpl->parse($this->moduleNameLC.'CategoryLevelPicture');
                } else {
                    $objTpl->hideBlock($this->moduleNameLC.'CategoryLevelPicture');
                }

                if(!empty($levelDescription) && $this->arrSettings['settingsShowLevelDescription'] == 1) {
                    $objTpl->parse($this->moduleNameLC.'CategoryLevelDescription');
                } else {
                    $objTpl->hideBlock($this->moduleNameLC.'CategoryLevelDescription');
                }

                $objTpl->parse($this->moduleNameLC.'CategoryLevelDetail');

                break;
            case 6:
                //Frontend Tree Placeholder
                $expandedLevelIds = $this->getExpandedLevelIds($levelId);
                $level = $this->getLevelById($this->nestedSetRootId);

                $tpl = <<<TEMPLATE
    <!-- BEGIN {$this->moduleNameLC}LevelsList -->
    <li class="level_{{$this->moduleLangVar}_LEVEL_LEVEL_NUMBER}">
        <a href="index.php?section={$this->moduleName}&amp;lid={{$this->moduleLangVar}_LEVEL_ID}" class="{{$this->moduleLangVar}_LEVEL_ACTIVE_STATUS}">
            {{$this->moduleLangVar}_LEVEL_NAME}
        </a>
    </li>
    {{$this->moduleLangVar}_LEVEL_CATEGORY}
    <!-- END {$this->moduleNameLC}LevelsList -->
TEMPLATE;
                $template = new \Cx\Core\Html\Sigma('.');
                $template->setTemplate($tpl);
                $this->parseLevelTree(
                    $template,
                    $level,
                    $expandedLevelIds,
                    false,
                    $level->getShowSublevels(),
                    true
                );
                return $template->get();
                break;
        }
    }

    /**
     * Get level dropdown for Entry edit view
     *
     * @param Level  $level              Parent level
     * @param array  $arrSelectedLevels  Array selected levels
     *
     * @return array
     */
    public function getLevelOptions4EditView(\Cx\Modules\MediaDir\Model\Entity\Level $level, $arrSelectedLevels)
    {
        $subLevels = $this->getSubLevelsByLevel($level);
        $strSelectedOptions = $strNotSelectedOptions = '';
        foreach ($subLevels as $subLevel) {
            $spacer       = str_repeat('----', $subLevel->getLvl() - 2);
            $spacer      .= $subLevel->getLvl() > 1 ? '&nbsp;' : '';
            $levelName = $this->getLevelName($subLevel);

            $option = '<option value="'. $subLevel->getId() .'">'. $spacer . contrexx_raw2xhtml($levelName).'</option>';
            if (in_array($subLevel->getId(), $arrSelectedLevels)) {
                $strSelectedOptions .= $option;
            } else {
                $strNotSelectedOptions .= $option;
            }
            list($selectedOptions, $notSelectedOptions) = $this->getLevelOptions4EditView($subLevel, $arrSelectedLevels);
            $strSelectedOptions    .= $selectedOptions;
            $strNotSelectedOptions .= $notSelectedOptions;
        }
        return array($strSelectedOptions, $strNotSelectedOptions);
    }

    /**
     * Get levels related to the entry
     *
     * @param integer $entryId Entry id
     *
     * @return array Array of level id's
     */
    public function getSelectedLevelsByEntryId($entryId)
    {
        global $objDatabase;

        $entryId = contrexx_input2int($entryId);
        if (!$entryId) {
            return array();
        }
        $levels = $objDatabase->Execute('SELECT
                                               `level_id`
                                            FROM
                                                `'. DBPREFIX .'module_'. $this->moduleTablePrefix .'_rel_entry_levels`
                                            WHERE
                                                `entry_id` = "'. $entryId .'"');
        if (!$levels) {
            return array();
        }
        $selectedLevels = array();
        while (!$levels->EOF) {
            $selectedLevels[] = $levels->fields['level_id'];
            $levels->MoveNext();
        }
        return $selectedLevels;
    }

    /**
     * Get name of level by output language
     *
     * @param Level $level
     *
     * @return string
     */
    public function getLevelName(\Cx\Modules\MediaDir\Model\Entity\Level $level)
    {
        global $_LANGID;

        $locale = $level->getLocaleByLang($_LANGID);
        return $locale ? $locale->getLevelName() : '';
    }

    /**
     * Get level description by output language
     *
     * @param Level $level
     *
     * @return string
     */
    public function getLevelDescription(\Cx\Modules\MediaDir\Model\Entity\Level $level)
    {
        global $_LANGID;

        $locale = $level->getLocaleByLang($_LANGID);
        return $locale ? $locale->getLevelDescription() : '';
    }

    /**
     * Get sub levels of a level, by applying the sorting and status
     *
     * @param Level $level
     *
     * @return array Array collection of sublevels
     */
    public function getSubLevelsByLevel(\Cx\Modules\MediaDir\Model\Entity\Level $level)
    {
        $addSql   = array();
        if ($this->cx->getMode() == \Cx\Core\Core\Controller\Cx::MODE_FRONTEND) {
            $addSql['where'] = 'active = 1';
        }
        $children = array();
        $childrenResult = $this->levelNestedSet->getChildren($level->getId(), true, true, false, $addSql);
        if (!$childrenResult) {
            return $children;
        }
        foreach ($childrenResult as $value) {
            $subLevel = $this->createLevelFromArray($value);
            $this->loadLevelLocale($subLevel);
            $children[] = $subLevel;
        }
        if (in_array($this->arrSettings['settingsLevelOrder'], array(1, 2))) {
            // sort by category name
            uasort($children, array($this, 'sortLevelsByName'));
        }
        return $children;
    }

    /**
     * Custom sorting callback function to sort the levels by name
     *
     * @param \Cx\Modules\MediaDir\Model\Entity\Level $a
     * @param \Cx\Modules\MediaDir\Model\Entity\Level $b
     *
     * @return boolean
     */
    public function sortLevelsByName(
        \Cx\Modules\MediaDir\Model\Entity\Level $a,
        \Cx\Modules\MediaDir\Model\Entity\Level $b
    ) {
        return strcmp($this->getLevelName($a), $this->getLevelName($b));
    }

    /**
     * Get the level dropdown
     *
     * @param Level $level
     *
     * @return string
     */
    public function getLevelDropDown(\Cx\Modules\MediaDir\Model\Entity\Level $level, $selectedLevelId = null)
    {
        $strDropdownOptions = '';
        $subLevels          = $this->getSubLevelsByLevel($level);
        foreach ($subLevels as $subLevel) {
            $strSelected  = $selectedLevelId == $subLevel->getId() ? 'selected="selected"' : '';
            $spacer       = str_repeat('----', $subLevel->getLvl() - 2);
            $spacer      .= $subLevel->getLvl() > 0 ? '&nbsp;' : '';
            $levelName    = $this->getLevelName($subLevel);

            $strDropdownOptions .= '<option value="'. $subLevel->getId() .'" '. $strSelected .' >'. $spacer . contrexx_raw2xhtml($levelName) .'</option>';
            $strDropdownOptions .= $this->getLevelDropDown($subLevel, $selectedLevelId);
        }
        return $strDropdownOptions;
    }

    /**
     * Get expanaged level id's
     *
     * @param integer $levelId Level id
     *
     * @return Array
     */
    public function getExpandedLevelIds($levelId)
    {
        $levels = $this->getExpandedLevels($levelId);
        $levelIds = array();
        foreach ($levels as $level) {
            $levelIds[] = $level->getId();
        }

        return $levelIds;
    }

    /**
     * Get all expanded levels
     *
     * @param type $levelId
     *
     * @return Array
     */
    function getExpandedLevels($levelId)
    {
        $levelId = contrexx_input2int($levelId);
        if (!$levelId) {
            return array();
        }
        $level = $this->getLevelById($levelId);
        if (!$level) {
            return array();
        }

        $parentlevels = $this->levelNestedSet->getParents($levelId, true);
        $levels       = array($level);
        foreach ($parentlevels as $parentLevel) {
            $parentId = $parentLevel['id'];
            if ($parentId == $this->nestedSetRootId) {
                continue;
            }
            $levels[] = $this->getLevelById($parentId);
        }
        return $levels;
    }

    /**
     * Save Level from given array
     *
     * @param Level     $level   Level instance
     * @param array     $arrData Array date to save
     *
     * @return boolean True on save success, false otherwise
     */
    function saveLevel(\Cx\Modules\MediaDir\Model\Entity\Level $level, $arrData)
    {
        global $_ARRAYLANG;

        $oldParentId = $level->getParent();
        $parentId    = intval($arrData['levelPosition']);

        $parentLevel = null;
        if ($parentId) {
            $parentLevel = $this->getLevelById($parentId) ? $parentId : null;
        } else {
            $parentLevel = $this->nestedSetRootId;
        }
        if (!$parentLevel) {
            return false;
        }
        if ($level->getId() && $level->getId() == $parentLevel) {
            return false;
        }

        //get data
        $intShowEntries    = intval($arrData['levelShowEntries']);
        $intShowSublevels  = isset($arrData['levelShowSublevels']) ? contrexx_input2int($arrData['levelShowSublevels']) : 0;
        $intShowCategories = isset($arrData['levelShowCategories']) ? contrexx_input2int($arrData['levelShowCategories']) : 0;
        $intActive         = intval($arrData['levelActive']);
        $strPicture        = contrexx_addslashes(contrexx_strip_tags($arrData['levelImage']));

        $arrName           = $arrData['levelName'];
        $arrDescription    = $arrData['levelDescription'];

        $level->setParent($parentLevel);
        $level->setShowEntries($intShowEntries);
        $level->setShowSublevels($intShowSublevels);
        $level->setShowCategories($intShowCategories);
        $level->setActive($intActive);
        $level->setPicture($strPicture);

        if(empty($arrName[0])) {
            $arrName[0] = "[[".$_ARRAYLANG['TXT_MEDIADIR_NEW_LEVEL']."]]";
        }
        foreach ($this->arrFrontendLanguages as $arrLang) {
            $langId         = $arrLang['id'];
            $strName        = $arrName[$langId] ? $arrName[$langId] : $arrName[0];
            $strDescription = $arrDescription[$langId];

            $levelLocale = $level->getLocaleByLang($langId);
            if (!$levelLocale) {
                $levelLocale = new \Cx\Modules\MediaDir\Model\Entity\LevelLocale();
            }

            $levelLocale->setLevel($level);
            $levelLocale->setLangId($arrLang['id']);
            $levelLocale->setLevelName($strName);
            $levelLocale->setLevelDescription($strDescription);

            if (!$level->getId()) {
                $level->addLocale($levelLocale);
            }
        }
        $values = array(
            'level_order'       => $level->getOrder(),
            'show_sublevels'    => $level->getShowSublevels(),
            'show_categories'   => $level->getShowCategories(),
            'show_entries'      => $level->getShowEntries(),
            'picture'           => $level->getPicture(),
            'active'            => $level->getActive(),
        );
        if (!$level->getId()) {
            $intId = $this->levelNestedSet->createSubNode($level->getParent(), $values);
            if (!$intId) {
                return false;
            }
            $level->setId($intId);
        } else {
            if ($oldParentId != $level->getParent()) {
                $this->levelNestedSet->moveTree($level->getId(), $level->getParent(), NESE_MOVE_BELOW);
            }
            $this->levelNestedSet->updateNode($level->getId(), $values);
        }

        foreach ($level->getLocale() as $locale) {
            $this->saveLevelLocale($locale);
        }

        return true;
    }

    /**
     * Save the level locale information
     *
     * @param \Cx\Modules\MediaDir\Model\Entity\LevelLocale $locale
     */
    public function saveLevelLocale(\Cx\Modules\MediaDir\Model\Entity\LevelLocale $locale)
    {
        global $objDatabase;

        $objDatabase->Execute('
            INSERT INTO
                `' . DBPREFIX . 'module_' . $this->moduleTablePrefix . '_level_names`
            SET
                `lang_id`="' . intval($locale->getLangId()) . '",
                `level_id`="' . ($locale->getLevel() ? $locale->getLevel()->getId() : 0) . '",
                `level_name`="' . contrexx_raw2db($locale->getLevelName()) . '",
                `level_description`="' . contrexx_raw2db($locale->getLevelDescription()) . '"
            ON DUPLICATE KEY UPDATE
                `level_name`="' . contrexx_raw2db($locale->getLevelName()) . '",
                `level_description`="' . contrexx_raw2db($locale->getLevelDescription()) . '"
        ');
    }

    /**
     * Get the level entity by given id
     *
     * @param integer $id Level id
     *
     * @return mixed \Cx\Modules\MediaDir\Model\Entity\Level instance if loaded, false otherwise
     */
    public function getLevelById($id)
    {
        $levelArray = $this->levelNestedSet->pickNode($id, true);
        if (empty($levelArray)) {
            return false;
        }

        $level = $this->createLevelFromArray($levelArray);
        $this->loadLevelLocale($level);

        return $level;
    }

    /**
     * Loaded the locale information of level from the database
     *
     * @param \Cx\Modules\MediaDir\Model\Entity\Level $level
     */
    public function loadLevelLocale(\Cx\Modules\MediaDir\Model\Entity\Level $level)
    {
        global $objDatabase;

        $levelAttributes = $objDatabase->Execute('
            SELECT
                `lang_id` AS `lang_id`,
                `level_name` AS `name`,
                `level_description` AS `description`
            FROM
                `' . DBPREFIX . 'module_' . $this->moduleTablePrefix . '_level_names`
            WHERE
                `level_id` =' . $level->getId()
        );

        if ($levelAttributes !== false) {
            while (!$levelAttributes->EOF) {
                $locale = new \Cx\Modules\MediaDir\Model\Entity\LevelLocale();
                $locale->setLangId($levelAttributes->fields['lang_id']);
                $locale->setLevelName($levelAttributes->fields['name']);
                $locale->setLevelDescription($levelAttributes->fields['description']);
                $level->addLocale($locale);
                $levelAttributes->MoveNext();
            }
        }
    }


    /**
     * Create the level instance and set the info of level from the given array
     * 
     * @param array $input
     *
     * @return \Cx\Modules\MediaDir\Model\Entity\Level
     */
    public function createLevelFromArray($input)
    {
        $level = new \Cx\Modules\MediaDir\Model\Entity\Level();
        $level->setId($input['id']);
        $level->setParent($input['rootid']);
        $level->setOrder($input['norder']);
        $level->setLvl($input['level']);
        $level->setShowSublevels($input['show_sublevels']);
        $level->setShowCategories($input['show_categories']);
        $level->setShowEntries($input['show_entries']);
        $level->setPicture($input['picture']);
        $level->setActive($input['active']);

        return $level;
    }

    /**
     * Remove the Level from database
     *
     * @param integer $levelId Level id
     *
     * @return boolean True on success, false otherwise
     */
    function deleteLevel($levelId = null)
    {
        global $objDatabase;

        if (!$levelId) {
            return false;
        }
        $subcats = $this->levelNestedSet->getSubBranch($levelId, true);
        if (count($subcats) > 0) {
            foreach ($subcats as $subcat) {
                if (!$this->deleteLevel(intval($subcat['id']))) {
                    return false;
                }
            }
        }
        if (   $objDatabase->Execute('DELETE FROM '.DBPREFIX.'module_'.$this->moduleTablePrefix.'_level_names WHERE level_id='. contrexx_input2int($levelId))
            && $objDatabase->Execute('DELETE FROM '.DBPREFIX.'module_'.$this->moduleTablePrefix.'_rel_entry_levels WHERE level_id='. contrexx_input2int($levelId))
            && $this->levelNestedSet->deleteNode($levelId) !== false
        ) {
            return true;
        }
        return true;
    }

    /**
     * Update sorting order
     *
     * @param array $arrData
     *
     * @return boolean
     */
    function saveOrder($arrData)
    {
        $sorting = !empty($arrData['levelOrder']) ? $arrData['levelOrder'] : array();
        asort($sorting);
        $levels = array_keys($sorting);
        foreach($levels as $levelId) {
            $level = $this->levelNestedSet->pickNode($levelId);
            if (!$level) {
                continue;
            }
            $this->levelNestedSet->moveTree($levelId, $level->rootid, NESE_MOVE_BELOW);
        }
        return true;
    }

    /**
     * Get sublevels id
     *
     * @param Level $level
     *
     * @return array sublevel id's
     */
    public function getSubLevels(\Cx\Modules\MediaDir\Model\Entity\Level $level)
    {
        $levels    = array();
        $subLevels = $this->getSubLevelsByLevel($level);
        foreach ($subLevels as $subLevel) {
            $levels = array_merge(
                array($subLevel->getId()),
                $this->getSubLevels($subLevel)
            );
        }
        return $levels;
    }

    /**
     * Count number of entries for the level
     *
     * @param integer $intLevelId Level id
     *
     * @return integer
     */
    function countEntries($intLevelId = null)
    {
        global $objDatabase, $_LANGID;

        if (!$this->countEntries) {
            return 0;
        }

        $intLevelId = intval($intLevelId);
        if (!$intLevelId) {
            $level = $this->getLevelById($this->nestedSetRootId);
        } else {
            $level = $this->getLevelById($intLevelId);
        }

        if (!$level) {
            return 0;
        }

        $levels = array_merge(
            array($level->getId()),
            $this->getSubLevels($level)
        );
        $whereLevel = '';
        if ($levels) {
            $whereLevel = " AND `rel_levels`.`level_id` IN (" . implode(', ', contrexx_input2int($levels)) .")";
        }
        $objCountEntriesRS = $objDatabase->Execute("
                                                SELECT COUNT(`entry`.`id`) as c
                                                FROM
                                                        `" . DBPREFIX . "module_".$this->moduleTablePrefix."_entries` AS `entry`
                                                INNER JOIN
                                                    `".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_levels` AS `rel_levels`
                                                ON
                                                    `rel_levels`.`entry_id` = `entry`.`id`
                                                LEFT JOIN
                                                    `".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_inputfields` AS rel_inputfield
                                                ON
                                                    rel_inputfield.`entry_id` = `entry`.`id`
                                                WHERE
                                                    `entry`.`active` = 1
                                                AND
                                                    (rel_inputfield.`form_id` = entry.`form_id`)
                                                AND
                                                    (rel_inputfield.`field_id` = (".$this->getQueryToFindFirstInputFieldId()."))
                                                AND
                                                    (rel_inputfield.`lang_id` = '".$_LANGID."')
                                                AND ((`entry`.`duration_type`=2 AND `entry`.`duration_start` <= ".time()." AND `entry`.`duration_end` >= ".time().") OR (`entry`.`duration_type`=1))
                                                    " . $whereLevel . "
                                                GROUP BY
                                                    `rel_levels`.`level_id`");

        return contrexx_input2int($objCountEntriesRS->fields['c']);
    }
}
