<?php
/**
 * Knowledge backend stuff
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Stefan Heinemann <sh@comvation.com>
 * @package     contrexx
 * @subpackage  module_knowledge
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH.'/knowledge/lib/knowledgeLib.class.php';
require_once ASCMS_MODULE_PATH.'/knowledge/lib/knowledgePaging.class.php';

define("KNOWLEDGE_ACCESS_ID_KNOWLEDGE",       129);
define("KNOWLEDGE_ACCESS_ID_OVERVIEW",        130);
define("KNOWLEDGE_ACCESS_ID_EDIT_ARTICLES",   131);
define("KNOWLEDGE_ACCESS_ID_CATEGORIES",      132);
define("KNOWLEDGE_ACCESS_ID_EDIT_CATEGORIES", 133);
define("KNOWLEDGE_ACCESS_ID_SETTINGS",        134);

/**
 * All the backend stuff of the knowledge module
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Stefan Heinemann <sh@comvation.com>
 * @package     contrexx
 * @subpackage  module_knowledge
 */
class KnowledgeAdmin extends KnowledgeLibrary
{
    /**
     * Page Title
     * @var string
     */
    private $pageTitle    = '';

    /**
     * Error message
     * @var string
     */
    private $errorMessage = "";

    /**
     * Ok message
     * @var string
     */
    private $okMessage     = '';

    /**
     * Container for the adodb Object
     * @var object
     */
    private $tpl;

    /**
     * The id of the current language
     * @var int
     */
    private $languageId = 1;

