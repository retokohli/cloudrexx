<?php

/**
 * News headlines
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @version 1.0.0
 * @package     contrexx
 * @subpackage  core_module_news
 * @todo        Edit PHP DocBlocks!
 */



require_once ASCMS_FRAMEWORK_PATH ."/Image.class.php";

/**
 * News headlines
 *
 * Gets all the news headlines
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @access public
 * @version 1.0.0
 * @package     contrexx
 * @subpackage  core_module_news
 */
class newsHeadlines
{
    public $_pageContent;
    public $_objTemplate;
    public $arrSettings = array();

    function __construct($pageContent)
    {
        $this->getSettings();
        $this->_pageContent = $pageContent;
        $this->_objTemplate = new HTML_Template_Sigma('.');
        CSRF::add_placeholder($this->_objTemplate);
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


    function getHomeHeadlines($catId=0)
    {
        global $_CORELANG, $objDatabase, $_LANGID;

        $objResult=0;
        $catId= intval($catId);

        $this->_objTemplate->setTemplate($this->_pageContent,true,true);
        $this->_objTemplate->setCurrentBlock('headlines_row');

        $newsLimit = intval($this->arrSettings['news_headlines_limit']);
        if ($newsLimit>50) { //limit to a maximum of 50 news
            $newsLimit=50;
        }

        if ($newsLimit<1) //do not get any news if 0 was specified as the limit.
        {
            $objResult=false;
        }
        else //fetch news
        { 
	    /*
SELECT contrexx_module_news.id AS id, contrexx_module_news.title AS title, date, teaser_image_path, teaser_image_thumbnail_path, teaser_text, redirect, firstname, lastname
FROM contrexx_module_news
INNER JOIN contrexx_access_user_profile ON userid = contrexx_access_user_profile.user_id
WHERE STATUS =1
AND teaser_only = '0'
AND lang =1
AND (
startdate <= '2010-10-11 13:54:50'
OR startdate = '0000-00-00 00:00:00'
	     */
            $objResult = $objDatabase->SelectLimit("
                SELECT ".DBPREFIX."module_news.id AS id, ".DBPREFIX."module_news.title AS title, date,
                       teaser_image_path, teaser_image_thumbnail_path,
                       teaser_text, redirect,
                       firstname, lastname
                  FROM ".DBPREFIX."module_news
                    INNER JOIN ".DBPREFIX."access_user_profile ON userid = ".DBPREFIX."access_user_profile.user_id
                  WHERE status=1".
                   ($catId > 0 ? " AND catid=$catId" : '')."
                   AND teaser_only='0'
                   AND lang=".$_LANGID."
                   AND (startdate<='".date('Y-m-d H:i:s')."' OR startdate='0000-00-00 00:00:00')
                   AND (enddate>='".date('Y-m-d H:i:s')."' OR enddate='0000-00-00 00:00:00')".
                   ($this->arrSettings['news_message_protection'] == '1' && !Permission::hasAllAccess()
                      ? (($objFWUser = FWUser::getFWUserObject()) && $objFWUser->objUser->login()
                          ? " AND (frontend_access_id IN (".
                            implode(',', array_merge(array(0), $objFWUser->objUser->getDynamicPermissionIds())).
                            ") OR userid=".$objFWUser->objUser->getId().") "
                          : " AND frontend_access_id=0 ")
                      : '').
                   "ORDER BY date DESC", $newsLimit);
        }

        if ($objResult !== false && $objResult->RecordCount() >= 0) {
        while (!$objResult->EOF) {
            $url = CONTREXX_SCRIPT_PATH;
        $newsid    = $objResult->fields['id'];
        $newstitle = htmlspecialchars(stripslashes($objResult->fields['title']), ENT_QUOTES, CONTREXX_CHARSET);
        $newsparam = 'section=news&amp;cmd=details';
	$name = htmlspecialchars(stripslashes($objResult->fields['firstname'] . " " . $objResult->fields['lastname']), ENT_QUOTES, CONTREXX_CHARSET);
        $news_link = (empty($objResult->fields['redirect']))
            ? '<a class="headlineLink" href="'.$url.'?'.$newsparam.'&amp;newsid='.$newsid.'" title="'.$newstitle.'">'.$newstitle.'</a>'
            : '<a class="headlineLink" href="'.$objResult->fields['redirect'].'" title="'.$newstitle.'">'.$newstitle.'</a>';
        if (!empty($objResult->fields['teaser_image_path'])) {
            $thumb_name = ImageManager::getThumbnailFilename(
                $objResult->fields['teaser_image_path']);
            if (!empty($objResult->fields['teaser_image_thumbnail_path'])) {
                $image = $objResult->fields['teaser_image_thumbnail_path'];
                    } elseif (file_exists(ASCMS_PATH.$thumb_name)) {
                $image = $thumb_name;
                    } else {
                $image = $objResult->fields['teaser_image_path'];
            }
                } else {
                    $image = "";
                }
                $this->_objTemplate->setVariable("HEADLINE_DATE", date(ASCMS_DATE_SHORT_FORMAT, $objResult->fields['date']));
                $this->_objTemplate->setVariable("HEADLINE_LINK", $news_link);
                $this->_objTemplate->setVariable("HEADLINE_IMAGE_PATH", $image);
                $this->_objTemplate->setVariable("HEADLINE_TEXT", nl2br($objResult->fields['teaser_text']));
                $this->_objTemplate->setVariable("HEADLINE_ID", intval($objResult->fields['id'])); 
                $this->_objTemplate->setVariable("HEADLINE_AUTHOR", $name);
                $this->_objTemplate->parseCurrentBlock();
                $objResult->MoveNext();
            }
        } else {
            $this->_objTemplate->hideBlock('headlines_row');
        }
        $this->_objTemplate->setVariable("TXT_MORE_NEWS", $_CORELANG['TXT_MORE_NEWS']);
        return $this->_objTemplate->get();
    }
}

?>
