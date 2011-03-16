<?php

/**
 * Support Categories
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */

/*

CREATE TABLE `contrexx_module_support_category` (
  `id`        int(11)    unsigned NOT NULL auto_increment,
  `parent_id` int(11)    unsigned NOT NULL default '0',
  `status`    tinyint(1) unsigned NOT NULL default '1',
  `order`     int(11)    unsigned NOT NULL default '0',
  PRIMARY KEY     (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `status`    (`status`)
) ENGINE=MyISAM;

CREATE TABLE `contrexx_module_support_category_language` (
  `support_category_id` int(10)      unsigned NOT NULL,
  `language_id`         int(10)      unsigned NOT NULL,
  `name`                varchar(255)          NOT NULL,
  PRIMARY KEY (`support_category_id`,`language_id`)
) ENGINE=MyISAM;

*/

/**
 * Support Categories
 *
 * This class provides added functionality for the {@link SupportCategory}
 * class, such as menues.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */

class SupportCategories
{
    /**
     * Support Categories tree array
     *
     * This is initialized on the first call to
     * {@link getSupportCategoryTreeArray()}.
     * It avoids many database accesses and speeds up the page setup.
     * @var array
     */
    var $arrSupportCategoryTree;

    /**
     * Support Categories name array
     *
     * This is initialized on the first call to
     * {@link getSupportCategoryNameArray()}.
     * It avoids many database accesses and speeds up the page setup.
     * Note that this is built using $arrSupportCategoryTree. If the
     * tree array is changed (different language or parent), the
     * name array is invalidated.  If {@link getSupportCategoryNameArray()}
     * is called with different values than {@link getSupportCategoryTreeArray()}
     * was called before, $arrSupportCategoryTree will first be reinitialized
     * using the new parameters.
     * @var array
     */
    var $arrSupportCategoryName;

    /**
     * Support Categories language ID
     *
     * Stores the language ID used to initialize the Support Categories
     * tree array.
     * @var integer
     */
    var $languageId;

    /**
     * Parent Support Category
     *
     * Stores the parent ID used to initialize the Support Categories
     * tree array.
     * @var integer
     */
    var $parentId;

    /**
     * Flag indicating whether to include non-active Support Categories
     *
     * Stores the corresponding flag used to initialize the Support Categories
     * tree array.
     * @var integer
     */
    var $flagActiveOnly;


    /**
     * Constructor (PHP4)
     *
     * @param       integer     $languageId     The language ID to use
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     * @see         __construct()
     */
    function SupportCategories($languageId)
    {
        $this->__construct($languageId);
    }

    /**
     * Constructor (PHP5)
     *
     * @param       integer     $languageId     The language ID to use
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     */
    function __construct($languageId)
    {
        $this->languageId = intval($languageId);
//if (MY_DEBUG) { echo("__construct(lang=$languageId): made ");var_export($this);echo("<br />"); }
    }


