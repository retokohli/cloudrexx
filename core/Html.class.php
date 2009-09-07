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
     * Index counter for all editable elements
     *
     * Incremented for and added to each element in the order they are created
     * @var   integer
     */
    private static $tabindex = 0;

    /**
     * Returns HTML code for a text imput field
     *
     * The $name parameter is used for both the element name and id attributes.
     * If the custom attributes parameter $attribute is empty, and
     * is_numeric($value) evaluates to true, the text is right aligned
     * within the input element.
     * @param   string    $name         The element name
     * @param   string    $value        The element value
     * @param   string    $attribute    Additional optional attributes
     * @return  string                  The HTML code for the element
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getInputText($name, $value, $attribute='')
    {
        return
            '<input type="text" name="'.$name.'" id="'.$name.'"'.
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
     * The $name parameter is used for both the element name and id attributes.
     * @param   string    $name         The element name
     * @param   string    $maxlength    The optional maximum accepted size
     * @param   string    $mimetype     The optional accepted MIME type
     * @param   string    $attribute    Additional optional attributes
     * @return  string                  The HTML code for the element
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getInputFileupload(
        $name, $maxlength='', $mimetype='', $attribute=''
    ) {
        return
            '<input type="file" name="'.$name.'" id="'.$name.'"'.
            ' tabindex="'.++self::$tabindex.'"'.
            ($maxlength ? ' maxlength="'.$maxlength.'"' : '').
            ($mimetype ? ' accept="'.$mimetype.'"' : '').
            ($attribute ? ' '.$attribute : '').
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
            '>'.$value."</textarea>\n";
    }


    /**
     * Returns HTML code for a hidden imput field
     *
     * The $name parameter is used for both the element name and id attributes.
     * @todo    Maybe the optional attributes will never be used
     *          and can be removed?
     * @param   string    $name         The element name
     * @param   string    $value        The element value
     * @param   string    $attribute    Additional optional attributes
     * @return  string                  The HTML code for the element
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getHidden($name, $value, $attribute='')
    {
        return
            '<input type="hidden" name="'.$name.'" id="'.$name.'"'.
            ' value="'.$value.'"'.
            ($attribute ? ' '.$attribute : '')." />\n";
    }


    /**
     * Returns HTML code for a dropdown menu
     *
     * If the name is empty, the empty string is returned.
     * The $name parameter is used for both the element name and id attributes.
     * Mind that thus, it *MUST* be unique on your page.
     * @param   string    $name         The element name
     * @param   array     $arrOptions   The options array
     * @param   string    $selected     The optional preselected option key
     * @param   string    $onchange     The optional onchange event script
     * @param   string    $attribute    Additional optional attributes
     * @return  string                  The HTML code for the element
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getSelect(
        $name, $arrOptions=array(), $selected='', $onchange='', $attribute=''
    ) {
//echo("getSelect($name, ".var_export($arrOptions, true).", $selected, $onchange, $attribute): Entered<br />");
        if (empty($name)) {
//die("getSelect($name, $arrOptions, $selected, $onchange, $attribute): Name empty");
            return '';
        }
        $menu =
            '<select id="'.$name.'" name="'.$name.'"'.
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
                ($selected == $key ? ' selected="selected"' : '').
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
        return '<span class="inputgroup">'.$radiogroup."</span>\n";
    }


    /**
     * Returns HTML code for a radio button
     *
     * If the name is empty, the empty string is returned.
     * Mind that the id *MUST* be unique on your page.
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
        $name, $value, $id='', $checked=false, $onchange='', $attribute='')
    {

//echo("getRadio($name, $value, $id, $checked, $onchange, $attribute): Entered<br />");

        if (empty($name)) return '';
        return
            '<input type="radio" name="'.$name.'" value="'.$value.'"'.
            ($id ? ' id="'.$id.'"' : '').
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
     * @param   string    $onchange     The optional onchange event script
     * @param   string    $attributeRadio    Additional optional attributes
     *                                  for the checkbox elements
     * @param   string    $attributeLabel    Additional optional attributes
     *                                  for the labels
     * @return  string                  The HTML code for the elements
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getCheckboxGroup(
        $name, $arrOptions, $arrLabel='', $arrChecked='',
        $onchange='', $attributeCheckbox='', $attributeLabel=''
    ) {
        static $index = array();

//echo("getCheckboxGroup($name, ".var_export($arrOptions, true).", ".var_export($arrLabel, true).", ".var_export($arrChecked, true).", $onchange, $attributeCheckbox, $attributeLabel): Entered<br />");

        if (empty($name)) return '';
        // Remove any bracketed construct from the end of the name
        $name_stripped = preg_replace('/\[.*$/', '', $name);
        if (!is_array($arrLabel)) $arrLabel = array();
        if (!is_array($arrChecked)) $arrChecked = array();
        $checkboxgroup = '';
        foreach ($arrOptions as $key => $value) {
            if (empty($index[$name_stripped])) $index[$name_stripped] = 0;
            $id = $name.'-'.++$index[$name_stripped];
            $checkboxgroup .=
                '<p>'.
                self::getCheckbox(
                    $name.'['.$key.']', $value, $id,
                    (in_array($key, $arrChecked)),
                    $onchange, $attributeCheckbox
                ).
                self::getLabel(
                    $id,
                    $arrLabel[$key],
                    $attributeLabel
                ).
                "</p>\n";
        }
        return $checkboxgroup;
    }


    /**
     * Returns HTML code for a checkbox
     *
     * If the name is empty, the empty string is returned.
     * Mind that the id parameter *MUST* be unique on your page.
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
        $name, $value=1, $id='', $checked=false, $onchange='', $attribute=''
    ) {

//echo("getCheckbox($name, $value, $id, $checked, $onchange, $attribute): Entered<br />");

        if (empty($name)) return '';
        return
            '<input type="checkbox" name="'.$name.'" value="'.$value.'"'.
            ($id ? ' id="'.$id.'"' : '').
            ($checked ? ' checked="checked"' : '').
            ' tabindex="'.++self::$tabindex.'"'.
            ($onchange ? ' onchange="'.$onchange.'"' : '').
            ($attribute ? ' '.$attribute : '').
            " />\n";
    }


    /**
     * Wraps the content in a label
     *
     * Mind that the $for parameter must match the id of the contained
     * element in $content.
     * @param   string    $for          The for attribute of the label
     * @param   string    $text         The text of the label
     * @param   string    $attribute    Additional optional attributes
     * @return unknown
     */
    static function getLabel($for, $text, $attribute='')
    {
        return
            '<label for="'.$for.'"'.
            ($attribute ? ' '.$attribute : '').
            '>'.$text."</label>\n";
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

        Javascript::registerCode(self::getJavascript());
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
     * See {@see Image::updatePostImages()} and {@see Image::uploadAndStore()}
     * for more information and examples.
     * @param   Image   $objImage       The image object
     * @param   string  $id             The base name for the elements IDs
     * @param   mixed   $imagetype_key  The optional Image type key
     * @return  string                  The HTML code for all the elements
     */
    static function getImageChooserUpload($objImage, $id, $imagetype_key=false)
    {
        global $_CORELANG;

        JS::registerCode(self::getJavascript());
        if (empty($objImage)) $objImage = new Image(0);
        return
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
                  self::getHidden($id.'_ord', $objImage->getOrd()).
                  self::getHidden($id.'_src', $objImage->getPath())
                : '').
            self::getInputFileupload($id);
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

        JS::registerCode(self::getJavascript());
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


    static function getJavascript()
    {
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

function clearImage(id, index)
{
  document.getElementById(id+"_img").src = "'.Image::NO_IMAGE_SRC.'";
  document.getElementById(id+"_clear").style.display = "none";
  if (document.getElementById(id+"_src"))
    document.getElementById(id+"_src").value = "";
  if (document.getElementById(id+"_width"))
    document.getElementById(id+"_width").value = "";
  if (document.getElementById(id+"_height"))
    document.getElementById(id+"_height").value = "";
}
';
    }

}

?>
