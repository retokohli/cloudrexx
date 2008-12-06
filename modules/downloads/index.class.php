<?php
if (0) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    $objDatabase->debug = 1;
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    $objDatabase->debug = 0;
}
/**
 * Downloadmodul
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Damir Beciragic <damie.beciragic@comvation.com>
 * @version        $Id: index.inc.php,v 1.00 $
 * @package     contrexx
 * @subpackage  downloads
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH.'/downloads/downloadsLib.class.php';

$_ARRAYLANG['TXT_DOWNLOADS']            = "Downloads";
$_ARRAYLANG['TXT_DOWNLOADS_SEARCH']     = "Suche";
$_ARRAYLANG['TXT_DOWNLOADS_FILTERS']    = "Suche";
$_ARRAYLANG['TXT_DOWNLOADS_CATEGORIES'] = "Kategorien";
$_ARRAYLANG['TXT_DOWNLOADS_LICENSE']    = "Lizenz";
$_ARRAYLANG['TXT_DOWNLOADS_VERSION']    = "Version";
$_ARRAYLANG['TXT_DOWNLOADS_SIZE']       = "Grösse";
$_ARRAYLANG['TXT_DOWNLOADS_SCREENSHOT'] = "Screenshot / Vorschau";
$_ARRAYLANG['TXT_DOWNLOADS_DOWNLOAD']   = "download";
$_ARRAYLANG['TXT_DOWNLOADS_LOGIN']      = "login";
$_ARRAYLANG['TXT_DOWNLOADS_ALL']        = "Alle";



//error_reporting(E_ALL);
//ini_set('display_errors', 1);

class Downloads extends DownloadsLibrary
{
    /**
     * @var HTML_Template_Sigma
     */
    var $objTemplate;
    var $_strStatusMessage = '';
    var $_strErrorMessage = '';

    /**
    * Constructor-Fix for non PHP5-Servers
    */
    function Downloads($strPageContent) {
        $this->__constructor($strPageContent);
    }


    /**
    * Constructor    -> Call parent-constructor, set language id and create local template-object
    * @global    integer        $_LANGID
    */
    function __constructor($strPageContent)
    {
        global $_LANGID;

        DownloadsLibrary::__constructor();

        $this->_intLanguageId = intval($_LANGID);

        $this->objTemplate = new HTML_Template_Sigma('.');
        $this->objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $this->objTemplate->setTemplate($strPageContent);
    }


    /**
    * Reads $_GET['cmd'] and selects (depending on the value) an action
    *
    */
    function getPage()
    {
        if (!isset($_GET['cmd'])) {
            $_GET['cmd'] = '';
        }

        switch ($_GET['cmd']) {
            case "file":
                $this->GetFile();
                break;
            /*case 'user';
                $this->showUserInfo();
                break;
            case 'check';
                $this->checkUser();
                break;*/
            default:
                $this->listDownloads();
                break;
        }

        return $this->objTemplate->get();
    }


    /**
     * Shows all existing entries of the blog in descending order.
     *
     * @global     array        $_ARRAYLANG
     * @global    object        $objDatabase
     * @global     array        $_CONFIG
     */
    function listDownloads()
    {
        global $_ARRAYLANG, $objDatabase, $_LANGID;

        // Request
        // ---------------------------------------------------------------------
        $category = (isset($_REQUEST['category']) ? $_REQUEST['category'] : '');
        $keyword = (isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : '');

        // Filter-Display
        // ---------------------------------------------------------------------
        if ($this->_arrConfig['filter']==1) {
            $filter_display = 'block';
        } else {
            $filter_display = 'none';
        }
        $this->objTemplate->setVariable(array('FILTER_DISPLAY' => $filter_display));

        // Icons
        // ---------------------------------------------------------------------
        if ($this->_arrConfig['design']>0) {
            $this->objTemplate->setVariable(array(
                'ICON_DISPLAY' => 'block',
                'ICON_FILTERS' => $this->_GetIconImage('filter.gif'),
                'ICON_INFO'    => $this->_GetIconImage('info.gif'),
            ));
        } else {
            $this->objTemplate->setVariable(array(
                'ICON_DISPLAY' => 'none',
                'ICON_FILTERS' => '',
                'ICON_INFO'    => '',
            ));
        }

        $FILTER_CATEGORIES_VALUE = $this->_GetCategoriesOption($category);

        // Categories
        // ---------------------------------------------------------------------
        if (intval($category<1)&&$keyword=='') {
            $this->objTemplate->setCurrentBlock('Categories_Row');
            $Categories = $this->_GetCategories();
            for($x=0;$x<count($Categories); $x++) {
                $CategoryInfo = $this->_CategoryInfo($Categories[$x]);
                if ($this->_arrConfig['design']>0) {
                    if ($CategoryInfo['category_img'] != '') {
                        $CategoryIcon = $this->_GetIconImage($CategoryInfo['category_img'], 1);
                    } else {
                        $CategoryIcon = $this->_GetIconImage('category.gif');
                    }
                } else {
                    $CategoryIcon = '';
                }

                $Categoryname = $CategoryInfo['category_loc']['lang'][$_LANGID]['name'];
                if ($Categoryname == '') {
                    $Categoryname = $CategoryInfo['category_loc'][0]['name'];
                }

                $this->objTemplate->setVariable(array(
                    'CATEGORY_ID'   => $CategoryInfo['category_id'],
                    'CATEGORY_NAME' => $Categoryname,
                    'CATEGORY_DESC' => $CategoryInfo['category_loc']['lang'][$_LANGID]['desc'],
                    'ICON_CATEGORY' => $CategoryIcon,
                ));

                $this->objTemplate->parse('Categories_Row');
            }

            $this->objTemplate->setVariable(array(
                'CATEGORIES_DISPLAY'             => 'block',
            ));
        } else {
            $this->objTemplate->setVariable(array(
                'CATEGORIES_DISPLAY'             => 'none',
            ));
        }


        // Files
        // ---------------------------------------------------------------------
        if (intval($category)>0) {
            $query = "
                SELECT rel_file, rel_category, file_id, file_name
                FROM ".DBPREFIX."module_downloads_rel_files_cat
                JOIN ".DBPREFIX."module_downloads_files ON ".DBPREFIX."module_downloads_rel_files_cat.rel_file=".DBPREFIX."module_downloads_files.file_id
                WHERE rel_category=".$category." AND file_state=1
                ORDER BY file_name";
        } else {
            $query = "
                SELECT file_id, file_name, rel_file
                FROM ".DBPREFIX."module_downloads_files
                LEFT JOIN ".DBPREFIX."module_downloads_rel_files_cat ON ".DBPREFIX."module_downloads_files.file_id=".DBPREFIX."module_downloads_rel_files_cat.rel_file
                WHERE rel_file is NULL AND file_state=1
                ORDER BY file_name";
        }

        // QUERY FOR SEARCH
        // ----------------------------------------------------------------------
        if ($keyword!='') {
            $query = "
                SELECT file_id, file_name, rel_file
                FROM ".DBPREFIX."module_downloads_files
                LEFT JOIN ".DBPREFIX."module_downloads_rel_files_cat ON ".DBPREFIX."module_downloads_files.file_id=".DBPREFIX."module_downloads_rel_files_cat.rel_file
                LEFT JOIN ".DBPREFIX."module_downloads_files_locales ON ".DBPREFIX."module_downloads_files.file_id=".DBPREFIX."module_downloads_files_locales.loc_file
                WHERE (loc_name LIKE '%".$keyword."%' OR loc_desc LIKE '%".$keyword."%') AND file_state=1
                GROUP BY file_id ORDER BY file_name";
        }

        $objResult = $objDatabase->Execute($query);
        if ($objResult && $objResult->RecordCount()) {
            $objFWUser = FWUser::getFWUserObject();
            $FilesJS = '';
            $openendJS = '';
            while (!$objResult->EOF) {

                $fileInfo = $this->_FileInfo($objResult->fields["file_id"]);

                if ($this->_arrConfig["design"]>0) {
                    if ($fileInfo['file_type']!='') {
                        $ImgName = $fileInfo['file_type'].'.gif';
                    } else {
                        $ImgName = 'file.gif';
                    }
                    $FileIcon = $this->_GetIconImage($ImgName);
                } else {
                    $FileIcon = '';
                }

                $FILE_SCREEN = '';
                if ($fileInfo['file_img']!='') {
                    $FILE_SCREEN = '<a href="'.$fileInfo['file_img'].'" target="_blank">'.$_ARRAYLANG['TXT_DOWNLOADS_SCREENSHOT'].'</a>';
                }

                // Downlaod-Link
                // --------------------------------------
                if ($fileInfo["file_protected"]==0) {
                    $DonwlodLink = '<a href="index.php?section=downloads&cmd=file&id='.$fileInfo['file_source'].'" target="_blank">'.$this->_GetIconImage('download.gif').'</a>';
                } else {
                    if ($objFWUser->objUser->login()) {
                        if (Permission::checkAccess($fileInfo['file_access_id'], 'dynamic', true)) {
                            $DonwlodLink = '<a href="index.php?section=downloads&cmd=file&id='.$fileInfo['file_source'].'" target="_blank">'.$this->_GetIconImage('download.gif').'</a>';
                        } else {
                            $DonwlodLink = '<a href="index.php?section=downloads&cmd=user">'.$this->_GetIconImage('lock.gif').'</a>';
                        }
                    } else {
                        $DonwlodLink = '<a href="index.php?section=login">'.$this->_GetIconImage('lock.gif').'</a>';
                    }
                }

                // TXT_DOWNLOADS_DOWNLOAD
                if ($this->_arrConfig["design"]==0) {
                if ($objFWUser->objUser->login()) {
                    if (!Permission::checkAccess($fileInfo['file_access_id'], 'dynamic', true)) {
                        $DonwlodLink = '<a href="index.php?section=downloads&cmd=file&id='.$fileInfo['file_source'].'" target="_blank">'.$_ARRAYLANG["TXT_DOWNLOADS_DOWNLOAD"].'</a>';
                    } else {
                        $DonwlodLink = '<a href="index.php?section=login">'.$_ARRAYLANG["TXT_DOWNLOADS_LOGIN"].'</a>';
                    }
                } else {
                    $DonwlodLink = '<a href="index.php?section=login">'.$_ARRAYLANG["TXT_DOWNLOADS_LOGIN"].'</a>';
                }
            }



            $this->objTemplate->setVariable(array(
                'FILE_ID'                  => $fileInfo['file_id'],
                'FILE_NAME'                => $fileInfo['file_loc']['lang'][$_LANGID]["name"],
                'FILE_DESC'                => str_replace(chr(13), '<br />', $fileInfo['file_loc']['lang'][$_LANGID]["desc"]),
                'FILE_TYPE'                => $fileInfo['file_type'],
                'FILE_TYPE'                => $fileInfo['file_type'],
                'FILE_SIZE'                => ($fileInfo['file_size']/1000)." KB",
                'FILE_IMG'                 => $fileInfo['file_img'],
                'FILE_AUTHOR'              => $fileInfo['file_autor'],
                'FILE_CREATED'             => $fileInfo['file_created'],
                'FILE_LICENSE'             => $fileInfo['file_license'],
                'FILE_VERSION'             => $fileInfo['file_version'],
                'ICON_FILE'                => $FileIcon,
                'ICON_INFO'                => $this->_GetIconImage('info.gif',0,'info_'.$fileInfo['file_id']),
                'ICON_DOWNLOAD'            => $DonwlodLink,
                'TXT_DOWNLOADS_LICENSE'    => $_ARRAYLANG['TXT_DOWNLOADS_LICENSE'],
                'TXT_DOWNLOADS_VERSION'    => $_ARRAYLANG['TXT_DOWNLOADS_VERSION'],
                'TXT_DOWNLOADS_SIZE'       => $_ARRAYLANG['TXT_DOWNLOADS_SIZE'],
                'TXT_DOWNLOADS_SCREENSHOT' => $_ARRAYLANG['TXT_DOWNLOADS_SCREENSHOT'],
                'FILE_SCREEN'              => $FILE_SCREEN,

            ));

            $openendJS .= "opened[".$fileInfo['file_id']."] = false;

            ";
            $FilesJS .= "Download[".$fileInfo['file_id']."] = new fx.Height('DownlaodLayer_".$fileInfo['file_id']."',{duration:1000});

            ";

            $this->objTemplate->parse('Files_Row');
            $objResult->MoveNext();
        }

        $DOWNLOADS_JS = "
            <script type=\"text/javascript\" src=\"lib/javascript/prototype.lite.js\"></script>
            <script type=\"text/javascript\" src=\"lib/javascript/moo.fx.js\"></script>
            <script type=\"text/javascript\">
            <!--
            opened = new Array()
            Download = new Array();
            ".$openendJS."
            window.onload = function() {
                ".$FilesJS."
            };

            function toggelopen(Obj) {
                if (opened[Obj]==false) {
                    Download[Obj].custom(24,180);
                    opened[Obj]=true;
                    document.getElementById('info_'+Obj).src = '".ASCMS_MODULE_WEB_PATH."/downloads/images/icons/".$this->_arrConfig["design"]."/info_act.gif';
                } else {
                    Download[Obj].custom(180,24);
                    opened[Obj]=false;
                    document.getElementById('info_'+Obj).src = '".ASCMS_MODULE_WEB_PATH."/downloads/images/icons/".$this->_arrConfig["design"]."/info.gif';
                }
            }
            //-->
            </script>
        ";

        $this->objTemplate->setVariable(array(
            'DOWNLOADS_JS'                 => $DOWNLOADS_JS,
        ));
    } else {
        $this->objTemplate->hideBlock('Files_Row');
    }

    if ($this->_arrConfig["filter"]==0) {
        $searchdisplay = 'none';
    } else {
        $searchdisplay = 'block';
    }

    $this->objTemplate->setVariable(array(
        'TXT_DOWNLOADS'            => $_ARRAYLANG['TXT_DOWNLOADS'],
        'TXT_DOWNLOADS_SEARCH'     => $_ARRAYLANG['TXT_DOWNLOADS_SEARCH'],
        'TXT_DOWNLOADS_FILTERS'    => $_ARRAYLANG['TXT_DOWNLOADS_FILTERS'],
        'TXT_DOWNLOADS_CATEGORIES' => $_ARRAYLANG['TXT_DOWNLOADS_CATEGORIES'],
        'FILTER_CATEGORIES_VALUE'  => $FILTER_CATEGORIES_VALUE,
        'FILTER_DISPLAY'           => $searchdisplay,
    ));
}