    /**
     * Get the Support Categories' language ID
     * @return  integer     The language ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getLanguageId()
    {
        return $this->languageId;
    }
    /**
     * Set this Support Categories' language ID
     * @param   integer     The language ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setLanguageId($languageId)
    {
        $this->languageId = intval($languageId);
    }


    /**
     * Returns an array of chosen Support Category IDs and names.
     *
     * Returns Support Category names with the same language ID.
     * If the optional $flagRecursive parameter is true, recursively adds
     * the Subcategories of the given parent ID.  Otherwise, only children
     * of the parent ID are returned.
     * The array has the form array(id => "Support Category name").
     * @param   integer $languageId         The language ID
     * @param   integer $parentId           The optional parent Support
     *                                      Category ID. Defaults to 0 (zero)
     * @param   boolean $flagActiveOnly     If true, returns only active
     *                                      Support Category names.
     *                                      Defaults to true.
     * @param   boolean $flagRecursive      If true, recursively adds
     *                                      Subcategories. Defaults to false.
     * @return  array                       The array of Support Categories'
     *                                      names on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getSupportCategoryNameArray(
        $languageId, $parentId=0,
        $flagActiveOnly=true, $flagRecursive=false
    ) {

        // if it has already been initialized with the same parameters,
        // just return it.
        // if it was initialized with parentId == 0, we don't care,
        // as all the names we possibly need are present.
        if (   $languageId == $this->languageId
            && $flagActiveOnly == $this->flagActiveOnly
            && is_array($this->arrSupportCategoryName)) {
            return $this->arrSupportCategoryName;
        }

        $arrSupportCategoryTree =
            $this->getSupportCategoryTreeArray(
                $languageId, $flagActiveOnly
            );

        // debug
        if (   !is_array($arrSupportCategoryTree)
            || count($arrSupportCategoryTree) == 0) {
if (MY_DEBUG) echo("getSupportCategoryNameArray(parent=$parentId, recurse=$flagRecursive, active=$flagActiveOnly): no or empty tree array<br />");
            // no categories here.  abort.
            return false;
        }

        // Build the ID => Name array.
        // This is lame coding, but provides all the possibilities
        // we need.  should be recoded, however...
        $arrResult = array();
        foreach ($arrSupportCategoryTree as $arrSupportCategory) {
            if ($arrSupportCategory['parentId'] == $parentId) {
                $id = $arrSupportCategory['id'];
                $arrResult[$id] = $arrSupportCategory['name'];
                if ($flagRecursive) {
                    $arrChildren =
                        $this->getSupportCategoryNameArray(
                            $languageId, $id, $flagActiveOnly, true
                        );
                    foreach ($arrChildren as $id => $name) {
                        $arrResult[$id] = $name;
                    }
                }
            }
        }
if (MY_DEBUG) { echo("getSupportCategoryNameArray(parent=$parentId, recurse=$flagRecursive, active=$flagActiveOnly): made array: ");var_export($arrResult);echo("<br />"); }
        return $arrResult;
    }


    /**
     * Returns an array of the Support Categories' data below the
     * given parent ID.
     *
     * If the array has been initialized before, simply returns it.
     * Otherwise, the array is set up on the fly first.
     * See {@link buildSupportCategoryTreeArray()} for a detailed
     * explanation.
     * @param   integer $languageId         The language ID. 0 (zero) means
     *                                      all languages.
     * @param   boolean $flagActiveOnly     If true, only active Support
     *                                      Categories are included.
     *                                      Defaults to true.
     * @return  array                       The array of Support Categories
     *                                      on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getSupportCategoryTreeArray(
        $languageId, $flagActiveOnly=true)
    {
        // if it has already been initialized with the correct language
        // and flag, just return it
        if (   $languageId == $this->languageId
            && $flagActiveOnly == $this->flagActiveOnly
            && is_array($this->arrSupportCategoryTree)) {
            return $this->arrSupportCategoryTree;
        }

        // The Support Category name array is invalidated by this!
        $this->arrSupportCategoryName = false;
        $this->languageId = $languageId;
        $this->flagActiveOnly = $flagActiveOnly;

        // (re-)initialize it
        $this->arrSupportCategoryTree =
            $this->buildSupportCategoryTreeArray();

        return $this->arrSupportCategoryTree;
    }


    /**
     * Returns an array of the Support Categories' data below the
     * given parent ID.
     *
     * Returns Support Category names with the same language ID
     * and active status as specified by the $languageId and $flagActiveOnly
     * object variables, respectively.
     * The array has the following form:
     *  array(
     *    index => array(
     *      'id          => ID
     *      'parentId'   => parent ID,
     *      'status'     => Status,
     *      'order'      => Sorting order,
     *      'languageId' => Language ID,
     *      'name'       => Name,
     *      'level'      => Indent level,
     *    ),
     *    ...
     *  )
     * Note that the index is in no way related to the Support Categories,
     * but represents their place within the tree according to the
     * sorting order.
     * @param   integer $parentId           The optional parent ID.
     *                                      Defaults to 0 (root).
     * @param   integer $level              The optional indent level.
     *                                      Initially 0 (zero).
     * @return  array                       The array of Support Categories
     *                                      on success, false otherwise.
     * @global  mixed   $objDatabase        Database object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function buildSupportCategoryTreeArray(
        $parentId=0, $level=0
    ) {
        global $objDatabase;
//if (MY_DEBUG) echo("supportcategories::buildSupportCategoryTreeArray(parentId=$parentId, level=$level): INFO: Entered.<br />");

        $query = "
            SELECT DISTINCT id
              FROM ".DBPREFIX."module_support_category
         LEFT JOIN ".DBPREFIX."module_support_category_language
                ON id=support_category_id
             WHERE parent_id=$parentId
               ".($this->flagActiveOnly ? 'AND status=1' : '')."
               ".($this->languageId     ? "AND language_id=$this->languageId" : '')."
          ORDER BY `order` ASC, language_id ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) { // || $objResult->RecordCount() == 0) {
//if (MY_DEBUG) echo("supportcategories::buildSupportCategoryTreeArray(parentId=$parentId, level=$level): ERROR: query failed: $query!<br />");
            return false;
        }
        // return array
        $arrSupportCategoryTree = array();
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $objSupportCategory =
                SupportCategory::getById($id, $this->languageId, true);
//if (MY_DEBUG) { echo("supportcategories::buildSupportCategoryTreeArray(parentId=$parentId, level=$level): INFO: made object ");var_export($objSupportCategory);echo(".<br />"); }
            $arrSupportCategory = array(
                'id'         => $id,
                'parentId'   => $objSupportCategory->getParentId(),
                'status'     => $objSupportCategory->getStatus(),
                'order'      => $objSupportCategory->getOrder(),
                'languageId' => $objSupportCategory->getLanguageId(),
                'name'       => $objSupportCategory->getName(),
                'arrName'    => $objSupportCategory->getNameArray(),
                'level'      => $level,
            );
            $arrSupportCategoryTree[] = $arrSupportCategory;
            $arrSupportCategoryTree = array_merge(
                $arrSupportCategoryTree,
                $this->buildSupportCategoryTreeArray(
                    $id, $level+1
                )
            );
            $objResult->MoveNext();
        }
        return $arrSupportCategoryTree;
    }


    /**
     * Invalidate the Support Category tree array
     *
     * This must be done after the data in the database table has been
     * changed by either an INSERT, UPDATE, or DELETE.
     */
    function invalidateSupportCategoryTreeArray()
    {
        $this->arrSupportCategoryTree = false;
    }


