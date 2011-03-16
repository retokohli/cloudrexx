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
     * Index counter for all editable elements
     *
     * Incremented for and added to each element in the order they are created
     * @var   integer
     */
    private static $tabindex = 0;


    /**
     * Returns HTML code for a text imput field
     *
     * If the $id parameter is false, the id attribute is not set.
     * If it's empty (but not false), the name is used instead.
     * If the custom attributes parameter $attribute is empty, and
     * is_numeric($value) evaluates to true, the text is right aligned
     * within the input element.
     * @param   string    $name         The element name
     * @param   string    $value        The element value
     * @param   string    $id           The optional element id
     * @param   string    $attribute    Additional optional attributes
     * @return  string                  The HTML code for the element
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getInputText($name, $value, $id=false, $attribute='')
    {
        return
            '<input type="text" name="'.$name.'"'.
            ($id ? ' id="'.$id.'"' : ($id === false ? '' : $name)).
            ' value="'.$value.'" tabindex="'.++self::$tabindex.'"'.
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
            ' value="'.$value.'" tabindex="'.++self::$tabindex.'"'.
            ($attribute ? ' '.$attribute : '').
            " />\n";
    }


    /**
     * Returns HTML code for a file upload input field
     *
     * If the $id parameter is false, the id attribute is not set.
     * If it's empty (but not false), the name is used instead.
     * @param   string    $name         The element name
     * @param   string    $id           The optional element id
     * @param   string    $maxlength    The optional maximum accepted size
     * @param   string    $mimetype     The optional accepted MIME type
     * @param   string    $attribute    Additional optional attributes
     * @param   boolean   $visible      If true, the input element is set
     *                                  invisible.  Defaults to false
     * @return  string                  The HTML code for the element
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getInputFileupload(
        $name, $id=false, $maxlength='', $mimetype='', $attribute='', $visible=true
    ) {
        return
            '<input type="file" name="'.$name.'"'.
            ($id === false ? '' : ' id="'.($id ? $id : $name).'"').
            ' id="'.($id ? $id : $name).'"'.
            ' tabindex="'.++self::$tabindex.'"'.
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
            ' tabindex="'.++self::$tabindex.'"'.
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
            ' value="'.$value.'"'.
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
        $id = ($id === false ? '' : ' id="'.($id ? $id : $name).'"');
        return
            '<input type="'.$type.'" name="'.$name.'"'.$id.
            ' tabindex="'.++self::$tabindex.'"'.
            ' value="'.$value.'"'.
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
//die("getSelect($name, $arrOptions, $selected, $onchange, $attribute): Name empty");
            return '';
        }
        $menu =
            '<select name="'.$name.'"'.
            ($id === false ? '' : ' id="'.($id ? $id : $name).'"').
            ' tabindex="'.++self::$tabindex.'"'.
            ($onchange ? ' onchange="'.$onchange.'"' : '').
            ($attribute ? ' '.$attribute : '').
            ">\n".self::getOptions($arrOptions, $selected)."</select>\n";
//echo("getSelect(): made menu: ".htmlentities($menu)."<br />");
        return $menu;
    }


    /**
     * Returns HTML code for selection options
     * @param   array   $arrOptions The options array
     * @param   integer $selected   The optional preselected option key
     * @return  string              The menu options HTML code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getOptions($arrOptions, $selected='')
    {
        $options = '';
        foreach ($arrOptions as $key => $value) {
            $options .=
                '<option value="'.$key.'"'.
                ("$selected" == "$key" ? ' selected="selected"' : '').
                '>'.$value."</option>\n";
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
     * @param   string    $checked     The optional preselected option key
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
        $radiogroup = '';
        foreach ($arrOptions as $value => $text) {
            $index[$name] = (empty($index[$name]) ? 1 : ++$index[$name]);
            $id = $name.'-'.$index[$name];
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
            '<input type="radio" name="'.$name.'" value="'.$value.'"'.
            ($id === false ? '' : ' id="'.($id ? $id : $name).'"').
            ($checked ? ' checked="checked"' : '').
            ' tabindex="'.++self::$tabindex.'"'.
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
     * to be added.  The keys are used to index the name in the checkboxes,
     * the value is put into the value attribute.
     * The $arrLabel should use the same keys, its values are appended
     * as label text to the respective checkboxes, if present.
     * The $arrChecked array may contain the values to be preselected
     * as array values.  It's keys are ignored.
     * @param   string    $name         The element name
     * @param   array     $arrOptions   The options array
     * @param   array     $arrLabel     The optional label text array
     * @param   string    $arrChecked   The optional preselected option keys
     * @param   string    $id           The optional element id
     * @param   string    $onchange     The optional onchange event script
     * @param   string    $attributeRadio    Additional optional attributes
     *                                  for the checkbox elements
     * @param   string    $attributeLabel    Additional optional attributes
     *                                  for the labels
     * @return  string                  The HTML code for the elements
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getCheckboxGroup(
        $name, $arrOptions, $arrLabel='', $arrChecked='', $id='',
        $onchange='', $attributeCheckbox='', $attributeLabel=''
    ) {
        static $index = array();

//echo("getCheckboxGroup($name, ".var_export($arrOptions, true).", ".var_export($arrLabel, true).", ".var_export($arrChecked, true).", $onchange, $attributeCheckbox, $attributeLabel): Entered<br />");

        if (empty($name)) return '';
        // Remove any bracketed construct from the end of the name
        $name_stripped = preg_replace('/\[.*$/', '', $name);
        if (!is_array($arrLabel)) $arrLabel = array();
        if (!is_array($arrChecked)) $arrChecked = array();
        if (empty($id)) $id = $name;
        $checkboxgroup = '';
        foreach ($arrOptions as $key => $value) {
            if (empty($index[$name_stripped])) $index[$name_stripped] = 0;
            $id_local = $id.'-'.++$index[$name_stripped];
            $checkboxgroup .=
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
     * @param   string    $checked      If true, the checkbox is checked
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
            ' tabindex="'.++self::$tabindex.'"'.
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
     * Returns an image tag for the given Image object
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
    static function getImage($objImage, $attribute='')
    {
        $width = $objImage->getWidth();
        $height = $objImage->getHeight();
        return
            '<img src="'.$objImage->getPath().'"'.
            (   empty($width)
             || preg_match('/width[:=]/i', $attribute)
              ? '' : ' width="'.$width.'"').
            (   empty($height)
             || preg_match('/height[:=]/i', $attribute)
              ? '' : ' height="'.$height.'"').
            (preg_match('/alt\=/i', $attribute)
              ? '' : ' alt="'.$objImage->getPath().'"').
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
        return self::getImage($objImage, $attribute);
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
            '<img id="'.$id.'_img" src="'.$objImage->getPath().'"'.
            ' style="width:'.$objImage->getWidth().
            'px; height:'.$objImage->getHeight().'px;"'.
            ' title="'.$_CORELANG['TXT_CORE_CHOOSE_IMAGE'].'"'.
            ' alt="'.$_CORELANG['TXT_CORE_CHOOSE_IMAGE'].'" />'."\n".
            self::getHidden($id.'_type',
              ($imagetype_key !== false
                ? $imagetype_key : $objImage->getImageTypeKey())).
            ($objImage->getPath()
                ? self::getClearImageCode($id).
                  self::getHidden($id.'_id', $objImage->getId()).
                  self::getHidden($id.'_ord', $objImage->getOrd())
                : '').
            self::getHidden($id.'_src', $objImage->getPath()).
            '<a href="javascript:void(0);" title="{TXT_CORE_CHOOSE_IMAGE}"'.
            ' tabindex="'.++self::$tabindex.'"'.
            ' onclick="openBrowser(\'index.php?cmd=fileBrowser&amp;standalone=true&amp;type=shop\',\'1\',\'width=800,height=640,resizable=yes,status=no,scrollbars=yes\');">'.
            $_CORELANG['TXT_CORE_CHOOSE_IMAGE']."</a>\n";
    }


    /**
     * Returns HTML code for an image element with form
     * elements for uploading an image file.
     *
     // If the optional $imagetype_key is missing (defaults to false),
     // no image type can be selected.  If it's a string, the type of the
     // Image is set to this key.  If it's an array of keys, the Image type
     // can be selected from these.
     * Uses the $id parameter as prefix for both the name and id attributes
     * of all HTML elements.  The names and respective suffixes are:
     *  - id+'_img' for the name and id of the <img> tag
     *  - id+'_src' for the name and id of the hidden <input> tag for the image path
     *  - id+'_width' for the name and id of the hidden <input> tag for the width
     *  - id+'_height' for the name and id of the hidden <input> tag for the height
     *  - id+'_file' for the name and id of the file upload element
     * The file upload element will provide the new image chosen by the user
     * when the form is posted, while the hidden fields represent the previous
     * state when the page was generated.
     * The $path_default will replace the path of the image shown if that is
     * empty, but the path posted back will remain empty so the default image
     * is not accidentally stored.
     * See {@see Image::updatePostImages()} and {@see Image::uploadAndStore()}
     * for more information and examples.
     * @param   Image   $objImage       The image object
     * @param   string  $id             The base name for the elements IDs
     * @param   mixed   $imagetype_key  The optional Image type key
     * @param   string  $path_default   The optional path of a default image
     * @return  string                  The HTML code for all the elements
     */
    static function getImageChooserUpload(
        $objImage, $id, $imagetype_key=false, $path_default=''
    ) {
        global $_CORELANG;

        JS::registerCode(self::getJavascript_Image($path_default));
        if (empty($objImage)) $objImage = new Image(0);
        $path = $objImage->getPath();
//        if (empty($path)) {
////echo("Html::getImageChooserUpload(): Fixed empty path to default $path_default<br />");
//            $path = $path_default;
//        }
        return
            '<img id="'.$id.'_img" src="'.($path ? $path : $path_default).'"'.
            ' style="width:'.$objImage->getWidth().
            'px; height:'.$objImage->getHeight().'px;"'.
            ' title="'.$_CORELANG['TXT_CORE_CHOOSE_IMAGE'].'"'.
            ' alt="'.$_CORELANG['TXT_CORE_CHOOSE_IMAGE'].'" />'."\n".
            self::getHidden($id.'_type',
              ($imagetype_key !== false
                ? $imagetype_key : $objImage->getImageTypeKey())).
            ($path
                ? self::getClearImageCode($id).
                  self::getHidden($id.'_id', $objImage->getId()).
                  self::getHidden($id.'_ord', $objImage->getOrd()).
                  self::getHidden($id.'_src', $objImage->getPath())
                : '').
            // Set the upload element to visible only if no image path is set
            self::getInputFileupload($id, '', '', '', '', empty($path));
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
            self::getImage($objImageRemove, 'border="0"').'</a>'.
            '<a href="javascript:void(0);" '.
            'onclick="cloneElement(\''.$id.'\');">'.
            self::getImage($objImageAdd, 'border="0"').'</a>';
    }


    static function getClearImageCode($id)
    {
        global $_CORELANG;

        return
            '<a href="javascript:void(0);" id="'.$id.'_clear"'.
            ' title="'.$_CORELANG['TXT_CORE_CLEAR_IMAGE'].'"'.
            ' onclick="clearImage(\''.$id.'\');">'."\n".
            '  <img src="'.Image::ICON_CLEAR_IMAGE_SRC.
            '" border="0" alt="'.$_CORELANG['TXT_CORE_CLEAR_IMAGE'].'"/>'."\n".
            '</a><br />'."\n";
    }


    static function getSelectDate($name, $value='', $onchange='', $attribute='')
    {
        static $index = 0;

        JS::activate('datepicker');
        return
            '<input type="text" name="'.$name.
//            '" id="DPC_edit'.++$index.'_YYYY-MM-DD" '.
            '" id="DPC_edit'.++$index.'_DD.MM.YYYY" '.
            'value="'.$value.'"'.
            ' tabindex="'.++self::$tabindex.'"'.
            ($onchange ? ' onchange='.$onchange : '').
            ($attribute ? ' '.$attribute : '').
            ' />';
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
     * The action parameter will look like "act=what_to_do" in most cases
     * and replaces any other parameter or parameters already present
     * in the current page URI.  You may also specify javascript code,
     * this *MUST* start with "javascript:" and will replace the page URI.
     * Empty actions will usually lead back to the module start page.
     * @param   array   $arrFunction    The array of functions and actions
     * @return  string                  The HTML code for the function column
     */
    static function getBackendFunctions($arrFunction)
    {
        $uri = htmlentities(CONTREXX_DIRECTORY_INDEX.'?'.$_SERVER['QUERY_STRING']);
        $function_html = '';
        foreach ($arrFunction as $function => $action) {
            $objImage = new Image();
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
              default:
                continue 2;
            }
            $objImage->setWidth(16);
            $objImage->setHeight(16);
            $_uri = $uri;
            if (preg_match('/^javascript\:/i', $action)) {
                $_uri = $action;
            } else {
                self::replaceUriParameter($_uri, $action);
            }
            $function_html .=
                '<a href="'.$_uri.'">'.
                self::getImage($objImage, 'border="0"').'</a>';
        }
        return '<div style="text-align: right;">'.$function_html.'</div>';
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
        $objImage->setWidth(10);
        $objImage->setHeight(10);
        $led_html = self::getImage(
            $objImage,
            'border="0"'.($alt ? ' alt="'.$alt.'" title="'.$alt.'"' : ''));
        if ($action) {
            $uri =
                htmlentities(CONTREXX_DIRECTORY_INDEX.
                    (empty($_SERVER['QUERY_STRING'])
                      ? '' : '?'.$_SERVER['QUERY_STRING']));
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
        $checkmark_html = self::getImage($objImage, 'border="0"');
        return $checkmark_html;
    }


    /**
     * Remove the parameter and its value from the URI string,
     * by reference
     *
     * If the parameter cannot be found, the URI is left unchanged.
     * @param   string    $uri              The URI, by reference
     * @param   string    $parameter_name   The name of the parameter
     * @return  string                      The former parameter value,
     *                                      or the empty string
     */
    static function stripUriParam(&$uri, $parameter_name)
    {
        $match = array();
//echo("Html::stripUriParam(".htmlentities($uri).", ".htmlentities($parameter_name)."): Entered<br />");

        $re =
            '/'.preg_quote($parameter_name, '/').
            '\=?([^&]*)(?:\&(?:amp\;)?)?/';
//echo("Html::stripUriParam(".htmlentities($uri).", ".htmlentities($parameter_name)."): regex ".htmlentities($re)."<br />");
        $uri = preg_match_replace(
            $re,
            '',
            $uri,
            $match
        );
        // Remove trailing '?', '&', or '&amp;'
        $uri = preg_replace('/(?:\?|\&(?:amp\;)?)$/', '', $uri);
//echo("Html::stripUriParam(".htmlentities($uri).", ".htmlentities($parameter_name)."): stripped $count times ".var_export($match, true)."<br />");
        if (empty($match[1])) return '';
        return $match[1];
    }


    /**
     * Replaces the URI parameters given in the URI, by reference
     *
     * The $parameter string is an URI query string starting with '?',
     * '&' or '&amp;', a parameter name, or none of those.
     * Parameters whose names are present in the URI already are replaced
     * with the new values from the $parameter string.
     * Parameters are not already present in the URI are appended.
     * The replaced/added parameters are separated by '&amp;'.
     * @param   string    $uri        The full URI, by reference
     * @param   string    $parameter  The parameters to be replaced or added
     * @return  void
     */
    static function replaceUriParameter(&$uri, $parameter)
    {
//echo("Html::replaceUriParameter(".htmlentities($uri).", ".htmlentities($parameter)."): Entered<br />");
        $match = array();
        // Remove
        if (preg_match('/^.*\?(.+)$/', $parameter, $match)) {
//        if (preg_match('/^(.*)\?(.+)$/', $parameter, $match)) {
//            $bogus_index = $match[1];
            $parameter = $match[2];
//echo("Html::replaceUriParameter(): Split parameter in bogus index $bogus_index and parameter ".htmlentities($parameter)."<br />");
        }
        $arrParts = preg_split('/\&(?:amp;)?/', $parameter, -1, PREG_SPLIT_NO_EMPTY);

//echo("Html::replaceUriParameter(): parts: ".htmlentities(var_export($arrParts, true))."<br />");
        foreach ($arrParts as $parameter) {
//echo("Html::replaceUriParameter(): processing parameter ".htmlentities($parameter)."<br />");

            if (!preg_match('/^([^=]+)\=?(.*)$/', $parameter, $match)) {
//echo("Html::replaceUriParameter(): skipped illegal parameter ".htmlentities($parameter)."<br />");
                continue;
            }
            self::stripUriParam($uri, $match[1]);
//            $old = self::stripUriParam($uri, $match[1]);
//echo("Html::replaceUriParameter(): stripped to ".htmlentities($uri).", removed ".htmlentities($old)."<br />");
            $uri .=
                (preg_match('/\?/', $uri) ? '&amp;' : '?').
                $parameter;
//echo("Html::replaceUriParameter(): added to ".htmlentities($uri)."<br />");
        }
//        $uri = ($index ? $index.'?' : '&amp;').$uri;
//echo("Html::replaceUriParameter(".htmlentities($uri).", ".htmlentities($parameter)."): Exiting<hr />");
    }


    /**
     * A few JS scripts used by the Html and Image classes
     * @todo    The Image class part should be moved
     * @param   string    $path_default     The optional path to the
     *                                      default image
     * @return  string                      The Javascript code
     */
    static function getJavascript_Image($path_default='')
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
  document.getElementById(id+"_img").src = "'.
  (empty($path_default) ? Image::PATH_NO_IMAGE : $path_default).'";
  document.getElementById(id+"_clear").style.display = "none";
  if (document.getElementById(id+"_src"))
    document.getElementById(id+"_src").value = "";
  if (document.getElementById(id+"_width"))
    document.getElementById(id+"_width").value = "";
  if (document.getElementById(id+"_height"))
    document.getElementById(id+"_height").value = "";
  if (document.getElementById(id))
    document.getElementById(id).style.display = "inline";
}
';
    }


    /**
     * A few JS scripts used by the Html class
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
      tab_element.setAttribute(\'class\', tab_element.getAttribute(\'class\').replace(/(?:_active)?$/, "_active"));
//alert("opened");
    } else {
      div_element.style.display = "none";
      tab_element.setAttribute(\'class\', tab_element.getAttribute(\'class\').replace(/(?:_active)?$/, ""));
//alert("closed");
    }
  }
}

// Removes the element with the given ID
function removeElement(id)
{
  var element = document.getElementById(id);
  if (!element) return;
  element.parentNode.removeChild(element);
}

// Appends a clone of the element with the given ID after itself
function cloneElement(id)
{
  var element = document.getElementById(id);
  if (!element) {
alert("Error: no such element: "+id);
    return;
  }
  var clone = Element.clone(element);
  clone.setAttribute("id", id+"-");
alert("Clone:\n"+clone.toString());
  element.appendChild(clone);
}
';
    }

}

?>
