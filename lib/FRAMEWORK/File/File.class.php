<?php
namespace Cx\Lib\FileSystem;

/**
 * @ignore
 */
require_once ASCMS_FRAMEWORK_PATH.'/File/File.interface.php';
/**
 * @ignore
 */
require_once ASCMS_FRAMEWORK_PATH.'/File/FileSystemFile.class.php';
/**
 * @ignore
 */
require_once ASCMS_FRAMEWORK_PATH.'/File/FTPFile.class.php';

class FileException extends \Exception {};

class File implements FileInterface
{
    private $file = null;
    
    public function __construct($file)
    {
        $this->file = $file;     
    }

    public function getData()
    {
        $data = file_get_contents($this->file);
        if ($data === false || empty($data)) {
            throw new \Cx\Lib\FileSystemException('Unable to read data from file '.$this->file.'!');
        }

        return $data;
    }
    
    public function write($data)
    {
        try {
            // try regular file access first
            $fsFile = new FileSystemFile($this->file);
            $fsFile->write($data);
        } catch (FileSystemFileException $e) {
            \DBG::msg('FileSystemFile: '.$e->getMessage());

            // try ftp access as fall-back in case regular file access failed
            try {
                $ftpFile = new FTPFile($this->file);
                $ftpFile->write($data);
            } catch (FTPFileException $e) {
                \DBG::msg('FTPFile: '.$e->getMessage());
                throw new \Cx\Lib\FileSystemException('File: Unable to write data to file '.$this->file.'!');
            }
        }
    }

    public function touch()
    {
        try {
            // try regular file access first
            $fsFile = new FileSystemFile($this->file);
            $fsFile->touch();
        } catch (FileSystemFileException $e) {
            \DBG::msg('FileSystemFile: '.$e->getMessage());

            // try ftp access as fall-back in case regular file access failed
            try {
                $ftpFile = new FTPFile($this->file);
                $ftpFile->touch();
            } catch (FTPFileException $e) {
                \DBG::msg('FTPFile: '.$e->getMessage());
                throw new \Cx\Lib\FileSystemException('File: Unable to touch file '.$this->file.'!');
            }
        }
    }

    public function makeWritable()
    {
        if (is_writable($this->file)) {
            return true;
        }

        try {
            $fsFile = new FileSystemFile($this->file);
            $fsFile->makeWritable();
        } catch (FileSystemFileException $e) {
            \DBG::msg('FileSystemFile: '.$e->getMessage());

            // try ftp access as fall-back in case regular file access failed
            try {
                $ftpFile = new FTPFile($this->file);
                $ftpFile->makeWritable();
            } catch (FTPFileException $e) {
                \DBG::msg('FTPFile: '.$e->getMessage());
                throw new \Cx\Lib\FileSystemException('File: Unable to set write access to file '.$this->file.'!');
            }
        }

        return true;
    }
}
