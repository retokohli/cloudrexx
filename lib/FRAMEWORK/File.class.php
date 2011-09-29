<?php

/**
 * File Manager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Janik Tschanz <janik.tschanz@comvation.com>
 * @version     $Id:  Exp $
 * @package     contrexx
 * @subpackage  lib_framework
 * @todo        Edit PHP DocBlocks!
 */

/**
 * File Manager
 *
 * Manages files and folders
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Janik Tschanz <janik.tschanz@comvation.com>
 * @version     $Id:  Exp $
 * @package     contrexx
 * @subpackage  lib_framework
 */
class File
{
    public $conn_id;                  // current Connections ID
    public $login_result;             // current Login

    public $ftp_is_activated;         // FTP is activated ( true/false )
    public $ftpHost;                  // FTP Host
    public $ftpUserName;              // FTP User Name
    public $ftpUserPass;              // FTP Password
    public $ftpDirectory;             // FTP start directory (htdocs)

    public $chmodFolder     = 0777;   // chmod for folder 0777
    public $chmodFile       = 0666;   // chmod for files  0644,0766

    public $saveMode;                 // save_mode is true/false

    /**
     * Creates a new File helper object. Uses FTP if configured,
     * direct file access otherwise.
     */
    function __construct()
    {
        global  $_FTPCONFIG;

        $this->ftp_is_activated = $_FTPCONFIG['is_activated'];
        $this->ftpHost = $_FTPCONFIG['host'];
        $this->ftpUserName = $_FTPCONFIG['username'];
        $this->ftpUserPass = $_FTPCONFIG['password'];
        $this->ftpDirectory = $_FTPCONFIG['path'];
        $this->saveMode = @ini_get('safe_mode');

        if ($this->ftp_is_activated == true) {
            $this->conn_id = ftp_connect($this->ftpHost);
        }
        if ($this->conn_id) {
            //logon with user and password
            $this->login_result = ftp_login($this->conn_id, $this->ftpUserName, $this->ftpUserPass);
        }
        else {
            // We can't connect to FTP, so we try
            // falling back to "normal" file mode.
            //FIXME: notify user in a useful manner.
            $this->ftp_is_activated = false;
        }

        $this->checkConnection();
    }


    function checkConnection()
    {
        //check connection
        if ((!$this->conn_id) || (!$this->login_result)) {
            $status = 'FTP: disabled - ';
            $this->ftp_is_activated = false;
        } else {
            $status = 'FTP: enabled - ';
        }

        if ($this->saveMode == true) {
            $status .= 'SAVEMODE: on';
        } else {
            $status .= 'SAVEMODE: off';
        }

        return $status;
    }



    function copyDir($orgPath, $orgWebPath, $orgDirName, $newPath, $newWebPath, $newDirName, $ignoreExists = false) {
        $orgWebPath=$this->checkWebPath($orgWebPath);
        $newWebPath=$this->checkWebPath($newWebPath);

        if (file_exists($newPath.$newDirName) && !$ignoreExists) {
            $newDirName = $newDirName.'_'.time();
        }

        $status = $this->mkDir($newPath, $newWebPath, $newDirName);

        if ($status!= 'error') {
            $openDir = opendir($orgPath.$orgDirName);
            $file = readdir($openDir);
            while ($file) {
                if ($file!='.' && $file!='..') {
                    if (!is_dir($orgPath.$orgDirName.'/'.$file)) {
                            $this->copyFile($orgPath, $orgDirName.'/'.$file, $newPath, $newDirName.'/'.$file);
                    } else {
                        $this->copyDir($orgPath, $orgWebPath, $orgDirName.'/'.$file, $newPath, $newWebPath, $newDirName.'/'.$file);
                    }
                }
                $file = readdir($openDir);
            }
            closedir($openDir);
        }
        return $status;
    }



    function copyFile($orgPath, $orgFileName, $newPath, $newFileName, $ignoreExists = false) {
        if (file_exists($newPath.$newFileName)) {
            $info   = pathinfo($newFileName);
            $exte   = $info['extension'];
            $exte   = (!empty($exte)) ? '.' . $exte : '';
            $part   = substr($newFileName, 0, strlen($newFileName) - strlen($exte));
            if (!$ignoreExists) {
                $newFileName  = $part . '_' . (time()) . $exte;
            }
        }
        if (!copy($orgPath.$orgFileName, $newPath.$newFileName)) {
            $status = 'error';
        } else {
            $this->setChmod($newPath, str_replace(ASCMS_PATH,'', $newPath), $newFileName);
            $status = $newFileName;
        }

        return $status;
    }

