<?php

/**
 * HTML element helpers
 *
 * Provides some commonly used HTML elements
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.2.0
 * @package     contrexx
 * @subpackage  module_hotelcard
 */

//require_once(ASCMS_CORE_PATH.'/HtmlTag.class.php');
require_once(ASCMS_FRAMEWORK_PATH.'/Javascript.class.php');
require_once(ASCMS_CORE_PATH.'/Image.class.php');

/**
 * Global constants defining the names of various status
 * for almost anything
 *
 * See {@see Html::getLed()}
 */
define('HTML_STATUS_RED',    'red');
define('HTML_STATUS_YELLOW', 'yellow');
define('HTML_STATUS_GREEN',  'green');

/**
 * Some basic and often used (and frequently misspelt) HTML attributes
 *
 * Note the leading space that allows you to add the placeholder right after
 * the preceeding attribute without wasting whitespace when it's unused
 */
define('HTML_ATTRIBUTE_CHECKED',  ' checked="checked"');
define('HTML_ATTRIBUTE_SELECTED', ' selected="selected"');
define('HTML_ATTRIBUTE_DISABLED', ' disabled="disabled"');
define('HTML_ATTRIBUTE_READONLY', ' readonly="readonly"');
// more...?

/**
 * HTML class
 *
 * Provides some commonly used HTML elements
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.2.0
 * @package     contrexx
 * @subpackage  module_hotelcard
 */
class Html
{
    /**
     * Icon used on the link for removing a HTML element
     */
    const ICON_ELEMENT_REMOVE  = 'images/icons/delete.gif';

    /**
     * Icon used on the link for adding a HTML element
     * @todo    Find a better icon for this
     */
    const ICON_ELEMENT_ADD     = 'images/icons/check.gif';

    /**
     * Icon used for omitted functions
     */
    const ICON_FUNCTION_BLANK = 'images/icons/pixel.gif';

    /**
     * Icon used on the link for viewing any entry
     */
    const ICON_FUNCTION_VIEW  = 'images/icons/viewmag.png';

    /**
     * Icon used on the link for deleting any entry
     */
    const ICON_FUNCTION_DELETE  = 'images/icons/delete.gif';

    /**
     * Icon used on the link for copying any entry
     */
    const ICON_FUNCTION_COPY    = 'images/icons/copy.gif';

    /**
     * Icon used on the link for editing any entry
     */
    const ICON_FUNCTION_EDIT    = 'images/icons/edit.gif';

    /**
     * Icon used on the link for removing an image
     */
    const ICON_FUNCTION_CLEAR_IMAGE = 'images/icons/delete.gif';

    /**
     * Icon used on the link for marking as not deleted
     */
    const ICON_FUNCTION_MARK_UNDELETED = 'images/icons/restore.gif';

    /**
     * Icon used on the link for marking as deleted
     */
    const ICON_FUNCTION_MARK_DELETED = 'images/icons/empty.gif';

    /**
     * Icon used on the link for marking as special
     */
    const ICON_FUNCTION_SPECIAL_ON = 'images/icons/special_on.png';

    /**
     * Icon used on the link for marking as not special
     */
    const ICON_FUNCTION_SPECIAL_OFF = 'images/icons/special_off.png';

    /**
     * Icon used on the link for downloading a PDF document
     */
    const ICON_FUNCTION_DOWNLOAD_PDF = 'images/icons/pdf.gif';

    /**
     * Icon used for red status
     */
    const ICON_STATUS_RED       = 'images/icons/status_red.gif';

    /**
     * Icon used for yellow status
     */
    const ICON_STATUS_YELLOW    = 'images/icons/status_yellow.gif';

    /**
     * Icon used for green status
     */
    const ICON_STATUS_GREEN     = 'images/icons/status_green.gif';

    /**
     * Icon used for the checked status
     */
    const ICON_STATUS_CHECKED   = 'images/icons/check.gif';

    /**
     * Icon used for the unchecked status
     */
    const ICON_STATUS_UNCHECKED = 'images/icons/pixel.gif';

    /**
     * Icon used for Comments (with tooltip containing the text)
     */
    const ICON_COMMENT = 'images/icons/comment.gif';

    /**
     * Icon used for Notes (with tooltip containing the text)
     */
    const ICON_NOTE = 'images/icons/info.gif';

    /**
     * Icon used for gift text (with tooltip containing the text)
     * Only for the hotelcard module
     */
    const ICON_GIFT = 'images/icons/gift.gif';

    /**
     * Icon used to indicate a link to details for the object.
     * Only for the hotelcard module
     */
    const ICON_DETAILS = 'images/icons/details.gif';

    /**
     * Icon used for omitted icons (for aligning/formatting)
     */
    const ICON_BLANK = 'images/icons/blank.gif';

    /**
     * Index counter for all form elements
     *
     * Incremented and added to the tabindex attribute for each element
     * in the order they are created
     * @var   integer
     */
    private static $index_tab = 0;

    /**
     * Index counter for all toggle elements
     *
     * Incremented and added to the id attribute for each toggle element
     * in the order they are created
     * @var   integer
     */
    private static $index_toggle = 0;

    /**
     * The base name for the elements created by {@see getToggle()}
     *
     * The current value of $index_toggle is appended for each such element,
     * starting from one.
     * The current status is stored in a hidden input, its id being the same
     * but prepended with "hidden-".
     */
    const TOGGLE_ID_BASE   = 'toggle';
    /**
     * These are the default values for the global (default) values
     * for the Toggle Javascript class handling the toggle element events.
     *
     * See {@see getToggle()}, {@see getJavascript_Toggle()}
     */
    const TOGGLE_KEY_OFF   = '0';
    const TOGGLE_KEY_ON    = '1';
    const TOGGLE_CLASS_OFF = 'room_off';
    const TOGGLE_CLASS_ON  = 'room_on';
    const TOGGLE_TITLE_OFF = '-';
    const TOGGLE_TITLE_ON  = '0';


