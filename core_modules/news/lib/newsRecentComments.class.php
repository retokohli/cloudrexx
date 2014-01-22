<?php

/**
 * News : Get recent comments
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @version     3.1.1
 * @package     contrexx
 * @subpackage  module_news
 */

/**
 * News : Get recent comments
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @version     3.1.1
 * @package     contrexx
 * @subpackage  module_news
 */
class newsRecentComments extends newsLibrary 
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
        CSRF::add_placeholder($this->_objTemplate);
    }
    
    function getRecentNewsComments()
    {
        global $objDatabase;
        
        $this->_objTemplate->setTemplate($this->_pageContent,true,true);
        
        // abort if template block is missing
        if (!$this->_objTemplate->blockExists('news_comments')) {
            return;
        }

        // abort if commenting system is not active
        if (!$this->arrSettings['news_comments_activated']) {
            $this->_objTemplate->hideBlock('news_comments');
            return;
        }
        
        $_ARRAYLANG = \Env::get('init')->loadLanguageData('news');
        $commentsCount = (int) $this->arrSettings['recent_news_message_limit'];
        
        $query = "SELECT  `title`,
                          `date`,
                          `poster_name`,
                          `userid`,
                          `text`
                    FROM  
                          `".DBPREFIX."module_news_comments`
                    WHERE       
                          `is_active` = '1'
                    ORDER BY
                          `date` DESC 
                    LIMIT 0, $commentsCount";

        $objResult = $objDatabase->Execute($query);

        // no comments for this message found
        if (!$objResult || $objResult->EOF) {
            if ($this->_objTemplate->blockExists('news_no_comment')) {
                $this->_objTemplate->setVariable('TXT_NEWS_COMMENTS_NONE_EXISTING', $_ARRAYLANG['TXT_NEWS_COMMENTS_NONE_EXISTING']);
                $this->_objTemplate->parse('news_no_comment');
            }

            $this->_objTemplate->hideBlock('news_comment_list');
            $this->_objTemplate->parse('news_comments');

            return $this->_objTemplate->get();
        }

        $i = 0;
        while (!$objResult->EOF) {
            self::parseUserAccountData($this->_objTemplate, $objResult->fields['userid'], $objResult->fields['poster_name'], 'news_comments_poster');

            $this->_objTemplate->setVariable(array(
               'NEWS_COMMENTS_CSS'          => 'row'.($i % 2 + 1),
               'NEWS_COMMENTS_TITLE'        => contrexx_raw2xhtml($objResult->fields['title']),
               'NEWS_COMMENTS_MESSAGE'      => nl2br(contrexx_raw2xhtml($objResult->fields['text'])),
               'NEWS_COMMENTS_LONG_DATE'    => date(ASCMS_DATE_FORMAT, $objResult->fields['date']),
               'NEWS_COMMENTS_DATE'         => date(ASCMS_DATE_FORMAT_DATE, $objResult->fields['date']),
               'NEWS_COMMENTS_TIME'         => date(ASCMS_DATE_FORMAT_TIME, $objResult->fields['date']),
            ));

            $this->_objTemplate->parse('news_comment');
            $i++;
            $objResult->MoveNext();
        }

        $this->_objTemplate->parse('news_comment_list');
        $this->_objTemplate->hideBlock('news_no_comment');
        
        return $this->_objTemplate->get();
    }
}
