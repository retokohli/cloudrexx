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

use Cx\Modules\MediaDir\Model\Entity\Level as Level;

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
     * @var Cx\Modules\MediaDir\Model\Repository\LevelRepository
     */
    public $levelRepository;

    /**
     * Default Constructor
     */
    function __construct($name)
    {
        parent::__construct('.', $name);
        $this->getSettings();
        $this->getFrontendLanguages();

        if ($this->arrSettings['settingsCountEntries'] == 1 || $this->cx->getMode() == \Cx\Core\Core\Controller\Cx::MODE_BACKEND) {
            $this->countEntries = true;
        }

        $this->levelRepository = $this->em->getRepository('Cx\Modules\MediaDir\Model\Entity\Level');
    }

    /**
     * Parse the level details
     *
     * @param \Cx\Core\Html\Sigma   $objTpl          Instance of template object
     * @param Level                 $level           Instance of level to parse
     * @param string                $strLevelIcon    String level icon
     * @param string                $strLevelClass   Class name for the level
     * @param string                $blockName       Parse block name
     */
    public function parseLevelDetail(
        \Cx\Core\Html\Sigma $objTpl,
        Level $level,
        $strLevelIcon = null,
        $strLevelClass = 'inactive',
        $blockName = 'LevelsList'
    ) {
        $intSpacerSize = ($level->getLvl() - 1) * 21;
        $spacer        = '<img src="../core/Core/View/Media/icons/pixel.gif" border="0" width="'.$intSpacerSize.'" height="11" alt="" />';

        $levelDesc = $this->getLevelDescription($level);
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
            $this->moduleLangVar.'_LEVEL_LEVEL_ID'             => $level->getLvl() - 1,
            $this->moduleLangVar.'_LEVEL_ACTIVE_STATUS'        => $strLevelClass,
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
     */
    public function parseLevelTree(
        \Cx\Core\Html\Sigma $objTpl,
        Level $level,
        $expandedLevelIds = array(),
        $expandAll = false,
        $checkShowSubLevel = false
    ) {
        $subLevels = $this->getSubLevelsByLevel($level);
        foreach ($subLevels as $sublevel) {
            $hasChildren = !\FWValidator::isEmpty($this->getSubLevelsByLevel($sublevel));
            $isExpanded  = (!$checkShowSubLevel || $sublevel->getShowSublevels())
                           && ($expandAll || in_array($sublevel->getId(), $expandedLevelIds));

            $strLevelClass = 'inactive';
            if ($hasChildren) {
                if ($isExpanded) {
                    $expLvlId = $sublevel->getParent()->getId();
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
            $this->parseLevelDetail($objTpl, $sublevel, $strLevelIcon, $strLevelClass);

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

                $this->parseLevelTree(
                    $objTpl,
                    $this->levelRepository->getRoot(),
                    $expandedLevelIds,
                    $expandLevel == 'all'
                );
                break;
            case 2:
                //Frontend View
                $intNumBlocks = count($arrExistingBlocks);
                $strIndexHeader = '';

                $i = $this->arrSettings['settingsLevelOrder'] == 2
                        ? $intNumBlocks - 1
                        : 0;

                //set first index header
                if($this->arrSettings['settingsLevelOrder'] == 2) {
                    $strFirstIndexHeader = null;
                }

                $level = $this->levelRepository->findOneById($parentLevelId);
                if (!$level) {
                    $level = $this->levelRepository->getRoot();
                }
                $intNumLevels = $this->levelRepository->getChildCount($level, true);

                if ($intNumLevels % $intNumBlocks != 0) {
                    $intNumLevels = $intNumLevels + ($intNumLevels % $intNumBlocks);
                }

                $intNumPerRow = intval($intNumLevels / $intNumBlocks);
                $x = 0;

                $subLevels = $this->getSubLevelsByLevel($level);
                foreach ($subLevels as $subLevel) {
                    $strLevelId        = isset($_GET['lid']) ? "&amp;lid=".intval($_GET['lid']) : '';

                    $strIndexHeaderTag = null;
                    if($this->arrSettings['settingsLevelOrder'] == 2) {
                        $strIndexHeader = strtoupper(substr($arrLevel['levelName'][0],0,1));

                        if ($strFirstIndexHeader != $strIndexHeader) {
                            $i = $i < $intNumBlocks - 1 ? $i + 1 : 0;
                            $strIndexHeaderTag = '<span class="' . $this->moduleNameLC . 'LevelLevelIndexHeader">' . $strIndexHeader . '</span><br />';
                        }
                    } else {
                        if ($x == $intNumPerRow) {
                            ++$i;

                            if ($i == $intNumBlocks) {
                                $i = 0;
                            }

                            $x = 1;
                        } else {
                            $x++;
                        }
                    }

                    //get ids
                    $strLevelCmd = isset($_GET['cmd'])
                                        ? '&amp;cmd='.$_GET['cmd']
                                        : '';

                    $childrenString = $this->createLevelsTree(
                        $subLevel,
                        $strLevelCmd,
                        $strLevelId
                    );

                    $levelName = $this->getLevelName($subLevel);
                    $levelDesc = $this->getLevelDescription($subLevel);
                    //parse variables
                    $objTpl->setVariable(array(
                        $this->moduleLangVar.'_CATEGORY_LEVEL_ID'             => $subLevel->getId(),
                        $this->moduleLangVar.'_CATEGORY_LEVEL_NAME'           => contrexx_raw2xhtml($levelName),
                        $this->moduleLangVar.'_CATEGORY_LEVEL_LINK'           => $strIndexHeaderTag.'<a href="index.php?section='.$this->moduleName.$strLevelCmd.$strLevelId.'&amp;cid='.$subLevel->getId().'">'.contrexx_raw2xhtml($levelName).'</a>',
                        $this->moduleLangVar.'_CATEGORY_LEVEL_DESCRIPTION'    => contrexx_raw2xhtml($levelDesc),
                        $this->moduleLangVar.'_CATEGORY_LEVEL_PICTURE'        => '<img src="'.$subLevel->getPicture().'" border="0" alt="'.contrexx_raw2xhtml($levelName).'" />',
                        $this->moduleLangVar.'_CATEGORY_LEVEL_PICTURE_SOURCE' => $subLevel->getPicture(),
                        $this->moduleLangVar.'_CATEGORY_LEVEL_NUM_ENTRIES'    => $this->countEntries($level->getId()),
                        $this->moduleLangVar.'_CATEGORY_LEVEL_CHILDREN'       => $childrenString,
                    ));

                    $intBlockId = $arrExistingBlocks[$i];


                    $objTpl->parse($this->moduleNameLC.'LevelsLevels_row_'.$intBlockId);
                    $objTpl->clearVariables();

                    $strFirstIndexHeader = $strIndexHeader;
                }
                break;
            case 3:
                //Dropdown Menu
                return $this->getLevelDropDown($this->levelRepository->getRoot(), $levelId);
                break;
            case 4:
                //level Selector (modify view)
                $arrSelectedLevels = $this->getSelectedLevelsByEntryId($intEntryId);
                list($selectedOptions, $notSelectedOptions) = $this->getLevelOptions4EditView($this->levelRepository->getRoot(), $arrSelectedLevels);
                $arrSelectorOptions = array(
                  'selected'     => $selectedOptions,
                  'not_selected' => $notSelectedOptions
                );
                return $arrSelectorOptions;
                break;
            case 5:
                //Frontend View Detail
                $level  = $this->levelRepository->findOneById($levelId);
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
                    $this->moduleLangVar.'_CATEGORY_LEVEL_LINK'           => '<a href="index.php?section='.$this->moduleName.$strLevelId.'&amp;cid='.$level->getId().'">'.contrexx_raw2xhtml($levelName).'</a>',
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

                $level = $this->levelRepository->findOneById($levelId);
                if (!$level) {
                    $level = $this->levelRepository->getRoot();
                }

                $tpl = <<<TEMPLATE
    <!-- BEGIN {$this->moduleNameLC}LevelsList -->
    <li class="level_{{$this->moduleLangVar}_LEVEL_ID}">
        <a href="index.php?section={$this->moduleName}{$strLevelId}&amp;cid={{$this->moduleLangVar}_LEVEL_ID}" class="{{$this->moduleLangVar}_LEVEL_ACTIVE_STATUS}">
            {{$this->moduleLangVar}_LEVEL_NAME}
        </a>
    </li>
    <!-- END {$this->moduleNameLC}LevelsList -->
TEMPLATE;
                $template = new \Cx\Core\Html\Sigma('.');
                $template->setTemplate($tpl);
                $this->parseLevelTree(
                    $template,
                    $level,
                    $expandedLevelIds,
                    $expandLevel == 'all',
                    $arrLevel['catShowSublevels'] == 1
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
    public function getLevelOptions4EditView(Level $level, $arrSelectedLevels)
    {
        $subLevels = $this->getSubLevelsByLevel($level);
        $strSelectedOptions = $strNotSelectedOptions = '';
        foreach ($subLevels as $subLevel) {
            $spacer       = str_repeat('----', $subLevel->getLvl() - 1);
            $spacer      .= $subLevel->getLvl() > 0 ? '&nbsp;' : '';
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
    public function getLevelName(Level $level)
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
    public function getLevelDescription(Level $level)
    {
        global $_LANGID;

        $locale = $level->getLocaleByLang($_LANGID);
        return $locale ? $locale->getLevelDescription() : '';
    }

    /**
     * Get settings sorting field
     *
     * @return string
     */
    public function getSortingField()
    {
        switch ($this->arrSettings['settingsLevelOrder']) {
            case 0;
                $sortOrder = 'node.order';
                break;
            case 1;
            case 2;
                $sortOrder = 'lc.level_name';
                break;
        }
        return $sortOrder;
    }

    /**
     * Get sub levels of a level, by applying the sorting and status
     *
     * @param Level $level
     *
     * @return array Array collection of sublevels
     */
    public function getSubLevelsByLevel(Level $level)
    {
        global $_LANGID;

        return $this->levelRepository->getChildren(
            $level,
            $_LANGID,
            $this->cx->getMode() == \Cx\Core\Core\Controller\Cx::MODE_FRONTEND,
            $this->getSortingField()
        );
    }

    /**
     * Get the level dropdown
     *
     * @param Level $level
     *
     * @return string
     */
    public function getLevelDropDown(Level $level, $selectedLevelId = null)
    {
        $strDropdownOptions = '';
        $subLevels          = $this->getSubLevelsByLevel($level);
        foreach ($subLevels as $subLevel) {
            $strSelected  = $selectedLevelId == $subLevel->getId() ? 'selected="selected"' : '';
            $spacer       = str_repeat('----', $subLevel->getLvl() - 1);
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
        $level = $this->levelRepository->findOneById($levelId);
        if (!$level) {
            return array();
        }
        $levels = array($level);
        while ($level = $level->getParent()) {
            $levels[] = $level;
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
    function saveLevel(Level $level, $arrData)
    {
        global $_ARRAYLANG;

        $parentId  = intval($arrData['levelPosition']);

        $parentLevel = null;
        if ($parentId) {
            $parentLevel = $this->levelRepository->findOneById($parentId);
        } else {
            $parentLevel = $this->levelRepository->getRoot();
        }
        if (!$parentLevel) {
            return false;
        }

        $intId    = $level->getId();

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

        if (empty($intId)) {
            $level->setOrder(0);
            $this->em->persist($level);
        }

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
            $this->em->persist($levelLocale);
        }
        $this->em->flush();

        return true;
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
        if ($levelId == null) {
            return false;
        }
        $level = $this->levelRepository->findOneById($levelId);
        $this->em->remove($level);
        $this->em->flush();

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
        foreach ($arrData['levelOrder'] as $levelId => $levelOrder) {
            $level = $this->levelRepository->findOneById($levelId);
            if (!$level) {
                continue;
            }
            $level->setOrder(contrexx_input2int($levelOrder));
        }
        $this->em->flush();
        $this->levelRepository->reorder($this->levelRepository->getRoot(), 'order');

        return true;
    }

    /**
     * Get sublevels id
     *
     * @param Level $level
     *
     * @return array sublevel id's
     */
    public function getSubLevels(Level $level)
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
            $level = $this->levelRepository->getRoot();
        } else {
            $level = $this->levelRepository->findOneById($intLevelId);
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

    /**
     * Create UL/LI of level tree
     *
     * @param Level  $level       Instance of level id
     * @param string    $strLevelCmd Url level id
     * @param string    $strLevelId     Url level id
     *
     * @return string
     */
    public function createLevelsTree(Level $level, $strLevelCmd, $strLevelId)
    {
        $subLevels = $this->getSubLevelsByLevel($level);
        if (empty($subLevels)) {
            return '';
        }
        $childrenString = '<ul>';
        foreach ($subLevels as $subLevel) {
            $levelName = $this->getLevelName($subLevel);

            $childrenString .= '<li><a href="index.php?section=' . $this->moduleName . $strLevelCmd . $strLevelId . '&amp;cid=' . $subLevel->getId() . '">' . contrexx_raw2xhtml($levelName) . '</a>';
            $childrenString .= $this->createLevelsTree($subLevel, $strLevelCmd, $strLevelId);
            $childrenString .= '</li>';
        }
        $childrenString .= '</ul>';

        return $childrenString;
    }
}
