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
 * Top news
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author Cloudrexx Development Team <info@cloudrexx.com>
 * @version 1.0.0
 * @package     cloudrexx
 * @subpackage  coremodule_news
 * @todo        Edit PHP DocBlocks!
 */

namespace Cx\Core_Modules\News\Controller;

/**
 * Top news
 *
 * Gets all the top news
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author Cloudrexx Development Team <info@cloudrexx.com>
 * @access public
 * @version 1.0.0
 * @package     cloudrexx
 * @subpackage  coremodule_news
 */
class NewsTop extends \Cx\Core_Modules\News\Controller\NewsLibrary
{
    public $_pageContent;
    public $_objTemplate;
    public $arrSettings = array();

    function __construct($pageContent)
    {
        parent::__construct();
        $this->getSettings();
        $this->_pageContent = $pageContent;
        $this->_objTemplate = new \Cx\Core\Html\Sigma('.');
        \Cx\Core\Csrf\Controller\Csrf::add_placeholder($this->_objTemplate);
    }


    function getSettings()
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            SELECT name, value FROM ".DBPREFIX."module_news_settings");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $this->arrSettings[$objResult->fields['name']] = $objResult->fields['value'];
                $objResult->MoveNext();
            }
        }
    }


    function getHomeTopNews($catId=0)
    {
        global $_CORELANG, $objDatabase;

        $catId= intval($catId);
        $i = 0;

        $this->_objTemplate->setTemplate($this->_pageContent,true,true);
        if ($this->_objTemplate->blockExists('newsrow')) {
            $this->_objTemplate->setCurrentBlock('newsrow');
        } else {
            return null;
        }

        $newsLimit = intval($this->arrSettings['news_top_limit']);
        if ($newsLimit>50) { //limit to a maximum of 50 news
            $newsLimit=50;
        }

        if ($newsLimit<1) { //do not get any news if 0 was specified as the limit.
            $objResult=false;
        } else {//fetch news
            $objResult = $objDatabase->SelectLimit("
                SELECT DISTINCT(tblN.id) AS newsid,
                       tblN.`date` AS newsdate,
                       tblN.teaser_image_path,
                       tblN.teaser_image_thumbnail_path,
                       tblN.redirect,
                       tblN.publisher,
                       tblN.publisher_id,
                       tblN.author,
                       tblN.author_id,
                       tblN.allow_comments AS commentactive,
                       tblN.source,
                       tblN.url1,
                       tblN.url2,
                       tblN.changelog,
                       tblN.enable_tags,
                       tblL.text NOT REGEXP '^(<br type=\"_moz\" />)?\$' AS newscontent,
                       tblL.text AS text,
                       tblL.title AS newstitle,
                       tblL.teaser_text
                  FROM ".DBPREFIX."module_news AS tblN
            INNER JOIN ".DBPREFIX."module_news_locale AS tblL ON tblL.news_id=tblN.id
            INNER JOIN ".DBPREFIX."module_news_rel_categories AS tblC ON tblC.news_id=tblL.news_id
                  WHERE tblN.status=1".
                   ($catId > 0 ? " AND tblC.category_id=$catId" : '')."
                   AND tblN.teaser_only='0'
                   AND tblL.lang_id=".FRONTEND_LANG_ID."
                   AND (startdate<='".date('Y-m-d H:i:s')."' OR startdate='0000-00-00 00:00:00')
                   AND (enddate>='".date('Y-m-d H:i:s')."' OR enddate='0000-00-00 00:00:00')".
                   ($this->arrSettings['news_message_protection'] == '1' && !\Permission::hasAllAccess()
                      ? (($objFWUser = \FWUser::getFWUserObject()) && $objFWUser->objUser->login()
                          ? " AND (frontend_access_id IN (".
                            implode(',', array_merge(array(0), $objFWUser->objUser->getDynamicPermissionIds())).
                            ") OR userid=".$objFWUser->objUser->getId().") "
                          : " AND frontend_access_id=0 ")
                      : '').
                   "ORDER BY
                       (SELECT COUNT(*) FROM ".DBPREFIX."module_news_stats_view WHERE news_id=tblN.id AND time>'".date_format(date_sub(date_create('now'), date_interval_create_from_date_string(intval($this->arrSettings['news_top_days']).' day')), 'Y-m-d H:i:s')."') DESC", $newsLimit);
        }

        if ($objResult !== false && $objResult->RecordCount()) {
            while (!$objResult->EOF) {
                //Parse all the news placeholders
                $this->parseNewsPlaceholders($this->_objTemplate, $objResult, array($catId));
                $this->_objTemplate->setVariable(array(
                    'NEWS_CSS' => 'row'.($i % 2 + 1),
                ));
                $this->_objTemplate->parseCurrentBlock();
                $i++;
                $objResult->MoveNext();
            }
        } else {
            $this->_objTemplate->hideBlock('newsrow');
        }
        $this->_objTemplate->setVariable("TXT_MORE_NEWS", $_CORELANG['TXT_MORE_NEWS']);
        return $this->_objTemplate->get();
    }
}