    /**
    * Constructor Create the module-menu and an internal template-object
    * @global $objInit
    * @global $objTemplate
    * @global $_CORELANG
    */
    public function __construct()
    {
        global $objInit, $objTemplate, $_ARRAYLANG;

        Permission::checkAccess(KNOWLEDGE_ACCESS_ID_KNOWLEDGE, 'static');

        KnowledgeLibrary::__construct();
        $this->tpl = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/knowledge'.MODULE_INDEX.'/template');
        CSRF::add_placeholder($this->tpl);
        $this->tpl->setErrorHandling(PEAR_ERROR_DIE);
        $this->languageId = $objInit->userFrontendLangId;
        $objTemplate->setVariable("CONTENT_NAVIGATION","   <a href=\"index.php?cmd=knowledge".MODULE_INDEX."&amp;section=articles\">".$_ARRAYLANG['TXT_KNOWLEDGE_ARTICLES']."</a>
                                                           <a href=\"index.php?cmd=knowledge".MODULE_INDEX."&amp;section=categories\">".$_ARRAYLANG['TXT_KNOWLEDGE_CATEGORIES']."</a>
                                                           <a href=\"index.php?cmd=knowledge".MODULE_INDEX."&amp;section=settings\">".$_ARRAYLANG['TXT_KNOWLEDGE_SETTINGS']."</a>
                                                           ");
    }


    /**
     * Return the page depending on the $_GET-params
     * @global $objPerm
     * @global $objTemplate
     * @global $_ARRAYLANG
     */
    function getPage()
    {
        global $objTemplate, $_ARRAYLANG;

        if(!isset($_GET['act'])) {
            $_GET['act']='';
        }
        $_GET['section'] = (empty($_GET['section'])) ? "" :  $_GET['section'];
        $active = '';
        switch ($_GET['section']) {
            // The categories
            case 'categories':
                switch ($_GET['act']) {
                    case 'add':
                        Permission::checkAccess(KNOWLEDGE_ACCESS_ID_EDIT_CATEGORIES, 'static');
                        $content = $this->editCategory(true);
                        $active = "add";
                        break;
                    case 'edit':
                        Permission::checkAccess(KNOWLEDGE_ACCESS_ID_EDIT_CATEGORIES, 'static');
                        $content = $this->editCategory();
                        break;
                    case 'update':
                        Permission::checkAccess(KNOWLEDGE_ACCESS_ID_EDIT_CATEGORIES, 'static');
                        $id = $this->updateCategory();
                        CSRF::header("Location: index.php?cmd=knowledge".MODULE_INDEX."&section=categories&act=overview&highlight=".$id);
                        break;
                    case 'insert':
                        Permission::checkAccess(KNOWLEDGE_ACCESS_ID_EDIT_CATEGORIES, 'static');
                        $id = $this->insertCategory();
                        CSRF::header("Location: index.php?cmd=knowledge".MODULE_INDEX."&section=categories&act=overview&highlight=".$id);
                        break;
                    case 'delete':
                        Permission::checkAccess(KNOWLEDGE_ACCESS_ID_EDIT_CATEGORIES, 'static');
                        $this->deleteCategory();
                        break;
                    case 'switchState':
                        $this->checkAjaxAccess(KNOWLEDGE_ACCESS_ID_EDIT_CATEGORIES);
                        $this->switchCategoryState();
                        break;
                    case 'sort':
                        $this->checkAjaxAccess(KNOWLEDGE_ACCESS_ID_EDIT_CATEGORIES);
                        $this->sortCategory();
                        break;
                    case 'overview':
                    default:
                       Permission::checkAccess(KNOWLEDGE_ACCESS_ID_CATEGORIES, 'static');
                       $content = $this->categoriesOverview();
                       $active = "overview";
                       break;
                }
                $this->categories($content, $active);
                break;

            // The articles
            case 'articles':
                switch ($_GET['act']) {
                    case 'add':
                        Permission::checkAccess(KNOWLEDGE_ACCESS_ID_EDIT_ARTICLES, 'static');
                        $content = $this->editArticle(true);
                        $active = "add";
                        break;
                    case 'edit':
                        Permission::checkAccess(KNOWLEDGE_ACCESS_ID_EDIT_ARTICLES, 'static');
                        $content = $this->editArticle();
                        break;
                    case 'insert':
                        Permission::checkAccess(KNOWLEDGE_ACCESS_ID_EDIT_ARTICLES, 'static');
                        $id = $this->insertArticle();
                        $content = $this->articleOverview();
                        $active = "overview";
                        break;
                    case 'update':
                        Permission::checkAccess(KNOWLEDGE_ACCESS_ID_EDIT_ARTICLES, 'static');
                        $id = $this->updateArticle();
                        $content = $this->articleOverview();
                        CSRF::header("Location: index.php?cmd=knowledge".MODULE_INDEX."&section=articles&act=edit&id=".$id."&updated=true");
                        break;
                    case 'getArticles':
                        Permission::checkAccess(KNOWLEDGE_ACCESS_ID_OVERVIEW, 'static');
                        $this->getArticles();
                        break;
                    case 'getArticlesByRating':
                        Permission::checkAccess(KNOWLEDGE_ACCESS_ID_OVERVIEW, 'static');
                        $this->getArticlesByRating();
                        break;
                    case 'getArticlesByViews':
                        Permission::checkAccess(KNOWLEDGE_ACCESS_ID_OVERVIEW, 'static');
                        $this->getArticlesByViews();
                        break;
                    case 'getArticlesGlossary':
                        Permission::checkAccess(KNOWLEDGE_ACCESS_ID_OVERVIEW, 'static');
                        $this->getArticlesGlossary();
                        break;
                    case 'searchArticles':
                        Permission::checkAccess(KNOWLEDGE_ACCESS_ID_OVERVIEW, 'static');
                        $this->searchArticles();
                        break;
                    case 'sort':
                        $this->checkAjaxAccess(KNOWLEDGE_ACCESS_ID_EDIT_ARTICLES);
                        $this->sortArticles();
                        break;
                    case 'switchState':
                        $this->checkAjaxAccess(KNOWLEDGE_ACCESS_ID_EDIT_ARTICLES);
                        $this->switchArticleState();
                        break;
                    case 'getTags':
                        Permission::checkAccess(KNOWLEDGE_ACCESS_ID_OVERVIEW, 'static');
                        $this->getTags();
                        break;
                    case 'delete':
                        $this->checkAjaxAccess(KNOWLEDGE_ACCESS_ID_EDIT_ARTICLES);
                        $this->deleteArticle();
                        break;
		    case 'getComments': //ajax request: comments for article_content_id
                        $this->checkAjaxAccess(KNOWLEDGE_ACCESS_ID_EDIT_ARTICLES);
			$this->getComments();
		    case 'delComment': //ajax request: delete comment with id id
                        $this->checkAjaxAccess(KNOWLEDGE_ACCESS_ID_EDIT_ARTICLES);
			$this->delComment();
                    case 'overview':
                    default:
                        Permission::checkAccess(KNOWLEDGE_ACCESS_ID_OVERVIEW, 'static');
                        $content = $this->articleOverview();
                        $active = "overview";
                        break;
                }
                $this->articles($content, $active);
                break;
            case 'settings':
                Permission::checkAccess(KNOWLEDGE_ACCESS_ID_SETTINGS, 'static');
                switch ($_GET['act']) {
                    case 'tidyTags':
                        $this->tidyTags();
                        break;
                    case 'resetVotes':
                        $this->resetVotes();
                        break;
                    case 'placeholders':
                        $content = $this->settingsPlaceholders();
                        $active = "placeholders";
                        break;
                    case 'update':
                        $this->updateSettings();
                        try {
                            $this->settings->readSettings();
                        } catch (DatabaseError $e) {
                            $this->errorMessage = $_ARRAYLANG['TXT_KNOWLEDGE_ERROR_OVERVIEW'];
                            $this->errorMessage .= $e->formatted();
                        }
                        $content = $this->settingsOverview();
                        $active = "settings";
                        break;
                    case 'show':
                    default:
                        $content = $this->settingsOverview();
                        $active = "settings";
                        break;
                }
                $this->settings($content, $active);
                break;
            default:
                CSRF::header("Location: index.php?cmd=knowledge".MODULE_INDEX."&section=articles");
        }
        $objTemplate->setVariable(array(
            'CONTENT_TITLE'          => $this->pageTitle,
            'CONTENT_OK_MESSAGE'     => $this->okMessage,
            'CONTENT_STATUS_MESSAGE' => $this->errorMessage,
            'ADMIN_CONTENT'          => $this->tpl->get(),
        ));
    }


    /**
     * Check acces for ajax request
     *
     * When the page is ajax requested the response should be
     * different so that the page can display a message that the user
     * hasn't got permissions to do what he tried.
     * Hence, this function returns a JSON object containing a status
     * code (0 for fail) and an error message.
     * @param int $id
     * @global $_ARRAYLANG
     */
    private function checkAjaxAccess($id)
    {
        global $_ARRAYLANG;

        if (!Permission::checkAccess($id, 'static', true)) {
            $this->sendAjaxError($_ARRAYLANG['TXT_KNOWLEDGE_ACCESS_DENIED']);
        }
    }


    /**
     * Send ajax error message
     *
     * Sends an json object for ajax request to communcate that there has been
     * an error.
     * @param string $message
     */
    private function sendAjaxError($message)
    {
        die("{'status' : 0, 'message' : '".$message."'}");
    }


    /**
     * Main function for categories
     *
     * Shows the bar on the top with the category section links
     * @param int $content
     * @param int $active
     * @global $_ARRAYLANG
     */
    private function categories($content, $active)
    {
        global $_ARRAYLANG;

        $this->tpl->loadTemplateFile('module_knowledge_categories.html', true, true);
        $this->tpl->setGlobalVariable("MODULE_INDEX", MODULE_INDEX);
        $this->tpl->setVariable(array(
            "CATEGORIES_FILE"       => $content,
            "ACTIVE_".strtoupper($active) => "class=\"subnavbar_active\"",
            "TXT_OVERVIEW"          => $_ARRAYLANG['TXT_OVERVIEW'],
            "TXT_ADD"               => $_ARRAYLANG['TXT_ADD'],
        ));
    }


    /**
     * Inserts a new category
     * @global $_ARRAYLANG
     * @global $objDatabase
     * @return int The id of the inserted category
     */
    private function insertCategory()
    {
        global $_ARRAYLANG;

        $parentCategory = intval($_POST['parentCategory']);
        $state = intval($_POST['state']);
        foreach ($_POST as $key => $var) {
            if (strstr($key, "name_")) {
                $lang = substr($key, 5);
                $this->categories->addContent($lang, $var);
            }
        }
        return $this->categories->insertCategory($state, $parentCategory);
    }


    /**
     * Delete a category
     *
     * This function is called through ajax and deletes
     * a category.
     * @param int $catId
     */
    private function deleteCategory($catId=null)
    {
        if (!isset($catId)) {
            $catId = intval($_GET['id']);
        }
        try {
            $deletedCategories = $this->categories->deleteCategory($catId);
            // delete the articles that were assigned to the deleted categories
            foreach ($deletedCategories as $cat) {
                $this->articles->deleteArticlesByCategory($cat);
            }
        } catch (DatabaseError $e) {
            $this->sendAjaxError($e->formatted());
        }
        die();
    }


    /**
     * Delete an article
     */
    private function deleteArticle()
    {
        $id = intval($_GET['id']);
        try {
            $this->articles->deleteOneArticle($id);
            $this->tags->clearTags($id);
        } catch (DatabaseError $e) {
            $this->sendAjaxError($e->formatted());
        }
    }


    /**
     * Update an existing category.
     * @global $_ARRAYLANG
     * @global $objDatabase
     * @return int Id of the updated category
     */
    private function updateCategory()
    {
        global $_ARRAYLANG;

        $id = intval($_POST['update_id']);
        $parentCategory = intval($_POST['parentCategory']);
        $state = intval($_POST['state']);
        foreach ($_POST as $key => $var) {
            if (strstr($key, "name_")) {
                $lang = substr($key, 5);
                $this->categories->addContent($lang, $var);
            }
        }
        $this->categories->updateCategory($id, $state, $parentCategory);
        return $id;
    }


    /**
     * Shows an overview of all entries.
     *
     * @global $_CORELANG
     * @global $_ARRAYLANG
     * @global $_LANGID
     * @return string HTML code for the overview
     */
    private function categoriesOverview()
    {
        global $_CORELANG, $_ARRAYLANG;

        $this->pageTitle = $_ARRAYLANG['TXT_KNOWLEDGE_CATEGORIES'];
        $this->tpl->loadTemplateFile('module_knowledge_categories_overview.html',true,true);
        $this->tpl->setGlobalVariable("MODULE_INDEX", MODULE_INDEX);

        $this->tpl->setVariable(array(
            'TXT_CATEGORIES'                 => $_ARRAYLANG['TXT_KNOWLEDGE_CATEGORIES'],
            'TXT_NAME'                       => $_ARRAYLANG['TXT_KNOWLEDGE_CATEGORY_NAME'],
            'TXT_ACTIONS'                    => $_ARRAYLANG['TXT_KNOWLEDGE_CATEGORY_ACTIONS'],
            'TXT_NO_CATEGORY_OBJECTS'        => $_ARRAYLANG['TXT_KNOWLEDGE_NO_CATEGORY_OBJECTS'],
            'TXT_CONFIRM_CATEGORY_DELETION'  => $_ARRAYLANG['TXT_KNOWLEDGE_CONFIRM_CATEGORY_DELETION'],
            'TXT_ENTRIES_AMOUNT'             => $_ARRAYLANG['TXT_KNOWLEDGE_ENTRIES_AMOUNT'],
            'TXT_SORT'                       => $_ARRAYLANG['TXT_KNOWLEDGE_SORT'],
            'TXT_ENTRIES_SUBTITLE_DATE'      => $_ARRAYLANG['TXT_KNOWLEDGE_ENTRY_MANAGE_DATE'],
            'TXT_ENTRIES_SUBTITLE_SUBJECT'   => $_ARRAYLANG['TXT_KNOWLEDGE_ENTRY_ADD_SUBJECT'],
            'TXT_ENTRIES_SUBTITLE_LANGUAGES' => $_ARRAYLANG['TXT_KNOWLEDGE_CATEGORY_ADD_LANGUAGES'],
            'TXT_ENTRIES_SUBTITLE_HITS'      => $_ARRAYLANG['TXT_KNOWLEDGE_ENTRY_MANAGE_HITS'],
            'TXT_ENTRIES_SUBTITLE_COMMENTS'  => $_ARRAYLANG['TXT_KNOWLEDGE_ENTRY_MANAGE_COMMENTS'],
            'TXT_ENTRIES_SUBTITLE_VOTES'     => $_ARRAYLANG['TXT_KNOWLEDGE_ENTRY_MANAGE_VOTE'],
            'TXT_ENTRIES_SUBTITLE_USER'      => $_CORELANG['TXT_USER'],
            'TXT_ENTRIES_SUBTITLE_EDITED'    => $_ARRAYLANG['TXT_KNOWLEDGE_ENTRY_MANAGE_UPDATED'],
            'TXT_ENTRIES_DELETE_ENTRY_JS'    => $_ARRAYLANG['TXT_KNOWLEDGE_ENTRY_DELETE_JS'],
            'TXT_ENTRIES_MARKED'             => $_ARRAYLANG['TXT_KNOWLEDGE_CATEGORY_MANAGE_SUBMIT_MARKED'],
            'TXT_ENTRIES_SELECT_ALL'         => $_ARRAYLANG['TXT_KNOWLEDGE_CATEGORY_MANAGE_SUBMIT_SELECT'],
            'TXT_ENTRIES_DESELECT_ALL'       => $_ARRAYLANG['TXT_KNOWLEDGE_CATEGORY_MANAGE_SUBMIT_DESELECT'],
            'TXT_ENTRIES_SUBMIT_SELECT'      => $_ARRAYLANG['TXT_KNOWLEDGE_CATEGORY_MANAGE_SUBMIT_ACTION'],
            'TXT_ENTRIES_SUBMIT_DELETE'      => $_ARRAYLANG['TXT_KNOWLEDGE_CATEGORY_MANAGE_SUBMIT_DELETE'],
            'TXT_ENTRIES_SUBMIT_DELETE_JS'   => $_ARRAYLANG['TXT_KNOWLEDGE_ENTRY_MANAGE_SUBMIT_DELETE_JS'],
            'EDIT_ALLOWED'                   => (Permission::checkAccess(KNOWLEDGE_ACCESS_ID_EDIT_CATEGORIES, 'static', true)) ? "true" : "false",
            'NOT_ALLOWED_MSG'                => $_ARRAYLANG['TXT_KNOWLEDGE_ACCESS_DENIED'],
        ));

        try {
            $this->categories->readCategories();
            $this->articles->readArticles();
        } catch (DatabaseError $e) {
            $this->errorMessage = $_ARRAYLANG['TXT_KNOWLEDGE_ERROR_OVERVIEW'];
            $this->errorMessage .= $e->formatted();
            return null;
        }
        $categories = $this->parseCategoryOverview($this->categories->categoryTree);
        $this->tpl->replaceBlock("remove_area", "");
        $this->tpl->setVariable("CATLIST", $categories);
        return $this->tpl->get();
    }


    /**
     * Recursive function to parse the categories
     * @param array $arr
     * @param int $level
     * @param int $id
     * @global $_LANGID
     * @return string
     */
    private function parseCategoryOverview($arr, $level=0, $id=0)
    {
        global $_LANGID;

        $rows = "";
        $ul_id = '';
        foreach ($arr as $key => $subcategories) {
            if (count($subcategories)) {
                $sub = $this->parseCategoryOverview($subcategories, $level+1, $key);
            } else {
                $sub = "";
            }
            $ul_id = "ul_".$id;
            $category = $this->categories->categories[$key];
            $this->tpl->setVariable(array(
                    'CSS_DISPLAY'               => ($level == 0) ? "" : "none",
                    'CSS_BGCOLOR'               => (!empty($_GET['highlight']) && $_GET['highlight'] == $key) ? "#ceff88" : "",
                       'CATEGORY_ID'                => $key,
                       'CATEGORY_ACTIVE_LED'       => ($category['active']) ? "green" : "red",
                       'CATEGORY_ACTIVE_STATE'     => ($category['active']) ? 0 : 1,
                       'CATEGORY_INDENT'           => ($level == 1) ? 18 : $level * 28,
                       'CATEGORY_SUBJECT'          => $category['content'][$_LANGID]['name'],
                    'CATEGORY_PLUS_VISIBLITY'   => (count($subcategories))  ? "visible" : "hidden",
                    'SUB'                       => $sub,
                    'ENTRIES_AMOUNT'            => $this->articles->countEntriesByCategory($key),
                    'SORTABLE_ID'               => $ul_id
                   ));
//            if ($level) {
//                $this->tpl->touchBlock("arrow");
//                $this->tpl->parse("arrow");
//            }
            $this->tpl->parse("row");
            $rows .= $this->tpl->get("row", true);
        }

        $this->tpl->setVariable(array(
            "ROWS"      => $rows,
            "UL_ID"     => $ul_id
        ));
        $this->tpl->touchBlock("list");
        $this->tpl->parse("list");
        $list = $this->tpl->get("list", true);

        $this->tpl->setVariable(array(
            "SORTABLE_ID"     => $ul_id
        ));
        $this->tpl->parse("sortable");
        return $list;
    }


    /**
     * Well, it seems that this is not needed anymore
     * @param $id
     * @param unknown_type $level
     */
    /*
    private function parseOverviewArticle($id, $level, $rowId)
    {
        global $_LANGID;

        $level = ($level > 0) ? $level+1 : $level;
        $article = &$this->articles->articles[$id];
        $this->tpl->setVariable(array(
            "QUESTION_ID"           => $id,
            "QUESTION"              => $article['content'][$_LANGID]['article'],
            "QUESTION_INDENT"       => $level * 17,
            "QUESTION_ACTIVE_LED"   => ($article['active']) ? "green" : "red",
            "QUESTION_ACTIVE_STATE" => ($article['active']) ? 0 : 1
        ));

        $this->tpl->parse("article");
        $this->tpl->setVariable(array(
            "ROW_ID"        => $rowId,
            "ROW_STYLE"     => "style=\"display: none;\"",
            "PARENT_CLASS"  => $article['category']
        ));
        $this->tpl->parse("row");
    }*/


    /**
     * Show the page for adding or editing a new category
     *
     * @param bool Is it a new category or are we editing?
     * @global $_ARRAYLANG
     * @return string
     */
    private function editCategory($new=false)
    {
        global $_ARRAYLANG;

        $this->pageTitle = $_ARRAYLANG['TXT_EDIT_CATEGORY'];
        $this->tpl->loadTemplateFile('module_knowledge_categories_edit.html',true,true);
        $this->tpl->setGlobalVariable("MODULE_INDEX", MODULE_INDEX);

        // the language variables
        $this->tpl->setGlobalVariable(array(
            "TXT_GENERAL_SETTINGS"  => ($new) ? $_ARRAYLANG['TXT_KNOWLEDGE_ADD_CATEGORY'] : $_ARRAYLANG['TXT_KNOWLEDGE_EDIT_CATEGORY'] ,
            "TXT_PARENT_CATEGORY"   => $_ARRAYLANG['TXT_KNOWLEDGE_PARENT_CATEGORY'],
            "TXT_STATE"             => $_ARRAYLANG['TXT_KNOWLEDGE_STATE'],
            "TXT_ACTIVE"            => $_ARRAYLANG['TXT_KNOWLEDGE_ACTIVE'],
            "TXT_INACTIVE"          => $_ARRAYLANG['TXT_KNOWLEDGE_INACTIVE'],
            "TXT_QUESTION"          => $_ARRAYLANG['TXT_KNOWLEDGE_QUESTION'],
            "TXT_ANSWER"            => $_ARRAYLANG['TXT_KNOWLEDGE_ANSWER'],
            "TXT_SUBMIT"            => $_ARRAYLANG['TXT_KNOWLEDGE_SUBMIT'],
            "TXT_NAME"              => $_ARRAYLANG['TXT_KNOWLEDGE_CATEGORY_NAME'],
            "TXT_EDIT_EXTENDED"     => $_ARRAYLANG['TXT_KNOWLEDGE_CATEGORY_ADD_EXTENDED'],
            "TXT_TOP_CATEGORY"      => $_ARRAYLANG['TXT_KNOWLEDGE_TOP_CATEGORY'],
        ));

        $languages = $this->createLanguageArray();

          try {
            $this->categories->readCategories();
        } catch (DatabaseError $e) {
            $this->errorMessage = $_ARRAYLANG['TXT_ERROR_READING_CATEGORIES'];
            $this->errorMessage .= $e->formatted();
            return '';
        }

        if ($new) {
            $catId = 0;
            // make a dummy category
            $category = array(
               'active'    => 1,
               'parent'    => 0,
               'content'  => array()
            );

            foreach (array_keys($languages) as $key) {
                $category['content'][$key] = array(
                   'name' => "",
                );
            }
        } else {
            // get the category data
            $catId = intval($_GET['id']);
            $category = $this->categories->categories[$catId];
        }

        $langKeys = array_keys($languages);

        // the general data
        $this->tpl->setVariable(array(
           "CATEGORY_DROPDOWN"     => $this->categoryDropdown($this->categories->categoryTree, $category['parent']),
           "ACTIVE_CHECKED"        => ($category['active']) ? "checked=\"checked\"" : "",
           "INACTIVE_CHECKED"      => ($category['active']) ? "" : "checked=\"checked\"",
           "EDIT_NAME"             => $category['content'][$langKeys[0]]['name'],
           "FORM_ACTION"           => ($new) ? "insert" : "update",
           "UPDATE_ID"             => $catId
        ));

        // the different languages
        foreach($languages as $langId => $lang) {
            $this->tpl->setVariable(array(
                'EDIT_NAME_LANGID'    =>    $langId,
                'EDIT_NAME_LANG'    =>    $lang['long'],
                'EDIT_NAME_VALUE'    =>    $category['content'][$langId]['name'] // empty since we make a new category
            ));
            $this->tpl->parse('lang_name');
        }
        return $this->tpl->get();
    }


    private function getIndexOptionList($index = 0)
    {
        $opts = "";
        for ($i = 0; $i < 26; $i++) {
            $letter = chr($i + 65);
            if ($index === $letter) {
                $select = "selected=\"selected\"";
            } else {
                $select = "";
            }
            $opts .= "<option value=\"".$letter."\" $select>".$letter."</option>";
        }
        return $opts;
    }


    /**
     * Category Dropdown
     *
     * Return a string of option-tags for a dropdown
     * containing all categories.
     * This function is called recursively
     * @param $categories The categories to parse
     * @param $select The id of the entry that should be selected in the dropdown
     * @param $level The level of the current recursion
     * @global $_LANGID
     * @return string The option-tags of the dropdown
     */
    private function categoryDropdown($categories, $select = 0, $level = 0, $lang=1)
    {
        $options = "";
        foreach ($categories as $category => $subcats) {
            // the option line
            $name = $this->categories->categories[$category]['content'][$lang]['name'];
            $selected = ($select == $category) ? "selected=\"selected\"" : "";
            $options .= "<option value=\"".$category."\" ".$selected.">".str_repeat("..", $level).$name."</option>";
            // do the subcategories
            if (count($subcats)) {
                $options .= $this->categoryDropdown($subcats, $select, $level+1, $lang);
            }
        }
        return $options;
    }

    /**
     * Returns menuoptions for assigning multiple categories
     * @param   array     $categories   The categories to parse
     * @param   string    $assigned     The comma separated list of assigned
     *                                  category IDs
     * @param   integer   $level        The level of the current recursion
     * @global  integer   $_LANGID      The language ID
     * @return  array                   The HTML options for the "assigned" and
     *                                  "unassigned" categories
     */
    private function categoryMultiselectOptions(
        $categories, $assigned='', $level=0
    ) {
        global $_LANGID;

        $options = array('unassigned' => '', 'assigned' => '');
        foreach ($categories as $category_id => $subcats) {
            $name = $this->categories->categories[$category_id]['content'][$_LANGID]['name'];
            $option =
                '<option value="'.$category_id.'"'.
                '>'.str_repeat('..', $level).$name.'</option>';
            if (FWValidator::is_value_in_comma_separated_list(
                $category_id, $assigned)
            ) {
                $options['assigned'] .= $option;
            } else {
                $options['unassigned'] .= $option;
            }
            // do the subcategories
            if (count($subcats)) {
                $suboptions = $this->categoryMultiselectOptions(
                    $subcats, $assigned, $level+1);
                $options['assigned'] .= $suboptions['assigned'];
                $options['unassigned'] .= $suboptions['unassigned'];
            }
        }
        return $options;
    }


    /**
     * This is not called anywhere
     *
     * @param unknown_type $tree
     * @return unknown
     */
    /*
    private function prepareJsTree($tree)
    {
        $arr = array();
        foreach ($tree as $key => $elem) {
            $arr[$key] = array(
                "id"            => $key,
                "state"         => 0
            );
            if (count($elem) > 0) {
                $arr[$key]["children"] =  $this->prepareJsTree($elem);
            }
        }
        return $arr;
    }
    */

    /**
     * Switch the category state
     *
     * Make a category either active or inactive. This is
     * called through ajax.
     */
    private function switchCategoryState()
    {
        $id = intval($_GET['id']);
        $action = intval($_GET['switchTo']);

        try {
            if ($action == 1) {
                $this->categories->activate($id);
            } else {
                $this->categories->deactivate($id);
            }
        } catch (DatabaseError $e) {
            $this->sendAjaxError($e->formatted());
        }

        die(1);
    }

    /**
     * Switch the entry state
     *
     * Make a state either active or inactive. This is
     * called through ajax.
     */
    private function switchArticleState()
    {
        $id = intval($_GET['id']);
        $action = intval($_GET['switchTo']);

        try {
            if ($action == 1) {
                $this->articles->activate($id);
            } else {
                $this->articles->deactivate($id);
            }
        } catch (DatabaseError $e) {
            $this->sendAjaxError($e->formatted());
        }
        die(1);
    }

    /**
     * Show the article title bar
     *
     * Show the article title bar and below the content of the
     * current page.
     * @global $_ARRAYLANG
     * @param string $content The content that should be display below the bar
     * @param string $active The menu entry that should be active
     */
    private function articles($content, $active)
    {
        global $_ARRAYLANG;

        $this->tpl->loadTemplateFile('module_knowledge_articles.html', true, true);
        $this->tpl->setGlobalVariable("MODULE_INDEX", MODULE_INDEX);
        $this->tpl->setVariable(array(
            "ARTICLES_FILE"                 => $content,
            "ACTIVE_".strtoupper($active)   => "class=\"subnavbar_active\"",
            "TXT_OVERVIEW"          => $_ARRAYLANG['TXT_OVERVIEW'],
            "TXT_ADD"               => $_ARRAYLANG['TXT_ADD'],
        ));
    }

    /**
     * Overview over the articles
     *
     * @global $_ARRAYLANG
     * @global $_CORELANG
     * @return string
     */
    private function articleOverview()
    {
        global $_ARRAYLANG, $_CORELANG;

        try {
            $articles = $this->articles->getNewestArticles();
        } catch (DatabaseError $e) {

        }
        $paging = "";
        $articlelist = $this->parseArticleList($articles, $_ARRAYLANG['TXT_KNOWLEDGE_LAST_ENTRIES'],
            $paging, true, false);

        $this->pageTitle = $_ARRAYLANG['TXT_KNOWLEDGE_ARTICLES'];
        $this->tpl->loadTemplateFile("module_knowledge_articles_overview.html");
        $this->tpl->setGlobalVariable("MODULE_INDEX", MODULE_INDEX);
        //$this->tpl->replaceBlock("remove_area", "");

        try {
            $this->categories->readCategories();
            $catTree = $this->overviewCategoryTreeSpawner($this->categories->categoryTree);
        } catch (DatabaseError $e) {
            $this->errorMessage = $_ARRAYLANG['TXT_KNOWLEDGE_ERROR_OVERVIEW'];
            $this->errorMessage .= $e->formatted();
            return null;
        }

        $this->tpl->setVariable(array(
            // language variables
            "TXT_SELECT_CATEGORY"           => $_ARRAYLANG['TXT_KNOWLEDGE_SELECT_CATEGORY'],
            "TXT_CONFIRM_ARTICLE_DELETION"  => $_ARRAYLANG['TXT_CONFIRM_ARTICLE_DELETION'],
            "TXT_JUMP_TO_ARTICLE"           => $_ARRAYLANG['TXT_KNOWLEDGE_JUMP_TO_ARTICLE'],
            'TXT_CATEGORIES'                => $_ARRAYLANG['TXT_KNOWLEDGE_CATEGORIES'],
            'TXT_OVERVIEW'                  => $_ARRAYLANG['TXT_OVERVIEW'],
            'TXT_SEARCH_INPUT'              => $_ARRAYLANG['TXT_KNOWLEDGE_SEARCH_INPUT'],
            'TXT_SEARCH_ARTICLES'           => $_ARRAYLANG['TXT_KNOWLEDGE_SEARCH_ARTICLES'],
            'TXT_SEARCH'                    => $_ARRAYLANG['TXT_KNOWLEDGE_SEARCH'],
            'TXT_JUMP'                      => $_ARRAYLANG['TXT_KNOWLEDGE_JUMP'],

            // other stuff
            "CATLIST"                       => $catTree,
            "EDIT_ALLOWED"                  => (Permission::checkAccess(KNOWLEDGE_ACCESS_ID_EDIT_ARTICLES, 'static', true)) ? "true" : "false",
            'NOT_ALLOWED_MSG'               => $_ARRAYLANG['TXT_KNOWLEDGE_ACCESS_DENIED'],
            'TXT_ARTICLES'                  => $articlelist
        ));

        return $this->tpl->get();
    }

    /**
     * The spawner for the category tree
     */
    private function overviewCategoryTreeSpawner($catTree)
    {
        global $_ARRAYLANG;

        $this->tpl->setGlobalVariable(array(
            'BEST_RATED'        => $_ARRAYLANG['TXT_KNOWLEDGE_BEST_RATED_ARTICLES'],
            'MOST_READ'         => $_ARRAYLANG['TXT_KNOWLEDGE_MOST_READ_ARTICLES'],
            'GLOSSARY'          => $_ARRAYLANG['TXT_KNOWLEDGE_GLOSSARY_VIEW']
        ));

        return $this->overviewCategoryTree($catTree);
    }

    /**
     * Parse the category tree
     *
     * Recursively parse the category tree on the left
     * side of the article's overview.
     * @param array $catTree
     * @param int $level
     * @global $_LANGID
     * @return string
     */
    private function overviewCategoryTree($catTree, $level=0)
    {
        global $_LANGID;

        $rows = "";
        foreach ($catTree as $key => $subcategories) {
            if (count($subcategories)) {
                $sub = $this->overviewCategoryTree($subcategories, $level+1);
            } else {
                $sub = "";
            }
            $this->tpl->setVariable(array(
                "ID"                        => $key,
                "CATEGORY_ID"               => $key,
                "NAME"                      => $this->categories->categories[$key]['content'][$_LANGID]['name'],
                "SUB"                       => $sub,
                "CATEGORY_PLUS_VISIBLITY"   => (count($subcategories)) ? "visible" : "hidden",
                "CSS_DISPLAY"               => ($level == 0) ? "" : "none",
                "ENTRY_COUNT"               => $this->articles->countEntriesByCategory($key),
//                "CAT_ROW_WIDTH"                => 230 + $level ,
            ));
//            $this->tpl->touchBlock("arrow");
//            $this->tpl->parse("arrow");
            $this->tpl->parse("row");
            $rows .= $this->tpl->get("row", true);
        }
        $this->tpl->setVariable("ROWS", $rows);
        $this->tpl->touchBlock("list");
        $this->tpl->parse("list");
        return $this->tpl->get("list", true);
    }

    /**
     * Generate an articlelist
     *
     * @param $articles An array of articles
     * @param $category Category information
     * @return String
     */
    private function parseArticleList($articles, $categoryname="", $paging, $standalone=false, $showSort=true)
    {
        global $_ARRAYLANG, $_CORELANG, $_LANGID;


        $tpl = new HTML_Template_Sigma(ASCMS_MODULE_PATH."/knowledge/template/");
        CSRF::add_placeholder($tpl);
        $tpl->setErrorHandling(PEAR_ERROR_DIE);

        $tpl->loadTemplateFile("module_knowledge_articles_overview_articlelist.html");
        $tpl->setGlobalVariable("MODULE_INDEX", MODULE_INDEX);

        $tpl->setGlobalVariable(array(
            // language variables
            "TXT_NAME"          => $_ARRAYLANG['TXT_NAME'],
            "TXT_VIEWED"        => $_ARRAYLANG['TXT_KNOWLEDGE_VIEWED'],
            "TXT_SORT"          => $_ARRAYLANG['TXT_KNOWLEDGE_SORT'],
            "TXT_STATE"         => $_ARRAYLANG['TXT_KNOWLEDGE_STATE'],
            "TXT_QUESTION"      => $_ARRAYLANG['TXT_KNOWLEDGE_QUESTION'],
            "TXT_HITS"          => $_ARRAYLANG['TXT_KNOWLEDGE_HITS'],
            "TXT_RATING"        => $_ARRAYLANG['TXT_KNOWLEDGE_RATING'],
            "TXT_ACTIONS"       => $_ARRAYLANG['TXT_KNOWLEDGE_ACTIONS'],
            "TXT_CATEGORY_NAME" => $categoryname ,
            // getPaging(count, position, extraargv, paging-text, showeverytime, limit)
            "PAGING"            => $paging,
            "TXT_BY"             => "bei",
            "TXT_VOTINGS"         => "Abstimmungen"
        ));

        if ($showSort) {
            $tpl->touchBlock('sort-th');
        }

        if (!empty($articles)) {
            foreach ($articles as $article) {
                $tpl->setVariable(array(
                    "ARTICLEID"             => $article['id'],
                    "QUESTION"              => $article['content'][$_LANGID]['question'],
                    "ACTIVE_STATE"          => abs($article['active']-1),
                    "CATEGORY_ACTIVE_LED"   => ($article['active']) ? "green" : "red",
                    "HITS"                  => $article['hits'],
                    "VOTEVALUE"             => round(
                        (($article['votes'] > 0) ? $article['votevalue'] / $article['votes'] : 0),
                        2),
                    "VOTECOUNT"             => $article['votes'],
                    "MAX_RATING"            => $this->settings->get("max_rating"),
                ));

                if ($showSort) {
                    $tpl->setVariable(array(
                        'ARTICLEID_DRAG'    =>  $article['id']
                    ));
                    $tpl->parse('sort-td');
                }

                $tpl->parse("row");
            }
        } else {
            $tpl->setVariable(array(
                "TXT_NO_ARTICLES"       => $_ARRAYLANG['TXT_KNOWLEDGE_NO_ARTICLES_IN_CAT']
            ));
            $tpl->parse("no_articles");
        }

        if ($standalone) {
            $tpl->touchBlock('jsinit');
            $tpl->parse('jsinit');
        }

        $tpl->parse("content");
        return $tpl->get("content");
    }


    /**
     * Get Articles
     *
     * This function is called through ajax.
     * Return a JSON object with all the needed information for the
     * article overview.
     * @global $_LANGID
     * @global $_ARRAYLANG
     * @global $_CORELANG
     */
    private function getArticles()
    {
        global $_LANGID, $_ARRAYLANG, $_CORELANG, $_CONFIG;

        $id = intval($_GET['id']);
        $position = (isset($_GET['pos'])) ? intval($_GET['pos']) : 0;

        try {
            $articles = $this->articles->getArticlesByCategory($id);
            $category = $this->categories->getOneCategory($id);
        } catch (DatabaseError $e) {
            die($e->plain());
        }

        $paging = getKnowledgePaging(count($articles), $position,
            'javascript: articles.getCategory('.$id.', %POS%)', '', true, 0);

        $position = ($position > 0) ? $position -= 3 : $position;
        $content = $this->parseArticleList(array_slice($articles, $position, $_CONFIG['corePagingLimit']+3),
            $category['content'][$_LANGID]['name'], $paging);

        $response = Array();
        $response['list'] = $content;
        $response['position'] = $position;

        require_once(ASCMS_LIBRARY_PATH."/PEAR/Services/JSON.php");
        $objJson = new Services_JSON();
        $jsonResponse = $objJson->encode($response);
        echo $jsonResponse;
        die();
    }

    /**
     * Return articles according to their rating
     *
     * This function is called through ajax.
     * @global $_LANGID
     * @global $_ARRAYLANG
     * @global $_CORELANG
     */
    private function getArticlesByRating()
    {
        global $_LANGID, $_ARRAYLANG, $_CORELANG, $_CONFIG;

        $position = (isset($_GET['pos'])) ? intval($_GET['pos']) : 0;

        try {
            $articles = $this->articles->getBestRated($_LANGID, null);
        } catch (DatabaseError $e) {
            die($e->plain());
        }

        $paging = getKnowledgePaging(count($articles), $position,
            'javascript: articles.getBestRated(%POS%)', '', true, 0);

        $content = $this->parseArticleList(array_slice($articles, $position, $_CONFIG['corePagingLimit']),
            $_ARRAYLANG['TXT_KNOWLEDGE_BEST_RATED_ARTICLES'], $paging, false, false);

        $response = Array();
        $response['list'] = $content;

        require_once(ASCMS_LIBRARY_PATH."/PEAR/Services/JSON.php");
        $objJson = new Services_JSON();
        $jsonResponse = $objJson->encode($response);

        die($jsonResponse);
    }

    /**
    * Return articles according to their rating
    *
    * This function is called through ajax.
    * @global $_LANGID
    * @global $_ARRAYLANG
    * @global $_CORELANG
    */
    private function getArticlesByViews()
    {
        global $_LANGID, $_ARRAYLANG, $_CORELANG, $_CONFIG;

        $position = (isset($_GET['pos'])) ? intval($_GET['pos']) : 0;

        try {
            $articles = $this->articles->getMostRead($_LANGID, null);
        } catch (DatabaseError $e) {
            die($e->plain());
        }

        $paging = getKnowledgePaging(count($articles), $position,
            'javascript: articles.getMostRead(%POS%)', '', true, 0);

        $content = $this->parseArticleList(array_slice($articles, $position, $_CONFIG['corePagingLimit']),
            $_ARRAYLANG['TXT_KNOWLEDGE_MOST_READ_ARTICLES'], $paging, false, false);

        $response = Array();
        $response['list'] = $content;

        require_once(ASCMS_LIBRARY_PATH."/PEAR/Services/JSON.php");
        $objJson = new Services_JSON();
        $jsonResponse = $objJson->encode($response);

        die($jsonResponse);
    }

    /**
     * Return articles in a glossary form
     *
     * This function is galled throught ajax
     * @global $_LANGID
     * @global $_ARRAYLANG
     * @global $_CORELANG
     */
    private function getArticlesGlossary()
    {
        global $_LANGID, $_ARRAYLANG, $_CORELANG, $_CONFIG;

        $position = (isset($_GET['pos'])) ? intval($_GET['pos']) : 0;

        try {
            $articles = $this->articles->getGlossary($_LANGID);
        } catch (DatabaseError $e) {
            die($e->plain());
        }

        $content = "";
        if (empty($articles)) {
            $content = $this->parseArticleList(array(), '', false, false);
        } else {
            $articlecounter = 0;
            $step = $_CONFIG['corePagingLimit'];
            foreach ($articles as $key => $entries) {
                $parseEntries = array();
                foreach ($entries as $entry) {
                    $articlecounter++;
                    if ($articlecounter > ($position / 30) * $step &&
                            $articlecounter <= (($position / 30) + 1) * $step) {
                        $parseEntries[] = $entry;
                    }
                }
                if (count($parseEntries)) {
                    $content .= $this->parseArticleList($entries, $key, '', false, false);
                }
            }
        }

        $paging = getKnowledgePaging($articlecounter, $position,
            'javascript: articles.getGlossary(%POS%)', '', true, 0);

        $content .= $paging;

        $response = Array();
        $response['list'] = $content;

        require_once(ASCMS_LIBRARY_PATH."/PEAR/Services/JSON.php");
        $objJson = new Services_JSON();
        $jsonResponse = $objJson->encode($response);

        die($jsonResponse);
    }


    /**
     * Search for arcticles
     *
     * This function is called throug ajax
     * @global $_LANGID
     * @global $_ARRAYLANG
     * @global $_CORELANG
     * @global $_CONFIG
     */
    private function searchArticles()
    {
        global $_LANGID, $_ARRAYLANG, $_CORELANG, $_CONFIG;

        $searchterm = $_GET['term'];
        if (empty($searchterm)) {
            die();
        }
        $position = (isset($_GET['pos'])) ? intval($_GET['pos']) : 0;

        try {
            $articles = $this->articles->searchArticles($searchterm, $_LANGID);
        } catch (DatabaseError $e) {
            die($e->plain());
        }

        $paging = getKnowledgePaging(count($articles), $position,
            'javascript: articles.searchArticles(\''.$searchterm.'\', %POS%)', '', true, 0);

        $title = $_ARRAYLANG['TXT_KNOWLEDGE_SEARCH_RESULTS_OF']." '".$searchterm."'";

        $content = $this->parseArticleList(array_slice($articles, $position, $_CONFIG['corePagingLimit']),
            $title, $paging, false, false);

        $response = Array();
        $response['list'] = $content;

        require_once(ASCMS_LIBRARY_PATH."/PEAR/Services/JSON.php");
        $objJson = new Services_JSON();
        $jsonResponse = $objJson->encode($response);

        die($jsonResponse);
    }


    /**
     * Show the edit form
     *
     * Show the form to edit or add an article
     * @param bool $new If this is going to be a new article
     * @global $_ARRAYLANG
     * @return string
     */
    private function editArticle($new = false)
    {
        global $_ARRAYLANG;

	JS::registerJS('lib/javascript/jquery-ui-1.8.2.js'); //this is a fix so jquery-ui works
	JS::registerJS('modules/knowledge/backend/commentInterface.js');

        $this->pageTitle = $_ARRAYLANG['TXT_EDIT_ARTICLE'];
        $this->tpl->loadTemplateFile('module_knowledge_articles_edit.html', true, true);
        $this->tpl->setGlobalVariable(array(
            "MODULE_INDEX"          =>  MODULE_INDEX,
            "TXT_INDEX"             => $_ARRAYLANG['TXT_KNOWLEDGE_INDEX'],
            "TXT_NO_INDEX"          => $_ARRAYLANG['TXT_KNOWLEDGE_NO_INDEX'],
        ));

        $id = (empty($_GET['id'])) ? 0 : $_GET['id'];
        try {
            $this->categories->readCategories();
            if (!$new) {
                $article = $this->articles->getOneArticle($id);
                if (!$article) {
                    $this->errorMessage = $_ARRAYLANG['TXT_KNOWLEDGE_ERROR_NO_ARTICLE'];
                    return null;
                }
                $tags = $this->tags->getByArticle($id);
            }
        } catch (DatabaseError $e) {
            $this->errorMessage = $_ARRAYLANG['TXT_KNOWLEDGE_ERROR_OVERVIEW'];
            $this->errorMessage .= $e->formatted();
            return '';
        }

        $languages = $this->createLanguageArray();
        // make an empty article if a new article is to be added
        if ($new) {
            $article = array(
               'category'      => 0,
               'active'        => 1,
               'content'       => array(),
            );
            foreach (array_keys($languages) as $key) {
                $article['content'][$key] = array(
                   'question' => "",
                   'answer' => "",
                   'index' => 0,
                );
            }
        }

        if (isset($_GET['updated']) && $_GET['updated']) {
           $this->okMessage = $_ARRAYLANG['TXT_KNOWLEDGE_SUCCESSFULLY_SAVED'];
        }
        $category_options = $this->categoryMultiselectOptions(
            $this->categories->categoryTree, $article['category']);
        $this->tpl->setGlobalVariable(array(
            'TXT_CATEGORY'               => $_ARRAYLANG['TXT_KNOWLEDGE_CATEGORY'],
            'TXT_ACTIVE'                 => $_ARRAYLANG['TXT_KNOWLEDGE_ACTIVE'],
            'TXT_STATUS'                 => $_ARRAYLANG['TXT_KNOWLEDGE_STATE'],
            'TXT_INACTIVE'               => $_ARRAYLANG['TXT_KNOWLEDGE_INACTIVE'],
            'TXT_SUBMIT'                 => $_ARRAYLANG['TXT_KNOWLEDGE_SUBMIT'],
            'TXT_CHOOSE_CATEGORY'        => $_ARRAYLANG['TXT_KNOWLEDGE_CHOOSE_CATEGORY'],
            'TXT_PLEASE_CHOOSE_CATEGORY' => $_ARRAYLANG['TXT_KNOWLEDGE_PLEASE_CHOOSE_CATEGORY'],
	    'TXT_KNOWLEDGE_CATEGORIES'   => $_ARRAYLANG['TXT_KNOWLEDGE_CATEGORIES'],
        ));

        $first = true;
        foreach ($languages as $langId => $lang) {
            // tags
            if (!$new) {
                $tagstring = "";
                foreach ($tags as $tag) {
                    if ($tag['lang'] == $langId) {
                       $tagstring.= $tag['name'].", ";
                    }
                }
                // chop the last ', ' off
                $tagstring = substr($tagstring, 0, -2);
                $this->tpl->setVariable(array(
                   "TAGS"      => $tagstring
                ));
            }

            $this->tpl->setVariable(array(
               "TABS_NAME"         => $lang['long'],
               "TABS_DIV_ID"       => $lang['long'],
               "TABS_LINK_ID"      => $lang['long'],
               "TABS_LANG_ID"      => $langId,
               "TABS_CLASS"        => ($first) ? "active" : "inactive",
               "CONTENT_ID"        => isset($article['content'][$langId]['id']) ? $article['content'][$langId]['id'] : -1,
            ));
            $this->tpl->parse("showLanguageTabs");

            $this->tpl->setVariable(array(
                "TXT_QUESTION"      => $_ARRAYLANG['TXT_KNOWLEDGE_QUESTION'],
                "TXT_ANSWER"        => $_ARRAYLANG['TXT_KNOWLEDGE_ANSWER'],
                "TXT_SORT_BY"       => $_ARRAYLANG['TXT_KNOWLEDGE_SORT_BY'],
                "TXT_TAGS"          => $_ARRAYLANG['TXT_KNOWLEDGE_TAGS'],
                "TXT_COMMA_SEPARATED" => $_ARRAYLANG['TXT_KNOWLEDGE_COMMA_SEPARATED'],
                "TXT_AVAILABLE_TAGS" => $_ARRAYLANG['TXT_KNOWLEDGE_AVAILABLE_TAGS'],
                "TXT_POPULARITY"    => $_ARRAYLANG['TXT_KNOWLEDGE_POPULARITY'],
                "TXT_ALPHABETICAL"  => $_ARRAYLANG['TXT_KNOWLEDGE_ALPHABETICAL'],

                "LANG"              => $lang['long'],
                "LANG_ID"           => $langId,
                "DISPLAY"           => ($first) ? "block" : "none",
                "ID"                => $lang['long'],
                "QUESTION"          => (isset($article['content'][$langId]) ?
                                       $article['content'][$langId]['question']
                                       : ''),
                "ANSWER"            => isset($article['content'][$langId]) ?
                                       htmlentities($article['content'][$langId]['answer'], ENT_QUOTES, CONTREXX_CHARSET)
                                       : '',
		"TXT_INDEX_OPTIONS"  => $this->getIndexOptionList(
                    (isset($article['content'][$langId])
                      ? $article['content'][$langId]['index'] : 0)),
//                "CATEGORIES"        => $this->categoryDropdown($this->categories->categoryTree, $article['category'], 0, $langId),
                "ACTION"            => ($new) ? "insert" : "update",
                "TITLE"             => ($new) ? $_ARRAYLANG['TXT_KNOWLEDGE_ADD'] : $_ARRAYLANG['TXT_KNOWLEDGE_EDIT'],

                "ACTIVE_CHECKED"    => $article['active'] ? "checked=\"checked\"" : "",
                "INACTIVE_CHECKED"  => $article['active'] ? "" : "checked=\"checked\"",
                'KNOWLEDGE_NOT_ASSIGNED_CATEGORIES' => $category_options['unassigned'],
                'KNOWLEDGE_ASSIGNED_CATEGORIES' => $category_options['assigned'],
                'TXT_KNOWLEDGE_AVAILABLE_CATEGORIES' => $_ARRAYLANG['TXT_KNOWLEDGE_AVAILABLE_CATEGORIES'],
                'TXT_KNOWLEDGE_CHECK_ALL' => $_ARRAYLANG['TXT_KNOWLEDGE_CHECK_ALL'],
                'TXT_KNOWLEDGE_UNCHECK_ALL' => $_ARRAYLANG['TXT_KNOWLEDGE_UNCHECK_ALL'],
                'TXT_KNOWLEDGE_ASSIGNED_CATEGORIES' => $_ARRAYLANG['TXT_KNOWLEDGE_ASSIGNED_CATEGORIES'],
		'TXT_MANAGE' => $_ARRAYLANG['TXT_MANAGE'],
		'TXT_CONFIRM_COMMENT_DELETION' => $_ARRAYLANG['TXT_CONFIRM_COMMENT_DELETION'],

		'TXT_DELETE' => $_ARRAYLANG['TXT_DELETE'],
		'TXT_CANCEL' => $_ARRAYLANG['TXT_CANCEL'],
		'TXT_NAME' => $_ARRAYLANG['TXT_NAME'],
		'TXT_EMAIL' => $_ARRAYLANG['TXT_EMAIL'],
		'TXT_TITLE' => $_ARRAYLANG['TXT_TITLE'],
		
            ));
            $this->tpl->parse("langDiv");
            if ($first) {
		
		/*
		 *comment_editing: set the initial language id so we can initialize the knowledgeInterface-JS correctly.
		 */
		$this->tpl->setVariable(array(
		    "CONTENT_ID"        => isset($article['content'][$langId]['id']) ? $article['content'][$langId]['id'] : -1,
		));
		$this->tpl->parse("comment_editing");

                $this->tpl->setVariable(array(
                    "ANSWER_PREVIEW"        => get_wysiwyg_editor('answer_preview',
                                               isset($article['content'][$langId]) ?
                                               $article['content'][$langId]['answer']
                                               : ''),
                    "KNOWLEDGE_ANSWER_LANG"  => $langId,
		    "CONTENT_ID"        => isset($article['content'][$langId]['id']) ? $article['content'][$langId]['id'] : -1,
                ));

		
            }
            $first = false;
        }
        if (!$new) {
            $this->tpl->setVariable("ID", $id);
            $this->tpl->parse("edit_id");
        }
        return $this->tpl->get();
    }


    /**
     * Insert an article
     * @global $_ARRAYLANG
     * @return int Id of the inserted article
     */
    private function insertArticle()
    {
        global $_ARRAYLANG;

        //$category = $_POST['category'];
        //$state = $_POST['state'];
        $languages = $this->createLanguageArray();

        $tags = array();
        // the following is a bit ugly
        // had to do this because the boss wanted to have those things inside
        // of all the actions in the edit form, so there are multiple input things
        // for every language that do the same, and here just the first is selected
        $lang_keys = array_keys($languages);
        $firstlang = $lang_keys[0];
        $state = $_POST['state_'.$firstlang];
//        $category = $_POST['category_'.$firstlang];
        // The categories may be posted in any of the available languages
        for ($lang = 1; $lang <= 6; ++$lang) {
            if (empty($_POST['knowledge_assigned_categories_'.$lang])) {
                continue;
            }
            $_POST['knowledge_assigned_categories'] =
                $_POST['knowledge_assigned_categories_'.$lang];
            break;
        }
        if (empty($_POST['knowledge_assigned_categories'])) {
            return null;
        }
        $categories = $_POST['knowledge_assigned_categories'];
        $tags = array();
        foreach ($languages as $langId => $lang) {
            $question = $_POST['question_'.$langId];
            $answer = $_POST['answer_'.$langId];
            $index = $_POST['index_'.$langId];

            $this->articles->addContent($langId, $question, $answer, $index);
            $tags[$langId] = $_POST['tags_'.$langId];
        }

        try {
//            $id = $this->articles->insert($category, $state);
            $id = $this->articles->insert($categories, $state);
            foreach ($tags as $lang => $tag) {
                $this->tags->insertFromString($id, $tag, $lang);
            }
        } catch (DatabaseError $e) {
            $this->errorMessage = $_ARRAYLANG['TXT_KNOWLEDGE_ERROR_OVERVIEW'];
            $this->errorMessage .= $e->formatted();
            return null;
        }
        return $id;
    }


    /**
     * Update an article
     * @global $_ARRAYLANG
     * @return $id Id of the updated article
     */
    private function updateArticle()
    {
        global $_ARRAYLANG;

        //$state = $_POST['state'];
        $id = $_POST['id'];

        $languages = $this->createLanguageArray();
        // the following is a bit ugly
        // had to do this because the boss wanted to have those things inside
        // of all the actions in the edit form, so there are multiple input things
        // for every language that do the same, and here just the first is selected

        $lang_keys = array_keys($languages);
        $firstlang = $lang_keys[0];
        $state = $_POST['state_'.$firstlang];
//        $category = $_POST['category_'.$firstlang];
        // The categories may be posted in any of the available languages
        for ($lang = 1; $lang <= 6; ++$lang) {
            if (empty($_POST['knowledge_assigned_categories_'.$lang])) {
                continue;
            }
            $_POST['knowledge_assigned_categories'] =
                $_POST['knowledge_assigned_categories_'.$lang];
            break;
        }
        if (empty($_POST['knowledge_assigned_categories'])) {
            return null;
        }
        $categories = $_POST['knowledge_assigned_categories'];

        foreach ($languages as $langId => $lang) {
            $question = $_POST['question_'.$langId];
            $answer = $_POST['answer_'.$langId];
            $index = $_POST['index_'.$langId];

            $this->articles->addContent($langId, $question, $answer, $index);
            $tags[$langId] = $_POST['tags_'.$langId];
        }

        try {
//            $this->articles->update($id, $category, $state);
            $this->articles->update($id, $categories, $state);
            $this->tags->clearTags($id);
            foreach ($tags as $lang => $tag) {
                $this->tags->insertFromString($id, $tag, $lang);
            }
        } catch (DatabaseError $e) {
            $this->errorMessage = $_ARRAYLANG['TXT_KNOWLEDGE_ERROR_OVERVIEW'];
            $this->errorMessage .= $e->formatted();
            return null;
        }
        return $id;
    }

    /**
     * Save article order
     *
     * Save the new order of article. Called through ajax.
     */
    private function sortArticles()
    {
        if (empty($_POST['articlelist'])) {
            die();
        }

        $offset = (isset($_GET['offset'])) ? intval($_GET['offset']) : 0;

        try {
            foreach ($_POST['articlelist'] as $position => $id) {
                $this->articles->setSort($id, $position + $offset);
            }
        } catch (DatabaseError $e) {
            $this->sendAjaxError($e->formatted());
        }

        die();

        /*
        print $_GET['order'];
        $order = split("articlelist\[\]=", $_GET['order']);

        foreach ($order as $sort => $id) {
            $id = intval($id);
            if ($id) {
                $this->articles->setSort($id, $sort);
            }
        }

        die();
        */
    }

    /**
     * Save a category's order
     *
     * Called through ajax.
     */
    private function sortCategory()
    {
        $keys = array_keys($_POST);
        try {
            if (preg_match("/ul_[0-9]*/", $keys[0])) {
                foreach ($_POST[$keys[0]] as $position => $id) {
                    $this->categories->setSort($id, $position);
                }
            }
        } catch (DatabaseError $e) {
            $this->sendAjaxError($e->formatted());
        }

        die(1);
    }

    /**
     * Return a list of tags
     *
     * Called through ajax
     */
    private function getTags()
    {
        $lang = (isset($_GET['lang'])) ? $_GET['lang'] : 1;
        try {
            if ($_GET['sort'] == "popularity") {
                $tags = $this->tags->getAllOrderByPopularity($lang);
            } else {
                $tags = $this->tags->getAllOrderAlphabetically($lang);
            }
        } catch (DatabaseError $e) {
            // TODO
            // this is not handled anyhow (and not only here)
            $this->sendAjaxError($e->formatted());
        }
        $this->tpl->loadTemplateFile('module_knowledge_articles_edit_taglist.html');
        $this->tpl->setGlobalVariable("MODULE_INDEX", MODULE_INDEX);
        $return_tags = array();
        $classnumber = 1;
        foreach ($tags as $tag) {
            $this->tpl->setVariable(array(
                "TAG"       => $tag['name'],
                "TAGID"     => $tag['id'],
                "CLASSNUMBER" => (++$classnumber % 2) + 1,
                "LANG"      => $lang,
            ));
            $this->tpl->parse("tag");
            $return_tags[$tag['id']] = $tag['name'];
        }
        $this->tpl->parse("taglist");
        $taglist = $this->tpl->get("taglist");

        require_once(ASCMS_LIBRARY_PATH."/PEAR/Services/JSON.php");
        $objJson = new Services_JSON();
        $jsonResponse = $objJson->encode(array("html" => $taglist, "available_tags" => $return_tags));

        die($jsonResponse);
    }

    /**
     * Show the settings title row
     *
     * @param string $content
     * @param string $active
     * @global $_ARRAYLANG
     * @global $_CORELANG
     */
    private function settings($content, $active)
    {
        global $_ARRAYLANG, $_CORELANG;

        $this->tpl->loadTemplateFile('module_knowledge_settings_top.html', true, true);
        $this->tpl->setGlobalVariable("MODULE_INDEX", MODULE_INDEX);

        $this->tpl->setVariable(array(
            "SETTINGS_FILE"                 => $content,
            "ACTIVE_".strtoupper($active)   => "class=\"subnavbar_active\""
        ));

        $this->tpl->setVariable(array(
            "TXT_SETTINGS"          => $_ARRAYLANG['TXT_KNOWLEDGE_SETTINGS'],
            "TXT_PLACEHOLDER"       => $_ARRAYLANG['TXT_KNOWLEDGE_PLACEHOLDER']
        ));
    }


    /**
     * Show the settings
     *
     * @global $_ARRAYLANG
     * @global $_CORELANG
     * @return string  The html code of the settings page
     */
    private function settingsOverview()
    {
        global $_ARRAYLANG, $_CORELANG;

        $this->pageTitle = $_ARRAYLANG['TXT_SETTINGS'];
        $this->tpl->loadTemplateFile('module_knowledge_settings.html',true,true);
        $this->tpl->setGlobalVariable("MODULE_INDEX", MODULE_INDEX);

        $this->tpl->setVariable(array(
           'TXT_SETTINGS'                          => $_ARRAYLANG['TXT_SETTINGS'],
           'TXT_FRONTPAGE'                         => $_ARRAYLANG['TXT_FRONTPAGE'],
           'TXT_MAX_SUBCATEGORIES'                 => $_ARRAYLANG['TXT_KNOWLEDGE_MAX_SUBCATEGORIES'],
           'TXT_MAX_SUBCATEGORIES_DESCRIPTION'     => $_ARRAYLANG['TXT_KNOWLEDGE_MAX_SUBCATEGORIES_DESCRIPTION'],
           'TXT_COLUMN_NUMBER'                     => $_ARRAYLANG['TXT_KNOWLEDGE_COLUMN_NUMBER'],
           'TXT_COLUMN_NUMBER_DESCRIPTION'         => $_ARRAYLANG['TXT_KNOWLEDGE_COLUMN_NUMBER_DESCRIPTION'],
           'TXT_SAVE'                              => $_CORELANG['TXT_SAVE'],
           'TXT_ARTICLES'                          => $_ARRAYLANG['TXT_KNOWLEDGE_ARTICLES'],
           'TXT_MAX_RATING'                        => $_ARRAYLANG['TXT_KNOWLEDGE_MAX_RATING'],
           'TXT_MAX_RATING_DESCRIPTION'            => $_ARRAYLANG['TXT_KNOWLEDGE_MAX_RATING_DESCRIPTION'],

           'TXT_COLUMN_MOST_READ_COUNT_DESCRIPTION'    => $_ARRAYLANG['TXT_KNOWLEDGE_COLUMN_MOST_READ_COUNT_DESCRIPTION'],
           'TXT_GENERAL'                           => $_ARRAYLANG['TXT_KNOWLEDGE_GENERAL'],
           'TXT_TIDY_TAGS'                         => $_ARRAYLANG['TXT_KNOWLEDGE_TIDY_TAGS'],
           'TXT_TIDY_TAGS_DESCRIPTION'             => $_ARRAYLANG['TXT_KNOWLEDGE_TIDY_TAGS_DESCRIPTION'],
           'TXT_RESET_HITS'                        => $_ARRAYLANG['TXT_KNOWLEDGE_RESET_HITS'],
           'TXT_RESET_HITS_DESCRIPTION'            => $_ARRAYLANG['TXT_KNOWLEDGE_RESET_HITS_DESCRIPTION'],
           'TXT_REPLACE_PLACEHOLDERS'              => $_ARRAYLANG['TXT_KNOWLEDGE_REPLACE_PLACEHOLDERS'],
           'TXT_REPLACE_PLACEHOLDERS_DESCRIPTION'  => $_ARRAYLANG['TXT_KNOWLEDGE_REPLACE_PLACEHOLDERS_DESCRIPTION'],

           'TXT_BEST_RATED_PLACEHOLDER'            => $_ARRAYLANG['TXT_KNOWLEDGE_BEST_RATED_PLACEHOLDER'],
           'TXT_BEST_RATED_AMOUNT'                 => $_ARRAYLANG['TXT_KNOWLEDGE_BEST_RATED_AMOUNT'],
           'TXT_BEST_RATED_AMOUNT_DESCRIPTION'     => $_ARRAYLANG['TXT_KNOWLEDGE_BEST_RATED_AMOUNT_DESCRIPTION'],
           'TXT_BEST_RATED_SIDEBAR_LENGTH'               => $_ARRAYLANG['TXT_KNOWLEDGE_BEST_RATED_SIDEBAR_LENGTH'],
           'TXT_BEST_RATED_SIDEBAR_LENGTH_DESCRIPTION'   => $_ARRAYLANG['TXT_KNOWLEDGE_BEST_RATED_SIDEBAR_LENGTH_DESCRIPTION'],
           'TXT_BEST_RATED_TEMPLATE'               => $_ARRAYLANG['TXT_KNOWLEDGE_BEST_RATED_TEMPLATE'],
           'TXT_BEST_RATED_TEMPLATE_DESCRIPTION'   => $_ARRAYLANG['TXT_KNOWLEDGE_BEST_RATED_TEMPLATE_DESCRIPTION'],

           'TXT_MOST_READ_PLACEHOLDER'             => $_ARRAYLANG['TXT_KNOWLEDGE_MOST_READ_PLACEHOLDER'],
           'TXT_MOST_READ_AMOUNT'                  => $_ARRAYLANG['TXT_KNOWLEDGE_MOST_READ_AMOUNT'],
           'TXT_MOST_READ_SIDEBAR_LENGTH'          => $_ARRAYLANG['TXT_KNOWLEDGE_MOST_READ_SIDEBAR_LENGTH'],
           'TXT_MOST_READ_SIDEBAR_LENGTH_DESCRIPTION' => $_ARRAYLANG['TXT_KNOWLEDGE_MOST_READ_SIDEBAR_LENGTH_DESCRIPTION'],
           'TXT_MOST_READ_TEMPLATE'                => $_ARRAYLANG['TXT_KNOWLEDGE_MOST_READ_TEMPLATE'],
           'TXT_MOST_READ_TEMPLATE_DESCRIPTION'    => $_ARRAYLANG['TXT_KNOWLEDGE_MOST_READ_TEMPLATE_DESCRIPTION']
        ));

        $this->tpl->setVariable(array(
           'COLUMN_NUMBER'                     => $this->settings->get('column_number'),
           'MAX_SUBCATEGORIES'                 => $this->settings->get('max_subcategories'),
           'MAX_RATING'                        => $this->settings->get('max_rating'),
           'BEST_RATED_SIDEBAR_LENGTH'         => $this->settings->get('best_rated_sidebar_length'),
           'BEST_RATED_SIDEBAR_AMOUNT'         => $this->settings->get('best_rated_sidebar_amount'),
           'BEST_RATED_SIDEBAR_TEMPLATE'       => $this->settings->get('best_rated_sidebar_template'),
           'REPLACE_PLACEHOLDERS_CHECKED'      => ($this->getGlobalSetting()) ? "checked=\"checked\"" : '',
           'MOST_READ_SIDEBAR_LENGTH'          => $this->settings->get('most_read_sidebar_length'),
           'MOST_READ_SIDEBAR_AMOUNT'          => $this->settings->get('most_read_sidebar_amount'),
           'MOST_READ_SIDEBAR_TEMPLATE'        => $this->settings->get('most_read_sidebar_template'),
           'MOST_READ_AMOUNT'                  => $this->settings->get('most_read_amount'),
           'BEST_RATED_AMOUNT'                 => $this->settings->get('best_rated_amount')
        ));

        return $this->tpl->get();
    }

    /**
     * Update settings
     *
     * Save the given settings
     */
    private function updateSettings()
    {
        try {
            $this->settings->set("column_number", $_POST['column_number']);
            $this->settings->set("max_subcategories", $_POST['max_subcategories']);
            $this->settings->set("max_rating", $_POST['max_rating']);
            $this->settings->set("best_rated_sidebar_template", $_POST['best_rated_sidebar_template']);
            $this->settings->set("best_rated_sidebar_length", $_POST['best_rated_sidebar_length']);
            $this->settings->set("best_rated_sidebar_amount", $_POST['best_rated_sidebar_amount']);
            $this->settings->set("most_read_sidebar_template", $_POST['most_read_sidebar_template']);
            $this->settings->set("most_read_sidebar_amount", $_POST['most_read_sidebar_amount']);
            $this->settings->set("most_read_sidebar_length", $_POST['most_read_sidebar_length']);
            $this->settings->set("most_read_amount", $_POST['most_read_amount']);
            $this->settings->set("best_rated_amount", $_POST['best_rated_amount']);

            $this->updateGlobalSetting(!empty($_POST['useKnowledgePlaceholders']) ? 1 : 0);
        } catch (DatabaseError $e) {
            global $_ARRAYLANG;
            $this->errorMessage = $_ARRAYLANG['TXT_KNOWLEDGE_ERROR_OVERVIEW'];
               $this->errorMessage .= $e->formatted();
        }
    }

    /**
     * Tidy the tags
     *
     * Call the function that removes unecessary tags.
     * Called through ajax.
     */
    private function tidyTags()
    {
        global $_ARRAYLANG;
        try {
            $this->tags->tidy();
            die(json_encode(array('ok' => $_ARRAYLANG['TXT_KNOWLEDGE_TIDY_TAGS_SUCCESSFUL'])));
        } catch (DatabaseError $e) {
            $this->sendAjaxError($e->formatted());
        }

        die();
    }

    /**
     * Reset the vote statistics
     *
     * Called through ajax.
     */
    private function resetVotes()
    {
        global $_ARRAYLANG;
        try {
            $this->articles->resetVotes();
            die(json_encode(array('ok' => $_ARRAYLANG['TXT_KNOWLEDGE_RESET_VOTES_SUCCESSFUL'])));
        } catch (DatabaseError $e) {
            $this->sendAjaxError($e->formatted());
        }

        die();
    }

    /**
     * Show the placeholder page
     *
     * @global $_ARRAYLANG
     * @global $_CORELANG
     * @return string
     */
    private function settingsPlaceholders()
    {
        global $_ARRAYLANG, $_CORELANG;

        $this->pageTitle = $_ARRAYLANG['TXT_SETTINGS'];
        $this->tpl->loadTemplateFile('module_knowledge_settings_placeholder.html',true,true);
        $this->tpl->setGlobalVariable("MODULE_INDEX", MODULE_INDEX);

        $this->tpl->setVariable(array(
           'TXT_PLACEHOLDERS'      => $_ARRAYLANG['TXT_KNOWLEDGE_PLACEHOLDER'],
           'TXT_PLACEHOLDER_TAG_CLOUD_DESCRIPTION'      => $_ARRAYLANG['TXT_KNOWLEDGE_PLACEHOLDER_TAG_CLOUD_DESCRIPTION'],
           'TXT_PLACEHOLDER_BEST_RATED_DESCRIPTION'     => $_ARRAYLANG['TXT_KNOWLEDGE_PLACEHOLDER_MOST_POPULAR_DESCRIPTION'],
           'TXT_PLACEHOLDER_MOST_READ_DESCRIPTION'     => $_ARRAYLANG['TXT_KNOWLEDGE_PLACEHOLDER_MOST_READ_DESCRIPTION']
        ));

        return $this->tpl->get();
    }

    /**
     * Get comments for articles' content.
     *
     * This is an ajax request.
     */
    private function getComments()
    {
	$id = intval($_GET['id']);
	$comments = $this->loadComments($id,true);
	die(json_encode($comments));
    }

    /**
     * Delete comment with provided id.
     *
     * This is an ajax request.
     */
    private function delComment()
    {
	$id = intval($_GET['id']);
	$this->deleteComment($id);
	die(json_encode(array('status' => 'success')));
    }
}
?>