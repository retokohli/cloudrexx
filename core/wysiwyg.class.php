<?php
/**
 * WYSIWYG editor interface
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Daeppen <thomas.daeppen@comvation.com>
 * @version     2.0.0
 * @package     contrexx
 * @subpackage  core
 */
//Security-Check
if (eregi('wysiwyg.class.php',$_SERVER['PHP_SELF']))
{
    header('Location: index.php');
    exit;
}

require_once ASCMS_CORE_PATH . '/Wysiwyg/Wysiwyg.class.php';

// set wysiwyg editor
$wysiwygEditor = 'FCKeditor';


// initialize variables
switch ($wysiwygEditor) {
	case 'FCKeditor':
		$FCKeditorBasePath = '/editor/fckeditor/';
		break;
}

/**
 * WYSIWYG editor
 *
 * Gets the HTML code for the wysiwyg editor as a string
 * @version   1.0        initial version
 * @return string The WYSIWYG editor code
 */
function get_wysiwyg_code()
{
	global $wysiwygEditor;

	$return = '';

	switch ($wysiwygEditor) {
		case 'FCKeditor':
			global $FCKeditorBasePath;

			$return = '';
			break;
	}
	return $return;
}


/**
 * WYSIWYG editor
 *
 * Gets the wysiwyg editor as a string
 * @version   1.0        initial version
 * @return string
 * @param string $name
 * @param string $value
 * @param string $mode
 */
function get_wysiwyg_editor($name, $value = '', $mode = '', $languageId = null, $absoluteURIs = false)
{
    $wysiwyg = new Wysiwyg($name, $value, $mode, $languageId, $absoluteURIs);
    return $wysiwyg->getCode();
}
