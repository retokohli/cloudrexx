<?php
/**
 * Forum library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Kaelin <thomas.kaelin@comvation.com>
 * @version	    $Id: index.inc.php,v 1.00 $
 * @package     contrexx
 * @subpackage  module_forum
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Forum library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Kaelin <thomas.kaelin@comvation.com>
 * @version	    $Id: index.inc.php,v 1.00 $
 * @package     contrexx
 * @subpackage  module_forum
 */
class ForumLibrary {

	var $_anonymousName 		= "Anonym";
	var $_intLangId;
	var $_arrSettings			= array();
	var $_arrLanguages 			= array();
	var $_arrTranslations 		= array();
	var $_arrIcons;
	var $_threadCount			= 0;
	var $_postCount				= 0;
	var $_arrGroups 			= array();
	var $_communityUserGroupId 	= array(0);
	var $_anonymousGroupId 		= array(0);
	var $_maxStringLenght		= 50;
	var $_minPostLenght			= 5;


	/**
	* Constructor-Fix for non PHP5-Servers
    *
    */
	function ForumLibrary() {
		$this->__constructor();
	}


	/**
	* Constructor
	*
    */
	function __constructor() {
		$this->_arrSettings		= $this->createSettingsArray();
		$this->_arrLanguages 	= $this->createLanguageArray();
		$this->_arrTranslations	= $this->createTranslationArray();
	}



	/**
	 * do checks and delete thread
	 *
	 * @param integer $intThreadId
	 * @return bool
	 */
	function _deleteThread($intThreadId, $intCatId = 0){
		global $objDatabase, $_ARRAYLANG;
		$intThreadId = intval($intThreadId);
		$intCatId = intval($intCatId);
		if($intThreadId < 1){ //something's fishy...
			return false;
		}
		if(!$intCatId){
			$intCatId = $this->_getCategoryIdFromThread($intThreadId);
		}

		if(!$this->_checkAuth($intCatId, 'delete')){ //check if the user has authorization to delete stuff in this category
			$this->_objTpl->setVariable('TXT_FORUM_ERROR', $_ARRAYLANG['TXT_FORUM_NO_ACCESS']);
			return false;
		}

		//get last post id from stats table
		$query = '	SELECT last_post_id FROM '.DBPREFIX.'module_forum_statistics
					WHERE category_id = '.$intCatId;
		$objRS = $objDatabase->SelectLimit($query, 1);
		if($objRS === false){
			die('Database error: '.$objDatabase->ErrorMsg());
		}
		$last_post_id = $objRS->fields['last_post_id'];

		//get all id's from the thread which is gonna be deleted
		$query = '	SELECT id FROM '.DBPREFIX.'module_forum_postings
					WHERE thread_id = '.$intThreadId;
		$objRS = $objDatabase->Execute($query);
		if($objRS === false){
			die('Database error: '.$objDatabase->ErrorMsg());
		}
		$deletePostIds = array();
		while(!$objRS->EOF){
			$deletePostIds[] = $objRS->fields['id'];
			$objRS->MoveNext();
		}

		//now compare the fetched ids with the last_post_id from the stats table we retrieved before
		if(in_array($last_post_id, $deletePostIds)){
			//last_post_id in module_forum_statistics is going to be deleted, get new 'last post id'
			$query = '	SELECT `id` FROM '.DBPREFIX.'module_forum_postings
						WHERE `category_id` = '.$intCatId.'
						AND `thread_id` != '.$intThreadId.'
						ORDER BY `thread_id` DESC, `id` DESC';
			if(($objRS = $objDatabase->SelectLimit($query, 1)) !== false){
				if($objRS->RecordCount() == 1){ //another thread found, setting new 'last post id'
					$new_last_post_id = $objRS->fields['id'];
				}else{ //no more threads, this category is empty now, hence we set the 'last post id' to 0
					$new_last_post_id = 0;
				}
			}else{
				die('Database error: '.$objDatabase->ErrorMsg());
			}
		}

		$query = '	DELETE FROM '.DBPREFIX.'module_forum_postings
					WHERE thread_id = '.$intThreadId;
		if($objDatabase->Execute($query) === false){
			die('Database error: '.$objDatabase->ErrorMsg());
		}
		$intAffectedRows = $objDatabase->Affected_Rows();
		if(!isset($new_last_post_id)){
			$query = '	UPDATE '.DBPREFIX.'module_forum_statistics
						SET 	`post_count` = `post_count` - '.$intAffectedRows.',
								`thread_count` = `thread_count` - 1
						WHERE 	category_id = '.$intCatId;
		}else{
			$query = '	UPDATE '.DBPREFIX.'module_forum_statistics
						SET 	`last_post_id` = '.$new_last_post_id.',
								`post_count` = `post_count` - '.$intAffectedRows.',
								`thread_count` = `thread_count` - 1
						WHERE 	category_id = '.$intCatId;
		}

		if($objDatabase->Execute($query) === false){
			die('Database error: '.$objDatabase->ErrorMsg());
		}

		$query = '	DELETE FROM `'.DBPREFIX.'module_forum_notification`
					WHERE `thread_id` = '.$intThreadId;
		if($objDatabase->Execute($query) === false){
			die('Database error: '.$objDatabase->ErrorMsg());
		}
//		$objCache = &new Cache();
//		$objCache->deleteAllFiles();
		return true;
	}


