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
class newsHeadlines {
    public $_pageContent;
    public $_objTemplate;
    public $arrSettings = array();

    function __construct($pageContent)
    {
        $this->getSettings();
        $this->_pageContent = $pageContent;
        $this->_objTemplate = new HTML_Template_Sigma('.');
    }

    function getSettings()
    {
        global $objDatabase;
        $objResult = $objDatabase->Execute("SELECT name, value FROM ".DBPREFIX."module_news_settings");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $this->arrSettings[$objResult->fields['name']] = $objResult->fields['value'];
                $objResult->MoveNext();
            }
        }
    }


    function getHomeHeadlines($catId=0)
    {
        global $_CORELANG, $objDatabase;

        $catId= intval($catId);
        $newsLimit = intval($this->arrSettings['news_headlines_limit']);
        if($newsLimit<1 OR $newsLimit>50){
            $newsLimit=10;
        }

        $this->_objTemplate->setTemplate($this->_pageContent,true,true);
        $this->_objTemplate->setCurrentBlock('headlines_row');

        $objResult = $objDatabase->SelectLimit("SELECT id,
                                                       title,
                                                       date,
                                                       teaser_image_path,
                                                       teaser_text,
                                                       redirect
                                                  FROM ".DBPREFIX."module_news
                                                 WHERE status = 1
                                                        ".($catId > 0 ? "AND catid = ".$catId : '')."
                                                       AND teaser_only='0'
                                                       AND lang=".FRONTEND_LANG_ID."
                                                       AND (startdate<=CURDATE() OR startdate='0000-00-00')
                                                       AND (enddate>=CURDATE() OR enddate='0000-00-00')
                                              ORDER BY date DESC", $newsLimit);

        if ($objResult !== false && $objResult->RecordCount()>=0) {
            while (!$objResult->EOF) {

                $url = CONTREXX_SCRIPT_PATH;
                $newsid    = $objResult->fields['id'];
                $newstitle = htmlspecialchars(stripslashes($objResult->fields['title']), ENT_QUOTES, CONTREXX_CHARSET);
                $newsparam = 'section=news&amp;cmd=details';
                $news_link = (empty($objResult->fields['redirect']))
                    ? '<a class="headlineLink" href="'.$url.'?'.$newsparam.'&amp;newsid='.$newsid.'" title="'.$newstitle.'">'.$newstitle.'</a>'
                    : '<a class="headlineLink" href="'.$objResult->fields['redirect'].'" title="'.$newstitle.'">'.$newstitle.'</a>';

                $this->_objTemplate->setVariable("HEADLINE_DATE", date(ASCMS_DATE_SHORT_FORMAT, $objResult->fields['date']));
				$this->_objTemplate->setVariable("HEADLINE_LINK", $news_link);
                $this->_objTemplate->setVariable("HEADLINE_IMAGE_PATH", $objResult->fields['teaser_image_path']);
                $this->_objTemplate->setVariable("HEADLINE_TEXT", nl2br($objResult->fields['teaser_text']));
                $this->_objTemplate->setVariable("HEADLINE_ID", intval($objResult->fields['id']));
                $this->_objTemplate->parseCurrentBlock();
                $objResult->MoveNext();
            }
            $this->_objTemplate->setVariable("TXT_MORE_NEWS", $_CORELANG['TXT_MORE_NEWS']);
        } else {
            $this->_objTemplate->hideBlock('headlines_row');
        }
        return $this->_objTemplate->get();
    }
}
?>