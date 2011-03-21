<?php

/**
 * Media Library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version       1.0.1
 * @package     contrexx
 * @subpackage  core_module_media
 * @todo        Edit PHP DocBlocks!
 */

require_once ASCMS_LIBRARY_PATH.'/FRAMEWORK/Validator.class.php';

/**
 * Media Library
 *
 * LibClass to manage cms media manager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @access        public
 * @version       1.0.1
 * @package     contrexx
 * @subpackage  core_module_media
 */
class MediaLibrary
{
    protected $sortBy = 'name';
    protected $sortDesc = false;


    // act: newDir
    // creates a new directory through php or ftp
    function _createNewDir($dirName)
    {
        global $_ARRAYLANG, $objTemplate;

        $obj_file = new File();
        $dirName = $obj_file->replaceCharacters($dirName);
        $this->dirLog=$obj_file->mkDir($this->path, $this->webPath, $dirName);
        if ($this->dirLog != "error") {
            $this->highlightName[] = $this->dirLog;
            $objTemplate->setVariable('CONTENT_OK_MESSAGE',$_ARRAYLANG['TXT_MEDIA_MSG_NEW_DIR']);
        } else {
            $objTemplate->setVariable('CONTENT_STATUS_MESSAGE',$_ARRAYLANG['TXT_MEDIA_MSG_ERROR_NEW_DIR']);
        }
    }


    // act: preview
    // previews the edited image
    function _previewImage()
    {
        $arr = explode(',', $this->getData);
        $this->_objImage->loadImage($this->path.$this->getFile);
        $this->_objImage->resizeImage($arr[0], $arr[1], $arr[2]);
        $this->_objImage->showNewImage();
    }


    // act: previewSize
    // previews the size of the edite image
    function _previewImageSize()
    {
        $arr = explode(',', $this->getData);
        $this->_objImage->loadImage($this->path.$this->getFile);
        $this->_objImage->resizeImage($arr[0], $arr[1], $arr[2]);

        $time = time();
        $this->_objImage->saveNewImage($this->path.$time);
        $size = @filesize($this->path.$time);

        @unlink($this->path.$time);
        $size = $this->_formatSize($size);

        $width   = strlen($size) * 7 + 10;
        $img     = imagecreate($width, 20);
        $colBody = imagecolorallocate($img, 255, 255, 255);
        ImageFilledRectangle($img, 0, 0, $width, 20, $colBody);
        $colFont = imagecolorallocate($img, 0, 0, 0);
        imagettftext($img, 10, 0, 5, 15, $colFont, $this->iconPath.'arial.ttf', $size);

        header("Content-type: image/jpeg");
        imagejpeg($img, '', 100);
    }

    /**
     * downloads the media
     *
     * act: download
     */
    function _downloadMediaOLD()
    {
        if (is_file($this->path.$this->getFile)) {
            CSRF::header("Location: ".$this->webPath.$this->getFile);
            exit;
        }
    }

    /**
     * Send a file for downloading
     */
    function _downloadMedia()
    {
        // The file is already checked (media paths only)
        $file = $this->path.$this->getFile;
        //First, see if the file exists
        if (!is_file($file)) { die("<b>404 File not found!</b>"); }

        $filename = basename($file);
        $file_extension = strtolower(substr(strrchr($filename,"."),1));

        //This will set the Content-Type to the appropriate setting for the file
        switch( $file_extension ) {
            case "pdf": $ctype="application/pdf"; break;
            case "exe": $ctype="application/octet-stream"; break;
            case "zip": $ctype="application/zip"; break;
            case "docx" :
            case "doc": $ctype="application/msword"; break;
            case "xlsx":
            case "xls": $ctype="application/vnd.ms-excel"; break;
            case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
            case "gif": $ctype="image/gif"; break;
            case "png": $ctype="image/png"; break;
            case "jpeg":
            case "jpg": $ctype="image/jpg"; break;
            case "mp3": $ctype="audio/mpeg"; break;
            case "wav": $ctype="audio/x-wav"; break;
            case "mpeg":
            case "mpg":
            case "mpe": $ctype="video/mpeg"; break;
            case "mov": $ctype="video/quicktime"; break;
            case "avi": $ctype="video/x-msvideo"; break;

            //The following are for extensions that shouldn't be downloaded (sensitive stuff, like php files)
            case "phps":
            case "php4":
            case "php5":
            case "php": die("<b>Cannot be used for ". $file_extension ." files!</b>"); break;

            default: $ctype="application/force-download";
        }

        require_once ASCMS_LIBRARY_PATH.'/PEAR/Download.php';
        $dl = new HTTP_Download(array(
          "file"                  => $file,
          "contenttype"           => $ctype
        ));
        $dl->send();

        exit;
    }