    /**
     * Returns HTML code for a form
     *
     * If the $id parameter is false, the id attribute is not set.
     * If it's empty (but not false), the name is used instead.
     * @param   string    $name         The element name
     * @param   string    $action       The action URI
     * @param   string    $content      The form content
     * @param   string    $id           The optional element id
     * @param   string    $method       The optional request method.
     *                                  Defaults to 'post'
     * @param   string    $attribute    Additional optional attributes
     * @return  string                  The HTML code for the element
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getForm(
        $name, $action, $content, $id=false, $method='post', $attribute=''
    ) {
//echo("Html::getForm(): action ".htmlentities($action)."<br />");
        return
            '<form name="'.$name.'"'.
            ($id === false ? '' : ' id="'.($id ? $id : $name).'"').
            'action="'.$action.'"'.
            'method="'.($method == 'post' ? 'post' : 'get').'"'.
            ($attribute ? ' '.$attribute : '').
            ">\n".$content."</form>\n";
    }


    /**
     * Returns HTML code for a text imput field
     *
     * If the $id parameter is false, the id attribute is not set.
     * If it's empty (but not false), the name is used instead.
     * If the custom attributes parameter $attribute is empty, and
     * is_numeric($value) evaluates to true, the text is right aligned
     * within the input element.
     * @param   string    $name         The element name
     * @param   string    $value        The element value, defaults to the
     *                                  empty string
     * @param   string    $id           The optional element id, defaults to
     *                                  false for none
     * @param   string    $attribute    Additional optional attributes
     * @return  string                  The HTML code for the element
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getInputText($name, $value='', $id=false, $attribute='')
    {
        return
            '<input type="text" name="'.$name.'"'.
            ($id === false ? '' : ' id="'.($id ? $id : $name).'"').
            ' value="'.htmlentities($value, ENT_QUOTES, CONTREXX_CHARSET).
            '" tabindex="'.++self::$index_tab.'"'.
            ($attribute
              ? ' '.$attribute
              : (is_numeric($value)
                  ? ' style="text-align: right;"'
                  : '')).
            " />\n";
    }


    /**
     * Returns HTML code for a password text imput field
     *
     * The $name parameter is used for both the element name and id attributes.
     * @param   string    $name         The element name
     * @param   string    $value        The element value
     * @param   string    $attribute    Additional optional attributes
     * @return  string                  The HTML code for the element
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getInputPassword($name, $value, $attribute='')
    {
        return
            '<input type="password" name="'.$name.'" id="'.$name.'"'.
            ' value="'.htmlentities($value, ENT_QUOTES, CONTREXX_CHARSET).
            '" tabindex="'.++self::$index_tab.'"'.
            ($attribute ? ' '.$attribute : '').
            " />\n";
    }


    /**
     * Returns HTML code for a file upload input field
     *
     * If the $id parameter is false, the id attribute is not set.
     * If it's empty (but not false), the name is used instead.
     * @param   string    $name         The element name
     * @param   string    $id           The optional element id.  You can
     *                                  only get the target path and delete
     *                                  functionality if this is not false.
     * @param   string    $maxlength    The optional maximum accepted size
     * @param   string    $mimetype     The optional accepted MIME type
     * @param   string    $attribute    Additional optional attributes
     * @param   boolean   $visible      If true, the input element is set
     *                                  visible.  Defaults to true
     * @param   string    $path         Optional path.  If not empty, and the
     *                                  $id is non-empty,
     *                                  the file path is shown
     *                                  on top of the upload element, with
     *                                  a clickable delete icon to the right.
     *                                  Defaults to false
     * @return  string                  The HTML code for the element
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getInputFileupload(
        $name, $id=false, $maxlength='', $mimetype='', $attribute='',
        $visible=true, $path=false
    ) {
//        global $_CORELANG;

        $input = '';
        if ($path) {
            $id_path = '';
            if ($id !== false) {
                $id = ($id ? $id : $name);
                $id_path = 'path-'.$id;
                $id_div = 'div-'.$id;
                $input =
                    '<div id="'.$id_div.'">'.
                    '<a href="'.urlencode(ASCMS_PATH_OFFSET.'/'.$path).'">'.
                    $path.'</a>&nbsp;'.
                    self::getBackendFunctions(
                        array('delete' =>
                            'javascript:'.
                            'document.getElementById(\''.$id_path.'\').value=\'\';'.
                            'document.getElementById(\''.$id_div.'\').style.display=\'none\';'
                        ),
                        false, false
                    ).
                    '</div>';
            }
            $input .= self::getHidden($name, $path, $id_path);
        }
        $id = ($id === false ? '' : ' id="'.($id ? $id : $name).'"');
        return
            $input.
            '<input type="file" name="'.$name.'"'.($id ? $id : '').
            ' tabindex="'.++self::$index_tab.'"'.
            ($maxlength ? ' maxlength="'.$maxlength.'"' : '').
            ($mimetype ? ' accept="'.$mimetype.'"' : '').
            ($attribute ? ' '.$attribute : '').
            ($visible ? '' : ' style="display: none;"').
            " />\n";
    }


    /**
     * Returns HTML code for a text area
     *
     * The $name parameter is used for both the element name and id attributes.
     * @param   string    $name         The element name
     * @param   string    $value        The element value
     * @param   string    $cols         The optional number of columns
     * @param   string    $rows         The optional number of rows
     * @param   string    $attribute    Additional optional attributes
     * @return  string                  The HTML code for the element
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getTextarea(
        $name, $value, $cols='', $rows='', $attribute=''
    ) {
        return
            '<textarea name="'.$name.'" id="'.$name.'"'.
            ' tabindex="'.++self::$index_tab.'"'.
            ($cols ? ' cols="'.$cols.'"' : '').
            ($rows ? ' rows="'.$rows.'"' : '').
            ($attribute ? ' '.$attribute : '').
            '>'.htmlentities($value, ENT_QUOTES, CONTREXX_CHARSET).
            "</textarea>\n";
    }


    /**
     * Returns HTML code for a hidden imput field
     *
     * If the $id parameter is false, the id attribute is not set.
     * If it's empty (but not false), the name is used instead.
     * @todo    Maybe the optional attributes will never be used
     *          and can be removed?
     * @param   string    $name         The element name
     * @param   string    $value        The element value
     * @param   string    $id           The element id, if non-empty
     * @param   string    $attribute    Additional optional attributes
     * @return  string                  The HTML code for the element
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getHidden($name, $value, $id=false, $attribute='')
    {
        return
            '<input type="hidden" name="'.$name.'"'.
            ($id === false ? '' : ' id="'.($id ? $id : $name).'"').
            ' value="'.htmlentities($value, ENT_QUOTES, CONTREXX_CHARSET).'"'.
            ($attribute ? ' '.$attribute : '')." />\n";
    }


    /**
     * Returns HTML code for a button
     *
     * If the $id parameter is false, the id attribute is not set.
     * If it's empty (but not false), the name is used instead.
     * @param   string    $name         The element name
     * @param   string    $value        The element value
     * @param   string    $type         The button type, defaults to 'submit'
     * @param   string    $id           The element id, if non-empty
     * @param   string    $attribute    Additional optional attributes
     * @param   string    $label        The optional label text
     * @param   string    $label_attribute  The optional label attributes
     * @return  string                  The HTML code for the element
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getInputButton(
        $name, $value, $type='submit', $id=false, $attribute='',
        $label='', $label_attribute=''
    ) {
        if (   $type != 'submit'
            && $type != 'reset'
            && $type != 'button') $type = 'submit';
        $id = ($id === false ? '' : ($id ? $id : $name));
        return
            '<input type="'.$type.'" name="'.$name.'"'.
            ($id ? ' id="'.$id.'"' : '').
            ' tabindex="'.++self::$index_tab.'"'.
            ' value="'.htmlentities($value, ENT_QUOTES, CONTREXX_CHARSET).'"'.
            ($attribute ? ' '.$attribute : '')." />\n".
            ($label
              ? Html::getLabel($id, $label, $label_attribute) : '');
    }


    /**
     * Returns HTML code for a dropdown menu
     *
     * If the name is empty, the empty string is returned.
     * If the $id parameter is false, the id attribute is not set.
     * If it's empty (but not false), the name is used instead.
     * @param   string    $name         The element name
     * @param   array     $arrOptions   The options array
     * @param   string    $selected     The optional preselected option key
     * @param   string    $id           The optional element id
     * @param   string    $onchange     The optional onchange event script
     * @param   string    $attribute    Additional optional attributes
     * @return  string                  The HTML code for the element
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getSelect(
        $name, $arrOptions=array(), $selected='', $id=false, $onchange='', $attribute=''
    ) {
//echo("getSelect($name, ".var_export($arrOptions, true).", $selected, $onchange, $attribute): Entered<br />");
        if (empty($name)) {
            return '';
        }
        $menu =
            '<select name="'.$name.'"'.
            ($id === false ? '' : ' id="'.($id ? $id : $name).'"').
            ' tabindex="'.++self::$index_tab.'"'.
            ($onchange ? ' onchange="'.$onchange.'"' : '').
            ($attribute ? ' '.$attribute : '').
            ">\n".self::getOptions($arrOptions, $selected)."</select>\n";
//echo("getSelect(): made menu: ".htmlentities($menu)."<br />");
        return $menu;
    }


    /**
     * Returns HTML code for selection options
     *
     * The optional $selected parameter may be an array, in which case all
     * IDs found in the array keys are selected.  The arrays' values are
     * ignored.
     * @param   array   $arrOptions The options array
     * @param   mixed   $selected   The optional preselected option key
     *                              or array of keys
     * @return  string              The menu options HTML code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getOptions($arrOptions, $selected='')
    {
        $options = '';
        foreach ($arrOptions as $key => $value) {
            $options .=
                '<option value="'.$key.'"'.
                (is_array($selected)
                    ? (isset($selected[$key]) ? HTML_ATTRIBUTE_SELECTED : '')
                    : ("$selected" == "$key"  ? HTML_ATTRIBUTE_SELECTED : '')
                ).
                '>'.htmlentities($value, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
        }
        return $options;
    }


    /**
     * Returns HTML code for a radio button group
     *
     * If the name is empty, the empty string is returned.
     * The $name parameter is both used for the name and id parameter
     * in the element.  For the id, a dash and an additional index are
     * appended, like '$name-$index'.  That index is increased accordingly
     * on each call to this method.  Mind that thus, it *MUST* be unique on
     * your page.
     * The $arrOptions array must contain the value-text pairs in the order
     * to be added.  The values are used in the radio butten, and the text
     * for the label appended.
     * @param   string    $name         The element name
     * @param   array     $arrOptions   The options array
     * @param   string    $checked      The optional preselected option key
     * @param   string    $onchange     The optional onchange event script
     * @param   string    $attributeRadio    Additional optional attributes
     *                                  for the radio button elements
     * @param   string    $attributeLabel    Additional optional attributes
     *                                  for the labels
     * @return  string                  The HTML code for the element
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getRadioGroup(
        $name, $arrOptions, $checked='', $onchange='',
        $attributeRadio='', $attributeLabel=''
    ) {
        static $index = array();

//echo("getRadioGroup($name, $arrOptions, $checked, $onchange, $attributeRadio, $attributeLabel): Entered<br />");

        if (empty($name)) return '';
        // Remove any bracketed construct from the end of the name
        $name_stripped = preg_replace('/\[.*$/', '', $name);
        $radiogroup = '';
        foreach ($arrOptions as $value => $text) {
            $index[$name_stripped] = (empty($index[$name_stripped])
                ? 1 : ++$index[$name_stripped]);
            $id = $name_stripped.'-'.$index[$name_stripped];
            $radiogroup .=
                self::getRadio(
                    $name, $value, $id, ($value == $checked),
                    $onchange, $attributeRadio
                ).
                self::getLabel(
                    $id,
                    $text,
                    $attributeLabel
                );
        }
//echo("getRadioGroup(): Made ".htmlentities($radiogroup, ENT_QUOTES, CONTREXX_CHARSET)."<br />");
        return $radiogroup;
    }


    /**
     * Returns HTML code for a radio button
     *
     * If the name is empty, the empty string is returned.
     * If the $id parameter is false, the id attribute is not set.
     * If it's empty (but not false), the name is used instead.
     * @param   string    $name         The element name
     * @param   array     $value        The element value
     * @param   string    $id           The optional element id
     * @param   boolean   $checked     If true, the radio button is
     *                                  preselected.  Defaults to false
     * @param   string    $onchange     The optional onchange event script
     * @param   string    $attribute    Additional optional attributes
     * @return  string                  The HTML code for the element
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getRadio(
        $name, $value, $id=false, $checked=false, $onchange='', $attribute='')
    {

//echo("getRadio($name, $value, $id, $checked, $onchange, $attribute): Entered<br />");

        if (empty($name)) return '';
        return
            '<input type="radio" name="'.$name.
            '" value="'.htmlentities($value, ENT_QUOTES, CONTREXX_CHARSET).'"'.
            ($id === false ? '' : ' id="'.($id ? $id : $name).'"').
            ($checked ? ' checked="checked"' : '').
            ' tabindex="'.++self::$index_tab.'"'.
            ($onchange ? ' onchange="'.$onchange.'"' : '').
            ($attribute ? ' '.$attribute : '').
            " />\n";
    }


    /**
     * Returns HTML code for a checkbox group
     *
     * If the name is empty, the empty string is returned.
     * The $name parameter is both used for the name and id parameter
     * in the element.  For the id, a dash and an additional index are
     * appended, like '$name-$index'.  That index is increased accordingly
     * on each call to this method.  Mind that thus, it *MUST* be unique on
     * your page.
     * The $arrOptions array must contain the key-value pairs in the order
     * to be added.  The keys are used to index the name attribute in the
     * checkboxes, the value is put into the value attribute.
     * The $arrLabel should use the same keys, its values are appended
     * as label text to the respective checkboxes, if present.
     * The $arrChecked array may contain the values to be preselected
     * as array values.  It's keys are ignored.
     * @param   string    $name         The element name
     * @param   array     $arrOptions   The options array
     * @param   array     $arrLabel     The optional label text array
     * @param   array     $arrChecked   The optional preselected option keys
     * @param   string    $id           The optional element id
     * @param   string    $onchange     The optional onchange event script
     * @param   string    $separator    The optional separator between
     *                                  checkboxes
     * @param   string    $attributeRadio    Additional optional attributes
     *                                  for the checkbox elements
     * @param   string    $attributeLabel    Additional optional attributes
     *                                  for the labels
     * @return  string                  The HTML code for the elements
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getCheckboxGroup(
        $name, $arrOptions, $arrLabel='', $arrChecked='', $id=false,
        $onchange='', $separator='',
        $attributeCheckbox='', $attributeLabel=''
    ) {
        static $index = array();

//echo("getCheckboxGroup($name, ".var_export($arrOptions, true).", ".var_export($arrLabel, true).", ".var_export($arrChecked, true).", $onchange, $attributeCheckbox, $attributeLabel): Entered<br />");

        if (empty($name)) return '';
        // Remove any bracketed construct from the end of the name
        $name_stripped = preg_replace('/\[.*$/', '', $name);
        if (!is_array($arrLabel)) $arrLabel = array();
        if (!is_array($arrChecked)) $arrChecked = array();
        if (empty($id) && $id !== false) $id = $name_stripped;
        $checkboxgroup = '';
        foreach ($arrOptions as $key => $value) {
            if (empty($index[$name_stripped])) $index[$name_stripped] = 0;
            $id_local = $id.'-'.++$index[$name_stripped];
            $checkboxgroup .=
                ($checkboxgroup ? $separator : '').
                self::getCheckbox(
                    $name.'['.$key.']', $value, $id_local,
                    (in_array($key, $arrChecked)),
                    $onchange, $attributeCheckbox
                ).
                self::getLabel(
                    $id_local,
                    $arrLabel[$key],
                    $attributeLabel
                );
        }
        return $checkboxgroup;
    }


    /**
     * Returns HTML code for a checkbox
     *
     * If the name is empty, the empty string is returned.
     * The $value is htmlentities()d to prevent side effects.
     * If the $id parameter is false, the id attribute is not set.
     * If it's empty (but not false), the name is used instead.
     * @param   string    $name         The element name
     * @param   string    $value        The element value, defaults to 1 (one)
     * @param   string    $id           The optional element id
     * @param   boolean   $checked      If true, the checkbox is checked
     * @param   string    $onchange     The optional onchange event script
     * @param   string    $attribute    Additional optional attributes
     * @return  string                  The HTML code for the element
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getCheckbox(
        $name, $value=1, $id=false, $checked=false, $onchange='', $attribute=''
    ) {

//echo("getCheckbox($name, $value, $id, $checked, $onchange, $attribute): Entered<br />");

        if (empty($name)) return '';
        return
            '<input type="checkbox" name="'.$name.'"'.
            ' value="'.htmlentities($value, ENT_QUOTES, CONTREXX_CHARSET).'"'.
            ($id === false ? '' : ' id="'.($id ? $id : $name).'"').
            ($checked ? ' checked="checked"' : '').
            ' tabindex="'.++self::$index_tab.'"'.
            ($onchange ? ' onchange="'.$onchange.'"' : '').
            ($attribute ? ' '.$attribute : '').
            " />\n";
    }


    /**
     * Wraps the content in a label
     *
     * The $text is htmlentities()d to prevent side effects.
     * Mind that the $for parameter must match the id attribute of the
     * contained element in $content.
     * @param   string    $for          The for attribute of the label
     * @param   string    $text         The text of the label
     * @param   string    $attribute    Additional optional attributes
     * @return  string                  The HTML code for the label with
     *                                  the text
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getLabel($for, $text, $attribute='')
    {
        return
            '<label for="'.$for.'"'.
            ($attribute ? ' '.$attribute : '').
            '>'.htmlentities($text, ENT_QUOTES, CONTREXX_CHARSET)."</label>\n";
    }


    /**
     * Returns an image tag for the given Image object in its original size
     *
     * This adds alt, width, and height attributes with the values returned
     * by {@see Image::getPath()}, {@see Image::getWidth()}, and
     * {@see Image::getHeight()} methods repectively.
     * If the $attribute parameter contains one of the alt, width, or height
     * attributes (or corresponding style information), these will override
     * the data from the Image object.
     * @param   Image     $objImage     The Image object
     * @param   string    $attribute    Additional optional attributes
     * @return  string                  The HTML code for the image tag
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getImageOriginal($objImage, $attribute='')
    {
        $width = $objImage->getWidth();
        $height = $objImage->getHeight();
        $path = $objImage->getPath();
        return
            '<img src="'.FWValidator::getEscapedSource($path).'"'.
            (   empty($width)
             || preg_match('/width[:=]/i', $attribute)
              ? '' : ' width="'.$width.'"').
            (   empty($height)
             || preg_match('/height[:=]/i', $attribute)
              ? '' : ' height="'.$height.'"').
            (preg_match('/alt\=/i', $attribute)
              ? '' : ' alt="'.$path.'"').
            ($attribute ? ' '.$attribute : '').
            " />";
    }


    /**
     * Returns an image tag for the given Image object in the size specified
     * by the corresponding Imagetype
     *
     * This adds alt, width, and height attributes with the values returned
     * by {@see Image::getPath()}, and {@see Imagetype::getInfoArray()} methods
     * repectively.
     * If the $attribute parameter contains one of the alt, width, or height
     * attributes (or corresponding style information), these will override
     * the data from the Image or Imagetype.
     * @param   Image     $objImage     The Image object
     * @param   string    $attribute    Additional optional attributes
     * @return  string                  The HTML code for the image tag
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getImage($objImage, $attribute='')
    {
        $type = $objImage->getImagetypeKey();
        $objImagetype = Imagetype::getInfoArray($type);
        $width = $objImagetype['width'];
        $height = $objImagetype['height'];
        $path = $objImage->getPath();
        return
            '<img src="'.FWValidator::getEscapedSource($path).'"'.
            (   empty($width)
             || preg_match('/width[:=]/i', $attribute)
              ? '' : ' width="'.$width.'"').
            (   empty($height)
             || preg_match('/height[:=]/i', $attribute)
              ? '' : ' height="'.$height.'"').
            (preg_match('/alt\=/i', $attribute)
              ? '' : ' alt="'.$path.'"').
            ($attribute ? ' '.$attribute : '').
            " />\n";
    }


    /**
     * Returns an image tag for the given Image object in the thumbnail
     * size specified by the corresponding Imagetype
     *
     * This adds alt, width, and height attributes with the values returned
     * by {@see Image::getPath()}, and {@see Imagetype::getInfoArrayWidth()}
     * methods repectively.
     * If the $attribute parameter contains one of the alt, width, or height
     * attributes (or corresponding style information), these will override
     * the data from the Image or Imagetype.
     * @param   Image     $objImage     The Image object
     * @param   string    $attribute    Additional optional attributes
     * @return  string                  The HTML code for the image tag
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getThumbnail($objImage, $attribute='')
    {
        $type = $objImage->getImagetypeKey();
        $objImagetype = Imagetype::getInfoArray($type);
        $width = $objImagetype['width_thumb'];
        $height = $objImagetype['height_thumb'];
        $path = $objImage->getPath();
        return
            '<img src="'.FWValidator::getEscapedSource($path).'"'.
            (   empty($width)
             || preg_match('/width[:=]/i', $attribute)
              ? '' : ' width="'.$width.'"').
            (   empty($height)
             || preg_match('/height[:=]/i', $attribute)
              ? '' : ' height="'.$height.'"').
            (preg_match('/alt\=/i', $attribute)
              ? '' : ' alt="'.$path.'"').
            ($attribute ? ' '.$attribute : '').
            " />\n";
    }


    /**
     * Returns an image tag for the given Image path
     *
     * This adds alt, width, and height attributes with the values returned
     * by {@see Image::getPath()}, {@see Image::getWidth()}, and
     * {@see Image::getHeight()} methods repectively.
     * If the $attribute parameter contains one of the alt, width, or height
     * attributes (or corresponding style information), these will override
     * the data from the Image object.
     * @param   Image     $objImage     The Image object
     * @param   string    $attribute    Additional optional attributes
     * @return  string                  The HTML code for the image tag
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getImageByPath($image_path, $attribute='')
    {
        $objImage = new Image();
        $objImage->setPath($image_path);
        return self::getImageOriginal($objImage, $attribute);
    }


    /**
     * Returns HTML code for an image element that links to
     * the filebrowser for choosing an image file on the server
     *
     * If the optional $imagetype_key is missing (defaults to false),
     * no image type can be selected.  If it's a string, the type of the
     * Image is set to this key.  If it's an array of keys, the Image type
     * can be selected from these.
     * Uses the $id parameter as prefix for both the name and id attributes
     * of all HTML elements.  The names and respective suffixes are:
     *  - id+'img' for the name and id of the <img> tag
     *  - id+'_src' for the name and id of the hidden <input> tag for the image URI
     *  - id+'_width' for the name and id of the hidden <input> tag for the width
     *  - id+'_height' for the name and id of the hidden <input> tag for the height
     * All of the elements with a suffix will provide the current selected
     * image information when the form is posted.
     * See {@see Image::updatePostImages()} and {@see Image::uploadAndStore()}
     * for more information and examples.
     * @param   Image   $objImage       The image object
     * @param   string  $id             The base name for the elements IDs
     * @param   mixed   $imagetype_key  The optional Image type key
     * @return  string                  The HTML code for all the elements
     */
    static function getImageChooserBrowser($objImage, $id, $imagetype_key=false)
    {
        global $_CORELANG;

        Javascript::registerCode(self::getJavascript_Image(Image::PATH_NO_IMAGE));
        if (empty($objImage)) $objImage = new Image(0);
        $type_element =
            '<input type="hidden" id="'.$id.'_type" name="'.$id.'_type"'.
            ' value="'.$imagetype_key.'" />'."\n";
// TODO: Implement...
/*
        if (is_array($imagetype_key)) {
            $arrImagetypeName = Imagetype::getNameArray();
            $type_element = self::getSelect($id.'_type', $arrImagetypeName);
        }
*/
        return
            $type_element.
            '<img id="'.$id.'_img" src="'.
            FWValidator::getEscapedSource($objImage->getPath()).'"'.
            ' style="width:'.$objImage->getWidth().
            'px; height:'.$objImage->getHeight().'px;"'.
            ' title="'.$_CORELANG['TXT_CORE_HTML_IMAGE_PREVIEW'].'"'.
            ' alt="'.$_CORELANG['TXT_CORE_HTML_IMAGE_PREVIEW'].'" />'."\n".
            self::getHidden(
                $id.'_type',
                ($imagetype_key !== false
                  ? $imagetype_key : $objImage->getImagetypeKey()),
                '' // Force id attribute like name!
            ).
            ($objImage->getPath()
              ? self::getClearImageCode($id).
                self::getHidden(
                    $id.'_id', $objImage->getId(),
                    '' // Force id attribute like name!
                ).
                self::getHidden(
                    $id.'_ord', $objImage->getOrd(),
                    '' // Force id attribute like name!
                )
              : '').
            self::getHidden(
                $id.'_src', $objImage->getPath(),
                '' // Force id attribute like name!
            ).
            '<a href="javascript:void(0);" title="{TXT_CORE_HTML_CHOOSE_IMAGE}"'.
            ' tabindex="'.++self::$index_tab.'"'.
            ' onclick="openBrowser(\'index.php?cmd=fileBrowser&amp;standalone=true&amp;type=shop\',\'1\',\'width=800,height=640,resizable=yes,status=no,scrollbars=yes\');">'.
            $_CORELANG['TXT_CORE_HTML_CHOOSE_IMAGE']."</a>\n";
    }