function GetFile()
{
    global $objDatabase;

    $code = $_REQUEST["id"];

    if ($code!='') {
        $objResult = $objDatabase->SelectLimit("SELECT `file_id` FROM ".DBPREFIX."module_downloads_files WHERE file_source='".$code."'", 1);
        if ($objResult !== false && $objResult->RecordCount() == 1) {
            $File_ID =  $objResult->fields['file_id'];
        } else {
            header('location:index.php?section=downloads');
            exit();
        }
    } else {
        header('location:index.php?section=downloads');
        exit();
    }

    $FileInfo = $this->_FileInfo($File_ID);

    if ($FileInfo["file_protected"]==0) {
        $StartDownload = true;
    } else {
        if (Permission::checkAccess($FileInfo['file_access_id'], 'dynamic', true)) {
            $StartDownload = true;
        } else {
            $StartDownload = false;
        }
    }

    if ($StartDownload) {
        if (substr($FileInfo['file_name'], 0, 7)!='http://' && substr($FileInfo['file_name'], 0, 8)!='https://') {
            $Dateiname  = basename($FileInfo['file_name']);
            $Size       = filesize(ASCMS_PATH.'/'.$FileInfo['file_name']);
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=".$Dateiname."");
            header("Content-Length: ".$Size."");
            readfile(ASCMS_PATH.'/'.$FileInfo['file_name']);
        } else {
            header('location:'.$FileInfo['file_name']);
            exit();
        }
    } else {
        header('location:index.php?section=login');
        exit();
    }
}


    /**
     * Show some information about the user account
     *
     * Note that this is a noser.com special for the download module.
     * This method redirects to the download module main page if there
     * is no User logged in, or if no User information can be retrieved.
     * @global  array   $_ARRAYLANG         Language array
     * @return  boolean                     True on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
/*    function showUserInfo()
    {
        global $_ARRAYLANG, $objAuth;

        if (empty($objAuth) || !$objAuth->checkAuth()) {
            $redirect = base64_encode("index.php?section=downloads&cmd=user");
            header("Location: index.php?section=login&redirect=$redirect");
            exit;
        }

        $arrInfo = userManagement::getUserInfo();
        if (!$arrInfo) {
            return false;
        }

        if (!empty($_POST['logout'])) {
            header("Location: index.php?section=logout");
            exit;
        }

        if (!empty($_POST['update'])) {
            // User tries to change the password
            $oldPass  = (isset($_POST['oldPass'])  ? contrexx_stripslashes($_POST['oldPass'])  : '');
            $newPass1 = (isset($_POST['newPass1']) ? contrexx_stripslashes($_POST['newPass1']) : '');
            $newPass2 = (isset($_POST['newPass2']) ? contrexx_stripslashes($_POST['newPass2']) : '');

            if ($oldPass == '') {
                $this->objTemplate->setVariable('DOWNLOAD_USER_STATUS', $_ARRAYLANG['TXT_DOWNLOAD_ENTER_CURRENT_PASSWORD']);
            }
            elseif (md5($oldPass) != $arrInfo['password']) {
                $this->objTemplate->setVariable('DOWNLOAD_USER_STATUS', $_ARRAYLANG['TXT_DOWNLOAD_WRONG_CURRENT_PASSWORD']);
            }
            elseif ($newPass1 == '') {
                $this->objTemplate->setVariable('DOWNLOAD_USER_STATUS', $_ARRAYLANG['TXT_DOWNLOAD_SPECIFY_NEW_PASSWORD']);
            }
            elseif ($newPass1 != $newPass2) {
                $this->objTemplate->setVariable('DOWNLOAD_USER_STATUS', $_ARRAYLANG['TXT_DOWNLOAD_PASSWORD_NOT_CONFIRMED']);
            }
            elseif (strlen($newPass1) < 6) {
                $this->objTemplate->setVariable('DOWNLOAD_USER_STATUS', $_ARRAYLANG['TXT_DOWNLOAD_INVALID_PASSWORD']);
            }
            else {
                $result = userManagement::changePasswordById($arrInfo['id'], $newPass1);
                if ($result) {
                    $this->objTemplate->setVariable('DOWNLOAD_USER_STATUS', $_ARRAYLANG['TXT_DOWNLOAD_PASSWORD_CHANGED_SUCCESSFULLY']);
                } else {
                    $this->objTemplate->setVariable('DOWNLOAD_USER_STATUS', $_ARRAYLANG['TXT_DOWNLOAD_UNABLE_SET_NEW_PASSWORD']);
                }
                // Clear POST array and refresh user Info
                unset($_POST);
                return $this->showUserInfo();
            }
        }
//echo("username: ".$_SESSION['shop']['username']."<br />");

        // Determine end date
        $orderUnixTimeStamp = strtotime($arrInfo['regdate']);
        $validity = $arrInfo['validity'] * 24 * 60 * 60;
        $endDate =
            ($validity > 0
                ? date('d.m.Y', ($orderUnixTimeStamp+$validity))
                : $_ARRAYLANG['TXT_DOWNLOAD_VALIDITY_UNLIMITED']
            );

        $this->objTemplate->setVariable(array(
            // Account information
            'TXT_DOWNLOAD_SURNAME' => $_ARRAYLANG['TXT_DOWNLOAD_SURNAME'],
            'TXT_DOWNLOAD_FIRSTNAME' => $_ARRAYLANG['TXT_DOWNLOAD_FIRSTNAME'],
            'TXT_DOWNLOAD_EMAIL' => $_ARRAYLANG['TXT_DOWNLOAD_EMAIL'],
            'TXT_DOWNLOAD_VALID_TIL' => $_ARRAYLANG['TXT_DOWNLOAD_VALID_TIL'],
            'DOWNLOAD_CUSTOMER_SURNAME' => $arrInfo['lastname'],
            'DOWNLOAD_CUSTOMER_FIRSTNAME' => $arrInfo['firstname'],
            'DOWNLOAD_CUSTOMER_EMAIL' => $arrInfo['email'],
            'DOWNLOAD_CUSTOMER_VALID_TIL' => $endDate,
            // Change password
            'TXT_DOWNLOAD_PASSWORD_MIN_CHARS' => $_ARRAYLANG['TXT_DOWNLOAD_PASSWORD_MIN_CHARS'],
            'TXT_DOWNLOAD_PASSWORD_CURRENT' => $_ARRAYLANG['TXT_DOWNLOAD_PASSWORD_CURRENT'],
            'TXT_DOWNLOAD_PASSWORD_NEW' => $_ARRAYLANG['TXT_DOWNLOAD_PASSWORD_NEW'],
            'TXT_DOWNLOAD_PASSWORD_CONFIRM' => $_ARRAYLANG['TXT_DOWNLOAD_PASSWORD_CONFIRM'],
            'TXT_DOWNLOAD_PASSWORD_CHANGE' => $_ARRAYLANG['TXT_DOWNLOAD_PASSWORD_CHANGE'],
            // Logout
            'TXT_DOWNLOAD_LOGOUT' => $_ARRAYLANG['TXT_DOWNLOAD_LOGOUT'],
        ));

        return true;
    }

    function checkUser(){
        global $objDatabase, $objAuth, $objPerm;
        if(isset($_REQUEST["id"])){
            $UserInfo = userManagement::getUserInfoByName($_REQUEST["id"]);

            $validity               = $UserInfo['validity'];
            $regdate                = $UserInfo['regdate'];
            $orderUnixTimeStamp     = strtotime($regdate);
            $endDate = ($validity > 0 ? date('d.m.Y', ($orderUnixTimeStamp+($validity * 24 * 60 * 60))) : '');

            $this->objTemplate->setVariable(array(
            'USER_INFO' => 'Expires: '.$endDate,
        ));
        }
    }
*/

}
?>