    /**
     * cuts the media -> paste insterts the media
     *
     * act: cut
     */
    function _cutMedia()
    {
        if (isset($_POST['formSelected']) && !empty($_POST['formSelected'])) {
            if (isset($_SESSION['mediaCutFile'])) {
                unset($_SESSION['mediaCutFile']);
            }
            if (isset($_SESSION['mediaCopyFile'])) {
                unset($_SESSION['mediaCopyFile']);
            }

            $_SESSION['mediaCutFile'][] = $this->path;
            $_SESSION['mediaCutFile'][] = $this->webPath;
            $_SESSION['mediaCutFile'][] = $_POST['formSelected'];
        }
    }


    /**
     * copies the media -> paste inserts the media
     *
     * act: copy
     */
    function _copyMedia()
    {
        if (isset($_POST['formSelected']) && !empty($_POST['formSelected'])) {
            if (isset($_SESSION['mediaCutFile'])) {
                unset($_SESSION['mediaCutFile']);
            }
            if (isset($_SESSION['mediaCopyFile'])) {
                unset($_SESSION['mediaCopyFile']);
            }

            $_SESSION['mediaCopyFile'][] = $this->path;
            $_SESSION['mediaCopyFile'][] = $this->webPath;
            $_SESSION['mediaCopyFile'][] = $_POST['formSelected'];
        }
    }


    /**
     * Inserts the file
     *
     * act: paste
     */
    function _pasteMedia()
    {
        global $_ARRAYLANG, $objTemplate;

        // cut
        if (isset($_SESSION['mediaCutFile']) && !empty($_SESSION['mediaCutFile'])) {
            $check = true;

            foreach ($_SESSION['mediaCutFile'][2] as $name) {
                if ($_SESSION['mediaCutFile'][0] != $this->path) {
                    $obj_file = new File();

                    if (is_dir($_SESSION['mediaCutFile'][0].$name)) {
                        $this->dirLog=$obj_file->copyDir($_SESSION['mediaCutFile'][0], $_SESSION['mediaCutFile'][1], $name, $this->path, $this->webPath, $name);
                        if ($this->dirLog == "error") {
                            $check = false;
                        } else {
                            $obj_file->delDir($_SESSION['mediaCutFile'][0], $_SESSION['mediaCutFile'][1], $name);
                        }
                    } else {
                        $this->dirLog=$obj_file->copyFile($_SESSION['mediaCutFile'][0], $name, $this->path, $name);
                        if ($this->dirLog == "error") {
                            $check = false;
                        } else {
                            $obj_file->delFile($_SESSION['mediaCutFile'][0], $_SESSION['mediaCutFile'][1], $name);
                        }
                    }

                    $this->highlightName[] = $this->dirLog;
                }
                else
                {
                    $this->highlightName[] = $name;
                }
            }

            if ($check != false) {
                $objTemplate->setVariable('CONTENT_OK_MESSAGE',$_ARRAYLANG['TXT_MEDIA_MSG_CUT']);
                unset($_SESSION['mediaCutFile']);
            } else {
                $objTemplate->setVariable('CONTENT_STATUS_MESSAGE',$_ARRAYLANG['TXT_MEDIA_MSG_ERROR_CUT']);
            }
        }

        // copy
        if (isset($_SESSION['mediaCopyFile']) && !empty($_SESSION['mediaCopyFile']))
        {
            $check = true;

            foreach ($_SESSION['mediaCopyFile'][2] as $name) {
                $obj_file = new File();

                if (is_dir($_SESSION['mediaCopyFile'][0].$name)) {
                    $this->dirLog=$obj_file->copyDir($_SESSION['mediaCopyFile'][0], $_SESSION['mediaCopyFile'][1], $name, $this->path, $this->webPath, $name);
                    if ($this->dirLog == "error") {
                        $check = false;
                    }
                } else {
                    $this->dirLog=$obj_file->copyFile($_SESSION['mediaCopyFile'][0], $name, $this->path, $name);
                    if ($this->dirLog == "error") {
                        $check = false;
                    }
                }

                $this->highlightName[] = $this->dirLog;
            }

            if ($check != false) {
                $objTemplate->setVariable('CONTENT_OK_MESSAGE',$_ARRAYLANG['TXT_MEDIA_MSG_COPY']);
                unset($_SESSION['mediaCopyFile']);
            } else {
                $objTemplate->setVariable('CONTENT_STATUS_MESSAGE',$_ARRAYLANG['TXT_MEDIA_MSG_ERROR_COPY']);
            }
        }
    }