    /**
     * Returns HTML code for an image element with form
     * elements for uploading an image file.
     *
     * Uses the $id parameter as prefix for both the name and id attributes
     * of all HTML elements.  The names and respective suffixes are:
     *  - id+'_img' for the name and id of the <img> tag
     *  - id+'_src' for the name and id of the hidden <input> tag for the image path
     *  - id+'_type' for the name and id of the hidden <input> tag or <select>
     *               menu for the Imagetype
     *  - id+'_width' for the name and id of the hidden <input> tag for the width
     *  - id+'_height' for the name and id of the hidden <input> tag for the height
     *  - id+'_file' for the name and id of the file upload element
     * The file upload element will provide the new image chosen by the user
     * when the form is posted, while the hidden fields represent the previous
     * state when the page was generated.
     * The $path_default will replace the path of the image shown if that is
     * empty, but the path posted back will remain empty so the default image
     * is not accidentally stored.
     * If the optional $imagetype_key is empty or not an array,
     * no image type can be selected.  If it's false, the image type field is
     * omitted.  If it's empty but not false, the current
     * Image's type is used.  If it's a string, the type is set to
     * that value, overriding the Image's current type.  If it's an array,
     * the Image type can be selected from its values, and the current Image's
     * key is preselected.  The array keys *SHOULD* contain the Imagetype key,
     * its values the Imagetype names in the current language.
     * See {@see Image::updatePostImages()} and {@see Image::uploadAndStore()}
     * for more information and examples.
     * @param   Image   $objImage       The image object
     * @param   string  $id             The base name for the elements IDs
     * @param   mixed   $imagetype_key  The optional Image type key or
     *                                  Imagetype array.  Defaults to the
     *                                  empty string
     * @param   string  $path_default   The optional path of a default image.
     *                                  Defaults to the empty string
     * @param   boolean $replace_only   If true, the image cannot be deleted,
     *                                  but there's always a file upload input
     *                                  to replace the current image.
     *                                  Defaults to false
     * @return  string                  The HTML code for all the elements
     */
    static function getImageChooserUpload(
        $objImage, $id, $imagetype_key='', $path_default='',
        $replace_only=false
    ) {
        global $_CORELANG;

        JS::registerCode(self::getJavascript_Image($path_default));
        if (empty($objImage)) $objImage = new Image(0);
        $path = $objImage->getPath();
        if ($path) {
            $path_thumb = ($path ? Image::getThumbnailPath($path) : '');
            list ($width, $height) = $objImage->getSizeArrayThumbnail();
        } else {
            $path_thumb = $path_default;
            if (empty($width) && empty($height)) {
                $key = (is_array($imagetype_key)
                    ? '' : $imagetype_key);
                $width = Imagetype::getWidthThumbnail($key);
                $height = Imagetype::getHeightThumbnail($key);
            }
        }
//        $arrImagetypeName = Imagetype::getNameArray();
//        if ($imagetype_key === false)
//            $imagetype_key = $objImage->getImagetypeKey();

        $path_preview = (defined('BACKEND_LANG_ID') ? '../' : '').$path_thumb;
        return
            self::getImageByPath(
                $path_preview,
                'style="width: '.$width.'px; height: '.$height.'px;"'.
                ' title="'.$_CORELANG['TXT_CORE_HTML_IMAGE_PREVIEW'].'"'.
                ' alt="'.$_CORELANG['TXT_CORE_HTML_IMAGE_PREVIEW'].'"'.
                ' id='.$id.'_img'
            )."\n".
            ($path
                ? ($replace_only ? '' : self::getClearImageCode($id)).
                  self::getHidden(
                      $id.'_src', $objImage->getPath(), '' // Force id attribute like name
                  )
                : '').'<br />'.
            '<br />'.
            ($path
              ? sprintf(
                  $_CORELANG['TXT_CORE_HTML_IMAGE_CURRENT'],
                  basename($objImage->getPath())).
                '<br />'
              : '').
            Imagetype::getMenu(
                $id, $objImage->getImagetypeKey(), $imagetype_key).
            self::getHidden(
                $id.'_id', $objImage->getId(), '' // Force id attribute like name
            ).
            self::getHidden(
                $id.'_ord', $objImage->getOrd(), '' // Force id attribute like name
            ).
            // Set the upload element to visible if no image path is set,
            // or if replace only is on
            '<br />'.
            self::getInputFileupload(
                $id, '', Image::MAXIMUM_UPLOAD_FILE_SIZE,
                Filetype::MIMETYPE_IMAGES_WEB, '', $replace_only || empty($path));
    }