    /**
     * Touch a file
     *
     * Creates a new empty file on the server.
     *
     * @param string Name (inkl. offset path) of the file to create. E.g. $file = '/sitemap.xml'
     * @return bolean
     */
    function touchFile($file)
    {
        $status = false;
        $fp = fopen('php://memory', 'w+');
        if ($fp) {
            if (ftp_fput($this->conn_id, $this->ftpDirectory.$file, $fp, FTP_ASCII)) {
                $status = true;
            }
            fclose($fp);
        }

        return $status;
    }


    function mkDir($path, $webPath, $dirName) {
        $webPath=$this->checkWebPath($webPath);

        if (file_exists($path.$dirName)) {
            $dirName = $dirName.'_'.time();
        }

        $newDir = $this->ftpDirectory.$webPath.$dirName;

        $status = '';
        if ($this->ftp_is_activated == true) {
            ftp_mkdir($this->conn_id, $newDir);

            ftp_chmod($this->conn_id, $this->chmodFolder, $newDir);
            $status = $dirName;
        } else {
            if (mkdir($path.$dirName)) {
                chmod ($path.$dirName, $this->chmodFolder);
                $status = $dirName;
            } else {
                $status = 'error';
            }
        }
        return $status;
    }




    function delDir($path, $webPath, $dirName) {
        $webPath=$this->checkWebPath($webPath);
        $status = '';
        $openDir = opendir($path.$dirName);
        $file = readdir($openDir);
        while ($file) {
            if ($file!='.' && $file!='..') {
                if ($this->ftp_is_activated == true) {
                    if (!is_dir($path.$dirName.'/'.$file)) {
                        ftp_delete($this->conn_id, $this->ftpDirectory.$webPath.$dirName.'/'.$file);
                    } else {
                        $this->delDir($path, $webPath, $dirName.'/'.$file);
                    }
                } else {
                    if (!is_dir($path.$dirName.'/'.$file)) {
                        $this->delFile($path, $webPath, $dirName.'/'.$file);
                    } else {
                        $this->delDir($path, $webPath, $dirName.'/'.$file);
                    }
                }
            }
            $file = readdir($openDir);
        }
        closedir($openDir);

        if ($this->ftp_is_activated == true) {
            if (!ftp_rmdir($this->conn_id,  $this->ftpDirectory.$webPath.$dirName)) {
                $status = 'error';
            }
        } else {
            if (!rmdir($path.$dirName.'/'.$file)) {
                $status = 'error';
            }
        }

        return $status;
    }



    function delFile($path, $webPath, $fileName)
    {
        $webPath = $this->checkWebPath($webPath);
        $delFile = $this->ftpDirectory.$webPath.$fileName;
        if ($this->ftp_is_activated) {
            if (ftp_delete($this->conn_id, $delFile)) return $delFile;
            return 'error';
        } else {
            //@unlink($path.$fileName);
            unlink($path.$fileName);
            clearstatcache();
            if (file_exists($path.$fileName)) {
                $filesys = eregi_replace('/', '\\', $path.$fileName);
                system("del $filesys");
                clearstatcache();

                // Doesn't work in safe mode
                if (file_exists($path.$fileName)) {
                    chmod ($path.$fileName, 0775);
                    unlink($path.$fileName);
                    system("del $filesys");
                }
            }
            clearstatcache();
            if (file_exists($path.$fileName)) return 'error';
        }
        return $fileName;
    }



    function checkWebPath($webPath)
    {
        if ($this->ftpDirectory == '') {
            if (substr($webPath, 0, 1) == '/') {
                $webPath = substr($webPath, 1);
            } else {
                $webPath = $webPath;
            }
        } else {
            $webPath = $webPath;
        }

        return $webPath;
    }



