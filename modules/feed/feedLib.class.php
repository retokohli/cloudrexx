<?php

/**
 * Feed library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Paulo M. Santos <pmsantos@astalavista.net>
 * @package     contrexx
 * @subpackage  module_feed
 * @todo        Edit PHP DocBlocks!
 */

// SECURITY CHECK
if (eregi('feedLib.class.php', $_SERVER['PHP_SELF']))
{
    CSRF::header('Location: index.php');
    die();
}

/**
 * Feed library
 *
 * Manage CMS feed
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Paulo M. Santos <pmsantos@astalavista.net>
 * @package     contrexx
 * @subpackage  module_feed
 */
class feedLibrary
{
    public $_objTpl;
    public $pageTitle;
    public $statusMessage;
    public $feedpath;

    function __construct()
    {
    }


    function showNewsRefresh($id, $time, $path)
    {
        global $objDatabase;

        //delete old #01
        $query = "SELECT link,
                           filename
                      FROM ".DBPREFIX."module_feed_news
                     WHERE id = '".$id."'";
        $objResult = $objDatabase->Execute($query);

        $old_link     = $objResult->fields['link'];
        $old_filename = $objResult->fields['filename'];

        if($old_link != '') {
            $filename = "feed_".$time."_".$this->_replaceCharacters(basename($old_link));
            @copy($old_link, $path.$filename);

            //rss class
            $rss = new XML_RSS($path.$filename);
            $rss->parse();
            $content = '';

            foreach($rss->getStructure() as $array) {
                $content .= $array;
            }
        }

        if($old_link == '') {
            $filename = $old_filename;
        }

        $query = "UPDATE ".DBPREFIX."module_feed_news
                       SET filename = '".$filename."',
                           time = '".$time."'
                     WHERE id = '".$id."'";
        $objDatabase->Execute($query);

        //delete old #02
        if($old_link != '') {
            @unlink($path.$old_filename);
        }
    }

    // replaces some characters
    function _replaceCharacters($string){
        // replace $change with ''
        $change = array('\\', '/', ':', '*', '?', '"', '<', '>', '|', '+');
        // replace $signs1 with $signs
        $signs1 = array(' ', 'ä', 'ö', 'ü', 'ç');
        $signs2 = array('_', 'ae', 'oe', 'ue', 'c');

        foreach($change as $str){
            $string = str_replace($str, '_', $string);
        }
        for($x = 0; $x < count($signs1); $x++){
            $string = str_replace($signs1[$x], $signs2[$x], $string);
        }
        $string = str_replace('__', '_', $string);

        if(strlen($string) > 60){
            $info       = pathinfo($string);
            $stringExt  = $info['extension'];

            $stringName = substr($string, 0, strlen($string) - (strlen($stringExt) + 1));
            $stringName = substr($stringName, 0, 60 - (strlen($stringExt) + 1));
            $string     = $stringName . '.' . $stringExt;
        }
        return $string;
    }
}

?>