	/**
	 * do checks and delete post
	 *
	 * @param integer $intCatId
	 * @param integer $intThreadId
	 * @param integer $intPostId
	 * @return bool true on success
	 */
	function _deletePost($intCatId, $intThreadId, $intPostId){
		global $objDatabase, $_ARRAYLANG;
		if($intPostId < 1){
			return false;
		}
		if(!$this->_checkAuth($intCatId, 'delete')){
			$this->_objTpl->setVariable('TXT_FORUM_ERROR', $_ARRAYLANG['TXT_FORUM_NO_ACCESS']);
			return false;
		}
		//check if it's the first post in a thread, warn and exit if true
		$query = '	SELECT 1 FROM '.DBPREFIX.'module_forum_postings
					WHERE id = '.$intPostId.'
					AND thread_id = '.$intThreadId.'
					AND category_id = '.$intCatId.'
					AND prev_post_id = 0';
		if(($objRS = $objDatabase->SelectLimit($query, 1)) !== false){
			if($objRS->RecordCount() == 1){
				$this->_objTpl->setVariable('TXT_FORUM_ERROR', $_ARRAYLANG['TXT_FORUM_FIRST_POST_IN_THREAD'].' '.$_ARRAYLANG['TXT_FORUM_DELETE_THREAD_INSTEAD']);
				return false;
			}
		}else{
			die('Database error: '.$objDatabase->ErrorMsg());
		}

		//if last post in thread, then update statistics
		$query = '	SELECT last_post_id FROM '.DBPREFIX.'module_forum_statistics
					WHERE category_id = '.$intCatId;
		$objRS = $objDatabase->SelectLimit($query, 1);
		if($objRS !== false){
			$last_post_id = !empty($objRS->fields['last_post_id']) ? $objRS->fields['last_post_id'] : 0;
		}

		if($last_post_id == $intPostId){
			$arrPosts = $this->createPostArray($intThreadId, -1);	//fetch all posts from this thread
			end($arrPosts);							//get second last post, which is now the new last post
			$new_last_post_id = prev($arrPosts);  	//and update the statistics table with the new values
			$new_last_post_id = $new_last_post_id['id'];
			$query = '	UPDATE '.DBPREFIX.'module_forum_statistics
						SET 	`last_post_id` = '.$new_last_post_id.',
								`post_count` = `post_count` - 1
						WHERE category_id = '.$intCatId;
			if($objDatabase->Execute($query) === false){
				die('Database error: '.$objDatabase->ErrorMsg());
			}
		}else{ //not last post, only update post_count
			$query = '	UPDATE '.DBPREFIX.'module_forum_statistics
						SET 	`post_count` = `post_count` - 1
						WHERE category_id = '.$intCatId;
			if($objDatabase->Execute($query) === false){
				die('Database error: '.$objDatabase->ErrorMsg());
			}
		}

		//check if any posts are associated with this one (not used yet)
		$query = '	SELECT id, category_id, thread_id, prev_post_id
					FROM '.DBPREFIX.'module_forum_postings
					WHERE prev_post_id = '.$intPostId.'
					AND thread_id = '.$intThreadId.'
					AND category_id = '.$intCatId.'
					AND id != '.($intPostId+1);
		if(($objRS = $objDatabase->Execute($query)) !== false){
			if($objRS->RecordCount() > 0){
				$this->_objTpl->setVariable('TXT_FORUM_ERROR', $_ARRAYLANG['TXT_FORUM_POST_STILL_ASSOCIATED'].' '.$_ARRAYLANG['TXT_FORUM_DELETE_ASSOCIATED_POSTS_FIRST']);
			}else{
				$query = '	DELETE FROM '.DBPREFIX.'module_forum_postings
							WHERE id='.$intPostId;
				if($objDatabase->Execute($query) !== false){
					$this->_objTpl->setVariable('TXT_FORUM_SUCCESS', $_ARRAYLANG['TXT_FORUM_ENTRY_SUCCESSFULLY_DELETED']);
					return true;
				}else{
					die('Database error: '.$objDatabase->ErrorMsg());
				}
			}
		}else{
			die('Database error: '.$objDatabase->ErrorMsg());
		}
//		$objCache = &new Cache();
//		$objCache->deleteAllFiles();
		return true;
	}