    /**
     * Returns HTML code for the Support Categories dropdown menu.
     *
     * Does only contain the <select> tag pair if the optional $menuName
     * is specified and evaluates to a true value.
     * This is meant for the backend, as it contains all Support Categories
     * ordered and indented.  It is intended to be used for selecting parent
     * SupportCategories.
     * @param   integer $languageId The language ID of the Support Categories
     *                              to be used
     * @param   integer $selectedId The optional preselected Support Category ID
     * @param   string  $menuName   The optional menu name, defaults to the
     *                              empty string.  Unless specified, no <select>
     *                              tag pair will be added.
     * @return  string              The dropdown menu HTML code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getAdminMenu($languageId, $selectedId=0, $menuName='')
    {
        $strMenu = '';
        foreach ($this->getSupportCategoryTreeArray($languageId, false)
                as $arrField) {
            $id    = $arrField['id'];
            $name  = $arrField['name'];
            $level = $arrField['level'];
//if (MY_DEBUG) echo("getAdminMenu(lang=$languageId, select=$selectedId, name=$menuName): id $id, name $name<br />");
            $strMenu .=
                "<option value='$id'".
                ($selectedId == $id ? ' selected="selected"' : '').
                '>'.
                ($level ? str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level-1).'+-&nbsp;' : '').
                "$name</option>\n";
        }
        if ($menuName) {
            $strMenu = "<select id='$menuName' name='$menuName'>\n$strMenu\n</select>\n";
        }
//if (MY_DEBUG) echo("getAdminMenu(lang=$languageId, select=$selectedId, name=$menuName): made menu: ".htmlentities($strMenu)."<br />");
        return $strMenu;
    }


    /**
     * Returns HTML code for the Support Categories dropdown menu.
     *
     * Does always contain the <select> tag pair, with the name attribute
     * set to 'supportCategoryId'.
     * This is meant for the frontend, as it only contains the active
     * children Support Categories of the given parent ID.
     * It should be used to let the customer select the Support Category/-ies
     * for her ticket.
     * @param   integer     $parentCategoryId   The parent Support Category ID
     * @param   integer     $selectedCategoryId The selected Support Category ID
     * @return  string                          The dropdown menu HTML code
     * @global  array       $_ARRAYLANG         Language array
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getMenu($parentCategoryId=0, $selectedCategoryId=0)
    {
        global $_ARRAYLANG;

        $strMenu = '';
        $flagMatchSelected = false;
        foreach ($this->getSupportCategoryTreeArray($this->languageId)
                as $arrField) {
            $id    = $arrField['id'];
            $name  = $arrField['name'];
            $level = $arrField['level'];
//if (MY_DEBUG) echo("getAdminMenu(lang=$languageId, select=$selectedId, name=$menuName): id $id, name $name<br />");
            $strMenu .=
                "<option value='$id'";
            if ($id == $selectedCategoryId) {
                $strMenu .= ' selected="selected"';
                $flagMatchSelected = true;
            }
            $strMenu .=
                '>'.
                ($level ? str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level-1).'+-&nbsp;' : '').
                "$name</option>\n";
        }
        if (!$flagMatchSelected) {
            $strMenu =
                '<option value="0" selected="selected">'.
                htmlentities(
                    $_ARRAYLANG['TXT_SUPPORT_REQUEST_PLEASE_CHOOSE'],
                    ENT_QUOTES, CONTREXX_CHARSET).
                "</option>\n".
                $strMenu;
        }
        return
            '<select id="supportCategoryId" name="supportCategoryId"
            onchange="JavaScript:supportContinue();">'.
            $strMenu."</select>\n";
    }
/*
    function getMenu($parentCategoryId=0, $selectedCategoryId=0)
    {
        global $_ARRAYLANG;

        $arrSupportCategoryTree =
            $this->getSupportCategoryTreeArray($this->languageId, true);
        // debug
        if (   !is_array($arrSupportCategoryTree)
            || count($arrSupportCategoryTree) == 0) {
if (MY_DEBUG) echo("SupportCategories::getMenu(parent=$parentCategoryId, selected=$selectedCategoryId): no or empty tree array<br />");
            // no categories here.  abort.
            return false;
        }

        $strMenu = '';
        $flagMatchSelected = false;
        foreach ($arrSupportCategoryTree as $arrSupportCategory) {
//            if ($arrSupportCategory['parentId'] == $parentCategoryId) {
                $id   = $arrSupportCategory['id'];
                $name = $arrSupportCategory['name'];
                $strMenu .= "<option value='$id'";
                if ($id == $selectedCategoryId) {
                    $strMenu .= ' selected="selected"';
                    $flagMatchSelected = true;
                }
                $strMenu .=
                    '>'.
                    htmlentities($name, ENT_QUOTES, CONTREXX_CHARSET).
                    "</option>\n";
//            }
        }
        if (!$flagMatchSelected) {
            $strMenu =
                '<option value="0" selected="selected">'.
                htmlentities(
                    $_ARRAYLANG['TXT_SUPPORT_REQUEST_PLEASE_CHOOSE'],
                    ENT_QUOTES, CONTREXX_CHARSET).
                "</option>\n".
                $strMenu;
        }
        return
            '<select id="supportCategoryId" name="supportCategoryId"
            onchange="JavaScript:supportContinue();">'.
            $strMenu."</select>\n";
    }
*/

}
?>
