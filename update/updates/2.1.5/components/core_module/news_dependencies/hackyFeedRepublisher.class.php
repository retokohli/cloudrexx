<?php

/*
    this was c&ped together from news/admin.class.php and news/lib/newsLib.class.php 
*/
class HackyFeedRepublisher {

    protected $arrSettings = array();

    public function runRepublishing() {
        initRepublishing();
    
        require_once 'Language.class.php';
        FWLanguage::init();

        $langIds = array_keys(FWLanguage::getLanguageArray());
        
        foreach($langIds as $id) {
            $this->createRSS($id);
        }
    }

    protected function initRepublishing()
    {
        global  $_ARRAYLANG, $objInit, $objTemplate, $_CONFIG;

        //getSettings
        global $objDatabase;
        $query = "SELECT name, value FROM ".DBPREFIX."module_news_settings";
        $objResult = $objDatabase->Execute($query);
        while (!$objResult->EOF) {
            $this->arrSettings[$objResult->fields['name']] = $objResult->fields['value'];
            $objResult->MoveNext();
        }
    }

    protected function createRSS($langId){
        global $_CONFIG, $objDatabase; 
        $_FRONTEND_LANGID = $langId;

        require_once 'validator.inc.php';
        require_once 'RSSWriter.class.php';

        if (intval($this->arrSettings['news_feed_status']) == 1) {
            $arrNews = array();
            $objRSSWriter = new RSSWriter();

            $objRSSWriter->characterEncoding = CONTREXX_CHARSET;
            $objRSSWriter->channelTitle = $this->arrSettings['news_feed_title'];
            $objRSSWriter->channelLink = 'http://'.$_CONFIG['domainUrl'].($_SERVER['SERVER_PORT'] == 80 ? "" : ":".intval($_SERVER['SERVER_PORT'])).ASCMS_PATH_OFFSET.($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang') : null).'/'.CONTREXX_DIRECTORY_INDEX.'?section=news';
            $objRSSWriter->channelDescription = $this->arrSettings['news_feed_description'];
            $objRSSWriter->channelLanguage = FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang');
            $objRSSWriter->channelCopyright = 'Copyright '.date('Y').', http://'.$_CONFIG['domainUrl'];

            if (!empty($this->arrSettings['news_feed_image'])) {
                $objRSSWriter->channelImageUrl = 'http://'.$_CONFIG['domainUrl'].($_SERVER['SERVER_PORT'] == 80 ? "" : ":".intval($_SERVER['SERVER_PORT'])).$this->arrSettings['news_feed_image'];
                $objRSSWriter->channelImageTitle = $objRSSWriter->channelTitle;
                $objRSSWriter->channelImageLink = $objRSSWriter->channelLink;
            }
            $objRSSWriter->channelWebMaster = $_CONFIG['coreAdminEmail'];

            $itemLink = "http://".$_CONFIG['domainUrl'].($_SERVER['SERVER_PORT'] == 80 ? "" : ":".intval($_SERVER['SERVER_PORT'])).ASCMS_PATH_OFFSET.($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang') : null).'/'.CONTREXX_DIRECTORY_INDEX.'?section=news&amp;cmd=details&amp;newsid=';

            $query = "
                SELECT      tblNews.id,
                            tblNews.date,
                            tblNews.title,
                            tblNews.text,
                            tblNews.redirect,
                            tblNews.source,
                            tblNews.catid AS categoryId,
                            tblNews.teaser_frames AS teaser_frames,
                            tblNews.teaser_text,
                            tblCategory.name AS category
                FROM        ".DBPREFIX."module_news AS tblNews
                INNER JOIN  ".DBPREFIX."module_news_categories AS tblCategory
                USING       (catid)
                WHERE       tblNews.status=1
                    AND     tblNews.lang = ".$_FRONTEND_LANGID."
                    AND     (tblNews.startdate <= CURDATE() OR tblNews.startdate = '0000-00-00 00:00:00')
                    AND     (tblNews.enddate >= CURDATE() OR tblNews.enddate = '0000-00-00 00:00:00')"
                    .($this->arrSettings['news_message_protection'] == '1' ? " AND tblNews.frontend_access_id=0 " : '')
                            ."ORDER BY tblNews.date DESC";

            if (($objResult = $objDatabase->SelectLimit($query, 20)) !== false && $objResult->RecordCount() > 0) {
                while (!$objResult->EOF) {
                    if (empty($objRSSWriter->channelLastBuildDate)) {
                        $objRSSWriter->channelLastBuildDate = date('r', $objResult->fields['date']);
                    }
                    $arrNews[$objResult->fields['id']] = array(
                        'date'          => $objResult->fields['date'],
                        'title'         => $objResult->fields['title'],
                        'text'          => empty($objResult->fields['redirect']) ? (!empty($objResult->fields['teaser_text']) ? nl2br($objResult->fields['teaser_text']).'<br /><br />' : '').$objResult->fields['text'] : (!empty($objResult->fields['teaser_text']) ? nl2br($objResult->fields['teaser_text']) : ''),
                        'redirect'      => $objResult->fields['redirect'],
                        'source'        => $objResult->fields['source'],
                        'category'      => $objResult->fields['category'],
                        'teaser_frames' => explode(';', $objResult->fields['teaser_frames']),
                        'categoryId'    => $objResult->fields['categoryId']
                    );
                    $objResult->MoveNext();
                }
            }

            // create rss feed
            $objRSSWriter->xmlDocumentPath = ASCMS_FEED_PATH.'/news_'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang').'.xml';
            foreach ($arrNews as $newsId => $arrNewsItem) {
                $objRSSWriter->addItem(
                    contrexx_raw2xml($arrNewsItem['title']),
                    (empty($arrNewsItem['redirect'])) ? ($itemLink.$newsId.(isset($arrNewsItem['teaser_frames'][0]) ? '&amp;teaserId='.$arrNewsItem['teaser_frames'][0] : '')) : htmlspecialchars($arrNewsItem['redirect'], ENT_QUOTES, CONTREXX_CHARSET),
                    contrexx_raw2xml($arrNewsItem['text']),
                    '',
                    array('domain' => "http://".$_CONFIG['domainUrl'].($_SERVER['SERVER_PORT'] == 80 ? "" : ":".intval($_SERVER['SERVER_PORT'])).ASCMS_PATH_OFFSET.($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang') : null).'/'.CONTREXX_DIRECTORY_INDEX.'?section=news&amp;category='.$arrNewsItem['categoryId'], 'title' => $arrNewsItem['category']),
                    '',
                    '',
                    '',
                    $arrNewsItem['date'],
                    array('url' => htmlspecialchars($arrNewsItem['source'], ENT_QUOTES, CONTREXX_CHARSET), 'title' => contrexx_raw2xml($arrNewsItem['title']))
               );
            }
            $status = $objRSSWriter->write();

            // create headlines rss feed
            $objRSSWriter->removeItems();
            $objRSSWriter->xmlDocumentPath = ASCMS_FEED_PATH.'/news_headlines_'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang').'.xml';
            foreach ($arrNews as $newsId => $arrNewsItem) {
                $objRSSWriter->addItem(
                    contrexx_raw2xml($arrNewsItem['title']),
                    $itemLink.$newsId.(isset($arrNewsItem['teaser_frames'][0]) ? "&amp;teaserId=".$arrNewsItem['teaser_frames'][0] : ""),
                    '',
                    '',
                    array('domain' => 'http://'.$_CONFIG['domainUrl'].($_SERVER['SERVER_PORT'] == 80 ? '' : ':'.intval($_SERVER['SERVER_PORT'])).ASCMS_PATH_OFFSET.($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang') : null).'/'.CONTREXX_DIRECTORY_INDEX.'?section=news&amp;category='.$arrNewsItem['categoryId'], 'title' => $arrNewsItem['category']),
                    '',
                    '',
                    '',
                    $arrNewsItem['date']
                );
            }
            $statusHeadlines = $objRSSWriter->write();

            $objRSSWriter->feedType = 'js';
            $objRSSWriter->xmlDocumentPath = ASCMS_FEED_PATH.'/news_'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang').'.js';
            $objRSSWriter->write();

            /*
            if (count($objRSSWriter->arrErrorMsg) > 0) {
                $this->strErrMessage .= implode('<br />', $objRSSWriter->arrErrorMsg);
            }
            if (count($objRSSWriter->arrWarningMsg) > 0) {
                $this->strErrMessage .= implode('<br />', $objRSSWriter->arrWarningMsg);
            }
            */
        } else {
            @unlink(ASCMS_FEED_PATH.'/news_'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang').'.xml');
            @unlink(ASCMS_FEED_PATH.'/news_headlines_'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang').'.xml');
            @unlink(ASCMS_FEED_PATH.'/news_'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang').'.js');
        }
    }
}