    // act: delete
    // deletes a file or an directory
    function _deleteMedia()
    {
        global $objTemplate;

        if (isset($this->getFile) && !empty($this->getFile)) {
            $objTemplate->setVariable('CONTENT_OK_MESSAGE',$this->_deleteMedia2($this->getFile));
        } elseif (isset($_POST['formSelected']) && !empty($_POST['formSelected'])) {
            foreach ($_POST['formSelected'] as $file) {
                $objTemplate->setVariable('CONTENT_OK_MESSAGE',$this->_deleteMedia2($file));
            }
        }
    }


    function _deleteMedia2($file)
    {
        global $_ARRAYLANG;

        $obj_file = new File();
        if (is_dir($this->path.$file)) {
            $this->dirLog=$obj_file->delDir($this->path, $this->webPath, $file);
            if ($this->dirLog != "error") {
                $status = $_ARRAYLANG['TXT_MEDIA_MSG_DIR_DELETE'];
            } else {
                $status = $_ARRAYLANG['TXT_MEDIA_MSG_ERROR_DIR'];
            }
         } else {
            if ($this->_isImage($this->path.$file)) {
                $thumb_name = ImageManager::getThumbnailFilename($file);
                if (file_exists($this->path.$thumb_name)) {
                    $this->dirLog=$obj_file->delFile($this->path, $this->webPath, $thumb_name);
                }
            }
            $this->dirLog=$obj_file->delFile($this->path, $this->webPath, $file);
            if ($this->dirLog != "error") {
                $status = $_ARRAYLANG['TXT_MEDIA_MSG_FILE_DELETE'];
            } else {
                $status = $_ARRAYLANG['TXT_MEDIA_MSG_ERROR_FILE'];
            }
        }
        return $status;
    }


    function _renMedia()
    {
        global $_ARRAYLANG, $objTemplate;

        $obj_file = new File();
        // file or dir
        if (empty($_POST['oldExt'])) {
            $fileName = $_POST['renName'];
            $oldName  = $_POST['oldName'];
        } else {
// TODO: $_POST['renName'] may be empty
            $ext      =
                (   !empty($_POST['renExt'])
                 && FWValidator::is_file_ending_harmless(
                        $_POST['renName'].$_POST['renExt'])
                    ? $_POST['renExt'] : 'txt');
            $fileName = $_POST['renName'].'.'.$ext;
            $oldName  = $_POST['oldName'].'.'.$_POST['oldExt'];
        }

        if (!isset($_POST['mediaInputAsCopy']) || $_POST['mediaInputAsCopy'] != 1) {
            // rename old to new
            if (is_dir($this->path.$oldName)) {
                $this->dirLog=$obj_file->renameDir($this->path, $this->webPath, $oldName, $fileName);
                if ($this->dirLog == "error") {
                    $objTemplate->setVariable('CONTENT_STATUS_MESSAGE',$_ARRAYLANG['TXT_MEDIA_MSG_ERROR_EDIT']);
                } else {
                    $this->highlightName[] = $this->dirLog;
                    $objTemplate->setVariable('CONTENT_OK_MESSAGE',$_ARRAYLANG['TXT_MEDIA_MSG_EDIT']);
                }
            } else {
                $this->dirLog=$obj_file->renameFile($this->path, $this->webPath, $oldName, $fileName);
                if ($this->dirLog == "error") {
                    $objTemplate->setVariable('CONTENT_STATUS_MESSAGE',$_ARRAYLANG['TXT_MEDIA_MSG_ERROR_EDIT']);
                } else {
                    $this->highlightName[] = $this->dirLog;
                    $objTemplate->setVariable('CONTENT_OK_MESSAGE',$_ARRAYLANG['TXT_MEDIA_MSG_EDIT']);
                }
            }
        } elseif (isset($_POST['mediaInputAsCopy']) && $_POST['mediaInputAsCopy'] == 1) {
            // copy old to new
            if (is_dir($this->path.$oldName)) {
                $this->dirLog=$obj_file->copyDir($this->path, $this->webPath, $oldName, $this->path, $this->webPath, $fileName);
                if ($this->dirLog == "error") {
                    $objTemplate->setVariable('CONTENT_STATUS_MESSAGE',$_ARRAYLANG['TXT_MEDIA_MSG_ERROR_EDIT']);
                } else {
                    $this->highlightName[] = $this->dirLog;
                     $objTemplate->setVariable('CONTENT_OK_MESSAGE',$_ARRAYLANG['TXT_MEDIA_MSG_EDIT']);
                }
            } else {
                $this->dirLog=$obj_file->copyFile($this->path, $oldName, $this->path, $fileName);
                if ($this->dirLog == "error") {
                    $objTemplate->setVariable('CONTENT_STATUS_MESSAGE',$_ARRAYLANG['TXT_MEDIA_MSG_ERROR_EDIT']);
                } else {
                    $this->highlightName[] = $this->dirLog;
                     $objTemplate->setVariable('CONTENT_OK_MESSAGE',$_ARRAYLANG['TXT_MEDIA_MSG_EDIT']);
                }
            }
        }

        // resize image
        if (isset($_POST['editImage']) && $_POST['editImage'] == 1) {
            if (isset($_POST['imgWidthPx']) && !empty($_POST['imgWidthPx']) && isset($_POST['imgHeightPx']) && !empty($_POST['imgWidthPx']) && !empty($_POST['imgQuality'])) {
                $this->_objImage->loadImage($this->path.$this->dirLog);
                $this->_objImage->resizeImage($_POST['imgWidthPx'], $_POST['imgHeightPx'], $_POST['imgQuality']);
                //unlink($this->path.$this->dirLog);
                $this->_objImage->saveNewImage($this->path.$this->dirLog, true);
            }
        }
    }


