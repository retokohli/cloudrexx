<?

/**
 * Support Categories
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
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
     * This is initialized in the constructor, it avoids many database
     * accesses and speeds up the page setup.  It is initialized on
     * demand by calling its access method
     * {@link getSupportCategoryTreeArray()}.
     * @var array
     */
    var $arrSupportCategoryTreeArray;

    /**
     * Support Categories language ID
     *
     * Stores the language ID used to initialize the Support Categories
     * tree array.
     * @var integer
     */
    var $languageId;


    /**
     * Constructor (PHP4)
     *
     * @param       integer     $languageId     The language ID to use
     * @copyright   CONTREXX CMS - COMVATION AG
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
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     */
    function __construct($languageId)
    {
        $this->languageId = intval($languageId);
echo("__construct(lang=$languageId): made ");var_export($this);echo("<br />");
    }


    /**
     * Get the Support Categories' language ID
     * @return  integer     The language ID
     */
    function getLanguageId()
    {
        return $this->languageId;
    }
    /**
     * Set this Support Categories' language ID
     * @param   integer     The language ID
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
     * @param   integer $parentCategoryId   The optional parent Support Category ID
     * @param   boolean $flagRecursive      If true, recursively adds Subcategories
     * @return  array                       The array of Support Categories
     * @global  mixed   $objDatabase        Database object
     * @global  array   $_CONFIG            Global configuration array
     */
    function getSupportCategoryNameArray(
        $parentCategoryId=0, $flagRecursive=false
    ) {
        // debug
        if (   !is_array($this->arrSupportCategoryTreeArray)
            || count($this->arrSupportCategoryTreeArray) == 0) {
echo("getSupportCategoriesMenu(parent=$parentCategoryId, recurse=$flagRecursive): no or empty tree array<br />");
            // no categories here.  abort.
            return false;
        }

        // Build the ID => Name array.
        // This is lame coding, but provides all the possibilities
        // we need.  should be recoded, however...
        $arrResult = array();
        foreach ($this->arrSupportCategoryTreeArray as $arrSupportCategory) {
            if ($arrSupportCategory['parentId'] == $parentCategoryId) {
                $id = $arrSupportCategory['id'];
                $arrResult[$id] = $arrSupportCategory['name'];
                if ($flagRecursive) {
                    $arrResult = array_merge(
                        $arrResult,
                        $this->getSupportCategoryNameArray($id, true)
                    );
                }
            }
        }
        return $arrResult;
    }


    /**
     * Returns an array of the Support Categories' data below the
     * given parent ID.
     *
     * Returns Support Category names with the same language ID only.
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
     * @param   integer $languageId         The language ID
     * @param   integer $parentCategoryId   The optional parent Support Category ID
     * @param   integer $level              The optional indent level.
     *                                      Initially 0 (zero).
     * @return  array                       The array of Support Categories
     * @global  mixed   $objDatabase        Database object
     * @global  array   $_CONFIG            Global configuration array
     */
    function getSupportCategoryTreeArray(
        $languageId, $parentCategoryId=0, $level=0
    ) {
        global $objDatabase;

        // if it has already been initialized with the correct language,
        // just return it
        if (   $languageId == $this->languageId
            && is_array($this->arrSupportCategoryTreeArray)) {
            return $this->arrSupportCategoryTreeArray;
        }

        // otherwise, initialize it
        $query = "
            SELECT *
              FROM ".DBPREFIX."module_support_category
        INNER JOIN ".DBPREFIX."module_support_category_language
                ON id=support_category_id
             WHERE parent_id=$parentCategoryId
               AND language_id=$languageId
          ORDER BY id ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) { // || $objResult->RecordCount() == 0) {
            return false;
        }
        // return array
        $arrSupportCategoryTree = array();
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $arrSupportCategory = array(
                'id'         => $id,
                'parentId'   => $objResult->fields['parent_id'],
                'status'     => $objResult->fields['status'],
                'order'      => $objResult->fields['order'],
                'languageId' => $objResult->fields['language_id'],
                'name'       => $objResult->fields['name'],
                'level'      => $level,
            );
echo("getSupportCategoryNameTreeArray(lang=$languageId, parent=$parentCategoryId, level=$level): ");var_export($arrSupportCategory);echo("<br />");
            $arrSupportCategoryTree[] = $arrSupportCategory;
            $arrSupportCategoryTree = array_merge(
                $arrSupportCategoryTree,
                $this->getSupportCategoryTreeArray(
                    $languageId, $id, $level+1
                )
            );
            $objResult->MoveNext();
        }
        return $arrSupportCategoryTree;
    }


    /**
     * Returns HTML code for the Support Categories dropdown menu.
     *
     * Does only contain the <select> tag pair if the optional $menuName
     * is specified and evaluates to a true value.
     * This is meant for the backend, as it contains all Support Categories
     * ordered and indented.  It should be used to select parent Support
     * Categories.
     * @param   integer $languageId The language ID of the Support Categories
     *                              to be used
     * @param   integer $selectedId The optional preselected Support Category ID
     * @param   string  $menuName   The optional menu name, defaults to the
     *                              empty string.  Unless specified, no <select>
     *                              tag pair will be added.
     * @return  string              The dropdown menu HTML code
     */
    function getAdminMenu($languageId, $selectedId=0, $menuName='')
    {
        $menu = '';
        foreach ($this->getSupportCategoryTreeArray(
                    $languageId,
                    0,
                    0
                ) as $arrField) {
            $id    = $arrField['id'];
            $name  = $arrField['name'];
            $level = $arrField['level'];
echo("getMenu(lang=$languageId, selected=$selectedId): id $id, name $name<br />");
            $menu .=
                "<option value='$id'".
                ($selectedId == $id ? ' selected="selected"' : '').
                '>'.
                ($level ? str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level-1).'+-&nbsp;' : '').
                "$name</option>\n";
        }
        if ($menuName) {
            $menu = "<select id='$menuName' name='$menuName'>\n$menu\n</select>\n";
        }
echo("getMenu(lang=$languageId, parent=$selectedId): made menu: ".htmlentities($menu)."<br />");
        return $menu;
    }


    /**
     * Returns HTML code for the Support Categories dropdown menu.
     *
     * Does not contain the <select> tag pair.
     * This is meant for the frontend, as it only contains the children Support
     * Categories of the given parent ID.  It should be used to let the
     * customer select the Support Category/-ies for her ticket.
     * @param   integer     $parentCategoryId   The parent Support Category ID
     * @param   integer     $selectedCategoryId The selected Support Category ID
     * @return  string                          The dropdown menu HTML code
     */
    function getMenu($parentCategoryId=0, $selectedCategoryId=0)
    {
        // debug
        if (   !is_array($this->arrSupportCategoryTreeArray)
            || count($this->arrSupportCategoryTreeArray) == 0) {
echo("getSupportCategoriesMenu($parentCategoryId=0, $selectedCategoryId=0): no or empty tree array<br />");
            // no categories here.  abort.
            return false;
        }

        $strOptions = '';
        foreach ($this->arrSupportCategoryTreeArray as $arrSupportCategory) {
            if ($arrSupportCategory['parentId'] == $parentCategoryId) {
                $id   = $arrSupportCategory['id'];
                $name = $arrSupportCategory['name'];
                $strOptions .=
                   "<option value='$id'".
                   ($id == $selectedCategoryId
                       ? ' selected="selected"'
                       : ''
                   ).'>'.
                   htmlentities($name, ENT_QUOTES, CONTREXX_CHARSET).
                   "</option>\n";
            }
        }
        return $strOptions;
    }}

?>
