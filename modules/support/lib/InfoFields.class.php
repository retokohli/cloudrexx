<?

/**
 * Info Fields
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */


/**
 * Info Fields
 *
 * This class provides added functionality for the {@link InfoField}
 * class, such as menues.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */

class InfoFields
{
    /**
     * Info Fields array
     *
     * This is initialized on the first call to
     * {@link getInfoFieldArray()}.
     * It avoids many database accesses and speeds up the page setup.
     * @var array
     */
    var $arrInfoField;

    /**
     * Info Fields language ID
     *
     * Stores the language ID used to initialize the Info Fields array.
     * @var integer
     */
    var $languageId;

    /**
     * Flag indicating whether to include non-active Info Fields
     *
     * Stores the corresponding flag used to initialize the Info Fields array.
     * @var integer
     */
    var $flagActiveOnly;


    /**
     * Constructor (PHP4)
     *
     * @param       integer     $languageId     The language ID to use
     * @param       boolean     $flagActiveOnly Flag indicating whether to
     *                                          include active Info Fields
     *                                          only.  Defaults to true.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     * @see         __construct()
     */
    function InfoFields($languageId, $flagActiveOnly=true)
    {
        $this->__construct($languageId, $flagActiveOnly);
    }

    /**
     * Constructor (PHP5)
     *
     * @param       integer     $languageId     The language ID to use
     * @param       boolean     $flagActiveOnly Flag indicating whether to
     *                                          include active Info Fields
     *                                          only.  Defaults to true.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     */
    function __construct($languageId, $flagActiveOnly=true)
    {
        $this->languageId     = intval($languageId);
        $this->flagActiveOnly = ($flagActiveOnly == true);
//if (MY_DEBUG) { echo("__construct(lang=$languageId): made ");var_export($this);echo("<br />"); }
    }