    // check if is image
    function _isImage($file)
    {
        $img  = @getimagesize($file);
        $type = $img[2];
        if ($type >= 1 && $type <= 3) {
            // 1 = gif, 2 = jpg, 3 = png
            return $type;
        }
        return false;
    }


    // creates an image thumbnail
    function _createThumbnail($file)
    {
        global $_ARRAYLANG;

        $tmpSize    = getimagesize($file);
        $thumbWidth = $this->thumbHeight / $tmpSize[1] * $tmpSize[0];
        $thumb_name = ImageManager::getThumbnailFilename($file);

        $tmp = new ImageManager();
        $tmp->loadImage($file);
        $tmp->resizeImage($thumbWidth, $this->thumbHeight, $this->thumbQuality);
        $tmp->saveNewImage($thumb_name);

        if (!file_exists($thumb_name)) {
            $img     = imagecreate(100, 50);
            $colBody = imagecolorallocate($img, 255, 255, 255);
            ImageFilledRectangle($img, 0, 0, 100, 50, $colBody);
            $colFont = imagecolorallocate($img, 0, 0, 0);
            imagettftext($img, 10, 0, 18, 29, $colFont, $this->iconPath.'arial.ttf', 'no preview');
            imagerectangle($img, 0, 0, 99, 49, $colFont);
            imagejpeg($img, $thumb_name, $this->thumbQuality);
        }
        chmod($thumb_name, $this->chmodFile);
    }

    // check for manual input in $_GET['path']
    function _pathCheck($path) {
        $check = false;
        if (!empty($path)) {
            foreach ($this->arrWebPaths as $tmp) {
                if (substr($path, 0, strlen($tmp)) == $tmp && file_exists($this->docRoot.$path)) {
                    $check = true;
                }
            }
        }
        if (empty($path) || $check == false) {
            $path = $this->arrWebPaths[$this->archive];
        }
        if (substr($path, -1) != '/') {
            $path = $path.'/';
        }
        return $path;
    }


