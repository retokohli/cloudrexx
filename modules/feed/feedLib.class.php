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
    header('Location: index.php');
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
	var $_objTpl;
	var $pageTitle;
	var $statusMessage;
	var $feedpath;

	function feedLibrary()
	{
		//nothing..
	}

	//FUNC refresh
	function showNewsRefresh($id, $time, $path)
	{
		global $objDatabase, $_ARRAYLANG, $_LANGID;

		//delete old #01
		$query = "SELECT link,
		                   filename
		              FROM ".DBPREFIX."module_feed_news
		             WHERE id = '".$id."'";
		$objResult = $objDatabase->Execute($query);

		$old_link     = $objResult->fields['link'];
		$old_filename = $objResult->fields['filename'];

		if($old_link != '') {
			$filename = "feed_".$time."_".basename($old_link);
			@copy($old_link, $path.$filename);

			//rss class
			$rss =& new XML_RSS($path.$filename);
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



    function replaceChars ($string)
    {
        $replace = array('Â€' => '€', 'Â?' => '?', 'Â‚' => '‚', 'Âƒ' => 'ƒ', 'Â„' => '„', 'Â…' => '…', 'Â†' => '†', 'Â‡' => '‡', 'Âˆ' => 'ˆ', 'Â‰' => '‰', 'ÂŠ' => 'Š', 'Â‹' => '‹', 'ÂŒ' => 'Œ', 'ÂŽ' => 'Ž', 'Â‘' => '‘', 'Â’' => '’', 'Â“' => '“', 'Â”' => '”', 'Â•' => '•', 'Â–' => '–', 'Â—' => '—', 'Â˜' => '˜', 'Â™' => '™', 'Âš' => 'š', 'Â›' => '›', 'Âœ' => 'œ', 'Âž' => 'ž', 'ÂŸ' => 'Ÿ', ' ' => ' ', 'Â¡' => '¡', 'Â¢' => '¢', 'Â£' => '£', 'Â¤' => '¤', 'Â¥' => '¥', 'Â¦' => '¦', 'Â§' => '§', 'Â¨' => '¨', 'Â©' => '©', 'Âª' => 'ª', 'Â«' => '«', 'Â¬' => '¬', 'Â­' => '­', 'Â®' => '®', 'Â¯' => '¯', 'Â°' => '°', 'Â±' => '±', 'Â²' => '²', 'Â³' => '³', 'Â´' => '´', 'Âµ' => 'µ', 'Â¶' => '¶', 'Â·' => '·', 'Â¸' => '¸', 'Â¹' => '¹', 'Âº' => 'º', 'Â»' => '»', 'Â¼' => '¼', 'Â½' => '½', 'Â¾' => '¾', 'Â¿' => '¿', 'Ã€' => 'À', 'Ã?' => 'Ý', 'Ã‚' => 'Â', 'Ãƒ' => 'Ã', 'Ã„' => 'Ä', 'Ã…' => 'Å', 'Ã†' => 'Æ', 'Ã‡' => 'Ç', 'Ãˆ' => 'È', 'Ã‰' => 'É', 'ÃŠ' => 'Ê', 'Ã‹' => 'Ë', 'ÃŒ' => 'Ì', 'ÃŽ' => 'Î', 'Ã‘' => 'Ñ', 'Ã’' => 'Ò', 'Ã“' => 'Ó', 'Ã”' => 'Ô', 'Ã•' => 'Õ', 'Ã–' => 'Ö', 'Ã—' => '×', 'Ã˜' => 'Ø', 'Ã™' => 'Ù', 'Ãš' => 'Ú', 'Ã›' => 'Û', 'Ãœ' => 'Ü', 'Ãž' => 'Þ', 'ÃŸ' => 'ß', 'Ã ' => 'à', 'Ã¡' => 'á', 'Ã¢' => 'â', 'Ã£' => 'ã', 'Ã¤' => 'ä', 'Ã¥' => 'å', 'Ã¦' => 'æ', 'Ã§' => 'ç', 'Ã¨' => 'è', 'Ã©' => 'é', 'Ãª' => 'ê', 'Ã«' => 'ë', 'Ã¬' => 'ì', 'Ã­' => 'í', 'Ã®' => 'î', 'Ã¯' => 'ï', 'Ã°' => 'ð', 'Ã±' => 'ñ', 'Ã²' => 'ò', 'Ã³' => 'ó', 'Ã´' => 'ô', 'Ãµ' => 'õ', 'Ã¶' => 'ö', 'Ã·' => '÷', 'Ã¸' => 'ø', 'Ã¹' => 'ù', 'Ãº' => 'ú', 'Ã»' => 'û', 'Ã¼' => 'ü', 'Ã½' => 'ý', 'Ã¾' => 'þ', 'Ã¿' => 'ÿ');
        foreach ($replace as $key => $val) {
            $string = str_replace($key, $val, $string);
        }

        return $string;
    }
}

?>