    // replaces some characters
    function replaceCharacters($string) {
        // replace $change with ''
        $change = array('+', '¦', '"', '@', '*', '#', '°', '%', '§', '&', '¬', '/', '|', '(', '¢', ')', '=', '?', '\'', '´', '`', '^', '~', '!', '¨', '[', ']', '{', '}', '£', '$', '-', '<', '>', '\\', ';', ',', ':');
        // replace $signs1 with $signs
        $signs1 = array(' ', 'ä', 'ö', 'ü', 'ç');
        $signs2 = array('_', 'ae', 'oe', 'ue', 'c');

        $string = strtolower($string);
        foreach($change as $str) {
            $string = str_replace($str, '', $string);
        }
        for($x = 0; $x < count($signs1); $x++) {
            $string = str_replace($signs1[$x], $signs2[$x], $string);
        }
        $string = str_replace('__', '_', $string);

        if (strlen($string) > 40) {
            $info       = pathinfo($string);
            $stringExt  = $info['extension'];

            $stringName = substr($string, 0, strlen($string) - (strlen($stringExt) + 1));
            $stringName = substr($stringName, 0, 40 - (strlen($stringExt) + 1));
            $string     = $stringName . '.' . $stringExt;
        }

        return $string;
    }

    /**
     * Move a file or directory from a to b.
     * You can use this to rename files!
     *
     * @return boolean true on success
     */
    function moveFile($sourcePath, $sourceWebPath, $sourceName, $targetPath, $targetWebPath, $targetName = null) {
        //make sure we use the original name if no new name was provided
        if($targetName === null)
            $targetName = $sourceName;

        $sameDir = ($targetPath == $sourcePath) || ($sourceWebPath == $targetWebPath);
        $sameName = $sourceName == $targetName;

        //do nothing where nothing needs to be done.
        if($sameDir && $sameName)
            return true; //we do not count this as error

        $errorOccurred = false;
        if ($this->ftp_is_activated) {
          if(!ftp_rename($this->conn_id, $this->ftpDirectory.$sourceWebPath.$sourceName, $this->ftpDirectory.$targetWebPath.$targetName))
                $errorOccurred = true;
        }
        else { //no ftp
            if(!rename($sourcePath.$sourceName, $targetPath.$targetName))
                $errorOccurred = true;
        }

        return !$errorOccurred;
    }

    /**
     * Renames a file and leaves it in the same directory.
     * Use moveFile instead.
     *
     * @deprecated
     */
    function renameFile($path, $webPath, $oldFileName, $newFileName) {
        $webPath=$this->checkWebPath($webPath);

        if ($oldFileName != $newFileName) {
            if (file_exists($path.$newFileName)) {
                $info   = pathinfo($newFileName);
                $exte   = $info['extension'];
                $exte   = (!empty($exte)) ? '.' . $exte : '';
                $part   = substr($newFileName, 0, strlen($newFileName) - strlen($exte));
                $newFileName  = $part . '_' . (time()) . $exte;
            }

            if ($this->ftp_is_activated == true) {
                if (!ftp_rename($this->conn_id, $this->ftpDirectory.$webPath.$oldFileName, $this->ftpDirectory.$webPath.$newFileName)) {
                    $status = 'error';
                } else {
                    $status = $newFileName;
                }
            } else {
                if (!rename($path.$oldFileName, $path.$newFileName)) {
                    $status = 'error';
                } else {
                    $status = $newFileName;
                }
            }
        } else {
            $status = $oldFileName;
        }

        return $status;
    }

    function renameDir($path, $webPath, $oldDirName, $newDirName) {
        $webPath=$this->checkWebPath($webPath);

        if ($oldDirName != $newDirName) {
            if (file_exists($path.$newDirName)) {
                $newDirName = $newDirName;
            }

            if ($this->ftp_is_activated == true) {
                if (!ftp_rename($this->conn_id, $this->ftpDirectory.$webPath.$oldDirName, $this->ftpDirectory.$webPath.$newDirName)) {
                    $status = 'error';
                } else {
                    $status = $newDirName;
                }
            } else {
                if (!rename($path.$oldDirName, $path.$newDirName)) {
                    $status = 'error';
                } else {
                    $status = $newDirName;
                }
            }
        } else {
            $status = $oldDirName;
        }
        return $status;
    }



