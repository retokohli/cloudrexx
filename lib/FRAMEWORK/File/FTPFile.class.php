<?php
namespace Cx\Lib\FileSystem;
/**
 * FTP File
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

class FTPFileException extends \Exception {};

/**
 * FTP File
 *
 * This class provides an object based interface to a file located 
 * on an FTP server.
 * In general, do no use this class. Instead use the class Cx\Lib\FileSystem\File
 * which is a wrapper that uses either this class or
 * Cx\Lib\FileSystem\FileSystemFile for file operations, depending on the
 * system configuration.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @version     3.0.0
 * @package     contrexx
 * @subpackage  lib_framework_file
 */
class FTPFile implements FileInterface
{
    private $file = null;
    private $filePath = null;
    private $passedFilePath = null;
    private $connection = null;
    private $connected = false;
    private $tempFileHandler = null;
    private $tempFile = null;
    private $ftpConfig = null;

    /**
     * Create a new FTPFile object that acts as an interface to
     * a file located on the FTP server.
     *
     * @param   string  Path to file on FTP server.
     * @param   array   FTP configuration array.
     */
    public function __construct($file, $ftpConfig = null)
    {
        if (empty($file)) {
            throw new FTPFileException('No file path specified!');
        }

        if (isset($ftpConfig)) {
            $this->ftpConfig = $ftpConfig;
        } else {
            $this->ftpConfig = \Env::get('ftpConfig');
        }

        $this->passedFilePath = $file;
        $pathInfo = pathinfo($file);
        $this->file = $pathInfo['basename'];
        $path = $pathInfo['dirname'];

        if (strpos($path, ASCMS_PATH) === 0) {
            $this->filePath = $this->ftpConfig['path'].substr($path, strlen(ASCMS_PATH));
        } elseif (strpos($path, ASCMS_PATH_OFFSET) === 0) {
            $this->filePath = $this->ftpConfig['path'].$path;
        } elseif (strpos($path, '/') === 0) {
            $this->filePath = $this->ftpConfig['path'].ASCMS_PATH_OFFSET.$path;
        } else {
            $this->filePath = $this->ftpConfig['path'].ASCMS_PATH_OFFSET.'/'.$path;
        }
    }

    /**
     * Writes data specified by $data to the file the object had been initialized with.
     * @param   string  $data
     */
    public function write($data)
    {
        $this->initConnection();
        $this->writeToTempFile($data);
        $this->uploadTempFile();
        $this->deleteTempFile();
    }

    public function touch()
    {
        $this->write('');
    }

    public function makeWritable()
    {
        $this->initConnection();

        // fetch current permissions on loaded file through FileSystemFile object
        try {
            $objFile = new \Cx\Lib\FileSystem\FileSystemFile($this->passedFilePath);
            $filePerms = $objFile->getFilePermissions();
        } catch (FileSystemFileException $e) {
            throw new FTPFileException($e->getMessage());
        }

        // set write access to file owner
        $filePerms |= \Cx\Lib\FileSystem::CHMOD_USER_WRITE;

        // log file permissions into the humand readable chmod() format
        \DBG::msg('CHMOD: '.substr(sprintf('%o', $filePerms), -4));

        if (!ftp_chmod($this->connection, $filePerms, $this->filePath.'/'.$this->file)) {
            throw new FTPFileException('Unable to set write access to file '.$this->filePath.'/'.$this->file.'!');
        }
    }

    public function delete()
    {
        $this->initConnection();

        if (!ftp_delete($this->connection, $this->filePath.'/'.$this->file)) {
            throw new FTPFileException('Unable to delete file '.$this->filePath.'/'.$this->file.'!');
        }
    }

    private function uploadTempFile()
    {
        // navigate to specified directory on FTP server
        /*ftp_chdir($this->connection, '/');
        foreach (explode('/', $this->filePath) as $dir) {
            if (!empty($dir)) {
                ftp_chdir($this->connection, $dir);
            }
        }

        if ($this->filePath != ftp_pwd($this->connection)) {
            throw new FTPFileException('Unable to navigation into directory '.$this->filePath.' on FTP server');
        }*/

        ftp_set_option($this->connection, FTP_TIMEOUT_SEC, 600);
        rewind($this->tempFileHandler);
        if (!ftp_fput($this->connection, $this->filePath.'/'.$this->file, $this->tempFileHandler, FTP_BINARY)) {
            throw new FTPFileException('FTP upload of file '.$this->file.' to directory '.$this->filePath.' failed !');
        }
    }

    private function openTempFileHandler()
    {
        global $sessionObj;

        // try memory first
        if (($this->tempFileHandler = fopen("php://memory", 'r+')) === false) {
            // unable to use memory as temporary storage location,
            // try to create file in the session temp path 
            if (empty($sessionObj)) { //session hasn't been initialized so far
                $sessionObj = new cmsSession();
            }
            $sessionTempPath = $sessionObj->getTempPath();
            $pathInfo = pathinfo($this->file);
            $tempFile = $sessionTempPath.'/'.$pathInfo['basename'];
            $idx = 1;
            while (file_exists($tempFile)) {
                $tempFile = $sessionTempPath.'/'.$pathInfo['filename'].$idx++.$pathInfo['extension'];
            }

            if (($this->tempFileHandler = fopen($tempFile, 'r+')) === false) {
                return false;
            }

            // remember tempFile, we will have to delete it after it fullfilled its purpose
            $this->tempFile = $tempFile;
        }

        return true;
    }

    private function deleteTempFile()
    {
        fclose($this->tempFileHandler);

        if (!empty($this->tempFile)) {
            unlink($this->tempFile);
            $this->tempFile = null;
        }
    }

    private function writeToTempFile($data)
    {
        if (!$this->openTempFileHandler()) {
            throw new FTPFileException('Unable to create a temporary file used to buffer the file data!');
        }

        rewind($this->tempFileHandler);
        if (fwrite($this->tempFileHandler, $data) === false) {
            throw new FTPFileException('Unable to write the data to the temporary file!');
        }
    }

    private function initConnection()
    {
        if ($this->connected) {
            return;
        }

        if (!$this->ftpConfig['is_activated']) {
            throw new FTPFileException('No FTP support on this system!');
        }

        $this->connection = ftp_connect($this->ftpConfig['host']);
        if (!$this->connection) {
            throw new FTPFileException('Unable to establish FTP connection. Probably wrong FTP host info specified in config/configuration.php');
        }
    
        if (!ftp_login($this->connection, $this->ftpConfig['username'], $this->ftpConfig['password'])) {
            throw new FTPFileException('Unable to authenticate on FTP server. Probably wrong FTP login credentials specified in config/configuration.php');
        }
    
        $this->connected = true;
    }
}

