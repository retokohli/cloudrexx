<?php

/**
 * Knowledge library
 *
 * @copyright Comvation AG <info@comvation.com>
 * @author Stefan Heinemann <sh@comvation.com>
 */

/**
 * Includes
 */
require_once ASCMS_LIBRARY_PATH.'/activecalendar/activecalendar.php';
require_once ASCMS_MODULE_PATH.'/knowledge/lib/Category.class.php';
require_once ASCMS_MODULE_PATH.'/knowledge/lib/Articles.class.php';
if (!class_exists("DatabaseError")) {
    require_once ASCMS_MODULE_PATH . '/knowledge/lib/databaseError.class.php';
}
require_once ASCMS_MODULE_PATH."/knowledge/lib/Settings.class.php";
require_once ASCMS_MODULE_PATH."/knowledge/lib/Tags.class.php";

/**
 * Knowledge library
 *
 * Some basic operations for the knowledge module
 * @copyright Comvation AG <info@comvation.com>
 * @author Stefan Heinemann <sh@comvation.com>
 * @package contrexx
 * @subpackage  module_knowledge
 */
class KnowledgeLibrary {
    /**
     * The settings object
     *
     * @var KnowledgeSettings
     */
    protected $settings;

    /**
     * The articles object
     *
     * @var KnowledgeArticles
     */
    protected $articles;

    /**
     * The categories object
     *
     * @var KnowledgeCategory
     */
    protected $categories;

    /**
     * The tags object
     *
     * @var KnowledgeTags
     */
    protected $tags;

    /**
     * Initialise the needed objects
     */
    public function __construct() {
        $this->categories = new KnowledgeCategory();
        $this->articles = new KnowledgeArticles();
        $this->settings = new KnowledgeSettings();
        $this->tags = new KnowledgeTags();

        $this->_arrLanguages     = $this->createLanguageArray();
    }

    /**
     * Update the global setting
     *
     * @param int $value
     * @throws DatabaseError
     * @global $objDatabase
     */
    protected function updateGlobalSetting($value)
    {
        global $objDatabase;

        // seems a bit dirty i know
        require_once ASCMS_DOCUMENT_ROOT . "/core/settings.class.php";

        $query = " UPDATE ".DBPREFIX."settings
                   SET setvalue = '".$value."'
                   WHERE setname = 'useKnowledgePlaceholders'";
        $objDatabase->Execute($query);
        if ($objDatabase->Execute($query) === false) {
            throw new DatabaseError("error setting the global value");
        }


        $objSettings = new settingsManager();
        $objSettings->writeSettingsFile();
    }

    /**
     * Return the global setting
     *
     * @return string
     * @throws DatabaseError
     * @global $objDatabase
     * @return mixed
     */
    protected function getGlobalSetting()
    {
        global $objDatabase;

        $query = " SELECT setvalue FROM ".DBPREFIX."settings
                   WHERE setname = 'useKnowledgePlaceholders'";
        $result = $objDatabase->Execute($query);

        if ($result === false) {
           throw new DatabaseError('error getting the global value');
        } else {
            if ($result->RecordCount()) {
                return $result->fields['setvalue'];
            }
        }
    }

    /**
     * Creates an array containing all frontend-languages.
     *
     * Contents:
     * $arrValue[$langId]['short']        =>    For Example: en, de, fr, ...
     * $arrValue[$langId]['long']        =>    For Example: 'English', 'Deutsch', 'French', ...
     *
     * this is old blog stuff actually...
     * @global     object        $objDatabase
     * @return    array        $arrReturn
     */
    function createLanguageArray()
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute('
            SELECT id, lang, name
              FROM '.DBPREFIX.'languages
             WHERE frontend=1
             ORDER BY id');
        $arrReturn = array();
        while (!$objResult->EOF) {
            $arrReturn[$objResult->fields['id']] = array(
                'short' => stripslashes($objResult->fields['lang']),
                'long'  => htmlentities(stripslashes($objResult->fields['name']),ENT_QUOTES, CONTREXX_CHARSET),
            );
            $objResult->MoveNext();
        }
        return $arrReturn;
    }

