<?php
/**
 * RSS Feed
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     $Id:     Exp $
 * @package     contrexx
 * @subpackage  module_directory
 * @todo        Edit PHP DocBlocks!
 */

namespace Cx\Modules\Directory\Controller;

/**
 * RSS Feed
 *
 * Creates and deletes rss feeds
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     $Id:     Exp $
 * @package     contrexx
 * @subpackage  module_directory
 */
class RssFeed
{
    var $xmlType;
    var $filePath;
    var $fileName = array();
    var $newsLimit;
    var $langId;
    var $catId;

    var $channelTitle;
    var $channelLink;
    var $channelDescription;
    var $channelLanguage;
    var $channelCopyright;
    var $channelGenerator;
    var $channelWebmaster;
    var $itemLink;

    /**
    * Constructor
    *
    * @global    array
    * @global    InitCMS
    * @global    ADONewConnection
    */
    function __construct($catId)
    {
        global $_CONFIG, $objInit, $objDatabase;

        //$db->query("SELECT lang FROM ".DBPREFIX."languages WHERE id='$this->langId'");
        //$db->next_record();
/*
define('\Env::get('cx')->getWebsiteFeedPath()',                   ASCMS_DOCUMENT_ROOT.'/feed');
define('ASCMS_FEED_WEB_PATH',               ASCMS_PATH_OFFSET.'/feed');
ASCMS_DIRECTORY_FEED_PATH
*/

        $this->filePath = ASCMS_DIRECTORY_FEED_PATH . '/';
        $this->channelCopyright = "http://".$_CONFIG['domainUrl'];
        $this->channelGenerator = $_CONFIG['coreCmsName'];
        $this->channelLanguage  = "English";
        $this->itemLink = "http://".$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET."/".CONTREXX_DIRECTORY_INDEX."?section=Directory&amp;cmd=detail&amp;id=";
        $this->fileName = "directory_latest.xml";
    }

    /**
    * checkPermissions: checks if the permissions on the feed-directory are correct
    *
    * @return   boolean
    */
    function checkPermissions()
    {
        if(is_writeable($this->filePath) AND is_dir($this->filePath)){
            return true;
        } else {
            return false;
        }
    }


    /**
    * deletes the rss news feed file
    *
    */
    function delete()
    {
        @unlink($this->filePath.$this->fileName);
    }



    /**
    * creates the rss news feed file
    *
    * @global   array
    * @global   integer
    * @global   ADONewConnection
    */
    function create()
    {
        global $_CONFIG, $_FRONTEND_LANGID, $objDatabase;

        $xmlOutput = "";

        if ($this->checkPermissions()){
            $xmlOutput .= "<?xml version=\"1.0\" encoding=\"".CONTREXX_CHARSET."\"?>\n";
            $xmlOutput .= "<rss version=\"2.0\">\n";
            $xmlOutput .= "<channel>\n";
            $xmlOutput .= "<title>".$this->channelTitle."</title>\n";
            $xmlOutput .= "<description>".$this->channelDescription."</description>\n";
            $xmlOutput .= "<link>".$this->channelLink."</link>\n";
            $xmlOutput .= "<copyright>".$this->channelCopyright."</copyright>\n";
            $xmlOutput .= "<webMaster>".$this->channelWebmaster."</webMaster>\n";
            $xmlOutput .= "<generator>".$this->channelGenerator."</generator>\n";
            $xmlOutput .= "<lastBuildDate>".date('r',time())."</lastBuildDate>\n";
            $xmlOutput .= "<language>".$this->channelLanguage."</language>\n";

            $query = "SELECT id, title, description FROM ".DBPREFIX."module_directory_dir
                       WHERE status != 0
                    ORDER BY id DESC";

            $objResult = $objDatabase->SelectLimit($query, $this->newsLimit, 0);

            if($objResult !== false){
            while(!$objResult->EOF){
                    $xmlOutput .= "<item>\n";
                    $xmlOutput .= "<title>".htmlspecialchars($objResult->fields['title'], ENT_QUOTES, CONTREXX_CHARSET)."</title>\n";
                    $xmlOutput .= "<description>".substr(htmlspecialchars($objResult->fields['description'], ENT_QUOTES, CONTREXX_CHARSET),0 ,200)."</description>\n";
                    $xmlOutput .= "<link>".$this->itemLink.$objResult->fields['id']."</link>\n";
                    $xmlOutput .= "<pubDate>".date('r',time())."</pubDate>\n";
                    $xmlOutput .= "</item>\n";
                    $objResult->MoveNext();
                }
            }
            $xmlOutput .= "</channel>\n";
            $xmlOutput .= "</rss>";

            $fileHandle = @fopen($this->filePath.$this->fileName,"w+");

            if($fileHandle){
                @fwrite($fileHandle,$xmlOutput);
                @fclose($fileHandle);
            }
        }
    }
}
?>