    // makes the dir tree with variables: icon, name, size, type, date, perm
    function _dirTree($path)
    {
        $dir  = array();
        $file = array();
        $forbidden_files = array('.', '..', '.svn', '.htaccess', 'index.php');

        if (is_dir($path)) {
            $fd = @opendir($path);
            $name = @readdir($fd);
            while ($name !== false) {
                if (!in_array($name, $forbidden_files)) {
                    if (is_dir($path.$name)) {
                        $dir['icon'][] = $this->_getIcon($path.$name);
                        $dir['name'][] = $name;
                        $dir['size'][] = $this->_getSize($path.$name);
                        $dir['type'][] = $this->_getType($path.$name);
                        $dir['date'][] = $this->_getDate($path.$name);
                        $dir['perm'][] = $this->_getPerm($path.$name);
                    } elseif (is_file($path.$name)) {
// TODO
// This won't work for .jpg thumbnails made from .png images and other
// ways to create thumbnail file names.  See the Image class.
                        if (substr($name, -6) == '.thumb') {
                            $tmpName = substr($name, 0, strlen($name) - strlen(substr($name, -6)));
                            if (!file_exists($path.$tmpName)) {
                                @unlink($path.$name);
                            }
                        } else {
                            $file['icon'][] = $this->_getIcon($path.$name);
                            $file['name'][] = $name;
                            $file['size'][] = $this->_getSize($path.$name);
                            $file['type'][] = $this->_getType($path.$name);
                            $file['date'][] = $this->_getDate($path.$name);
                            $file['perm'][] = $this->_getPerm($path.$name);
                        }
                    }
                }
                $name = @readdir($fd);
            }
            @closedir($fd);
            clearstatcache();
        }
        $dirTree['dir']  = $dir;
        $dirTree['file'] = $file;
        return $dirTree;
    }


    function _sortDirTree($tree)
    {
        $d    = $tree['dir'];
        $f    = $tree['file'];
        $direction = $this->sortDesc ? SORT_DESC : SORT_ASC;

        switch ($this->sortBy) {
            // sort by size
            case 'size':
                @array_multisort($d['size'], $direction, $d['name'], $d['type'], $d['date'], $d['perm'], $d['icon']);
                @array_multisort($f['size'], $direction, $f['name'], $f['type'], $f['date'], $f['perm'], $f['icon']);
                break;
            // sort by type
            case 'type':
                @array_multisort($d['type'], $direction, $d['name'], $d['size'], $d['date'], $d['perm'], $d['icon']);
                @array_multisort($f['type'], $direction, $f['name'], $f['size'], $f['date'], $f['perm'], $f['icon']);
                break;
            //sort by date
            case 'date':
                @array_multisort($d['date'], $direction, $d['name'], $d['size'], $d['type'], $d['perm'], $d['icon']);
                @array_multisort($f['date'], $direction, $f['name'], $f['size'], $f['type'], $f['perm'], $f['icon']);
                break;
            //sort by perm
            case 'perm':
                $direction = !$this->sortDesc ? SORT_DESC : SORT_ASC;
                @array_multisort($d['perm'], $direction, $d['name'], $d['size'], $d['type'], $d['date'], $d['icon']);
                @array_multisort($f['perm'], $direction, $f['name'], $f['size'], $f['type'], $f['date'], $f['icon']);
                break;
            // sort by name
            case 'name':
            default:
                @array_multisort($d['name'], $direction, $d['size'], $d['type'], $d['date'], $d['perm'], $d['icon']);
                @array_multisort($f['name'], $direction, $f['size'], $f['type'], $f['date'], $f['perm'], $f['icon']);
                break;
        }
        $dirTree['dir']  = $d;
        $dirTree['file'] = $f;
        return $dirTree;
    }


    // designs the sorting icons
    function _sortingIcons()
    {
        $icon         = array(
            'size'    => null,
            'type'    => null,
            'date'    => null,
            'perm'    => null,
            'name'    => null
        );
        $icon1        = '&darr;';     // sort desc
        $icon2        = '&uarr;';     // sort asc
        switch($this->sortBy) {
            case 'size':
                $icon['size'] = $this->sortDesc ? $icon1 : $icon2;
                break;
            case 'type':
                $icon['type'] = $this->sortDesc ? $icon1 : $icon2;
                break;
            case 'date':
                $icon['date'] = $this->sortDesc ? $icon1 : $icon2;
                break;
            case 'perm':
                $icon['perm'] = $this->sortDesc ? $icon1 : $icon2;
                break;
            default:
                $icon['name'] = $this->sortDesc ? $icon1 : $icon2;
        }
        return $icon;
    }