    static function getRemoveAddLinks($id)
    {
        JS::registerCode(self::getJavascript_Element());
        $objImageRemove = new Image();
        $objImageRemove->setPath(self::ICON_ELEMENT_REMOVE );
        $objImageRemove->setWidth(16);
        $objImageRemove->setHeight(16);
        $objImageAdd = new Image();
        $objImageAdd->setPath(self::ICON_ELEMENT_ADD);
        $objImageAdd->setWidth(16);
        $objImageAdd->setHeight(16);
        return
            '<a href="javascript:void(0);" '.
            'onclick="removeElement(\''.$id.'\');">'.
            self::getImageOriginal($objImageRemove, 'border="0"').'</a>'.
            '<a href="javascript:void(0);" '.
            'onclick="cloneElement(\''.$id.'\');">'.
            self::getImageOriginal($objImageAdd, 'border="0"').'</a>';
    }


    static function getClearImageCode($id)
    {
        global $_CORELANG;

        $objImage = new Image();
        $objImage->setPath(self::ICON_FUNCTION_CLEAR_IMAGE);
        // Fix the image paths in case we're not in the backend
        if (!defined('BACKEND_LANG_ID')) {
            $objImage->setPath(
                ASCMS_PATH_OFFSET.ASCMS_BACKEND_PATH.'/'.
                $objImage->getPath());
        }
        $objImage->setWidth(16);
        $objImage->setHeight(16);
        return
            '<a id="'.$id.'_clear" href="javascript:void(0);"'.
            ' onclick="clearImage(\''.$id.'\');">'.
            self::getImageOriginal(
                $objImage,
                'border="0" alt="'.$_CORELANG['TXT_CORE_HTML_DELETE_IMAGE'].
                '" title="'.$_CORELANG['TXT_CORE_HTML_DELETE_IMAGE'].
                '"'
            ).
            '</a>';
    }


