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
 * Gallery home content
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Cloudrexx Development Team
 * @version     1.0.0
 * @package     cloudrexx
 * @subpackage  module_gallery
 * @todo        Edit PHP DocBlocks!
 */
namespace Cx\Modules\Gallery\Controller;
/**
 * Gallery home content
 *
 * Show Gallery Block Content (Random, Last)
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Cloudrexx Development Team
 * @access      public
 * @version     1.0.0
 * @package     cloudrexx
 * @subpackage  module_gallery
 */
class GalleryHomeContent extends GalleryLibrary
{
    public $_intLangId;
    public $_strWebPath;

    /**
     * Constructor php5
     */
    function __construct() {
        global $_LANGID;

        $this->getSettings();
        $this->_intLangId   = $_LANGID;
        $this->_strWebPath  = ASCMS_GALLERY_THUMBNAIL_WEB_PATH . '/';
    }


    /**
     * Check if the random-function is activated
     * @return boolean
     */
    function checkRandom() {
        if ($this->arrSettings['show_random'] == 'on') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if the latest-function is activated
     *
     * @return boolean
     */
    function checkLatest() {
        if ($this->arrSettings['show_latest'] == 'on') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get image ids by language id
     *
     * @param integer $langId language id
     *
     * @return array
     */
    public function getImageIdsByLang($langId)
    {
        if (empty($langId)) {
            $langId = $this->_intLangId;
        }
        $objDatabase = \Cx\Core\Core\Controller\Cx::instanciate()
            ->getDb()->getAdoDb();
        $objFWUser   = \FWUser::getFWUserObject();
        $where       = '';

        if (!$objFWUser->objUser->login()) {
            $where = ' AND `categories`.`frontendProtected` = 0';
        }

        if (
            $objFWUser->objUser->login() &&
            !$objFWUser->objUser->getAdminStatus()
        ) {
            $where = ' AND (`categories`.`frontendProtected` = 0' .
                (count($objFWUser->objUser->getDynamicPermissionIds())
                    ? ' OR `categories`.`frontend_access_id` IN (' .
                        implode(
                            ', ',
                            $objFWUser->objUser->getDynamicPermissionIds()
                        ) . ')'
                    : ''
                ) . ')';
        }
        $query = '
            SELECT `pics`.`id` as picId
                FROM  `' . DBPREFIX . 'module_gallery_categories` AS categories
                INNER JOIN `' . DBPREFIX . 'module_gallery_pictures` AS pics
                    ON `pics`.`catid` = `categories`.`id`
                INNER JOIN `' . DBPREFIX . 'module_gallery_language_pics` AS lang
                    ON `lang`.`picture_id` = `pics`.`id`
                WHERE `categories`.`status` = "1"
                    AND `pics`.`validated`  = "1"
                    AND `pics`.`status`     = "1"
                    AND `lang`.`lang_id`    = ' . $langId . $where . '
                ORDER BY `pics`.`sorting`';
        $objResult = $objDatabase->Execute($query);
        $entryIds  = array();
        if ($objResult && $objResult->RecordCount()) {
            while (!$objResult->EOF) {
                $entryIds[] = $objResult->fields['picId'];
                $objResult->MoveNext();
            }
        }
        return $entryIds;
    }

    /**
     * Get image by parameter
     *
     * @param integer $id     image id
     * @param integer $langId language id
     *
     * @return string
     */
    public function getImage($id, $langId)
    {
        if (empty($id)) {
            return;
        }

        if (empty($langId)) {
            $langId = $this->_intLangId;
        }

        $objDatabase = \Cx\Core\Core\Controller\Cx::instanciate()
            ->getDb()->getAdoDb();
        $query = '
            SELECT `pics`.`catid` AS catId,
                   `pics`.`path` AS path,
                   `lang`.`name` AS name
                FROM `' . DBPREFIX . 'module_gallery_categories` AS categories
                LEFT JOIN `' . DBPREFIX . 'module_gallery_pictures` AS pics
                    ON `pics`.`catid` = `categories`.`id`
                LEFT JOIN  `' . DBPREFIX . 'module_gallery_language_pics` AS lang
                    ON `pics`.`id` = `lang`.`picture_id`
                WHERE `pics`.`validated` = "1"
                    AND `pics`.`status`  = "1"
                    AND `pics`.`id`      = ' . contrexx_input2db($id) . '
                    AND `lang`.`lang_id` = ' . contrexx_input2db($langId);
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->RecordCount() == 0) {
            return '';
        }

        $pictures = $objDatabase->Execute(
            'SELECT `id`
                FROM `' . DBPREFIX . 'module_gallery_pictures`
                WHERE   `status`    = "1"
                    AND `validated` = "1"
                    AND `catid`     = ' . $objResult->fields['catId'] . '
                ORDER BY `sorting`'
        );
        $position = 0;
        if ($pictures && $pictures->RecordCount()) {
            while (!$pictures->EOF) {
                if ($pictures->fields['id'] == $id) {
                    break;
                }
                $position++;
                $pictures->MoveNext();
            }
        }
        $paging = $objDatabase->getOne(
            'SELECT `value`
                FROM `' . DBPREFIX . 'module_gallery_settings`
                WHERE `name` = "paging"'
        );
        $pos = 0;
        if ($position > $paging) {
            $pageNum = ceil($position / $paging);
            $pos     = (($pageNum - 1) * $paging);
        }
        if ($position == $paging) {
            $pos = $position;
        }
        $url = \Cx\Core\Routing\Url::fromModuleAndCmd(
            'Gallery',
            '',
            '',
            array('cid' => $objResult->fields['catId'], 'pos' => $pos)
        )->toString();
        $imgName = contrexx_raw2xhtml($objResult->fields['name']);
        $image   = \Html::getImageByPath(
            '/' . $this->_strWebPath . $objResult->fields['path'],
            'alt="' . $imgName . '" title="' . $imgName . '"'
        );

        return \Html::getLink($url, $image, '_self');
    }


    /**
     * Returns the last inserted image from database
     *
     * @param integer $langId language id
     *
     * @return string Complete <img>-tag for a randomized image
     */
    function getLastImage($langId)
    {
        global $objDatabase;

        if (empty($langId)) {
            $langId = $this->_intLangId;
        }

        $picNr = 0;
        $objResult = $objDatabase->Execute(
            'SELECT `pics`.`id`,
                    `pics`.`catid` AS CATID,
                    `pics`.`path` AS PATH,
                    `lang`.`name` AS NAME
                FROM `' . DBPREFIX . 'module_gallery_pictures` AS pics
                INNER JOIN `' . DBPREFIX . 'module_gallery_language_pics` AS lang
                    ON `pics`.`id` = `lang`.`picture_id`
                INNER JOIN `' . DBPREFIX . 'module_gallery_categories` AS categories
                    ON `pics`.`catid` = `categories`.`id`
                WHERE   `categories`.`status` = "1"
                    AND `pics`.`validated` = "1"
                    AND `pics`.`status` = "1"
                    AND `lang`.`lang_id` = ' . $langId . '
                ORDER BY `pics`.`id` DESC
                LIMIT 1'
        );

        if ($objResult->RecordCount() == 0) {
            return '';
        }

        $paging = $objDatabase->getOne(
            'SELECT `value`
                FROM `' . DBPREFIX . 'module_gallery_settings`
                 WHERE `name` = "paging"'
        );

        $objPos = $objDatabase->Execute(
            'SELECT `pics`.`id`
                FROM `' . DBPREFIX . 'module_gallery_pictures` AS pics
                INNER JOIN `' . DBPREFIX . 'module_gallery_language_pics` AS lang
                    ON `pics`.`id` = `lang`.`picture_id`
                INNER JOIN `' . DBPREFIX . 'module_gallery_categories` AS categories
                    ON `pics`.`catid` = `categories`.`id`
                WHERE   `categories`.`status` = "1"
                    AND `pics`.`validated` = "1"
                    AND `pics`.`status` = "1"
                    AND `categories`.`id` = ' . $objResult->fields['CATID'] . '
                    AND `lang`.`lang_id` = ' . $langId . '
                ORDER BY `pics`.`sorting`'
        );
        if ($objPos !== false) {
            while (!$objPos->EOF) {
                if ($objPos->fields['id'] == $objResult->fields['id']) {
                    break;
                } else {
                    $picNr++;
                }
                $objPos->MoveNext();
            }
        }

        $pos = 0;
        if ($picNr > $paging) {
            $pageNum = ceil($picNr / $paging);
            $pos     = (($pageNum - 1) * $paging);
        }
        if ($picNr == $paging) {
            $pos = $picNr;
        }
        $url = \Cx\Core\Routing\Url::fromModuleAndCmd(
            'Gallery',
            '',
            '',
            array('cid' => $objResult->fields['CATID'], 'pos' => $pos)
        )->toString();
        $imgName = contrexx_raw2xhtml($objResult->fields['NAME']);
        $image   = \Html::getImageByPath(
            '/' . $this->_strWebPath . $objResult->fields['PATH'],
            'alt="' . $imgName . '" title="' . $imgName . '"'
        );

        return \Html::getLink($url, $image, '_self');
    }
}

