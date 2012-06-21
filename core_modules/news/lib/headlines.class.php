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

/**
 * @ignore
 */
require_once ASCMS_CORE_MODULE_PATH . '/news/lib/newsLib.class.php';

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
class newsHeadlines extends newsLibrary
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

        $i = 0;
        $catId= intval($catId);

        $this->_objTemplate->setTemplate($this->_pageContent,true,true);
        $this->_objTemplate->setCurrentBlock('headlines_row');

        $newsLimit = intval($this->arrSettings['news_headlines_limit']);
        if ($newsLimit>50) { //limit to a maximum of 50 news
            $newsLimit=50;
        }

        if ($newsLimit<1) { //do not get any news if 0 was specified as the limit.
            $objResult=false;
        } else {//fetch news
            $objResult = $objDatabase->SelectLimit("
                SELECT tblN.id AS id,
                       tblN.`date`, 
                       tblN.teaser_image_path,
                       tblN.teaser_image_thumbnail_path,
                       tblN.redirect,
                       tblN.publisher,
                       tblN.publisher_id,
                       tblN.author,
                       tblN.author_id,
                       tblL.title AS title, 
                       tblL.teaser_text
                  FROM ".DBPREFIX."module_news AS tblN
            INNER JOIN ".DBPREFIX."module_news_locale AS tblL ON tblL.news_id=tblN.id
                  WHERE tblN.status=1".
                   ($catId > 0 ? " AND tblN.catid=$catId" : '')."
                   AND tblN.teaser_only='0'
                   AND tblL.lang_id=".$_LANGID."
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
                $newsid    = $objResult->fields['id'];
                $newstitle = $objResult->fields['title'];
                $newsUrl    = empty($objResult->fields['redirect'])
                                ? CONTREXX_SCRIPT_PATH.'?section=news&cmd=details&newsid='.$newsid
                                : $objResult->fields['redirect'];
                $htmlLink   = self::parseLink($newsUrl, $newstitle, contrexx_raw2xhtml($newstitle), 'headlineLink');

                list($image, $htmlLinkImage, $imageSource) = self::parseImageThumbnail($objResult->fields['teaser_image_path'],
                                                                                       $objResult->fields['teaser_image_thumbnail_path'],
                                                                                       $newstitle,
                                                                                       $newsUrl);

                $author     = FWUser::getParsedUserTitle($objResult->fields['author_id'], $objResult->fields['author']);
                $publisher  = FWUser::getParsedUserTitle($objResult->fields['publisher_id'], $objResult->fields['publisher']);

                $this->_objTemplate->setVariable(array(
                    'NEWS_ID'           => $newsid,
                    'NEWS_CSS'          => 'row'.($i % 2 + 1),
                    'NEWS_DATE'         => date(ASCMS_DATE_SHORT_FORMAT, $objResult->fields['date']),
                    'NEWS_LONG_DATE'    => date(ASCMS_DATE_FORMAT, $objResult->fields['date']),
                    'NEWS_TITLE'        => contrexx_raw2xhtml($newstitle),
                    'NEWS_TEASER'       => nl2br($objResult->fields['teaser_text']),
                    'NEWS_LINK'         => $htmlLink,
                    'NEWS_LINK_URL'     => contrexx_raw2xhtml($newsUrl),
                    'NEWS_AUTHOR'       => contrexx_raw2xhtml($author),
                    'NEWS_PUBLISHER'    => contrexx_raw2xhtml($publisher),

                    // Backward compatibility for templates pre 3.0
                    'HEADLINE_ID'       => $newsid,
                    'HEADLINE_DATE'     => date(ASCMS_DATE_SHORT_FORMAT, $objResult->fields['date']),
                    'HEADLINE_TEXT'     => nl2br($objResult->fields['teaser_text']),
                    'HEADLINE_LINK'     => $htmlLink,
                    'HEADLINE_AUTHOR'   => contrexx_raw2xhtml($author),
                ));

                if (!empty($image)) {
                    $this->_objTemplate->setVariable(array(
                        'NEWS_IMAGE'         => $image,
                        'NEWS_IMAGE_SRC'     => contrexx_raw2xhtml($imageSource),
                        'NEWS_IMAGE_ALT'     => contrexx_raw2xhtml($newstitle),
                        'NEWS_IMAGE_LINK'    => $htmlLinkImage,

                        // Backward compatibility for templates pre 3.0
                        'HEADLINE_IMAGE_PATH'     => contrexx_raw2xhtml($objResult->fields['teaser_image_path']),
                        'HEADLINE_THUMBNAIL_PATH' => contrexx_raw2xhtml($imageSource),
                    ));

                    if ($this->_objTemplate->blockExists('news_image')) {
                        $this->_objTemplate->parse('news_image');
                    }
                } else {
                    if ($this->_objTemplate->blockExists('news_image')) {
                        $this->_objTemplate->hideBlock('news_image');
                    }
                }

                $this->_objTemplate->parseCurrentBlock();
                $i++;
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
