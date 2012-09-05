<?php
namespace Cx\Lib\FileSystem;

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
            throw new FileSystemException('Unable to read data from file '.$this->file.'!');
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
                throw new FileSystemException('File: Unable to write data to file '.$this->file.'!');
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
                throw new FileSystemException('File: Unable to touch file '.$this->file.'!');
            }
        }
    }

    public function makeWritable()
    {
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
                throw new FileSystemException('File: Unable to set write access to file '.$this->file.'!');
            }
        }

        return true;
    }

    public function delete()
    {
        try {
            $fsFile = new FileSystemFile($this->file);
            $fsFile->delete();
        } catch (FileSystemFileException $e) {
            \DBG::msg('FileSystemFile: '.$e->getMessage());

            // try ftp access as fall-back in case regular file access failed
            try {
                $ftpFile = new FTPFile($this->file);
                $ftpFile->delete();
            } catch (FTPFileException $e) {
                \DBG::msg('FTPFile: '.$e->getMessage());
                throw new FileSystemException('File: Unable to delete file '.$this->file.'!');
            }
        }

        clearstatcache();
        if (file_exists($this->file)) {
            throw new FileSystemException('File: Unable to delete file '.$this->file.'!');
        }
    }
}
