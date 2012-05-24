<?php
namespace Cx\Lib\FileSystem;
/**
 * File System File
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @version     3.0.0
 * @package     contrexx
 * @subpackage  lib_framework_file
 */

/**
 * @ignore
 */
require_once ASCMS_FRAMEWORK_PATH.'/File/File.interface.php';

class FileSystemFileException extends \Exception {};

/**
 * File System File
 *
 * This class provides an object based interface to a file that resides 
 * on the local file system.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @version     3.0.0
 * @package     contrexx
 * @subpackage  lib_framework_file
 */
class FileSystemFile implements FileInterface
{
    private $filePath = null;

    /**
     * Create a new FileSystemFile object that acts as an interface to
     * a file located on the local file system.
     *
     * @param   string Path to file on local file system.
     */
    public function __construct($file)
    {
        if (empty($file)) {
            throw new FileSystemFileException('No file path specified!');
        }

        if (strpos($file, ASCMS_DOCUMENT_ROOT) === 0) {
            $this->filePath = $file;
        } elseif (strpos($file, ASCMS_PATH_OFFSET) === 0) {
            $this->filePath = ASCMS_PATH.$file;
        } elseif (strpos($file, '/') === 0) {
            $this->filePath = ASCMS_DOCUMENT_ROOT.$file;
        } else {
            $this->filePath = ASCMS_DOCUMENT_ROOT.'/'.$file;
        }
    }

    public function write($data)
    {
        // first try 
        $fp = @fopen($this->filePath, 'r+');
        if (!$fp) {
            // try to set write access
            $this->makeWritable($this->filePath);
        }

        // second try 
        $fp = @fopen($this->filePath, 'r+');
        if (!$fp) { 
            throw new FileSystemFileException('Unable to open file '.$this->filePath.' for writting!');
        }

        // acquire exclusive file lock
        flock($fp, LOCK_EX);

        // write data to file
        $writeStatus = fwrite($fp, $data);

        // release exclusive file lock
        flock($fp, LOCK_UN);
        if ($writeStatus === false) {
            throw new FileSystemFileException('Unable to write data to file '.$this->filePath.'!');
        }
    }

    public function touch()
    {
        if (!touch($this->filePath)) {
            throw new FileSystemFileException('Unable to touch file in file system!');
        }
    }

    public function getFilePermissions()
    {
        // fetch current permissions on loaded file
        $filePerms = fileperms($this->filePath);
        if ($filePerms === false) {
            throw new FileSystemFileException('Unable to fetch file permissions on file '.$this->filePath.'!');
        }

        // Strip BITs that are not related to the file permissions.
        // Only the first 9 BITs are related (i.e: rwxrwxrwx) -> bindec(111111111) = 511
        $filePerms = $filePerms & 511;

        return $filePerms;
    }

    public function makeWritable()
    {
        // fetch current permissions on loaded file
        $filePerms = $this->getFilePermissions();

        // set write access to file owner
        $filePerms |= \Cx\Lib\FileSystem::CHMOD_USER_WRITE;

        // log file permissions into the humand readable chmod() format
        \DBG::msg('CHMOD: '.substr(sprintf('%o', $filePerms), -4));

        if (!@chmod($this->filePath, $filePerms)) {
            throw new FileSystemFileException('Unable to set write access to file '.$this->filePath.'!');
        }
    }

    public function delete()
    {
        if (!unlink($this->filePath)) {
            throw new FileSystemFileException('Unable to delete file '.$this->filePath.'!');
        }
    }
}