    function setChmod($path, $webPath, $fileName)
    {
        global $_FTPCONFIG;

        if (!file_exists($path.$fileName)) return false;
        if (is_dir($path.$fileName)) {
            if (chmod($path.$fileName, $this->chmodFolder)) return true;
            if ($this->ftp_is_activated) {
                return ftp_chmod($this->conn_id, $this->chmodFolder, $this->ftpDirectory.$webPath.$fileName);
            }
        } else {
            if (chmod($path.$fileName, $this->chmodFile)) return true;
            if ($this->ftp_is_activated) {
                return ftp_chmod($this->conn_id, $this->chmodFile, $this->ftpDirectory.$webPath.$fileName);
            }
        }
        return false;
    }


    /**
     * Moves an uploaded file to a specified path
     *
     * Returns true if the file exists, matches one of the accepted file types,
     * if any, is not too large, and can be moved successfully.
     * Mind that the target path *MUST NOT* include ASCMS_PATH,
     * but only the path relative to it.
     * @param   string  $upload_field_name  File input field name
     * @param   string  $target_path        Target path, relative to the document
     *                                      root, including a leading path separator
     *                                      and the file name
     * @param   integer $maximum_size       The optional maximum allowed file size
     * @param   array   $accepted_extensions  The optional array of allowed file
     *                                      extensions, without the dot
     * @return  boolean                     True on success, false otherwise
     */
    static function uploadFileHttp(
        $upload_field_name, $target_path,
        $maximum_size=0, $accepted_extensions=false)
    {
        if (   empty($upload_field_name)
            || empty($_FILES[$upload_field_name])
            || empty($target_path))
            return false;
        $tmp_path = $_FILES[$upload_field_name]['tmp_name'];
        if ($accepted_extensions) {
            $path_parts = pathinfo($tmp_path, PATHINFO_EXTENSION);
            if (!in_array($path_parts['extension'], $accepted_extensions))
                return false;
        }
        if ($maximum_size > 0 && filesize($tmp_path) > $maximum_size)
            return false;
        if (!move_uploaded_file($tmp_path, ASCMS_PATH.$target_path)) return false;
        return true;
    }


    /**
     * Takes the path given by reference and removes any leading
     * folders up to and including the ASCMS_PATH_OFFSET, including
     * path separators (\ and /).
     *
     * Important note: The regex used to cut away the excess path
     * is non-greedy and works fine in most cases.  However, there
     * is a small risk that it may go wrong if two things occur at the
     * same time, namely:
     * - the ASCMS_PATH_OFFSET is not part of the path provided, and
     * - the path contains a folder or file with the same name.
     * If this is the case, you can either change the offset or the
     * name of the subfolder or file, whichever is acceptable.
     * @param   string    $target_path    Any absolute or relative path
     * @return  void
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @since   2.2.0
     */
    static function pathRelativeToRoot(&$path)
    {
        // If ASCMS_PATH_OFFSET is empty this won't work, so test that
        if (!preg_match('/[\\\\\\/]\w+/', ASCMS_PATH_OFFSET)) {
            return;
        }
        $path = preg_replace(
            '/^(?:.*'.preg_quote(ASCMS_PATH_OFFSET, '/').')?[\\\\\\/]*/',
             '', $path);
    }


    /**
     * Determines the file size of a given file by $file and returns its size in bytes.
     *
     * @param   string The relative path to a file
     * @return  integer Filesize of $file in bytes
     */
    function getFileSizeInBytes($file)
    {
        $size = sprintf('%u', filesize(ASCMS_DOCUMENT_ROOT.'/'.$file));
        if ((!$size || $size > PHP_INT_MAX) && $this->ftp_is_activated) {
            $result = ftp_raw($this->conn_id, 'SIZE '.$this->ftpDirectory.ASCMS_PATH_OFFSET.'/'.$file);
            if (preg_match('/^213\s(.*)$/', $result[0], $match)) {
                $size = $match[1];
            }
        }

        return $size;
    }


    /**
     * Wrapper for file_exists()
     *
     * Prepends ASCMS_DOCUMENT_ROOT to the (relative) path.
     * @todo    Maybe clearstatcache() should be called first?
     * @param   string    $path     The file or folder path
     * @return  boolean             True if the file exists, false otherwise
     */
    static function exists($path)
    {
        return file_exists(ASCMS_DOCUMENT_ROOT.'/'.$path);
    }

}
