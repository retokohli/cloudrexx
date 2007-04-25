<?php
//Security-Check
if (eregi("wysiwyg.class.php",$_SERVER['PHP_SELF']))
{
    Header("Location: index.php");
    die();
}

/* RichEditor v1.9 modification for Astalavista
/*************************************************
	see /editor/richeditor19_README.txt
*/

/* FCKeditor v2.0 RC3 modification for Astalavista
/***************************************************
	see /editor/FCKeditor_2.0rc3_README.txt
*/

// set wysiwyg editor
$wysiwygEditor = "FCKeditor";


// initialize variables
switch ($wysiwygEditor) {
	case 'richeditor19':
		$relativeEditorPath = "/editor/richeditor19/";
		$class_path = ASCMS_PATH_OFFSET.$relativeEditorPath;

		//determine browser type
		$user_agent = @$_SERVER["HTTP_USER_AGENT"];
		if(!$user_agent) $user_agent = $GLOBALS["HTTP_USER_AGENT"];

		$rich_prefix = '';
		$rich_browser= '';
		if(ereg("MSIE ([0-9|\.]+)", $user_agent, $regs) &&
		   ereg("Win", $user_agent) && !strstr($user_agent, 'Opera')){
			$rich_browser = 'msie';
		}else{
		    if(ereg("Mozilla/([0-9|\.]+)", $user_agent, $regs) &&
		       ereg("Gecko", $user_agent) && (double)$regs[1]>=5.0){
				$rich_browser = 'ns';
				$rich_prefix = '_ns';
		    }
		}
		break;
	case 'FCKeditor':
		$FCKeditorBasePath = "/editor/fckeditor/";
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

	$return = "";

	switch ($wysiwygEditor) {
		case 'richeditor19':
			global $rich_prefix, $relativeEditorPath;

			$return = "<link rel='StyleSheet' type='text/css' href='".ASCMS_PATH_OFFSET.$relativeEditorPath."class/rich_files/rich".$rich_prefix.".css' />\n";
			$return .= "<script language='JavaScript' type='text/javascript' src='".ASCMS_PATH_OFFSET.$relativeEditorPath."class/rich_files/rich".$rich_prefix.".js'></script>\n";
			$return .= "<script language='JavaScript' type='text/javascript' src='".ASCMS_PATH_OFFSET.$relativeEditorPath."class/rich_files/rich_xhtml.js'></script>\n";
			break;
		case 'FCKeditor':
			global $FCKeditorBasePath;

			$return = "";
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
function get_wysiwyg_editor($name, $value, $mode="active")
{
	global $wysiwygEditor;

	switch ($wysiwygEditor) {
		case 'richeditor19':
			global $_CONFIG, $class_path, $rich_prefix, $rich_browser, $user_agent, $relativeEditorPath;

			if($mode!="html")
			{
				// do not remove this dublicate!
				$class_path = ASCMS_PATH_OFFSET.$relativeEditorPath."class/";
				$include_path = ASCMS_DOCUMENT_ROOT.$relativeEditorPath;
				require_once($include_path."class/class.rich.php");
				//require($include_path."class/rich_files/lang/class.rich_lang.php");

			    if($mode=="shop")
			    {
				    $editor = &new rich('',
				                        $name,
				                        $value,
				                        "95%",
				                        "200px",
				                        ASCMS_CONTENT_IMAGE_WEB_PATH."/",
				                        ASCMS_CONTENT_IMAGE_WEB_PATH."/",
				                        false,
				                        false);
			    	$editor->active_mode();
				    $editor->hide_tb('font', true);
				    $editor->hide_tb('form', true);
				    $editor->hide_tb('snippets', true);
				    $editor->hide_tb('page_properties', true);
				    $editor->hide_tb('style', true);
			    	/*
			    	$editor = &new rich('',
				                        $name,
				                        $value,
				                        "95%",
				                        "160px",
				                        ASCMS_CONTENT_IMAGE_WEB_PATH."/",
				                        ASCMS_CONTENT_IMAGE_WEB_PATH."/",
				                        false,
				                        false);
			    	$editor->simple_mode();
			    	$editor->hide_tb('link', false);
			    	$editor->hide_tb('image', false);
			    	$editor->hide_tb('help', true);
				    $editor->hide_tb('list', false);
				    */
			    }
			    else
			    {
				    $editor = &new rich('',
				                        $name,
				                        $value,
				                        "100%",
				                        "450px",
				                        ASCMS_CONTENT_IMAGE_WEB_PATH."/",
				                        ASCMS_CONTENT_IMAGE_WEB_PATH."/",
				                        false,
				                        false);
			    	$editor->active_mode();
				    $editor->hide_tb('font', true);
				    $editor->hide_tb('form', true);
				    $editor->hide_tb('snippets', true);
				    $editor->hide_tb('page_properties', true);
				    $editor->hide_tb('style', true);
			    }

			    $editor->set_lang('de');
			    $editor->set_borders_visibility(true);
			    $editor->xhtml_mode(true);

			     //$editor->set_default_stylesheet('style1.css');
			    // return $editor->rich_code();
			    return $editor->get_rich();
			}
			else {
			    $editor = "<textarea name='".$name."' style='width:100%; height:450px;'>".$value."</textarea>";
			    return $editor;
			}
			break;
		case 'FCKeditor':
			global $FCKeditorBasePath;

			$include_path = ASCMS_DOCUMENT_ROOT.$FCKeditorBasePath;
			require_once($include_path."fckeditor.php");

			$objFCKeditor = new FCKeditor($name) ;
			$objFCKeditor->BasePath	=  ASCMS_PATH_OFFSET.$FCKeditorBasePath;
			$objFCKeditor->Config['CustomConfigurationsPath'] = ASCMS_PATH_OFFSET.'/editor/FCKeditorConfig.php';
			$objFCKeditor->Value = empty($value) ? "" : $value;

			if ($mode != "html") {
				switch ($mode) {
				case "shop":
					$objFCKeditor->Width = "100%";
					$objFCKeditor->Height = "200";
					break;

				case "news":
					$objFCKeditor->Width = "100%";
					$objFCKeditor->Height = "350";
					$objFCKeditor->ToolbarSet = "News";
					break;

				case "teaser":
					$objFCKeditor->Width = "100%";
					$objFCKeditor->Height = "100";
					$objFCKeditor->ToolbarSet = "News";
					break;

				case "fullpage":
					$objFCKeditor->Width = "100%";
					$objFCKeditor->Height = "450";
					$objFCKeditor->Config['FullPage'] = true;
					break;

				default:
					$objFCKeditor->Width = "100%";
					$objFCKeditor->Height = "450";
					break;
				}
				$editor = $objFCKeditor->CreateHtml();
			} else {
			    $editor = "<textarea name='".$name."' style='width:100%; height:450px;'>".$value."</textarea>";
			}
			return $editor;
			break;
	}
}
?>