    // designs the sorting class
    function _sortingClass()
    {
        $class         = array(
            'size'    => null,
            'type'    => null,
            'date'    => null,
            'perm'    => null,
            'name'    => null
        );
        $class1        = 'sort';     // sort desc
        $class2        = 'sort';     // sort asc

        switch($this->sortBy) {
            case 'size':
                $class['size'] = $this->sortDesc ? $class1 : $class2;
                break;
            case 'type':
                $class['type'] = $this->sortDesc ? $class1 : $class2;
                break;
            case 'date':
                $class['date'] = $this->sortDesc ? $class1 : $class2;
                break;
            case 'perm':
                $class['perm'] = $this->sortDesc ? $class1 : $class2;
                break;
            default:
                $class['name'] = $this->sortDesc ? $class1 : $class2;
        }
        return $class;
    }


    // gets the icon for the file
    function _getIcon($file)
    {
        if (is_file($file)) {
            $info = pathinfo($file);
            $icon = strtolower($info['extension']);
        }
        if (is_dir($file)) {
            $icon = '_folder';
        }
        if (!file_exists($this->iconPath.$icon.'.gif') or !isset($icon)) {
            $icon = '_blank';
        }
        return $icon;
    }


    // gets the filesize
    function _getSize($file)
    {
        if (is_file($file)) {
            if (@filesize($file)) {
                $size = filesize($file);
            }
        }
        if (is_dir($file)) {
            $size = '[folder]';
        }
        (!isset($size) or empty($size)) ? $size = '0' : '';
        return $size;
    }


    // formats the filesize
    function _formatSize($size)
    {
        $multi = 1024;
        $divid = 1000;
        $arrEnd = array(' Byte', ' Bytes', ' KB', ' MB', ' GB');
        if ($size != '[folder]') {
            if ($size >= ($multi * $multi * $multi)) {
                $size = round($size / ($multi * $multi * $multi), 2);
                $end  = 4;
            }
            elseif ($size >= ($multi * $multi)) {
                $size = round($size / ($multi * $multi), 2);
                $end  = 3;
            }
            elseif ($size >= $multi) {
                $size = round($size / $multi, 2);
                $end  = 2;
            }
            elseif ($size < $multi && $size > 1) {
                $size = $size;
                $end  = 1;
            } else {
                $size = $size;
                $end  = 0;
            }
            if ($size >= $divid) {
                $size = round($size / $multi, 2);
                $end  = $end + 1;
            }
            $size = $size.$arrEnd[$end];
        } else {
            $size = '-';
        }
        return $size;
    }


    // gets the filetype
    function _getType($file)
    {
        if (is_file($file)) {
            $info = pathinfo($file);
            $type = strtoupper($info['extension']);
        }
        if (is_dir($file)) {
            $type = '[folder]';
        }
        (!isset($type) or empty($type)) ? $type = '-' : '';
        return $type;
    }


    // formats the filetype
    function _formatType($type)
    {
        global $_ARRAYLANG;

        if ($type != '-' && $type != '[folder]') {
            $type = $type.'-'.$_ARRAYLANG['TXT_MEDIA_FILE'];
        } elseif ($type == '[folder]') {
            $type = $_ARRAYLANG['TXT_MEDIA_FILE_DIRECTORY'];
        }
        return $type;
    }


    // gets the date of the last modification of the file
    function _getDate($file)
    {
        if (@filectime($file)) {
            $date = filectime($file);
        }
        if (!isset($file)) {
            $date = '';
        }
        return $date;
    }


    // formats the date of the last modification of the file
    function _formatDate($date)
    {
        if (!empty($date)) {
            $date = date(ASCMS_DATE_FILE_FORMAT, $date);
        } else {
            $date = '-';
        }
        return $date;
    }


    // gets the permission of the file
    function _getPerm($file)
    {
        if (@fileperms($file)) {
            $perm = substr(decoct(fileperms($file)), -4);
        }
        if (!isset($perm)) {
            $perm = '';
        }
        return $perm;
    }


    // formats the permission of the file
    function _formatPerm($perm, $key)
    {
        if (!empty($perm)) {
            $per   = array();
            $per[] = $perm[1];
            $per[] = $perm[2];
            $per[] = $perm[3];

            ($key == 'dir')  ? $perm = 'd'       : '';
            ($key == 'file') ? $perm = '&minus;' : '';
            foreach ($per as $out) {
                switch($out) {
                    case 7:
                        $perm .= ' rwx';
                        break;
                    case 6:
                        $perm .= ' rw&minus;';
                        break;
                    case 5:
                        $perm .= ' r&minus;x';
                        break;
                    case 4:
                        $perm .= ' r&minus;&minus;';
                        break;
                    case 3:
                        $perm .= ' &minus;wx';
                        break;
                    case 2:
                        $perm .= ' &minus;w&minus;';
                        break;
                    case 1:
                        $perm .= ' &minus;&minus;x';
                        break;
                    default:
                        $perm .= ' &minus;&minus;&minus;';
                }
            }
        } else {
            $perm = '-';
        }
        return $perm;
    }