	/**
	 * convert BB code to HTML
	 *
	 * @param string $content
	 * @return string $content
	 * @see http://www.christian-seiler.de/projekte/php/bbcode/doc/phpdoc/earthli/index.html
	 */
	function BBCodeToHTML($content){
		global $_ARRAYLANG;
		require_once ASCMS_LIBRARY_PATH.'/bbcode/stringparser_bbcode.class.php';
		$objBBCode = new StringParser_BBCode();
		$objBBCode->addFilter(STRINGPARSER_FILTER_PRE, array(&$this, 'convertlinebreaks')); //unify all linebreak variants from different systems
		$objBBCode->addFilter(STRINGPARSER_FILTER_PRE, array(&$this, 'convertlinks'));
//		$objBBCode->addFilter(STRINGPARSER_FILTER_POST, array(&$this, 'stripBBtags'));
		$objBBCode->addFilter(STRINGPARSER_FILTER_POST, array(&$this, 'removeDoubleEscapes'));
		$objBBCode->addParser(array ('block', 'inline', 'link', 'listitem'), 'htmlspecialchars');
		$objBBCode->addParser(array ('block', 'inline', 'link', 'listitem'), 'nl2br');
		$objBBCode->addParser('list', array(&$this, 'bbcode_stripcontents'));
		$objBBCode->addCode('b', 'simple_replace', null, array ('start_tag' => '<b>', 'end_tag' => '</b>'), 'inline', array ('block', 'inline'), array ());
		$objBBCode->addCode('i', 'simple_replace', null, array ('start_tag' => '<i>', 'end_tag' => '</i>'), 'inline', array ('block', 'inline'), array ());
		$objBBCode->addCode('u', 'simple_replace', null, array ('start_tag' => '<u>', 'end_tag' => '</u>'), 'inline', array ('block', 'inline'), array ());
		$objBBCode->addCode('s', 'simple_replace', null, array ('start_tag' => '<strike>', 'end_tag' => '</strike>'), 'inline', array ('block', 'inline'), array ());
		$objBBCode->addCode('url', 'usecontent?', array(&$this, 'do_bbcode_url'), array ('usecontent_param' => 'default'), 'inline', array ('listitem', 'block', 'inline'), array ('link'));
		$objBBCode->addCode('img', 'usecontent', array(&$this, 'do_bbcode_img'), array ('usecontent_param' => array('w', 'h')), 'image', array ('listitem', 'block', 'inline', 'link'), array ());
		$objBBCode->addCode('quote', 'callback_replace', array(&$this, 'do_bbcode_quote'), array('usecontent_param' => 'default'), 'block', array('block', 'inline'), array('list', 'listitem'));
		$objBBCode->addCode('code', 'usecontent', array(&$this, 'do_bbcode_code'), array('usecontent_param' => 'default'), 'block', array('block', 'inline'), array('list', 'listitem'));
		$objBBCode->addCode('list', 'simple_replace', null, array ('start_tag' => '<ul>', 'end_tag' => '</ul>'), 'list', array ('block', 'listitem'), array ());
		$objBBCode->addCode('*', 'simple_replace', null, array ('start_tag' => '<li>', 'end_tag' => '</li>'), 'listitem', array ('list'), array ());
		$objBBCode->setCodeFlag('*', 'closetag', BBCODE_CLOSETAG_OPTIONAL);
		$objBBCode->setCodeFlag('*', 'paragraphs', true);
		$objBBCode->setCodeFlag('list', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
		$objBBCode->setCodeFlag('list', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
		$objBBCode->setCodeFlag('list', 'closetag.before.newline', BBCODE_NEWLINE_DROP);

		$objBBCode->setOccurrenceType('img', 'image');
		$objBBCode->setMaxOccurrences('image', 5);

		$objBBCode->setRootParagraphHandling(false); //do not convert new lines to paragraphs, see stringparser_bbcode::setParagraphHandlingParameters();
		$content = $objBBCode->parse($content);
		return $content;
	}

	function removeDoubleEscapes($text){
		return html_entity_decode($text, ENT_QUOTES, CONTREXX_CHARSET);
	}

	function convertlinks($text){
		if(preg_match('#^http://.*#', $text)){
			return preg_replace('#(http://)+(www\.)?([a-zA-Z][a-zA-Z0-9-/]+\.[a-zA-Z][a-zA-Z0-9-/&\#\+=\?\.:;%]+)+(\[/url\])?#i', '[url]$2$3$4$5[/url]' , $text);
		}
		return preg_replace('#[\s](http://)+(www\.)?([a-zA-Z][a-zA-Z0-9-/]+\.[a-zA-Z][a-zA-Z0-9-/&\#\+=\?\.:;%]+)+(\[/url\])?#i', '[url]$2$3$4$5[/url]' , $text);
	}
	/**
	 * strip BB tags
	 *
	 * @param string $text
	 * @return unknown
	 */
	function stripBBtags($text){
	    return preg_replace ("#\[(.*[^\]])\](.*)\[/(.*[^\]])\]#", "$2", $text);
	}


	/**
	 * convert different linebreaks to \n
	 *
	 * @param	string $text
	 * @return	string $text with unified newlines (\n)
	 */
	function convertlinebreaks ($text) {
	    return preg_replace ("#\015\012|\015|\012#", "\n", $text);
	}

	/**
	 * remove everything but newlines
	 *
	 * @param	string $text
	 * @return	string $text with newlines
	 */
	function bbcode_stripcontents ($text) {
	    return preg_replace ("#[^\n]#", '', $text);
	}

	/**
	 * convert [quote] tags
	 * @see http://www.christian-seiler.de/projekte/php/bbcode/doc/de
	 */
	function do_bbcode_quote($action, $attributes, $content, $params, $node_object){
		global $_ARRAYLANG;
		if($action == 'validate'){
			return true;
		}
		if(!isset($attributes['default'])){
			return '<span class="quote_from">'.$_ARRAYLANG['TXT_FORUM_SOMEONE_UNKNOWN'].' '.$_ARRAYLANG['TXT_FORUM_WROTE'].'</span><br /><div class="quote">'.$content.'</div>';
		}
		return '<span class="quote_from">'.$attributes['default'].' '.$_ARRAYLANG['TXT_FORUM_WROTE'].'</span><br /><div class="quote">'.$content.'</div>';
//<p class="quote"> <span class="quote_from">asdf </span><br /><br /> <p class="quote"> <span class="quote_from">qwer </span><br /><br /> yxcv </p>ghk </p>
	}

	/**
	 * convert [code] tags
	 * @see http://www.christian-seiler.de/projekte/php/bbcode/doc/de
	 */
	function do_bbcode_code($action, $attributes, $content, $params, $node_object){
		if($action == 'validate'){
			return true;
		}
		return 'Code:<br /><div class="code">'.$content.'</div>';
	}

	/**
	 * embed URLs
	 * @see http://www.christian-seiler.de/projekte/php/bbcode/doc/de
	 */
	function do_bbcode_url ($action, $attributes, $content, $params, $node_object) {
//		$urlRegex = '#([a-zA-Z]+://)?(.*)#';
	    if ($action == 'validate') {
	    	if(!isset ($attributes['default'])) {
	    		return $this->is_valid_url($content);
	   		}else{
	    		return $this->is_valid_url($attributes['default']);
	   		}
	    }
		$httpRegex = '#^(http://)?(www\.)?([a-zA-Z][a-zA-Z0-9-/]+\.[a-zA-Z][a-zA-Z0-9-/&\#\+=\?\.;%]+)+#i';
	    if(!isset ($attributes['default'])) {
	    	$content = preg_replace($httpRegex, 'http://$2$3' ,$content);
	        return '<a href="'.htmlspecialchars ($content, ENT_QUOTES, CONTREXX_CHARSET).'">'.htmlspecialchars ($content, ENT_QUOTES, CONTREXX_CHARSET).'</a>';
	    }
    	$attributes['default'] = preg_replace($httpRegex, 'http://$2$3' , $attributes['default']);
	    return '<a href="'.htmlspecialchars ($attributes['default'], ENT_QUOTES, CONTREXX_CHARSET).'">'.$content.'</a>';
	}

	/**
	 * for embedding images
	 * @see http://www.christian-seiler.de/projekte/php/bbcode/doc/de
	 */
	function do_bbcode_img ($action, $attributes, $content, $params, $node_object) {
	    if ($action == 'validate') {
	        return true;
	    }

	    $content = $this->stripBBtags($content);

    	if(isset($attributes['w']) && isset($attributes['h'])){
		    return '<img border="0" width="'.$attributes['w'].'" height="'.$attributes['h'].'" src="'.htmlspecialchars($content, ENT_QUOTES, CONTREXX_CHARSET).'" alt="embedded_image" />';
	    }
	    return '<img border="0" src="'.htmlspecialchars($content, ENT_QUOTES, CONTREXX_CHARSET).'" alt="embedded_image" />';
	}

	/**
	 * dummy function which returns true (causes problems otherwise, since it's already been 'regexed' in convertlinks())
	 *
	 * @param	string $url
	 * @return	bool true
 	 */
	function is_valid_url($url){
		return true;
	}


	/**
	 * set the community login links
	 *
	 * @return void
	 */
	function _communityLogin(){
		global $_ARRAYLANG;
		if(!isset($_SESSION['auth']) || $_SESSION['auth']['userid'] <= 0){
			$strForumCommunityLinks = '	<a href="?section=login&amp;redirect='.((isset($_SERVER['REQUEST_URI'])) ? base64_encode($_SERVER['REQUEST_URI'])  : '?section=forum' ).'"> '.$_ARRAYLANG['TXT_FORUM_LOGIN'].'</a> |
										<a href="?section=community&amp;cmd=register">'.$_ARRAYLANG['TXT_FORUM_REGISTER'].'</a>';
		}else{
			$strForumCommunityLinks = '<a href="?section=forum&amp;cmd=notification">'.$_ARRAYLANG['TXT_FORUM_NOTIFICATION'].'</a> | <a href="?section=community&amp;cmd=profile">'.$_ARRAYLANG['TXT_FORUM_PROFILE'].'</a>
									| <a href="?section=logout&amp;redirect='.((isset($_SERVER['REQUEST_URI'])) ? base64_encode($_SERVER['REQUEST_URI'])  : '?section=forum' ).'"> '.$_ARRAYLANG['TXT_FORUM_LOGOUT'].'</a>';
		}
		$this->_objTpl->setVariable('FORUM_COMMUNITY_LINKS', $strForumCommunityLinks);
	}

	/**
	 * Create an array containing all settings of the forum-module. Example: $arrSettings[$strSettingName].
	 *
	 * @global	object		$objDatabase
	 * @return 	array		$arrReturn
	 */
	function createSettingsArray() {
		global $objDatabase;
		$arrReturn = array();

		$objResult = $objDatabase->Execute('SELECT	name,
													value
											FROM	'.DBPREFIX.'module_forum_settings
										');
		while (!$objResult->EOF) {
			$arrReturn[$objResult->fields['name']] = stripslashes(htmlspecialchars($objResult->fields['value'], ENT_QUOTES, CONTREXX_CHARSET));
			$objResult->MoveNext();
		}

		return $arrReturn;
	}


	/**
	 * Creates an array containing all frontend-languages. Example: $arrValue[$langId]['short'] or $arrValue[$langId]['long']
	 *
	 * @global 	object		$objDatabase
	 * @return	array		$arrReturn
	 */
	function createLanguageArray() {
		global $objDatabase;

		$arrReturn = array();

		$objResult = $objDatabase->Execute('SELECT		id,
														lang,
														name
											FROM		'.DBPREFIX.'languages
											WHERE		frontend=1
											ORDER BY	id
										');
		while (!$objResult->EOF) {
			$arrReturn[$objResult->fields['id']] = array(	'short'	=>	stripslashes($objResult->fields['lang']),
															'long'	=>	htmlentities(stripslashes($objResult->fields['name']),ENT_QUOTES, CONTREXX_CHARSET)
														);
			$objResult->MoveNext();
		}

		return $arrReturn;
	}


	/**
	 * Creates an array containing all translations of the categories. Example: $arrValue[$categoryId][$langId]['name'].
	 *
	 * @global 	object		$objDatabase
	 * @return	array		$arrReturn
	 */
	function createTranslationArray() {
		global $objDatabase;

		$arrReturn = array();

		$objResult = $objDatabase->Execute('SELECT		category_id,
														lang_id,
														name,
														description
											FROM		'.DBPREFIX.'module_forum_categories_lang
											ORDER BY	category_id ASC
										');

		while (!$objResult->EOF) {
			$arrReturn[$objResult->fields['category_id']][$objResult->fields['lang_id']] = array(	'name'	=>	htmlentities(stripslashes($objResult->fields['name']),ENT_QUOTES, CONTREXX_CHARSET),
																									'desc'	=>	htmlentities(stripslashes($objResult->fields['description']),ENT_QUOTES, CONTREXX_CHARSET)
																								);
			$objResult->MoveNext();
		}

		return $arrReturn;
	}


	/**
	 * Create an array containing all "thread-icons". Key of the array is a number: 1.gif -> 1.
	 *
	 * @return	array		$arrReturn
	 */
	function createThreadIconArray() {
		$arrReturn = array();

		$handleDir = dir(ASCMS_MODULE_IMAGE_PATH.'/forum/thread');
		while ($strFile = $handleDir->read()) {
			if ($strFile != '.' && $strFile != '..'){
				$arrFileInfos = pathinfo(ASCMS_MODULE_IMAGE_PATH.'/forum/thread/'.$strFile);
			}else{
				continue;
			}
			if ($arrFileInfos['extension'] == 'gif') {
				$arrReturn[basename($strFile,'.gif')] = '<img src="'.ASCMS_MODULE_IMAGE_WEB_PATH.'/forum/thread/'.$strFile.'" border="0" alt="'.$strFile.'" title="'.$strFile.'" />';
			}
		}
		$handleDir->close();

		return $arrReturn;
	}

	/**
	 * Returns the <img>-Code for a desired icon.
	 *
	 * @param	integer		$intIcon: The icon with this "id" (1.gif -> 1) will be return
	 * @return	string		<img>-Sourcecode if the id exists, otherwise "nbsp;"
	 */
	function getThreadIcon($intIcon) {
		$intIcon = intval($intIcon);

		if (!is_array($this->_arrIcons)) {
			$this->_arrIcons = $this->createThreadIconArray();
		}

		if ($intIcon != 0 && array_key_exists($intIcon,$this->_arrIcons)) {
			return $this->_arrIcons[$intIcon];
		} else {
			return '&nbsp;';
		}
	}

	/**
	 * Creates and returns an array containing all forum-information
	 *
	 * @param 	integer		$intLangId: If this param has another value then zero, only the forums for the lang with this id will be loaded
	 * @param 	integer     $intParCat
	 * @param 	integer		$intLevel
	 * @return 	array		$arrForums
	 */
	function createForumArray($intLangId = 0, $intParCat = 0, $intLevel = 0) {
		$arrForums = array();
		$this->createForumTree($arrForums, $intParCat, $intLevel, $intLangId);
		return $arrForums;
	}

	/**
	 * This is a recursive help-function of "createForumArray()".
	 *
	 * @global 	object		$objDatabase
	 * @global 	array		$_ARRAYLANG
	 * @param 	reference	$arrForums: reference to an array. To this array the information are written.
	 * @param 	integer		$intParCat: the recursive-step starts with this category
	 * @param 	integer		$intLevel: Current level (0 is base-level)
	 * @param 	integer		$intLangId: Only forums with this lang-id will be loaded (0 = all languages)
	 */
	function createForumTree(&$arrForums, $intParCat=0, $intLevel=0, $intLangId=0) {
		global $objDatabase, $_ARRAYLANG;
		$intParCat 	= intval($intParCat);
		$intLevel	= intval($intLevel);
		$intLangId	= intval($intLangId);

		$objResult = $objDatabase->Execute('SELECT		id			AS cId,
														parent_id	AS cParentId,
														order_id	AS cOrderId,
														`status`	AS cStatus
											FROM		'.DBPREFIX.'module_forum_categories
											WHERE		parent_id = '.$intParCat.'
											ORDER BY	order_id ASC'
										);
		while (!$objResult->EOF) {
			//Last post information
			if ($intLangId == 0 || array_key_exists($intLangId,$this->_arrTranslations[$objResult->fields['cId']])) {
				if ($intLevel == 0) {
					$strPostCount 	= '';
					$strLastPost 	= '';
				} else {
					$objSubResult = $objDatabase->Execute('	SELECT	thread_count	AS sThreadCount,
																	post_count		AS sPostCount,
																	last_post_id	AS sLastPostId
															FROM	'.DBPREFIX.'module_forum_statistics
															WHERE	category_id = '.$objResult->fields['cId'].'
															LIMIT	1
														');

					$intThreadCount	= intval($objSubResult->fields['sThreadCount']);
					$intPostCount	= intval($objSubResult->fields['sPostCount']);
					$intLastPost	= intval($objSubResult->fields['sLastPostId']);


					if ($intLastPost != 0) {
						//get information about the topic
						$objSubResult = $objDatabase->Execute('	SELECT	time_created	AS pTimeCreated,
																		subject			AS pSubject
																FROM	'.DBPREFIX.'module_forum_postings
																WHERE	id = '.$intLastPost.'
																LIMIT	1
															');


						$strLastPost 		= $this->_shortenString($objSubResult->fields['pSubject'], $this->_maxStringLenght/2);
						$strLastPostDate 	= date(ASCMS_DATE_FORMAT,$objSubResult->fields['pTimeCreated']);
					} else {
						// no last topic, write text into array
						$strLastPost 		= $_ARRAYLANG['TXT_FORUM_NO_POSTINGS'];
						$strLastPostDate	= '';
					}
				}

				$arrForums[$objResult->fields['cId']] = array(	'id'				=>	$objResult->fields['cId'],
																'parent_id'			=>	$objResult->fields['cParentId'],
																'level'				=>	$intLevel,
																'status'			=>	$objResult->fields['cStatus'],
																'order_id'			=>	$objResult->fields['cOrderId'],
																'thread_count'		=>	!empty($intThreadCount) ? $intThreadCount : 0,
																'post_count'		=>	!empty($intPostCount) ? $intPostCount : 0,
																'last_post_id'		=>	!empty($intLastPost) ? $intLastPost : 0,
																'last_post_str'		=>	!empty($strLastPost) ? $strLastPost : $_ARRAYLANG['TXT_FORUM_NO_SUBJECT'],
																'last_post_date'	=>	!empty($strLastPostDate) ? $strLastPostDate : '',
																'languages'			=>	$this->_arrTranslations[$objResult->fields['cId']],
																'name'				=>	$this->_arrTranslations[$objResult->fields['cId']][$this->_intLangId]['name'],
																'description'		=>	$this->_arrTranslations[$objResult->fields['cId']][$this->_intLangId]['desc'],
											);
				$this->createForumTree($arrForums,$objResult->fields['cId'],$intLevel+1);
			}
			$objResult->MoveNext();
		}
	}

	/**
	 * create an array containing all posts from the specified thread
	 * if the second argument $pos is -1, then all posts are being returned, otherwise
	 * it will be limited to the thread_paging setting
	 *
	 * @param 	integer $intThreadId ID of the thread
	 * @param 	integer $pos position at which the posts will be read from (for paging)
	 * @return 	array 	$arrReturn
	 */
	function createPostArray($intThreadId=0, $pos=0)
	{
		global $objDatabase, $_ARRAYLANG;

		$intThreadId = intval($intThreadId);
		$arrReturn = array();

		$objRSCount = $objDatabase->SelectLimit('	SELECT count(1) AS `cnt` FROM '.DBPREFIX.'module_forum_postings
													WHERE thread_id='.$intThreadId, 1);
		if($objRSCount !== false){
			$this->_postCount = $objRSCount->fields['cnt'];
		}
		if($pos == -1){
			$this->_arrSettings['thread_paging'] = $this->_postCount+1;
			$pos = 0;
		}
		$objResult = $objDatabase->SelectLimit('SELECT		id,
															category_id,
															user_id,
															time_created,
															time_edited,
															is_locked,
															is_sticky,
															views,
															icon,
															subject,
															content
												FROM		'.DBPREFIX.'module_forum_postings
												WHERE		thread_id='.$intThreadId.'
												ORDER BY	prev_post_id, time_created ASC
											', $this->_arrSettings['thread_paging'], $pos);
		$intReplies = $objResult->RecordCount();

		$postNumber=$pos+1;
		while (!$objResult->EOF) {
			$strAuthor = $this->_getUserName($objResult->fields['user_id']);

			$content = stripslashes($objResult->fields['content']);
			$content = $this->BBCodeToHTML($content);

			$arrReturn[$objResult->fields['id']] =	array(	'id'				=>	$objResult->fields['id'],
															'category_id'		=>	$objResult->fields['category_id'],
															'user_id'			=>	$objResult->fields['user_id'],
															'user_name'			=>	$strAuthor,
															'time_created'		=>	date(ASCMS_DATE_FORMAT,$objResult->fields['time_created']),
															'time_edited'		=>	date(ASCMS_DATE_FORMAT,$objResult->fields['time_edited']),
															'is_locked'			=>	intval($objResult->fields['is_locked']),
															'is_sticky'			=>	intval($objResult->fields['is_sticky']),
															'post_icon'			=>	$this->getThreadIcon($objResult->fields['icon']),
															'replies'			=>	$intReplies,
															'views'				=>	intval($objResult->fields['views']),
															'subject'			=>	(!trim($objResult->fields['subject']) == '') ? htmlspecialchars($objResult->fields['subject'], ENT_QUOTES, CONTREXX_CHARSET) : $_ARRAYLANG['TXT_FORUM_NO_SUBJECT'],
															'content'			=>	$content,
															'post_number'		=>  $postNumber++,
														);
			$objResult->MoveNext();
		}
		return $arrReturn;
	}


	/**
	 * get the post data for a specific posting
	 *
	 * @param integer $intPostId
	 * @return assoc. array containig the post data
	 */
	function _getPostingData($intPostId){
		global $objDatabase;
		$query = '	SELECT * FROM `'.DBPREFIX.'module_forum_postings`
					WHERE `id` = '.$intPostId;
		if( ($objRS = $objDatabase->SelectLimit($query, 1)) !== false){
			return $objRS->fields;
		}else{
			die('DB error: '.$objDatabase->ErrorMsg());
		}
	}

	/**
	 * return username by userId
	 *
	 * @param integer $userId
	 * @return string name on success, bool false if empty record
	 */
	function _getUserName($userId)
	{
		global $objDatabase;
		if($userId < 1){
			return $this->_anonymousName;
		}
		$objRS = $objDatabase->SelectLimit('	SELECT	username
												FROM	'.DBPREFIX.'access_users
												WHERE	id='.$userId, 1);
		if($objRS->RecordCount() == 0){//no record found for thus $userid
			return $this->_anonymousName;
		}
		$strAuthor = htmlentities(stripslashes($objRS->fields['username']), ENT_QUOTES, CONTREXX_CHARSET);
		return $strAuthor;
	}

	/**
	 * creates an array with thread information
	 *
	 * @param integer $intForumId
	 * @param integer $pos
	 * @return array
	 */
	function createThreadArray($intForumId=0, $pos=0)
	{
		global $objDatabase, $_ARRAYLANG;

		$intForumId = intval($intForumId);
		$arrReturn 	= array();

		$objRSCount = $objDatabase->SelectLimit('SELECT count(1) AS `cnt` FROM '.DBPREFIX.'module_forum_postings
												WHERE	prev_post_id=0
												AND		category_id='.$intForumId, 1);
		if($objRSCount !== false){
			$this->_threadCount = $objRSCount->fields['cnt'];
		}

		$objResult = $objDatabase->SelectLimit('SELECT		id,
															category_id,
															user_id,
															thread_id,
															time_created,
															time_edited,
															is_locked,
															is_sticky,
															views,
															icon,
															subject,
															content
												FROM		'.DBPREFIX.'module_forum_postings
												WHERE	prev_post_id=0 	AND
															category_id='.$intForumId.'
												ORDER BY	time_created DESC
											', $this->_arrSettings['thread_paging'], $pos);
		while (!$objResult->EOF) {
			//Count replies
			$objSubResult = $objDatabase->Execute('	SELECT	id
													FROM	'.DBPREFIX.'module_forum_postings
													WHERE	thread_id='.$objResult->fields['thread_id'].'
												');
			$intReplies = intval($objSubResult->RecordCount()-1);

			$strAuthor = $this->_getUserName($objResult->fields['user_id']);


			//Get information about last written answer
			$objSubResult = $objDatabase->Execute('	SELECT		p.id			AS pId,
																p.time_created	AS pTime,
																u.username		AS uUsername
													FROM		'.DBPREFIX.'module_forum_postings	AS p
													LEFT JOIN	'.DBPREFIX.'access_users			AS u
													ON			p.user_id = u.id
													WHERE		p.thread_id = '.$objResult->fields['thread_id'].'
													ORDER BY	p.time_created DESC
													LIMIT		1
												');
			if ($objSubResult->RecordCount() == 1) {
				$intLastpostId 		= intval($objSubResult->fields['pId']);
				$strLastpostDate 	= date(ASCMS_DATE_FORMAT, $objSubResult->fields['pTime']);
				$strLastpostUser	= !empty($objSubResult->fields['uUsername']) ? htmlentities(stripslashes($objSubResult->fields['uUsername']), ENT_QUOTES, CONTREXX_CHARSET) : $this->_anonymousName;
			} else {
				//There are no replies yet
				$intLastpostId		= intval($objResult->fields['id']);
				$strLastpostDate	= date(ASCMS_DATE_FORMAT,$objResult->fields['time_created']);
				$strLastpostUser	= $strAuthor;
			}

			$arrReturn[$objResult->fields['id']] =	array(	'id'				=>	$objResult->fields['id'],
													'category_id'		=>	$objResult->fields['category_id'],
													'thread_id'			=>	$objResult->fields['thread_id'],
													'user_id'			=>	$objResult->fields['user_id'],
													'user_name'			=>	$strAuthor,
													'time_created'		=>	date(ASCMS_DATE_FORMAT, $objResult->fields['time_created']),
													'time_edited'		=>	date(ASCMS_DATE_FORMAT, $objResult->fields['time_edited']),
													'is_locked'			=>	intval($objResult->fields['is_locked']),
													'is_sticky'			=>	intval($objResult->fields['is_sticky']),
													'thread_icon'		=>	$this->getThreadIcon($objResult->fields['icon']),
													'replies'			=>	$intReplies,
													'views'				=>	intval($objResult->fields['views']),
													'lastpost_id'		=>	$intLastpostId,
													'lastpost_time'		=>	$strLastpostDate,
													'lastpost_author'	=>	$strLastpostUser,
													'subject'			=>	(!trim($objResult->fields['subject']) == '') ? htmlspecialchars($objResult->fields['subject'], ENT_QUOTES, CONTREXX_CHARSET) : $_ARRAYLANG['TXT_FORUM_NO_SUBJECT'],
													'content'			=>	stripslashes($objResult->fields['content'])
												);
			$objResult->MoveNext();
		}
		return $arrReturn;
	}

	/**
	 * update views of an item
	 *
	 * @param integer $intThreadId
	 * @return bool success
	 */

	function updateViews($intThreadId){
		global $objDatabase;
		$query = '	UPDATE '.DBPREFIX.'module_forum_postings
					SET views = (views + 1)
					WHERE `thread_id` = '.$intThreadId.'
					AND prev_post_id = 0
					LIMIT 1';
		if($objDatabase->Execute($query) === false){
			return false;
			echo "DB error in function: updateViews()";
		}
		return true;
	}
	/**
	 * update views when adding a new item
	 *
	 * @param integer $intCatId category ID
	 * @param integer $last_post_id last post id of the thread
	 * @param bool	$updatePostOnly whether to update only the post count
	 * @return bool success
	 */
	function updateViewsNewItem($intCatId, $last_post_id, $updatePostOnly = false){
		global $objDatabase;

		if ($updatePostOnly){
			$updateQueryStats = "UPDATE `".DBPREFIX."module_forum_statistics` SET `post_count` = `post_count`+1,
										`last_post_id` = ".$last_post_id."
										WHERE `category_id` = ".$intCatId." LIMIT 1";

		}else{
			$updateQueryStats = "UPDATE `".DBPREFIX."module_forum_statistics` SET `thread_count` = `thread_count`+1,
										`post_count` = `post_count`+1,
										`last_post_id` = ".$last_post_id."
										WHERE `category_id` = ".$intCatId." LIMIT 1";

		}

		if($objDatabase->Execute($updateQueryStats)){
			return true;
		}
		return false;
	}

	/**
	 * Create the Navtree for the forums
	 *
	 * @param integer $intForumId
	 * @param array $arrForums
	 * @return string HTML representation of the generated NavTree
	 */
	function _createNavTree($intForumId, $arrForums = null){
		global $objDatabase, $_ARRAYLANG;
		if(!$arrForums){
			$arrForums = $this->createForumArray($this->_intLangId);
		}
		$strNavTree = '';
		$pId = $arrForums[$intForumId]['parent_id'];

		$query = "SELECT `id` FROM ".DBPREFIX."module_forum_categories WHERE `parent_id` = 0";
		if(($objRS = $objDatabase->Execute($query)) !== false){
			while(!$objRS->EOF){
				$parents[] = $objRS->fields['id'];
				$objRS->MoveNext();
			}
		}
		while($pId > 0){
			$intForumId = $pId;
			if(in_array($intForumId, $parents)){
				$strNavTree = '<a href="?section=forum&amp;cmd=cat&amp;id='.$intForumId.'">'.$this->_shortenString($arrForums[$intForumId]['name'], $this->_maxStringLenght)."</a> > \n".$strNavTree;
			}else{
				$strNavTree = '<a href="?section=forum&amp;cmd=board&amp;id='.$intForumId.'">'.$this->_shortenString($arrForums[$intForumId]['name'], $this->_maxStringLenght)."</a> > \n".$strNavTree;
			}
			$pId = $arrForums[$pId]['parent_id'];
		}

		$strNavTree = '<a href="?section=forum"> '.$_ARRAYLANG['TXT_FORUM_OVERVIEW_FORUM'].' </a> >'."\n".$strNavTree;
		return $strNavTree;
	}

	/**
	 * This function create html-source for a dropdown-menu containing all categories / forums
	 *
	 * @param 	string		$strSelectName: name-attribute of the <select>-tag
	 * @param 	integer		$intSelected: The category / forum with this id will be "selected"
	 * @param 	string		$strSelectAdds: Additional tags / styles for the <select>-tag
	 * @param 	string		$strOptionAdds: Additional tags / styles for the <option>-tag
	 * @return 	string		$strSource: HTML-Source of the dropdown-menu
	 */
	function createForumDD($strSelectName,$intSelected=0,$strSelectAdds='', $strOptionAdds='', $useCat = true, $backend = false) {
		global $objDatabase, $_ARRAYLANG;
		$intSelected 	= intval($intSelected);
		$arrForums 		= $this->createForumArray();

		$strSource 		= '<select name="'.$strSelectName.'" '.$strSelectAdds." >\n"
						. '<option value="0"> --'.$_ARRAYLANG['TXT_FORUM_OVERVIEW_FORUM'].'-- </option>';

		if (count($arrForums) > 0) {
			foreach ($arrForums as $intKey => $arrValues) {
				if(!$arrValues['status'] && !$backend){//skip non-active
					continue;
				}
				($arrValues['id'] == $intSelected) ? $strSelected = ' selected="selected"' : $strSelected = '';

				$strSpacer = '';
				for($i=0; $i<$arrValues['level'];++$i) {
					$strSpacer .= '&nbsp;&nbsp;&nbsp;&nbsp;';
				}
				if($arrValues['parent_id'] != 0){
					$strSource .= '<option value="'.$arrValues['id'].'" '.$strOptionAdds.$strSelected.'>'.$strSpacer.$this->_shortenString($arrValues['name'], $this->_maxStringLenght+10+($arrValues['level']*4)).'</option>';
				}else{
					if($useCat){
						$strSource .= '<option value="'.$arrValues['id'].'_cat" '.$strOptionAdds.$strSelected.'>'.$strSpacer.$this->_shortenString($arrValues['name'], $this->_maxStringLenght+($arrValues['level']*4)).'</option>';
					}else{
						$strSource .= '<option value="'.$arrValues['id'].'" '.$strOptionAdds.$strSelected.'>'.$strSpacer.$this->_shortenString($arrValues['name'], $this->_maxStringLenght+10+($arrValues['level']*4)).'</option>';
					}
				}
			}
		}
		$strSource .= '</select>';
		return $strSource;
	}

	/**
	 * Get name of category
	 *
	 * @param integer $intCatId
	 * @return array (name, description)
	 */
	function _getCategoryName($intCatId){
		global $objDatabase;
		$query = 'SELECT `name`, `description` FROM ".DBPREFIX."module_forum_categories_lang WHERE category_id='.$intCatId
		.' AND lang_id='.$this->_intLangId;
		if(($objRS = $objDatabase->SelectLimit($query, 1)) !== false){
			return array('name' => $objRS->fields['name'], 'description' => $objRS->fields['description']);
		}
	}


	/**
	 * fetch the latest entries
	 *
	 * @return array $arrLatestEntries
	 */
	function _getLastestEntries(){
		global $objDatabase, $_ARRAYLANG;
		$index = 0;
		$query = "	SELECT `id`, `category_id`, `thread_id`, `subject`, `user_id`, `time_created` FROM `".DBPREFIX."module_forum_postings`
					ORDER by `time_created` DESC";
		if(($objRS = $objDatabase->SelectLimit($query, $this->_arrSettings['latest_entries_count'])) !== false){
			while(!$objRS->EOF){
				$arrLatestEntries[$index]['subject'] = !empty($objRS->fields['subject']) ? $objRS->fields['subject'] : $_ARRAYLANG['TXT_FORUM_NO_SUBJECT'];
				$arrLatestEntries[$index]['post_id'] = $objRS->fields['id'];
				$arrLatestEntries[$index]['thread_id'] = $objRS->fields['thread_id'];
				$arrLatestEntries[$index]['cat_id'] = $objRS->fields['category_id'];
				$arrLatestEntries[$index]['user_id'] = $objRS->fields['user_id'];
				$arrLatestEntries[$index]['time'] = $this->_createLatestEntriesDate($objRS->fields['time_created']);
				$query = "	SELECT `users`.`username` AS `uName`, `categories`.`name` AS `cName`
							FROM `".DBPREFIX."module_forum_categories_lang` AS `categories`,
								 `".DBPREFIX."access_users` AS `users`
							WHERE `category_id` = ".$objRS->fields['category_id']."
							AND `lang_id` = ".$this->_intLangId;
				if($objRS->fields['user_id'] > 0){
					$query .= " AND `users`.`id` = ".$objRS->fields['user_id'];
				}else{
					$arrLatestEntries[$index]['username'] = $this->_anonymousName;
				}

				if(($objRSNames = $objDatabase->SelectLimit($query, 1)) !== false){
					if(empty($arrLatestEntries[$index]['username'])){
						$arrLatestEntries[$index]['username'] 	= $objRSNames->fields['uName'];
					}
					$arrLatestEntries[$index]['category_name']  = $objRSNames->fields['cName'];
				}else{
					die('DB error: '.$objDatabase->ErrorMsg());
				}

				$query = "	SELECT 1 FROM `".DBPREFIX."module_forum_postings`
							WHERE `thread_id` = ".$objRS->fields['thread_id'];
				if(($objRSCount = $objDatabase->Execute($query)) !== false){
					$arrLatestEntries[$index]['postcount'] = $objRSCount->RecordCount();
				}else{
					die('DB error: '.$objDatabase->ErrorMsg());
				}
				$objRS->MoveNext();
				$index++;
			}
		}else{
			die('DB error: '.$objDatabase->ErrorMsg());
		}
		return $arrLatestEntries;
	}


	/**
	 * prepare date for the lates entries
	 *
	 * if $date is from today, return time, otherwise date
	 *
	 * @param int timestamp $date
	 * @return formatted date|time
	 */
	function _createLatestEntriesDate($date){
		if(date('d.m.Y', time()) == date('d.m.Y', $date)){
			return date('H:i:s', $date);
		}else{
			return date('d.m.Y', $date);
		}
	}

	/**
	 * show the latest entries
	 *
	 * @param array $arrLatestEntries latest entries
	 * @return void
	 */
	function _showLatestEntries($arrLatestEntries){
		global $_ARRAYLANG;
		$count = min(count($arrLatestEntries), $this->_arrSettings['latest_entries_count']);
		$this->_objTpl->setGlobalVariable(array(
			'TXT_FORUM_THREAD' 				=> $_ARRAYLANG['TXT_FORUM_THREAD'],
			'TXT_FORUM_OVERVIEW_FORUM' 		=> $_ARRAYLANG['TXT_FORUM_OVERVIEW_FORUM'],
			'TXT_FORUM_THREAD_STRATER'	 	=> $_ARRAYLANG['TXT_FORUM_THREAD_STRATER'],
			'TXT_FORUM_POST_COUNT' 			=> $_ARRAYLANG['TXT_FORUM_POST_COUNT'],
			'TXT_FORUM_THREAD_CREATE_DATE' 	=> $_ARRAYLANG['TXT_FORUM_THREAD_CREATE_DATE'],
			'TXT_FORUM_LATEST_ENTRIES'		=> sprintf($_ARRAYLANG['TXT_FORUM_LATEST_ENTRIES'], $count),
		));
		$rowclass=0;
		foreach ($arrLatestEntries as $entry) {
			$strUserProfileLink = ($entry['user_id'] > 0) ? '<a href="?section=forum&amp;cmd=userinfo&amp;id='.$entry['user_id'].'" title="'.$entry['username'].'">'.$entry['username'].'</a>' : $entry['username'] ;
			$this->_objTpl->setVariable(array(
				'FORUM_THREAD'				=>	'<a href="?section=forum&amp;cmd=thread&amp;postid='.$entry['post_id'].'&amp;l=1&amp;id='.$entry['thread_id'].'#p'.$entry['post_id'].'" title="'.$entry['subject'].'">'.$this->_shortenString($entry['subject'], $this->_maxStringLenght).'</a>',
				'FORUM_FORUM_NAME'			=>	'<a href="?section=forum&amp;cmd=board&amp;id='.$entry['cat_id'].'" title="'.$entry['category_name'].'">'.$this->_shortenString($entry['category_name'], $this->_maxStringLenght/2).'</a>',
				'FORUM_THREAD_STARTER'		=>	$strUserProfileLink,
				'FORUM_POST_COUNT'			=>	$entry['postcount'],
				'FORUM_THREAD_CREATE_DATE'	=>	$entry['time'],
				'FORUM_ROWCLASS'			=>	($rowclass++ % 2) + 1
			));
			$this->_objTpl->parse('latestPosts');
		}
	}


	/**
	 * check for permission
	 *
	 * @param integer $intCatId
	 * @param string|array $mixedMode
	 * @return bool hasAccess
	 */
	function _checkAuth($intCatId, $mixedMode='read')
	{
		global $objPerm;

		if ($objPerm->allAccess) {
			return true;
		}

		$arrAccess = $this->createAccessArray($intCatId);
		if(empty($_SESSION['auth']['groups'])){
			$_SESSION['auth']['groups'] = $this->_anonymousGroupId;
			$_SESSION['auth']['userid'] = -1;
 		}

		if(is_array($mixedMode)){
			foreach ($mixedMode as $mode){
				if($this->_checkGroupAccess($arrAccess, $mode)){
					return true;
				}
			}
		}elseif (is_string($mixedMode)){
			return $this->_checkGroupAccess($arrAccess, $mixedMode);
		}
		return false;
	}

	function _checkGroupAccess($arrAccess, $mode){
		if(empty($this->_arrGroups)){
			$this->_arrGroups = array_intersect($_SESSION['auth']['groups'], array_keys($arrAccess));
		}
		foreach ($this->_arrGroups as $group) {
			if(!empty($arrAccess[$group][$mode]) && $arrAccess[$group][$mode] == 1){ //has access
				return true;
			}
		}
		return false; //has no access
	}


	/**
	 * Creates an array containing all access rights for a category. The index of the array is the group_id.
	 *
	 * @param	integer		$intCategoryId: The rights of this category will be returned
	 * @return	array		$arrAccess
	 */
	function createAccessArray($intCategoryId) {
		global $objDatabase, $_ARRAYLANG;
		$intCategoryId 	= intval($intCategoryId);
		$arrAccess 		= array();

		$objResult = $objDatabase->Execute('SELECT		group_id,
														group_name,
														group_description
											FROM		'.DBPREFIX.'access_user_groups
											WHERE		type = "frontend"
											ORDER BY	group_id ASC
										');
		while (!$objResult->EOF) {
			$objSubResult = $objDatabase->SelectLimit('	SELECT	`read`,
																`write`,
																`edit`,
																`delete`,
																`move`,
																`close`,
																`sticky`
														FROM	'.DBPREFIX.'module_forum_access
														WHERE	category_id = '.$intCategoryId.'
														AND		group_id = '.$objResult->fields['group_id'], 1);
			if ($objSubResult->RecordCount() == 1) {
				//there are rights existing for this group
				$arrAccess[$objResult->fields['group_id']] = array(	'name'		=>	htmlentities(stripslashes($objResult->fields['group_name']),ENT_QUOTES, CONTREXX_CHARSET),
																	'desc'		=>	htmlentities(stripslashes($objResult->fields['group_description']),ENT_QUOTES, CONTREXX_CHARSET),
																	'read'		=>	$objSubResult->fields['read'],
																	'write'		=>	$objSubResult->fields['write'],
																	'edit'		=>	$objSubResult->fields['edit'],
																	'delete'	=>	$objSubResult->fields['delete'],
																	'move'		=>	$objSubResult->fields['move'],
																	'close'		=>	$objSubResult->fields['close'],
																	'sticky'	=>	$objSubResult->fields['sticky']
																);
			} else {
				//no rights in database for this group
				$arrAccess[$objResult->fields['group_id']] = array(	'name'		=>	htmlentities(stripslashes($objResult->fields['group_name']),ENT_QUOTES, CONTREXX_CHARSET),
																	'desc'		=>	htmlentities(stripslashes($objResult->fields['group_description']),ENT_QUOTES, CONTREXX_CHARSET)
																);
			}

			$objResult->MoveNext();
		}

		//anonymous access
		$objSubResult = $objDatabase->SelectLimit('	SELECT		`read`,
																`write`,
																`edit`,
																`delete`,
																`move`,
																`close`,
																`sticky`
														FROM	'.DBPREFIX.'module_forum_access
														WHERE	category_id = '.$intCategoryId.'
														AND		group_id = 0', 1);

		$arrAccess[0] = array(	'name'		=>	$_ARRAYLANG['TXT_FORUM_ANONYMOUS_GROUP_NAME'],
								'desc'		=>	$_ARRAYLANG['TXT_FORUM_ANONYMOUS_GROUP_DESC'],
								'read'		=>	$objSubResult->fields['read'],
								'write'		=>	$objSubResult->fields['write'],
								'edit'		=>	$objSubResult->fields['edit'],
								'delete'	=>	$objSubResult->fields['delete'],
								'move'		=>	$objSubResult->fields['move'],
								'close'		=>	$objSubResult->fields['close'],
								'sticky'	=>	$objSubResult->fields['sticky']
							);
		return $arrAccess;
	}

	/**
	 * returns the category ID of the specified thread
	 *
	 * @param int $intThreadId
	 * @return integer on succes, bool false on failure
	 */
	function _getCategoryIdFromThread($intThreadId){
		global $objDatabase;
		$query = '	SELECT category_id
					FROM '.DBPREFIX.'module_forum_postings
					WHERE thread_id = '.$intThreadId;

		$objRS = $objDatabase->SelectLimit($query, 1);
		if($objRS !== false){
			if($objRS->RecordCount() == 1){
				return $objRS->fields['category_id'];
			}else{
				return false;
			}
		}else{
			echo "Database Error:".$objDatabase->ErrorMsg();
			return false;
		}
	}

	/**
	 * shorten a string to a custom length
	 *
	 * @param string $str input string
	 * @param integer $maxLength desired maximum length
	 * @return string $str shortened string, if longer than specified max lenght
	 */
	function _shortenString($str, $maxLength){
		if(strlen($str) > $maxLength){
			return substr($str, 0, $maxLength-3).'...';
		}
		return $str;
	}

	/**
	 * return position of the last posting in the thread
	 *
	 * @param int $intPostId
	 * @param int $intThreadId
	 * @return int $pos
	 */
	function _getLastPos($intPostId, $intThreadId=0){
		global $objDatabase;
		if($intThreadId < 1){ //thread ID not supplied, select from DB
			$query = "	SELECT `thread_id` FROM `".DBPREFIX."module_forum_postings`
						WHERE `id` = ".$intPostId;
			if( ($objRS = $objDatabase->SelectLimit($query,1)) !== false){
				$intThreadId = $objRS->fields['thread_id'];
			}
		}
		$query = "	SELECT count(1) AS `cnt` FROM `".DBPREFIX."module_forum_postings`
					WHERE `thread_id` = ".$intThreadId.'
					ORDER BY `time_created` ASC';
		if(($objRS = $objDatabase->SelectLimit($query, 1)) !== false){
			$pos = $objRS->fields['cnt']-1;
			if($pos < $this->_arrSettings['thread_paging']){ //pos is in the first paging page, return 0
				return 0;
			}else{ //not in first page, return position
				$remain = $pos % $this->_arrSettings['thread_paging'];
				$pos -= $remain;
				return $pos;
			}
		}
		return false;
	}

	/**
	 * return postition of the selected post to edit
	 *
	 * @param integer $intPostId
	 * @param integer $intThreadId
	 * @return unknown
	 */
	function _getEditPos($intPostId, $intThreadId){
		global $objDatabase;
		$count = 0;
		$query = "	SELECT `id` FROM `".DBPREFIX."module_forum_postings`
					WHERE `thread_id` = ".$intThreadId.'
					ORDER BY `time_created` ASC';
		if(($objRS = $objDatabase->Execute($query)) !== false){
			while(!$objRS->EOF){
				if($objRS->fields['id'] == $intPostId){//id matched, return position of that post
					$remain = $count % $this->_arrSettings['thread_paging'];
					$pos = $count - $remain;
					if($pos > 0){
						return $pos;
					}else{
						return 0;
					}
				}
				$count++;
				$objRS->MoveNext();
			}
		}
	}
}
?>