    /**
     * Returns a date selection element
     *
     * Uses and activates 'datepicker' (See {@see JS::activate()}).
     * The ID created for the element is returned in the $id parameter.
     * @internal  Ignore the code analyzer warning for $id.
     * @param   string    $name       The element name
     * @param   string    $value      The optional default value
     * @param   string    $attribute  The optional attributes
     * @param   string    $id         The optional ID returned by reference
     * @return  string                The datepicker element HTML code
     * @internal  Ignore the code analyzer warning about $id
     */
    static function getSelectDate($name, $value='', $attribute='', &$id='')
    {
        static $index = 0;

        JS::activate('datepicker');
        $id .= 'DPC_edit'.++$index.'_'.ASCMS_DATE_SHORT_FORMAT;
        return self::getInputText(
            $name, $value, $id, $attribute);
    }


    /**
     * Returns HTML code for the functions available in many list views
     *
     * The $arrFunction array must look something like:
     *  array(
     *    'view'   => The view action parameter,
     *    'copy'   => The copy action parameter,
     *    'edit'   => The edit action parameter,
     *    'delete' => The delete action parameter,
     *  )
     * You may omit any indices that do not apply, those icons will not be
     * included.
     * The action parameter may be a full absolute or relative URI,
     * or just a partial query string, like "act=what_to_do".
     * In the first case, it is used unchanged.  In the second case,
     * the parameters in the string replace those of the same name or are
     * added to the current page URI.
     * You may also specify javascript code,
     * this *MUST* start with "javascript:" and will replace the page URI.
     * Empty actions will usually lead back to the module start page.
     * Mind that you *MUST* use single quotes (') in all of those action
     * strings *ONLY*.
     * The optional $arrConfirmation array may contain any of the same indices,
     * plus some text.  If present, these texts are shown in a confirmation box,
     * and action is only taken if it is confirmed.
     * Note that in that case, the action parameter *MUST* be a valid
     * URI, *NOT* some javascript statement!
     * Any icon's alt and title tag is fitted with the default core language
     * entry whose index is formed like "TXT_CORE_HTML_" plus the function name
     * in upper case, e.g. "TXT_CORE_HTML_DELETE".  This text may be overridden
     * by entries in the $arrText array, indexed like $arrFunction.
     * //Also, the current CSRF key and value pair is added to any URI processed.
     * @param   array   $arrFunction      The array of functions and actions
     * @param   array   $arrConfirmation  The optional array with confirmation
     *                                    texts
     * @param   boolean $align_right      Align the function icons to the right
     *                                    using an enclosing <div> if true.
     *                                    Defaults to true
     * @param   array   $arrText          Alternative/title text for the icons.
     *                                    Defaults to null, in which case the
     *                                    core language entry is used,
     *                                    if present
     * @return  string                    The HTML code for the function column
     */
    static function getBackendFunctions(
        $arrFunction, $arrConfirmation=false, $align_right=true, $arrText=null
    ) {
        global $_CORELANG;

        $uri = Html::getRelativeUri_entities();
        $function_html = '';
        foreach ($arrFunction as $function => $action) {
            $objImage = new Image();
            $objImage->setWidth(16);
            $objImage->setHeight(16);
            if (empty($action)) {
                $objImage->setPath(self::ICON_FUNCTION_BLANK);
                $function_html .=
                    self::getImageOriginal($objImage, 'border="0" alt="" title=""');
                    continue;
            }
            switch ($function) {
              case 'view':
                $objImage->setPath(self::ICON_FUNCTION_VIEW);
                break;
              case 'copy':
                $objImage->setPath(self::ICON_FUNCTION_COPY);
                break;
              case 'edit':
                $objImage->setPath(self::ICON_FUNCTION_EDIT);
                break;
              case 'delete':
                $objImage->setPath(self::ICON_FUNCTION_DELETE);
                break;
              case 'mark_undeleted':
                $objImage->setPath(self::ICON_FUNCTION_MARK_UNDELETED);
                break;
              case 'mark_deleted':
                $objImage->setPath(self::ICON_FUNCTION_MARK_DELETED);
                break;
              case 'special_on':
                $objImage->setPath(self::ICON_FUNCTION_SPECIAL_ON);
                break;
              case 'special_off':
                $objImage->setPath(self::ICON_FUNCTION_SPECIAL_OFF);
                break;
              case 'download_pdf':
              case 'download_pdf_hotelcard':
              case 'download_pdf_invoice':
              case 'download_pdf_letter':
                $objImage->setPath(self::ICON_FUNCTION_DOWNLOAD_PDF);
                break;
              case 'details':
                $objImage->setPath(self::ICON_DETAILS);
                break;
              case 'gift':
                $objImage->setPath(self::ICON_GIFT);
                break;
              default:
                continue 2;
            }
            // Fix the image paths in case we're not in the backend
            if (!defined('BACKEND_LANG_ID')) {
                $objImage->setPath(
                    ASCMS_PATH_OFFSET.ASCMS_BACKEND_PATH.'/'.
                    $objImage->getPath());
            }

// Cases:
// $action is a regular URI, no confirmation
// $action is a regular URI, with confirmation
// $action is JS code, no confirmation
// $action is JS code, with confirmation
            $_uri = $uri;
            $onclick = '';
            $match = array();
            if (preg_match('/^javascript\:(.+)$/i', $action, $match)) {
// $action is JS code
                $_uri = 'javascript:void(0);';
                $onclick = $match[1];
            } else {
// $action is a regular URI or just some parameters
                if (preg_match('/'.preg_quote(CONTREXX_DIRECTORY_INDEX).'/', $action)) {
                    // Keep the URI if it includes more than just parameters
                    $_uri = $action;
                } else {
                    // Use the current URI and replace action parameters otherwise
                    self::replaceUriParameter($_uri, $action);
                }
            }

            if (!empty($arrConfirmation[$function])) {
// Ask for confirmation, then either run the JS or go to the target URI
                $onclick =
                    'if (confirm(\''.$arrConfirmation[$function].'\'))'.
                    ($onclick
                      ? $onclick
                      : 'window.location.replace(\''.$_uri.'\');');
// No direct link to the target URI
                $_uri = 'javascript:void(0);';
            }
            $text = (isset($arrText[$function])
                ? $arrText[$function]
                : (isset($_CORELANG['TXT_CORE_HTML_'.strtoupper($function)])
                    ? $_CORELANG['TXT_CORE_HTML_'.strtoupper($function)]
                    : ''));
            $function_html .=
                '<a href="'.$_uri.'"'.
                ($onclick ? ' onclick="'.$onclick.'"' : '').'>'.
                self::getImageOriginal(
                    $objImage, 'border="0" alt="'.$text.
                    '" title="'.$text.'"').'</a>';
        }
//echo("Backend function: ".htmlentities($function_html)."<hr />");
// No longer needed
//        JS::registerCode(self::getJavascript_Function());

        return ($align_right
            ? '<div style="text-align: right;">'.$function_html.'</div>'
            : $function_html);
    }