    function _getJavaScriptCodePreview()
    {
        $code = <<<END
<script language="JavaScript" type="text/javascript">
  function preview(file, width, height)
  {
    var f = file;
    var w = width + 10;
    var h = height + 10;
    var l = (screen.availWidth - width) / 2;
    var t = (screen.availHeight - 50 - height) / 2;
    prev  = window.open('', '', "width="+w+", height="+h+", left="+l+", top="+t+", scrollbars=no, toolbars=no, status=no, resizable=yes");
    prev.document.open();
    prev.document.write('<html><title>'+f+'<\\/title><body style="margin: 5px; padding: 0px;">');
    prev.document.write('<img src=\\"'+f+'\\" width='+width+' height='+height+' alt=\\"'+f+'\\">');
    prev.document.write('<\\/body><\\/html>');
    prev.document.close();
    prev.focus();
  }
</script>
END;
        return $code;
    }

    /**
     * this is called as soon as uploads have finished.
     * takes care of moving them to the right folder
     * 
     * @return string the directory to move to
     */
    public static function uploadFinished($tempPath, $data, $uploadId){
        $path = $data['path'];
        $webPath = $data['webPath'];

        //we remember the names of the uploaded files here. they are stored in the session afterwards,
        //so we can later display them highlighted.
        $arrFiles = array(); 
        
        //rename files, delete unwanted
        $arrFilesToRename = array(); //used to remember the files we need to rename
        $h = opendir($tempPath);
        while(false !== ($file = readdir($h))) {
            //delete potentially malicious files
            if(!FWValidator::is_file_ending_harmless($file)) {
                @unlink($file);
                continue;
            }
            //skip . and ..           
            if($file == '.' || $file == '..')
                continue;

			//clean file name
            $newName = self::cleanFileName($nameToEscape);

            $newName = '';
            //check if file needs to be renamed
            if (file_exists($path.$file)) {
                $info     = pathinfo($file);
                $exte     = $info['extension'];
                $exte     = (!empty($exte)) ? '.'.$exte : '';
                $part1    = $info['filename'];
                if (empty($_REQUEST['uploadForceOverwrite']) || !intval($_REQUEST['uploadForceOverwrite'] > 0)) {
                    $newName = $part1.'_'.time().$exte;
                }
            }
 
            //if the name has changed, the file needs to be renamed afterwards
            if($newName != $file)
                $arrFilesToRename[$file] = $newName;

            array_push($arrFiles, $newName);
        }
        //rename files where needed
        foreach($arrFilesToRename as $oldName => $newName){
            rename($tempPath.'/'.$oldName, $tempPath.'/'.$newName);
        }

        //remeber the uploaded files
        $_SESSION["media_upload_files_$uploadId"] = $arrFiles;

        /* unwanted files have been deleted, unallowed filenames corrected.
           we can now simply return the desired target path, as only valid
           files are present in $tempPath                                   */
	 
        return array($data['path'],$data['webPath']);
    }

    // replaces some characters
    protected static function cleanFileName($string)
    {
        //contrexx file name policies
        $string = FWValidator::getCleanFileName($string);

        //media library special changes; code depends on those
        // replace $change with ''
        $change = array('+');
        // replace $signs1 with $signs
        $signs1 = array(' ', 'ä', 'ö', 'ü', 'ç');
        $signs2 = array('_', 'ae', 'oe', 'ue', 'c');

        foreach ($change as $str) {
            $string = str_replace($str, '_', $string);
        }
        for ($x = 0; $x < count($signs1); $x++) {
            $string = str_replace($signs1[$x], $signs2[$x], $string);
        }

        $string = str_replace('__', '_', $string);
        if (strlen($string) > 60) {
            $info       = pathinfo($string);
            $stringExt  = $info['extension'];

            $stringName = substr($string, 0, strlen($string) - (strlen($stringExt) + 1));
            $stringName = substr($stringName, 0, 60 - (strlen($stringExt) + 1));
            $string     = $stringName.'.'.$stringExt;
        }
        return $string;
    }

}
?>