    /**
     * Get the Info Fields' language ID
     * @return  integer     The language ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getLanguageId()
    {
        return $this->languageId;
    }


    function invalidateInfoFieldArray()
    {
        $this->arrInfoField = false;
        return true;
    }


    /**
     * Returns an array of the Info Fields' data.
     *
     * If the array has been initialized before, simply returns it.
     * Otherwise, the array is set up on the fly first.
     * See {@link buildInfoFieldTreeArray()} for a detailed
     * explanation.
     * @param   integer $languageId         The language ID. 0 (zero) means
     *                                      all languages.
     * @param   boolean $flagActiveOnly     If true, only active Info Fields
     *                                      are included.  Defaults to true.
     * @return  array                       The array of Info Fields
     *                                      on success, false otherwise.
     * @global  mixed   $objDatabase        Database object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getInfoFieldArray($languageId, $flagActiveOnly=true)
    {
        global $objDatabase;
echo("infofields::getInfoFieldArray(languageId=$languageId, flagActiveOnly=$flagActiveOnly): INFO: Entered.<br />");
        // if it has already been initialized with the correct language
        // and flag, just return it
        if (   is_array($this->arrInfoField)
            && $languageId == $this->languageId
            && $flagActiveOnly == $this->flagActiveOnly) {
            return $this->arrInfoField;
        }

echo("infofields::getInfoFieldArray(languageId=$languageId, flagActiveOnly=$flagActiveOnly): INFO: Creating new array.<br />");
        // The Info Field name array is invalidated by this!
        $this->arrInfoFieldName = false;
        $this->languageId = $languageId;
        $this->flagActiveOnly = $flagActiveOnly;

        // (re-)initialize it
        $this->arrInfoField =
            $this->buildInfoFieldArray();

        return $this->arrInfoField;
    }


    /**
     * Returns an array of the Info Fields' data.
     *
     * Returns Info Field data for the language ID
     * and active status as specified by the $languageId and $flagActiveOnly
     * object variables, respectively.
     * The array has the following form:
     *  array(
     *    index => array(
     *      'id'         => ID
     *      'status'     => Status,
     *      'order'      => Sorting order,
     *      'type'       => Type,
     *      'mandatory'  => Mandatory flag,
     *      'multiple'   => Multiple flag,
     *      'languageId' => Language ID,
     *      'name'       => Name,
     *    ),
     *    ...
     *  )
     * Note that the index is in no way related to the Info Fields,
     * but represents their place within the array according to the
     * sorting order.
     * @access  protected
     * @return  array                       The array of Info Fields
     *                                      on success, false otherwise.
     * @global  mixed   $objDatabase        Database object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function buildInfoFieldArray()
    {
        global $objDatabase;

        $query = "
            SELECT DISTINCT id
              FROM ".DBPREFIX."module_support_info_field
        INNER JOIN ".DBPREFIX."module_support_info_field_language
                ON id=info_field_id
             WHERE 1
               ".($this->flagActiveOnly ? " AND status=1" : '')."
               ".($this->languageId     ? " AND language_id=$this->languageId" : '')."
          ORDER BY id ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) { // || $objResult->RecordCount() == 0) {
            return false;
        }
        // return array
        $arrInfoField = array();
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $objInfoField = InfoField::getById($id, $this->languageId, true);
            $arrInfoField[] = array(
                'id'         => $id,
                'status'     => $objInfoField->getStatus(),
                'order'      => $objInfoField->getOrder(),
                'type'       => $objInfoField->getType(),
                'mandatory'  => $objInfoField->getMandatory(),
                'multiple'   => $objInfoField->getMultiple(),
                'languageId' => $objInfoField->getLanguageId(),
                'name'       => $objInfoField->getName(),
                'arrName'    => $objInfoField->getNameArray(),
            );
            $objResult->MoveNext();
        }
        return $arrInfoField;
    }


    /**
     * Returns HTML code for the Info Fields dropdown menu.
     *
     * Does only contain the <select> tag pair if the optional $menuName
     * is specified and evaluates to a true value.
     * This is meant for the backend, as it contains all Info Fields,
     * active and inactive, sorted by the order field.
     * @param   integer $languageId The language ID of the Info Fields
     *                              to be used
     * @param   integer $selectedId The optional preselected Info Field ID
     * @param   string  $menuName   The optional menu name, defaults to the
     *                              empty string.  Unless specified, no <select>
     *                              tag pair will be added.
     * @return  string              The dropdown menu HTML code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getAdminMenu($languageId, $selectedId=0, $menuName='')
    {
        $menu = '';
        foreach ($this->getInfoFieldArray($languageId, false)
                as $arrField) {
            $id    = $arrField['id'];
            $name  = $arrField['name'];
if (MY_DEBUG) echo("getAdminMenu(lang=$languageId, select=$selectedId, name=$menuName): id $id, name $name<br />");
            $menu .=
                "<option value='$id'".
                ($selectedId == $id ? ' selected="selected"' : '').
                ">$name</option>\n";
        }
        if ($menuName) {
            $menu = "<select id='$menuName' name='$menuName'>\n$menu\n</select>\n";
        }
if (MY_DEBUG) echo("getAdminMenu(lang=$languageId, select=$selectedId, name=$menuName): made menu: ".htmlentities($menu)."<br />");
        return $menu;
    }


    /**
     * Returns HTML code for the Info Fields type dropdown menu.
     *
     * Does only contain the <select> tag pair if the optional $menuName
     * is specified and evaluates to a true value.
     * @static
     * @param   integer $selectedType   The optional preselected Info Field type
     * @param   string  $menuName   The optional menu name, defaults to the
     *                              empty string.  Unless specified, no <select>
     *                              tag pair will be added.
     * @return  string              The dropdown menu HTML code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getTypeMenu($selectedType=0, $menuName='')
    {
        $menu = '';
        // Skip type SUPPORT_INFO_FIELD_TYPE_UNKNOWN (0),
        // we don't want the User to choose that from the menu.
        for ($i = 1; $i < SUPPORT_INFO_FIELD_TYPE_COUNT; ++$i) {
            $type  = InfoField::getTypeString($i);
if (MY_DEBUG) echo("InfoFields::getTypeMenu(select=$selectedType, name=$menuName): i $i, type $type<br />");
            $menu .=
                "<option value='$i'".
                ($selectedType == $i ? ' selected="selected"' : '').
                ">$type</option>\n";
        }
        if ($menuName) {
            $menu = "<select id='$menuName' name='$menuName'>\n$menu\n</select>\n";
        }
if (MY_DEBUG) echo("InfoFields::getTypeMenu(select=$selectedType, name=$menuName): made menu: ".htmlentities($menu)."<br />");
        return $menu;
    }

}

?>