    /**
     * Handles comment database operations.
     * Expects cleaned data! (do injection checking on arguments first)
     *
     * @param string command 'create'
     * @param int articleContentId commented article contents' id
     * @param array commentData { 'comment' => string, 'email' => string, 'name' => string, 'title' => string }
     * @param int commentId optional. supply if we're commenting a comment (follow up comment).
     * @return array { 'status' => 'success'|'error', ['message' => string] }
     */
    protected function comment($command, $articleContentId, $commentData, $commentId = 0)
    {
	global $objDatabase;

	$qTarget;
	//check if comments target exists
	if($commentId == 0) //target is an article
        {
	    $qTarget = '
              SELECT id 
                FROM '.DBPREFIX.'module_knowledge_article_content
               WHERE id='.$articleContentId.'
               LIMIT 1';
        }
	else //target is a comment
	{
	    $qTarget = '
              SELECT id 
                FROM '.DBPREFIX.'module_knowledge_comments
               WHERE id='.$commentId.'
               LIMIT 1';
	}
	//try to fetch the target
	$objTarget = $objDatabase->Execute($qTarget);
	if($objTarget->EOF) //no target
	    return array('status' => 'error', 'message' => 'non-existent target given');

	$query = '';
	//create mysql string for columns
	$dataColumns = join(array_keys($commentData),',');
	//create mysql string for data
	$dataValues = array_values($commentData);
	// { val, val2 } => { 'val', 'val2' }
	array_walk($dataValues, array($this, 'addStringTokens'));
	$dataValues = join($dataValues,',');

	//our target is valid. next, decide what's to do.
	switch($command)
	{
	case 'create':
	    $query = '
              INSERT INTO '.DBPREFIX.'module_knowledge_comments
              (article_content_id, parent_id, '.$dataColumns.') VALUES (
              '.$articleContentId.','.$commentId.','.$dataValues.')';
	}
	if($objDatabase->Execute($query) === false)
	    return array('status' => 'error', 'message' => 'query failed.');

	return array('status' => 'success', 'data' => $commentData);
    }

    /**
     * @see KnowledgeLib::comment()
     */
    public function addStringTokens(&$string)
    {
	$string = "'".$string."'";
    }

    /**
     * Loads comments for an Article's content.
     * Expects cleaned data! (do injection checking on arguments first)
     *
     * @global $objDatabase
     * @param int $articleContentId
     * @param boolean $addHiddenProperties include email in output. used for backend.
     * @return array { 'id' => int, 'name' => string, 'title' => string, 'subject' => string [,'email' => string] }
     */
    protected function loadComments($articleContentId, $addHiddenProperties = false)
    {
	global $objDatabase;

	$additionalColumns = '';
	if($addHiddenProperties)
	    $additionalColumns = ', email';
	//todo: do not select comments without article (join)
	$query = 'SELECT id, parent_id, name, subject, comment'.$additionalColumns.'
                  FROM '.DBPREFIX.'module_knowledge_comments
                  WHERE article_content_id = '.$articleContentId.'
                  ORDER BY article_content_id, parent_id';

	$comments = array();
       
	$rows = $objDatabase->Execute($query);
	while(!$rows->EOF) {
	    $tmp = array(
			 'id' => $rows->fields['id'],
			 'name' => $rows->fields['name'],
			 'subject' => $rows->fields['subject'],
			 'comment' => $rows->fields['comment']
			 );
	    if($addHiddenProperties)
		$tmp['email'] = $rows->fields['email'];

	    $parentId = $rows->fields['parent_id'];
	    if($parentId == 0){
		array_push($comments,$tmp);
	    }
	
	    $rows->MoveNext();
	}

	return $comments;
    }

    /**
     * Counts comments of an article.
     * Expects cleaned data! (do injection checking on arguments first)
     *
     * @global $objDatabase
     * @param int $articleId
     * @return int
     */
    protected function countComments($articleContentId)
    {
	global $objDatabase;
	$query = 'SELECT COUNT(id) as c
                  FROM '.DBPREFIX.'module_knowledge_comments
                  WHERE article_content_id = '.$articleContentId;

	$rows = $objDatabase->Execute($query);
	if(!$rows->EOF)
	{
	    return $rows->fields['c'];
	}
	return -1;
    }

    /**
     * Delete a comment.
     *
     * @global $objDatabase
     * @param int $commentId
     */
    protected function deleteComment($commentId)
    {
	global $objDatabase;
	$query = 'DELETE
                  FROM '.DBPREFIX.'module_knowledge_comments
                  WHERE id = '.$commentId;

	$rows = $objDatabase->Execute($query);
    }
}

?>
