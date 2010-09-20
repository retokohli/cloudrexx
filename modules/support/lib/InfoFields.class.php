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
     * It contains the complete InfoFields data and is ordered
     * by the order field.
     * It avoids many database accesses and speeds up the page setup.
     * @var array
     */
    var $arrInfoField = false;

    /**
     * Info Fields index array
     *
     * This is initialized on the first call to
     * {@link getInfoFieldArray()}.
     * It looks like:  array( ID => index, ... )
     * where ID is the InfoField ID, and index is the index of
     * that InfoField in the $arrInfoField array.
     * @var array
     */
    var $arrInfoFieldIndex = false;

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


    /**
     * Invalidates the $arrInfoField and $arrInfoFieldIndex arrays
     *
     * This must be called after each change to the InfoFields database
     * table, so that the arrays are forced to be reinitialized with the
     * current data.
     * @return  boolean                 True.  Always.
     */
    function invalidateInfoFieldArray()
    {
        $this->arrInfoField = false;
        $this->arrInfoFieldIndex = false;
        return true;
    }


    /**
     * Returns the array of the Info Fields data.
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
//if (MY_DEBUG) echo("infofields::getInfoFieldArray(languageId=$languageId, flagActiveOnly=$flagActiveOnly): INFO: Entered.<br />");
        // if it has already been initialized with the correct language
        // and flag, just return it
        if (   is_array($this->arrInfoField)
            && $languageId == $this->languageId
            && $flagActiveOnly == $this->flagActiveOnly) {
            return $this->arrInfoField;
        }

//if (MY_DEBUG) echo("infofields::getInfoFieldArray(languageId=$languageId, flagActiveOnly=$flagActiveOnly): INFO: Creating new array.<br />");
        // The Info Field name array is invalidated by this!
        $this->languageId = $languageId;
        $this->flagActiveOnly = $flagActiveOnly;

        // (re-)initialize it
        if (!$this->buildInfoFieldArray()) {
            return false;
        }
        return $this->arrInfoField;
    }


    /**
     * Builds arrays of the Info Fields' data.
     *
     * Initializes both arrInfoField and arrInfoFieldIndex, if necessary.
     * arrInfoField contains Info Field data for the language ID
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
     *
     * arrInfoFieldIndex maps the InfoField ID to the corresponding index
     * in arrInfoField, like:
     *  array( ID => index, ... )
     *
     * Note that the index value is in no way related to the Info Fields
     * themselves, but only represents their place within the array
     * according to the sorting order.
     * Also note that the Index starts at 1 (one).
     * @access  protected
     * @return  boolean                     True on success, false otherwise.
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
        $this->arrInfoField = array();
        $this->arrInfoFieldIndex = array();
        $index = 0;
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $objInfoField = InfoField::getById($id, $this->languageId, true);
            $this->arrInfoField[++$index] = array(
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
            $this->arrInfoFieldIndex[$id] = $index;
            $objResult->MoveNext();
        }
        return true;
    }


    /**
     * Returns an array with data for a single InfoField
     * selected by its ID.
     *
     * The language is specified by the $languageId object variable.
     * Note that this method requires that the $arrInfoField and
     * $arrInfoFieldIndex object variables have been initialized
     * with the correct language.  If they are uninitialized, this
     * method will fail and return false.
     * @param       integer     $id             The InfoField ID
     * @return      mixed                       The InfoField array
     *                                          on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getArrayById($id)
    {
        if (!$this->arrInfoField) {
if (MY_DEBUG) echo("InfoFields::getArrayById($id): ERROR: InfoField array is missing!<br />");
            return false;
        }
        return $this->arrInfoField[$this->arrInfoFieldIndex[$id]];
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


    /**
     * Returns the HTML code for the InfoField as specified by the
     * array provided.
     *
     * Note that the array contains additional elements to the ones
     * returned by {@link getInfoFieldArray()}.
     * 'index' *MUST* be set to a unique value for any InfoFields of the
     * same kind (same InfoField ID).  This is needed to distinguish
     * individual InfoFields being created if the 'multiple' flag is true.
     * The array element 'value' may contain the initial value
     * of the HTML input element being created.
     * See {@link getInfoFieldArray()} and {@link buildInfoFieldArray()}
     * for details on the other array elements.
     * @param   array   $arrInfoField   The array describing the InfoField
     * @param   boolean $flagContinue   Flag indicating whether to include
     *                                  the continue function call in the
     *                                  onchange attribute, defaults to true
     * @return  string                  The HTML code for the InfoField
     * @global  array   $_ARRAYLANG     Language array
     */
    function getHtml($arrInfoField, $flagContinue=true)
    {
        global $_ARRAYLANG;

//if (MY_DEBUG) { echo("InfoFields::getHtml(): ");var_export($arrInfoField);echo("<br />"); }

        $id = $arrInfoField['id'];
        $index = (!empty($arrInfoField['index']) ? $arrInfoField['index'] : 0);
        $strHtml = '
            <div id="txt-'.$id.'-'.$index.'">'.
              $arrInfoField['name'].
              ($arrInfoField['mandatory']
                ? $_ARRAYLANG['TXT_SUPPORT_INFOFIELD_MANDATORY']
                : ''
              ).'
            </div>
            <div id="inp-'.$id.'-'.$index.'">'.
              // *NO* whitespace in the case of the file field!
              // Everything between the enclosing <div> tag and the
              // actual InfoField input tag must be skipped as child #0
              // in both the cases of text and file inputs!
              ($arrInfoField['type'] == SUPPORT_INFO_FIELD_TYPE_FILE
                ? '<input type="hidden" name="MAX_FILE_SIZE" value="'.
                  InfoFields::ini2int(ini_get('post_max_size')).'" />'
                : ' ').
              '<input type="'.
              ($arrInfoField['type'] == SUPPORT_INFO_FIELD_TYPE_FILE
                  ? 'file' : 'text'
              ).'"'.
                ' id="arrSupportInfoField_'.$id.'_'.$index.'"'.
                ' name="arrSupportInfoField['.$id.']'."[".$index."]".'"'.
                ' value="'.$arrInfoField['value'].
                '" tabindex="4" '.
                ($flagContinue ? 'onchange="JavaScript:supportContinue();"' : '').
                ' />'.
              ($arrInfoField['multiple']
                ? '&nbsp;<input type="button" name="addInfoField" value="+"'.
                  ' onclick="JavaScript:cloneInfoField('.
                      $id.', '.$index.');" />'.
                  '&nbsp;<input type="button" name="delInfoField" value="-"'.
                  ' onclick="JavaScript:deleteInfoField('.
                      $id.', '.$index.');" />'
                : ''
              ).'
            </div>';
        return $strHtml;
    }


    /**
     * Converts php.ini memory settings strings to their integer equivalent.
     *
     * This should be placed in some static tool class in the core.
     * @param   string  $strMemory      The setting string
     * @return  integer                 The integer value
     */
    function ini2int($strMemory) {
        $strMemory = trim($strMemory);
        $last = strtolower($strMemory{strlen($strMemory)-1});
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $strMemory *= 1024;
            case 'm':
                $strMemory *= 1024;
            case 'k':
                $strMemory *= 1024;
        }
        return $strMemory;
    }


    /**
     * Verifies that the values contained in the array are complete
     * according to the InfoFields definition.
     *
     * That is, mandatory fields must contain a non-empty value.
     * Note that this method requires that the $arrInfoField and
     * $arrInfoFieldIndex object variables have been initialized
     * using the correct language ID.  If they are uninitialized, this
     * method will fail and return false.
     * @return  boolean                 True if the data is complete,
     *                                  false otherwise
     */
    function isComplete()
    {
        if (!$this->arrInfoField) {
if (MY_DEBUG) echo("InfoFields::isComplete(): ERROR: InfoField array is missing!<br />");
            return false;
        }
        $arrInfoFieldsPost =
            (isset($_REQUEST['arrSupportInfoField'])
                ? $_REQUEST['arrSupportInfoField'] : array()
            );
        $arrInfoFieldsPost += $_FILES['arrSupportInfoField'];
if (MY_DEBUG) echo("InfoFields::isComplete(): INFO: made array ");var_export($arrInfoFieldsPost);echo("<br />");
        $isComplete = true;
        $uploadSuccess = true;
if (MY_DEBUG) { echo("InfoFields::isComplete(): FILES: ");var_export($_FILES);echo("<br />"); }
        foreach ($this->arrInfoField as $arrInfoField) {
            // Don't bother about non-mandatory fields
            if (!$arrInfoField['mandatory']) {
                continue;
            }
            $id = $arrInfoField['id'];
            if ($arrInfoField['type'] == SUPPORT_INFO_FIELD_TYPE_FILE) {
                // Verify all attachments.
                // If they were not uploaded, the customer needs to be notified.
                foreach ($arrInfoFieldsPost['error'][$id] as $index => $error) {
                    if ($error != UPLOAD_ERR_OK) {
                        $name = $arrInfoFieldsPost['name'][$id][$index];
//                        $this->addMessage($name.': '.$_ARRAYLANG['TXT_PHP_UPLOAD_ERR_'.$error]);
if (MY_DEBUG) echo("InfoFields::isComplete(): $name: ERROR: $error (".$_ARRAYLANG['TXT_PHP_UPLOAD_ERR_'.$error].')<br />');
                        $uploadSuccess = false;
                    }
                }
            } else {
                foreach ($arrInfoFieldsPost[$id] as $index => $infoFieldValue) {
// TODO: Verify the value range (numbers, strings, ...)
                    if (empty($infoFieldValue)) {
if (MY_DEBUG) echo("InfoFields::isComplete(): WARNING: $id-$index has no value!<br />");
                        $isComplete = false;
                    }
                }
            }
        }
        return $isComplete;
    }


    /**
     * Converts the InfoField data from the posted array into
     * human readable text form, ready to be included with the Ticket
     *
     * Note that this method requires that the $arrInfoField and
     * $arrInfoFieldIndex object variables have been initialized
     * with the correct language.  If they are uninitialized, this
     * method will fail and return false.
     * @param   array   $arrInfoFieldPost   The posted InfoField array
     * @global  array   $_ARRAYLANG     Language array
     */
    function arrayToText($arrInfoFieldsPost, $languageId)
    {
        global $_ARRAYLANG;

        if (!$this->arrInfoField) {
if (MY_DEBUG) echo("InfoFields::arrayToText(): ERROR: InfoField array is missing!<br />");
            return false;
        }
        $strInfoFields = "\n\n".$_ARRAYLANG['TXT_SUPPORT_INFOFIELDS']."\n";
        foreach ($arrInfoFieldsPost as $id => $arrInfoFieldPost) {
            $arrInfoField = $this->getArrayById($id);
            $strInfoFields .=
                $arrInfoField['name'].': '.join(', ', $arrInfoFieldPost);
        }
        return $strInfoFields;
    }

}

?>
