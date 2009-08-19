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

//require_once(ASCMS_CORE_PATH.'/HtmlTag.class');

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
     * Returns HTML code for a text imput field
     * @param   string    $name         The element name
     * @param   string    $value        The element value
     * @param   string    $attribute    Additional optional attributes
     * @return  string                  The HTML code for the element
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getInputText($name, $value, $attribute='')
    {
        return
            '<input type="text" name="'.$name.'" value="'.$value.'"'.
            ($attribute ? ' '.$attribute : '').
            ' />';
    }


    /**
     * Returns HTML code for a password text imput field
     * @param   string    $name         The element name
     * @param   string    $value        The element value
     * @param   string    $attribute    Additional optional attributes
     * @return  string                  The HTML code for the element
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getInputPassword($name, $value, $attribute='')
    {
        return
            '<input type="password" name="'.$name.'" value="'.$value.'"'.
            ($attribute ? ' '.$attribute : '').
            ' />';
    }


    /**
     * Returns HTML code for a file upload input field
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
            '<input type="file" name="'.$name.'"'.
            ($maxlength ? ' maxlength="'.$maxlength.'"' : '').
            ($mimetype ? ' accept="'.$mimetype.'"' : '').
            ($attribute ? ' '.$attribute : '').
            ' />';
    }


    /**
     * Returns HTML code for a text area
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
            '<textarea name="'.$name.'"'.
            ($cols ? ' cols="'.$cols.'"' : '').
            ($rows ? ' rows="'.$rows.'"' : '').
            ($attribute ? ' '.$attribute : '').
            '>'.$value.'</textarea>';
    }


    /**
     * Returns HTML code for a dropdown menu
     *
     * If the name is empty, the empty string is returned.
     * The $name parameter is both used as the name and id parameter
     * in the element.  Mind that thus, it *MUST* be unique on your page.
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

echo("getSelect($name, $arrOptions, $selected, $onchange, $attribute): Entered<br />");

        if (empty($name)) return '';
        return
            '<select id="'.$name.'" name="'.$name.'"'.
            ($onchange ? ' onchange="'.$onchange.'"' : '').
            ($attribute ? ' '.$attribute : '').
            ">\n".self::getOptions($arrOptions, $selected)."</select>\n";
//echo("getMenu(select=$selectedId, name=$menuName, onchange=$onchange): made menu: ".htmlentities($menu)."<br />");
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

echo("getRadioGroup($name, $arrOptions, $checked, $onchange, $attributeRadio, $attributeLabel): Entered<br />");

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
        return $radiogroup;
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

echo("getRadio($name, $value, $id, $checked, $onchange, $attribute): Entered<br />");

        if (empty($name)) return '';
        return
            '<input type="radio" name="'.$name.'" value="'.$value.'"'.
            ($id ? ' id="'.$id.'"' : '').
            ($checked ? ' checked="checked"' : '').
            ($onchange ? ' onchange="'.$onchange.'"' : '').
            ($attribute ? ' '.$attribute : '').
            ">\n";
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

echo("getCheckboxGroup($name, ".var_export($arrOptions, true).", ".var_export($arrLabel, true).", ".var_export($arrChecked, true).", $onchange, $attributeCheckbox, $attributeLabel): Entered<br />");

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
                self::getCheckbox(
                    $name.'['.$key.']', $value, $id,
                    (in_array($key, $arrChecked)),
                    $onchange, $attributeCheckbox
                ).
                self::getLabel(
                    $id,
                    $arrLabel[$key],
                    $attributeLabel
                ).'<br />';
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

echo("getCheckbox($name, $value, $id, $checked, $onchange, $attribute): Entered<br />");

        if (empty($name)) return '';
        return
            '<input type="checkbox" name="'.$name.'" value="'.$value.'"'.
            ($id ? ' id="'.$id.'"' : '').
            ($checked ? ' checked="checked"' : '').
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
            '>'.$text.'</label>';
    }


    /**
     * Returns HTML and JS code for an image element that links to
     * the filebrowser for choosing an image file on the server
     *
     * Uses the $id parameter as prefix for both the name and id attributes
     * of all HTML elements.  The names and respective suffixes are:
     *  - id+'' (none) for the name and id of the <img> tag
     *  - id+'_src' for the name and id of the hidden <input> tag for the image URI
     *  - id+'_width' for the name and id of the hidden <input> tag for the width
     *  - id+'_height' for the name and id of the hidden <input> tag for the height
     * All of the elements with a suffix will provide the current selected
     * image information when the form is posted.
     */
    static function getImageChooserBrowser($objImage, $id)
    {
        global $_CORELANG;

        return
            '<img id="'.$id.'" src="'.$objImage->getPath().'" '.
            '    style="width:'.$objImage->getWidth().'px; height:'.$objImage->getHeight().'px; border: none;" '.
            '    title="'.$_CORELANG['TXT_CORE_CHOOSE_IMAGE'].'" '.
            '    alt="'.$_CORELANG['TXT_CORE_CHOOSE_IMAGE'].'" />'.
            '<a href="javascript:void(0);" title="'.$_CORELANG['TXT_CORE_CLEAR_IMAGE'].'">'.
            '    onclick="clearImage(\''.$id.'\');"'.
            '  <img src="'.Image::CLEAR_IMAGE_ICON.'" border="0" alt="'.$_CORELANG['TXT_CORE_CLEAR_IMAGE'].'"/>'.
            '</a><br />'.
            '<a href="javascript:void(0);" title="{TXT_CORE_CHOOSE_IMAGE}" '.
                'onclick="openBrowser(\'index.php?cmd=fileBrowser&amp;standalone=true&amp;type=shop\',\'1\',\'width=800,height=640,resizable=yes,status=no,scrollbars=yes\');"  >'.
            '  '.$_CORELANG['TXT_CORE_CHOOSE_IMAGE'].
            '<input type="hidden" id="'.$id.'_src" name="'.$id.'_src" value="'.$objImage->getPath().'" />'.
            '<input type="hidden" id="'.$id.'_width" name="'.$id.'_width" value="'.$objImage->getWidth().'" />'.
            '<input type="hidden" id="'.$id.'_height" name="'.$id.'_height" value="'.$objImage->getHeight().'" />';
    }


    /**
     * Returns HTML and JS code for an image element with form
     * elements for uploading an image file.
     *
     * Uses the $id parameter as prefix for both the name and id attributes
     * of all HTML elements.  The names and respective suffixes are:
     *  - id+'' (none) for the name and id of the <img> tag
     *  - id+'_file' for the name and id of the file upload element
     * The file upload element will provide the current selected image
     * path when the form is posted.
     */
    static function getImageChooserUpload($objImage, $id)
    {
        global $_CORELANG;

        return
            '<img id="'.$id.'" src="'.$objImage->getPath().'" '.
            '    style="width:'.$objImage->getWidth().'px; height:'.$objImage->getHeight().'px; border: none;" '.
            '    title="'.$_CORELANG['TXT_CORE_CHOOSE_IMAGE'].'" '.
            '    alt="'.$_CORELANG['TXT_CORE_CHOOSE_IMAGE'].'" />'.
            '<a href="javascript:void(0);" title="'.$_CORELANG['TXT_CORE_CLEAR_IMAGE'].'">'.
            '    onclick="clearImage(\''.$id.'\');"'.
            '  <img src="'.Image::ICON_CLEAR_IMAGE_SRC.'" border="0" alt="'.$_CORELANG['TXT_CORE_CLEAR_IMAGE'].'"/>'.
            '</a><br />'.
            self::getInputFileupload($id.'_file');
    }
}

?>