    /**
     * Returns HTML code to represent some status with a colored LED image
     *
     * Colors currently available include green, yellow, and red.
     * For unknown colors, the empty string is returned.
     * The $alt parameter value is added as the images' alt attribute value,
     * if non-empty.
     * The $action parameter may include URI parameters to be inserted in the
     * href attribute of a link, which is added if $action is non-empty.
     * @param   string    $color      The LED color
     * @param   string    $alt        The optional alt attribute for the image
     * @param   string    $action     The optional action URI parameters
     * @return  string                The LED HTML code on success, the
     *                                empty string otherwise
     */
    static function getLed($status='', $alt='', $action='')
    {
        $objImage = new Image();
        switch ($status) {
          case 'green':
            $objImage->setPath(self::ICON_STATUS_GREEN);
            break;
          case 'yellow':
            $objImage->setPath(self::ICON_STATUS_YELLOW);
            break;
          case 'red':
            $objImage->setPath(self::ICON_STATUS_RED);
            break;
          default:
            // Unknown color.  Return the empty string.
            return '';
        }
//echo("Html::getLed($status, $action): led is ".$objImage->getPath()."<br />");
        $objImage->setWidth(11);
        $objImage->setHeight(11);
        $led_html = self::getImageOriginal(
            $objImage,
            'border="0"'.($alt ? ' alt="'.$alt.'" title="'.$alt.'"' : ''));
        if ($action) {
            $uri = self::getRelativeUri_entities();
            self::replaceUriParameter($uri, $action);
            $led_html = '<a href="'.$uri.'">'.$led_html.'</a>';
        }
        return $led_html;
    }


    /**
     * Returns HTML code for either a checked or unchecked icon
     * for indicating yes/no status
     *
     * For the time being, the unchecked status is represented by
     * an empty space, aka pixel.gif
     * @param   boolean   $status     If true, the checked box is returned,
     *                                the unchecked otherwise
     * @return  string                The HTML code with the checkbox icon
     * @todo    There should be an unchecked icon other than "pixel.gif"
     */
    static function getCheckmark($status='')
    {
        $objImage = new Image();
        $objImage->setPath($status
            ? self::ICON_STATUS_CHECKED : self::ICON_STATUS_UNCHECKED);
        $objImage->setWidth(16);
        $objImage->setHeight(16);
        $checkmark_html = self::getImageOriginal($objImage, 'border="0"');
        return $checkmark_html;
    }


    /**
     * Returns a clickable element that toggles its color and value
     *
     * The $arrStatus array contains two key-value pairs.  The key is
     * the element's value, the value the corresponding HTML color.
     * The first pair is used for off, the second for on.
     * The initial value of the element is determined by $init;
     * if it is true, the element starts on.
     * The optional $arrTitle array may contain values for the title
     * attribute to be used for the two states, in the same order as
     * $arrStatus.
     * If the $id parameter is empty, the class constant {@see TOGGLE_ID_BASE}
     * is used for the id attribute base name.
     * If the optional $hidden_id is empty, an additional hidden input element
     * with the id attribute "hidden-" plus $id is created.  Otherwise,
     * this element must exist on the page and may be any kind of element
     * with a value.
     * Either one of the ids are also suffixed with the current $index_toggle
     * number if they are empty and thus autocreated.
     * You can style the element by defining a CSS class with the name
     * "toggle" plus your $id; this defaults to "toggletoggle"... - sorry.  :)
     * @param   string    $name         The element name
     * @param   array     $arrStatus    The status array
     * @param   string    $id           The optional id attribute name
     * @param   string    $hidden_id    The optional id attribute name of
     *                                  the element carrying the value
     * @param   boolean   $init         The optional initial status.
     *                                  Defaults to false
     * @param   array     $arrTitle     The optional title array
     * @return  string                  The element code
     */
    static function getToggle(
        $name, $arrStatus, $id='', $hidden_id='', $init=false, $arrTitle=false
    ) {
//echo("getToggle($name, $arrStatus, $init, $arrTitle): Entered<br />");
        list ($key_off, $class_off) = each($arrStatus);
        list ($key_on, $class_on) = each($arrStatus);
        list ($title_off, $title_on) =
            (is_array($arrTitle) && count($arrTitle) == 2
              ? array_values($arrTitle) : array('', ''));
        ++self::$index_toggle;
        if (empty($id)) $id = self::TOGGLE_ID_BASE.self::$index_toggle;
        $element_hidden = '&nbsp;';
        if (empty($hidden_id)) {
            $hidden_id = 'hidden-'.self::TOGGLE_ID_BASE.self::$index_toggle;
            $element_hidden = self::getHidden(
                $name,
                ($init ? $key_on : $key_off),
                $hidden_id
            );
        }
//echo("getToggle(): name $name, id $id, hidden_id $hidden_id<br />");
//echo("getToggle(): key_off $key_off class_off $class_off title_off $title_off key_on $key_on class_on $class_on title_on $title_on<br />");
        return
            '<div id="'.$id.'"'.
            ' class="'.($init ? $class_on : $class_off).'"'.
            ' title="'.($init ? $title_on : $title_off).'"'.
            ' onclick="Toggler.click(this)">'.
            $element_hidden."</div>\n";
    }


