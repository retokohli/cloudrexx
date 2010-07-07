<?php

/**
 * Banner management
 *
 * This module will get all the news pages
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core_module_banner
 * @todo        Edit PHP DocBlocks!
 */

require_once ASCMS_CORE_MODULE_PATH . '/banner/bannerLib.class.php';

/**
 * Banner
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core_module_banner
 */
class Banner extends bannerLibrary
{
    public $arrGroups = array();

    /**
     * PHP5 constructor
     * @param  string  $pageContent
     * @global string  $_LANGID
     * @access public
     */
    function __construct()
    {
        global $_LANGID;
        $this->_getBannerGroupStatus();
        $this->langId = $_LANGID;
    }


    /**
     * Initialized the banner group array
     *
     * @global    object     $objDatabase
     */
    function _getBannerGroupStatus()
    {
        global $objDatabase;
        $query = "SELECT id, status FROM ".DBPREFIX."module_banner_groups";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
            while (!$objResult->EOF) {
                $this->arrGroups[$objResult->fields['id']] = $objResult->fields['status'];
                $objResult->MoveNext();
            }
        }
    }


    /**
     * Get page
     * @access public
     * @global object $objDatabase
     * @return string bannerCode
     */
    function getBannerCode($groupId, $pageId)
    {
        global $objDatabase;

        $groupId = intval($groupId);
        $pageId  = intval($pageId);

        $debugMessage = '';

        if (!empty($this->arrGroups[$groupId])) {
            ///////////////////////////////////
            // The Banner group is active
            ///////////////////////////////////
            if (isset($_GET['teaserId'])) {
                $teaserId=intval($_GET['teaserId']);

                $query = "SELECT system.banner_code AS banner_code,
                                 system.id AS id
                            FROM ".DBPREFIX."module_banner_relations AS relation,
                                 ".DBPREFIX."module_banner_system AS system
                           WHERE relation.group_id = ".$groupId."
                             AND relation.page_id = ".$teaserId."
                             AND relation.banner_id = system.id
                             AND relation.type='teaser'
                             AND system.status=1";
            } elseif (isset($_GET['lid'])) {
                $levelId=intval($_GET['lid']);

                $query = "SELECT system.banner_code AS banner_code,
                                 system.id AS id
                            FROM ".DBPREFIX."module_banner_relations AS relation,
                                 ".DBPREFIX."module_banner_system AS system
                           WHERE relation.group_id = ".$groupId."
                             AND relation.page_id = ".$levelId."
                             AND relation.banner_id = system.id
                             AND relation.type='level'
                             AND system.status=1";
            } elseif (isset($_GET['section']) 
                    && $_GET['section'] == 'blog' 
                    && isset($_GET['cmd'])
                    && (
                        $_GET['cmd'] == 'details' 
                        ||
                        (
                            $_GET['cmd'] == 'search'
                            &&
                            isset($_GET['category'])
                        )
                    )
            ) {
                if ($_GET['cmd'] == 'details') {
                    $id = intval($_GET['id']); 
                    $query = "
                        SELECT
                            `category_id`
                        FROM
                            `".DBPREFIX."module_blog_message_to_category` 
                        WHERE
                            `message_id` = ".$id;

                    $res = $objDatabase->execute($query);
                    $arr = array();
                    while (!$res->EOF) {
                        $arr[] = $res->fields['category_id'];
                        $res->MoveNext();
                    }
                    $arr[] = 0; // do this so the query doesn't fail if there 
                    //are no categories

                    $list = implode(', ', $arr);
                    $query = "SELECT system.banner_code AS banner_code,
                                     system.id AS id
                                FROM ".DBPREFIX."module_banner_relations AS relation,
                                     ".DBPREFIX."module_banner_system AS system
                               WHERE relation.group_id = ".$groupId."
                                 AND relation.page_id IN ( ".$list.")
                                 AND relation.banner_id = system.id
                                 AND relation.type='blog'
                                 AND system.status=1";
                } elseif ($_GET['cmd'] == 'search' && isset($_GET['category'])) {
                    $category = intval($_GET['category']);

                    $query = "SELECT system.banner_code AS banner_code,
                                     system.id AS id
                                FROM ".DBPREFIX."module_banner_relations AS relation,
                                     ".DBPREFIX."module_banner_system AS system
                               WHERE relation.group_id = ".$groupId."
                                 AND relation.page_id = ".$category."
                                 AND relation.banner_id = system.id
                                 AND relation.type='blog'
                                 AND system.status=1";
                }
            } else {
                $query = "SELECT system.banner_code AS banner_code,
                                 system.id AS id
                            FROM ".DBPREFIX."module_banner_relations AS relation,
                                 ".DBPREFIX."module_banner_system AS system
                           WHERE relation.group_id = ".$groupId."
                             AND relation.page_id = ".$pageId."
                             AND relation.banner_id = system.id
                             AND relation.type='content'
                             AND system.status=1";
            }

            $objResult = $objDatabase->Execute($query);
            $counBanner = $objResult->RecordCount();

            if ($objResult !== false && $counBanner>=1) {
                $arrRandom = array();

                while (!$objResult->EOF) {
                    $arrRandom[$objResult->fields['id']] = stripslashes($objResult->fields['banner_code']);
                    $objResult->MoveNext();
                }

                $ranId = @array_rand($arrRandom, 1);

                $this->updateViews($ranId);

                $bannerCode = $arrRandom[$ranId];
                $bannerCode = str_replace('<a ', '<a onclick="bannerClicks(\''.$ranId.'\')" ', $bannerCode);

                return $debugMessage.$bannerCode;
            } else {
                ///////////////////////////////////
                // show the default banner for this group
                ///////////////////////////////////
                $query = "SELECT id, banner_code FROM ".DBPREFIX."module_banner_system WHERE parent_id = ".$groupId." AND is_default=1 AND status=1";
                $objResult = $objDatabase->SelectLimit($query, 1);
                if ($objResult !== false) {

                    $this->updateViews($objResult->fields['id']);

                    $bannerCode = $bannerCode = stripslashes($objResult->fields['banner_code']);
                    $bannerCode = str_replace('<a ', '<a onclick="bannerClicks(\''.$objResult->fields['id'].'\')" ', $bannerCode);

                    return $debugMessage.$bannerCode;
                }
            }
        //} else {
            ///////////////////////////////////
            // The Banner group is inactive
            ///////////////////////////////////
        }
        return $debugMessage;
    }


    function updateViews($bannerId)
    {
        global $objDatabase;

        $objDatabase->Execute("
            UPDATE ".DBPREFIX."module_banner_system
               SET views=views+1
             WHERE id=$bannerId
        ");
    }


    function updateClicks($bannerId)
    {
        global $objDatabase;
        $objDatabase->Execute("
            UPDATE ".DBPREFIX."module_banner_system
               SET clicks=clicks+1
             WHERE id=$bannerId
        ");
    }


    function getBannerJS()
    {
        return "
<script language='JavaScript'>
<!--

function bannerClicks(bannerId)
{
    img=document.createElement('img');
    img.src='?bannerId='+bannerId;
    img='';
}

//-->
</script>";
    }

}

?>