    /**
     * Returns the javascript needed to make elements returned by
     * {@see getToggle()} work
     *
     * The $hidden_id_base *MUST* be letters and dash (-) *ONLY*.
     * Everything else is considered to be part of the internal
     * element index, including digits and underscore.
     * @param   string  $toggle_id_base     The base name of the toggle elements
     * @param   string  $hidden_id_base     The base name of the (hidden) value elements
     * @param   array   $arrValues          The array of individual toggle
     * @param   array   $arrTypeIds         The array of type IDs
     * @return  string                      The javascript code
     */
    static function getJavascript_Toggle(
        $toggle_id_base, $hidden_id_base, $arrValues=array(), $arrTypeIds=array()
    ) {
//echo("getJavascript_Toggle($toggle_id_base, $hidden_id_base, ".var_export($arrValues, true)."): Entered<br />");
        $strJavascript = '
var Toggler = {
  types: ['.join(',', $arrTypeIds).'],
  key_off:"'.  ($arrValues['key_off']
      ? $arrValues['key_off']   : self::TOGGLE_KEY_OFF).'",
  key_on:"'.   ($arrValues['key_on']
      ? $arrValues['key_on']    : self::TOGGLE_KEY_ON).'",
  class_off:"'.($arrValues['class_off']
      ? $arrValues['class_off'] : self::TOGGLE_CLASS_OFF).'",
  class_on:"'. ($arrValues['class_on']
      ? $arrValues['class_on']  : self::TOGGLE_CLASS_ON).'",
  title_off:"'.($arrValues['title_off']
      ? $arrValues['title_off'] : self::TOGGLE_TITLE_OFF).'",
  title_on:"'. ($arrValues['title_on']
      ? $arrValues['title_on']  : self::TOGGLE_TITLE_ON).'",
  values:{';
        $strValues_all = '';
        foreach ($arrValues as $index => $arrValue) {
            if (!is_array($arrValue)) continue;
            $strValues_row = '';
            foreach ($arrValue as $name => $value) {
                $strValues_row .=
                    ($strValues_row ? ',' : '').
                    $name.':"'.$value.'"';
            }
            $strValues_all .=
                ($strValues_all ? ',' : '').
                $index.':{'.$strValues_row.'}';
        }
        return $strJavascript.$strValues_all.'},

  // One of the toggle elements has been clicked
  click: function(element_toggle)
  {
    var id = element_toggle.getAttribute("id");
//alert("ID: "+id);
    // Extract the room type ID and date from the element ID
    if (!/^[-a-z]+_(\d+)_(\d+_\d+_\d+)$/i.match(id)) {
//alert("click: ID mismatch: "+id);
      return;
    }
    // If the type ID is zero, all types will be toggled separately!
    var clicked_type_id = parseInt(RegExp.$1);
    var date = RegExp.$2;
    var status = 1;
    number = 0;
    if (element_toggle.className == Toggler.class_off) {
      // Current status is off.  Turn this element on.
      status = 0;
    }
    for (var i = 0; i < Toggler.types.length; ++i) {
      type_id = Toggler.types[i];
//alert("Clicked: "+clicked_type_id+", ID: "+type_id);
      if (clicked_type_id > 0 && clicked_type_id != type_id) continue;
      // The array indices all have to start with a leading underscore
      var _id = "_"+type_id+"_"+date;
      if (status == 0) {
        // If there is a room number value stored in the array, use that
        if (Toggler.values[_id]) {
          number = Toggler.values[_id]["key_on"];
        }
      } else {
        // This is on.
        // Store the current value in the array before turning it off.
        // This only works for single room types, but not if "All roomtypes"
        // is clicked!
        var element_hidden = document.getElementById("'.$hidden_id_base.'"+_id);
        if (element_hidden) {
          Toggler.values[_id]["key_on"] = element_hidden.value;
        } else {
//alert("click: Could not find hidden for ID "+_id);
        }
      }
      Toggler.loading("dummy"+_id);
      new Ajax.Request(
        location.href,
        {
          method: "get",
          parameters: "click="+_id+"&status="+status+"&number="+number, //+"&price="+price
          onComplete: Toggler.update
          //,onFailure: alert("FAIL")
        }
      );
    }
  },
  // One of the availability numbers or prices has been changed
  change: function(id)
  {
// OK for numbers, prices
//alert("change: id "+id);
    if (!/^[-a-z]+_(\d+_\d+_\d+_\d+)$/i.match(id)) {
alert("change: ID mismatch: "+id);
      return;
    }
    var _id = "_"+RegExp.$1;
//alert("change: _id "+_id);
    var element_hidden = document.getElementById("'.$hidden_id_base.'"+_id);
    if (!element_hidden) {
//alert("change: ERROR: hidden element '.$hidden_id_base.'"+_id+" not found");
      return;
    }
    var number = element_hidden.value;
    // If the value is set to greater than zero, remember it
    if (number > 0) {
      Toggler.values[_id]["key_on"] = element_hidden.value;
    }
//alert("change: number "+number);
    var price = document.getElementById("price"+_id).value;
//alert("change: number "+number+", price "+price);
    Toggler.loading(id);
    new Ajax.Request(
      location.href,
      {
        method: "get",
        parameters: "change="+id+"&number="+number+"&price="+price,
        onComplete: Toggler.update
        //,onFailure: alert("FAIL")
      }
    );
//alert("change sent: ID: "+id);
  },
  // Set the corresponding toggle element to loading state
  loading: function(id)
  {
// OK for single types
// *SHOULD* set all types to loading for "all rooms" type
    if (!/^[-a-z]+_(\d+)_(\d+_\d+_\d+)$/i.match(id)) {
//alert("loading: ID mismatch: "+id);
      return;
    }
    var type = RegExp.$1;
    var date = RegExp.$2;
    var _id = "'.$toggle_id_base.'_"+type+"_"+date;
    $(_id).setAttribute("class", "room_loading");
//alert("loading: changing class for _id: "+_id);
  },
  // Flip the state of one of the toggle elements after it has been clicked
  update: function(request)
  {
// OK for change: alert("Response: "+request.responseText);
    var arr_roomtypes = eval(request.responseText);
    // The date is the same for all room types, so we use the first only
    var date = arr_roomtypes[0].date;
//alert("update: date: "+date);
    var number_total_all = 0;
    for (var i = 0; i < arr_roomtypes.length; ++i) {
      var roomtype = arr_roomtypes[i];
      var room_type_id = roomtype.room_type_id;
      // Have to cast to integer, or JS will concatenate strings for the total
      var number_total = parseInt(roomtype.number_total);
      var price        = roomtype.price;
      number_total_all += number_total;
      var _id = "_"+room_type_id+"_"+date;
//alert("Index "+i+" => "+arr_roomtypes[i].room_type_id+", _id: "+_id);
      Toggler.settoggle(_id, number_total);
      Toggler.sethidden(_id, number_total, price);
    }
//alert("All rooms count: "+number_total_all);
    // Update the "all rooms" toggle
    var _id = "_0_"+date;
    Toggler.settoggle(_id, number_total_all);
//alert("Updated all");
  },
  // Update any single toggle element with a new number.
  // Sets the class and title accordingly.
  settoggle: function(_id, number_total)
  {
//alert("settoggle: setting id "+_id+", number "+number_total);
    var element_toggle = document.getElementById("'.$toggle_id_base.'"+_id);
    if (!element_toggle) {
//alert("settoggle: ERROR: toggle element '.$toggle_id_base.'"+_id+" not found");
      return;
    }
    var classname;
    var title;
    if (number_total > 0) {
      // Turn on
      classname = (Toggler.values[_id].class_on
        ? Toggler.values[_id].class_on  : Toggler.class_on);
      title = (Toggler.values[_id].title_on
        ? Toggler.values[_id].title_on  : Toggler.title_on);
    } else {
      // Turn off
      classname = (Toggler.values[_id].class_off
        ? Toggler.values[_id].class_off : Toggler.class_off);
      title = (Toggler.values[_id].title_off
        ? Toggler.values[_id].title_off : Toggler.title_off);
    }
    element_toggle.setAttribute("class", classname);
    // There must be a sprintf() like format placeholder in the title
    title = title.replace(/\%\d?\$?\w/, number_total);
    element_toggle.title = title;
  },
  // Update any single hidden element with a new number.
  sethidden: function(_id, number_total, price)
  {
//alert("set_hidden: setting id "+_id+", number "+number_total+", price "+price);
    var element_hidden = document.getElementById("'.$hidden_id_base.'"+_id);
    if (!element_hidden) {
//alert("update: TEST: ERROR: hidden element '.$hidden_id_base.'"+_id+" not found");
      return;
    }
    if (number_total == 0 && element_hidden.value > 0) {
      // Store the current "on" value
      Toggler.values[_id].key_on = element_hidden.value;
    }
    element_hidden.value = number_total;
    var element_price = document.getElementById("price"+_id);
    if (!element_price) {
//alert("update: TEST: ERROR: price element price"+_id+" not found");
      return;
    }
    element_price.value = price;
  }
};
';
    }


    /**
     * Returns the page URI with all special characters substituted
     * by their corresponding HTML entities
     *
     * The URI contains neither the host nor the directory,
     * but only the script file name and query string.
     * //including the current CSRF parameter.
     * This is ready for use in any href or action attribute.
     * Apply html_entity_decode() before using it with javascript.
     * @see     getRelativeUri()
     * @return  string                  The URI with entities
     */
    static function getRelativeUri_entities()
    {
        return htmlentities(self::getRelativeUri());
    }


    /**
     * Returns the page URI
     *
     * The URI contains neither the host nor the directory,
     * but only the script file name and query string.
     * //including the current CSRF parameter.
     * This is ready for use with javascript.
     * Apply htmlentities() before using it in any href or action attribute.
     * @see     getRelativeUri_entities()
     * @return  string                  The URI
     */
    static function getRelativeUri()
    {
        return
            CONTREXX_DIRECTORY_INDEX.'?'.
            (empty($_SERVER['QUERY_STRING'])
              ? '' : $_SERVER['QUERY_STRING']
//              .'&'.CSRF::param()
            );
    }


    /**
     * Remove the parameter and its value from the URI string,
     * by reference
     *
     * If the parameter cannot be found, the URI is left unchanged.
     * Note that this expects and produces URIs in the form as returned by
     * {@see getRelativeUri_entities()}.  It operates on a URI without
     * entities, too, but the result will probably not be correct.
     * @param   string    $uri              The URI, by reference
     * @param   string    $parameter_name   The name of the parameter
     * @return  string                      The former parameter value,
     *                                      or the empty string
     */
    static function stripUriParam(&$uri, $parameter_name)
    {
        $match = array();
//DBG::log("Html::stripUriParam(".htmlentities($uri).", ".htmlentities($parameter_name)."): Entered");

        // Match the parameter *WITH* equal sign and value (possibly empty)
        $uri = preg_match_replace(
            '/(\?|\&(?:amp\;)?)'.preg_quote($parameter_name, '/').'\=([^&]*)(?:\&(?:amp\;)?|$)/',
            '$1', $uri, $match
        );
        // Match the parameter *WITHOUT* equal sign and value
        $uri = preg_match_replace(
            '/(\?|\&(?:amp\;)?)'.preg_quote($parameter_name, '/').'(?:\&(?:amp\;)?|$)/',
            '$1', $uri
        );
//echo("Html::stripUriParam(".htmlentities($uri).", ".htmlentities($parameter_name)."): regex ".htmlentities($re)."<br />");
        // Remove trailing '?', '&', or '&amp;'.  One of those will be
        // left over when the last parameter was removed from the URI!
        $uri = preg_replace('/(?:\?|\&(?:amp\;)?)$/', '', $uri);
//echo("Html::stripUriParam(".htmlentities($uri).", ".htmlentities($parameter_name)."): stripped $count times ".var_export($match, true)."<br />");
        if (empty($match[2])) return '';
        return $match[2];
    }


    /**
     * Replaces the URI parameters given in the URI, by reference
     *
     * Parameters whose names are present in the URI already are replaced
     * with the new values from the $parameter string.
     * Parameters from $parameter that are not already present in the URI
     * are appended.
     * The replaced/added parameters are separated by '&amp;'.
     * Note that this expects and produces URIs in the form as returned by
     * {@see getRelativeUri_entities()}.  It operates on a URI without
     * entities, too, but the result will probably not be correct.
     * @param   string    $uri        The full URI, by reference
     * @param   string    $parameter  The parameters to be replaced or added
     * @return  void
     */
    static function replaceUriParameter(&$uri, $parameter)
    {
//DBG::log("Html::replaceUriParameter($uri, $parameter): Entered");
        $match = array();
        // Remove script name and leading question mark, if any
        if (preg_match('/^.*\?(.+)$/', $parameter, $match)) {
//        if (preg_match('/^(.*)\?(.+)$/', $parameter, $match)) {
//            $bogus_index = $match[1];
            $parameter = $match[1];
//DBG::log("Html::replaceUriParameter(): Split parameter in bogus index /$bogus_index/ and parameter /".htmlentities($parameter)."/");
        }
        $arrParts = preg_split('/\&(?:amp;)?/', $parameter, -1, PREG_SPLIT_NO_EMPTY);

//DBG::log("Html::replaceUriParameter(): parts: ".var_export($arrParts, true));
        foreach ($arrParts as $parameter) {
//DBG::log("Html::replaceUriParameter(): processing parameter ".htmlentities($parameter));

            if (!preg_match('/^([^=]+)\=?(.*)$/', $parameter, $match)) {
//DBG::log("Html::replaceUriParameter(): skipped illegal parameter ".htmlentities($parameter));
                continue;
            }
            self::stripUriParam($uri, $match[1]);
//            $old = self::stripUriParam($uri, $match[1]);
//DBG::log("Html::replaceUriParameter(): stripped to $uri, removed $old");
            $uri .=
                (preg_match('/\?/', $uri) ? '&amp;' : '?').
                $parameter;
//DBG::log("Html::replaceUriParameter(): added to $uri");
        }
//        $uri = ($index ? $index.'?' : '&amp;').$uri;
//DBG::log("Html::replaceUriParameter($uri, $parameter): Exiting");
//die();
    }


    /**
     * Shortens text and appends an optional link to "more...".
     * Mind: the returned string is htmlentities()d for the current charset!
     * @param   string    $text         The text
     * @param   integer   $max_length   The maximum length
     * @param   string    $more_uri     The optional URI
     * @param   string    $more_uri     The optional target window
     * @return  string                  The shortened text, with the
     *                                  optional link appended
     */
    static function shortenText(
        $text, $max_length=30, $more_uri=null, $more_target=null
    ) {
        global $_CORELANG;

        $text = preg_replace('/(?:\r|\n|\s\s+)/s', ' ', $text);
        if (strlen($text) > $max_length) {
            $text = preg_replace(
                '/^(.{0,'.$max_length.'})(?:\s|$).+$/is',
                '$1',
                $text
            );
// TODO: I should probably wrap this in htmlentities()
            $text = sprintf(
                $_CORELANG['TXT_CORE_HTML_ETC'],
                htmlentities($text, ENT_QUOTES, CONTREXX_CHARSET));
        }
        return
            $text.
            ($more_uri
                ? '&nbsp;<a href="'.$more_uri.'"'.
                  ($more_target ? ' target="'.$more_target.'"' : '').
                  '>'.$_CORELANG['TXT_CORE_HTML_MORE'].'</a>'
                : ''
            );
    }


    /**
     * A few JS scripts used by the Html and Image classes
     *
     * The functions included are required to show the file browser and
     * file upload components defined in the {@see self::getImageChooserBrowser()}
     * and {@see self::getImageChooserUpload()} methods, respectively.
     * @param   string    $path         The optional path to a default image.
     *                                  Defaults to the Image::PATH_NO_IMAGE
     *                                  class constant if empty.
     * @return  string                  The Javascript code
     */
    static function getJavascript_Image($path='')
    {
        global $_CORELANG; //$_ARRAYLANG,

        return '
function openWindow(theURL, winName, features)
{
  window.open(theURL, winName, features);
}

field_id = false;
function openBrowser(url, id, attrs)
{
  field_id = id;
  try {
    if (!browserPopup.closed) {
      return browserPopup.focus();
    }
  } catch(e) {}
  if (!window.focus) return true;
  browserPopup = window.open(url, "", attrs);
  browserPopup.focus();
  return false;
}

function SetUrl(url, width, height, alt)
{
  var fact = 80 / height;
  if (width > height) fact = 80 / width;
  var element_img = document.getElementById(field_id+"_img").
  element_img.setAttribute("src", url);
  element_img.style.width = parseInt(width*fact)+"px";
  element_img.style.height = parseInt(height*fact)+"px";
  document.getElementById(field_id+"_src").value = url;
  document.getElementById(field_id+"_width").value = width;
  document.getElementById(field_id+"_height").value = height;
}

// Clear the image data and replace it with the no-image.
// Also (re)display the element with ID id, usually the file upload input
function clearImage(id)
{
//alert("clearImage("+id+")");
  if (!confirm("'.$_CORELANG['TXT_CORE_HTML_CONFIRM_DELETE_IMAGE'].'")) return;
  if (document.getElementById(id+"_img"))
    document.getElementById(id+"_img").src = "'.
    (empty($path) ? Image::PATH_NO_IMAGE : $path).'";
  // Clear the previous image path
  if (document.getElementById(id+"_src"))
    document.getElementById(id+"_src").value = "";
// Width and height are not required (yet)
//  if (document.getElementById(id+"_width"))
//    document.getElementById(id+"_width").value = "";
//  if (document.getElementById(id+"_height"))
//    document.getElementById(id+"_height").value = "";
  // Display the file upload input again, if available
  if (document.getElementById(id))
    document.getElementById(id).style.display = "inline";
  // Hide the "clear image" link
  if (document.getElementById(id+"_clear"))
    document.getElementById(id+"_clear").style.display = "none";
}
';
    }


    /**
     * A few JS scripts used by the Html class
     *
     * Includes the following methods:
     *  lengthLimit(textarea, count_min, count_max, limit_min, limit_max)
     *    Limit the length of the element "textarea" to the range of
     *    "limit_min" to "limit_max" characters (inclusive).
     *    Updates the minimum and maximum required characters in the elements
     *    "count_min" and "count_max" values.
     * @return  string                      The Javascript code
     */
    static function getJavascript_Text()
    {
        global $_CORELANG; //$_ARRAYLANG,

        return '
/**
 * Limit the textarea content length
 *
 * The count_min and count_max elements show the required and possible
 * number of characters left.
 * limit_min and limit_max specify the required and possible number of
 * characters.
 */
function lengthLimit(textarea, count_min, count_max, limit_min, limit_max)
{
  if (textarea.value.length > limit_max) {
    textarea.value = textarea.value.substring(0, limit_max);
  } else {
    count_max.value = limit_max - textarea.value.length;
  }
  if (textarea.value.length > limit_min) {
    count_min.value = 0;
  } else {
    count_min.value = limit_min - textarea.value.length;
  }
}
';
    }


    /**
     * A few JS scripts used by the Html class
     *
     * Includes the following methods for manipulating HTML elements:
     *  toggleDisplay(button_element, target_id)
     *    Toggles the visibility (style.display) of the element with id
     *    attribute target_id and updates the .innerHTML property of the
     *    button_element with the appropriate text ("open" or "close").
     *  showTab(tab_id_base, div_id_base, active_suffix, min_suffix, max_suffix)
     *    Activate a tab and show the corresponding content.
     *    Displays the element with id "div_id_base"+"active_suffix", and hides
     *    all other such elements within the "min_suffix" to "max_suffix" range
     *    (inclusive).  Sets the class of the element with id "tab_id_base"+
     *    "active_suffix" to "active", any other suffix to "".
     *  removeElement(id) -- UNTESTED!
     *    Remove the element with id "id".
     *  cloneElement(id) -- UNTESTED!
     *    Adds a clone of the element with id "id" as its next sibling.
     * @return  string                      The Javascript code
     */
    static function getJavascript_Element()
    {
        global $_CORELANG; //$_ARRAYLANG,

        return '
function toggleDisplay(button_element, target_id)
{
  var target_element = document.getElementById(target_id);
  if (!target_element) alert("cannot find target "+target_id);

  if (target_element.style.display == "block") {
    target_element.style.display = "none";
    button_element.innerHTML = "'.$_CORELANG['TXT_CORE_HTML_TOGGLE_OPEN'].'";
//alert("closed");
  } else {
    target_element.style.display = "block";
    button_element.innerHTML = "'.$_CORELANG['TXT_CORE_HTML_TOGGLE_CLOSE'].'";
//alert("opened");
  }
}

function showTab(tab_id_base, div_id_base, active_suffix, min_suffix, max_suffix)
{
  for (var i = min_suffix; i <= max_suffix; ++i) {
    var tab_id = tab_id_base + i;
    var tab_element = document.getElementById(tab_id);
if (!tab_element) return; //alert("cannot find tab "+tab_id);
    var div_id = div_id_base + i;
    var div_element = document.getElementById(div_id);
if (!div_element) return; //alert("cannot find div "+div_id);

    if (active_suffix == i) {
      div_element.style.display = "block";
      tab_element.className = tab_element.className.replace(/(?:_active)?$/, "_active");
//alert("opened");
    } else {
      div_element.style.display = "none";
      tab_element.className = tab_element.className.replace(/(?:_active)?$/, "");
//alert("closed");
    }
  }
}

// UNTESTED -- PROBABLY DOES NOT WORK AT ALL
// Removes the element with the given ID
function removeElement(id)
{
  var element = document.getElementById(id);
  if (!element) return;
  element.parentNode.removeChild(element);
}

// UNTESTED -- PROBABLY DOES NOT WORK AT ALL
// Appends a clone of the element with the given ID after itself
function cloneElement(id)
{
  var element = document.getElementById(id);
  if (!element) {
//alert("Error: no such element: "+id);
    return;
  }
  var clone = Element.clone(element);
  clone.setAttribute("id", id+"-");
//alert("Clone:\n"+clone.toString());
  element.appendChild(clone);
}
';
    }

}

?>
