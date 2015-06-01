<?php
/**
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Core\MediaSource\Model\Entity;


use Cx\Core_Modules\Uploader\Controller\UploaderConfiguration;

class LocalFileSystem implements FileSystem
{

    private $cx;

    private $rootPath;

    function __construct($path) {
        $this->cx = \Cx\Core\Core\Controller\Cx::instanciate();
        if (!$path) {
            throw new \InvalidArgumentException(
                "Path shouldn't be empty: Given: " . $path
            );
        }
        $this->rootPath = rtrim($path, '/');
    }

    /**
     * @param $path
     *
     * @return LocalFileSystem
     */
    public static function createFromPath($path) {
        return new self($path);
    }

    public function getFileList($directory, $recursive = false) {
        $recursiveIteratorIterator = new \RegexIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $this->rootPath . '/' . $directory
                ),
                \RecursiveIteratorIterator::SELF_FIRST
            ), '/^((?!thumb(_[a-z]+)?).)*$/'
        );

        $jsonFileArray = array();

        $thumbnailList = UploaderConfiguration::getInstance()->getThumbnails();

        foreach ($recursiveIteratorIterator as $file) {
            /**
             * @var $file \SplFileInfo
             */
            $extension = 'Dir';
            if (!$file->isDir()) {
                $extension = ucfirst(
                    pathinfo($file->getFilename(), PATHINFO_EXTENSION)
                );
            }

            // filters
            if (
                $file->getFilename() == '.'
                || $file->getFilename() == 'index.php'
                || (0 === strpos($file->getFilename(), '.'))
            ) {
                continue;
            }

            // set preview if image
            $preview = 'none';


            $thumbnails = array();
            if ($this->isImage($extension)) {
                $thumbnails = $this->getThumbnails(
                    $thumbnailList, $extension, $file, $thumbnails
                );
                if (file_exists($this->cx->getWebsitePath() . $thumbnails)) {
                    $preview = current($thumbnails);
                }
            }

            $fileInfos = array(
                'filepath' => mb_strcut(
                    $file->getPath() . '/' . $file->getFilename(),
                    mb_strlen($this->cx->getWebsitePath())
                ),
                // preselect in mediabrowser or mark a folder
                'name' => $file->getFilename(),
                'size' => $this->formatBytes($file->getSize()),
                'cleansize' => $file->getSize(),
                'extension' => ucfirst(mb_strtolower($extension)),
                'preview' => $preview,
                'active' => false, // preselect in mediabrowser or mark a folder
                'type' => $file->getType(),
                'thumbnail' => $thumbnails
            );

            // filters
            if (
                $fileInfos['name'] == '.'
                || preg_match(
                    '/\.thumb/', $fileInfos['name']
                )
                || $fileInfos['name'] == 'index.php'
                || (0 === strpos($fileInfos['name'], '.'))
            ) {
                continue;
            }

            $path = array(
                $file->getFilename() => array('datainfo' => $fileInfos)
            );

            for (
                $depth = $recursiveIteratorIterator->getDepth() - 1;
                $depth >= 0; $depth--
            ) {
                $path = array(
                    $recursiveIteratorIterator->getSubIterator($depth)->current(
                    )->getFilename() => $path
                );
            }
            $jsonFileArray = array_merge_recursive($jsonFileArray, $path);
        }
        return $jsonFileArray;
    }

    /**
     * @param $extension
     *
     * @return int
     */
    public function isImage(
        $extension
    ) {
        return preg_match("/(jpg|jpeg|gif|png)/i", ucfirst($extension));
    }

    /**
     * @param $thumbnailList
     * @param $extension
     * @param $file
     * @param $thumbnails
     *
     * @return mixed
     */
    public function getThumbnails(
        $thumbnailList, $extension, $file, $thumbnails
    ) {
        foreach (
            $thumbnailList as
            $thumbnail
        ) {
            $thumbnails[$thumbnail['size']] = preg_replace(
                '/\.' . lcfirst($extension) . '$/',
                $thumbnail['value'] . '.' . lcfirst($extension),
                $this->cx->getWebsiteOffsetPath() . str_replace(
                    $this->cx->getWebsitePath(), '',
                    $file->getRealPath()
                )
            );
        }
        return $thumbnails;
    }

    /**
     * Format bytes
     *
     * @param        $bytes
     * @param string $unit
     * @param int    $decimals
     *
     * @return string
     */
    protected
    function formatBytes(
        $bytes, $unit = "", $decimals = 2
    ) {
        $units = array(
            'B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4,
            'PB' => 5, 'EB' => 6, 'ZB' => 7, 'YB' => 8
        );

        $value = 0;
        if ($bytes > 0) {
            // Generate automatic prefix by bytes
            // If wrong prefix given
            if (!array_key_exists($unit, $units)) {
                $pow  = floor(log($bytes) / log(1024));
                $unit = array_search($pow, $units);
            }

            // Calculate byte value by prefix
            $value = ($bytes / pow(1024, floor($units[$unit])));
        }

        // If decimals is not numeric or decimals is less than 0
        // then set default value
        if (!is_numeric($decimals) || $decimals < 0) {
            $decimals = 2;
        }

        // Format output
        return sprintf('%.' . $decimals . 'f ' . $unit, $value);
    }

    public function removeFile(File $file) {
        global $_ARRAYLANG;
        if ($file->getExtension() == ''
            && is_dir(
                $this->rootPath . ltrim($file->getPath(), '.') . '/'
                . $file->getName()
            )
        ) {
            $filename = $file->getName();
        } else {
            $filename = $file->getName() . '.' . $file->getExtension();
        }

        $strPath = $file->getPath();
        if (!empty($filename)
            && !empty($strPath)
        ) {
            if (is_dir(
                $this->rootPath . ltrim($file->getPath(), '.') . '/'
                . $file->getName()
            )) {
                if (\Cx\Lib\FileSystem\FileSystem::delete_folder(
                    $this->rootPath . $strPath . $filename, true
                )
                ) {
                    return (
                    sprintf(
                        $_ARRAYLANG['TXT_FILEBROWSER_DIRECTORY_SUCCESSFULLY_REMOVED'],
                        $filename
                    )
                    );
                } else {
                    return (
                    sprintf(
                        $_ARRAYLANG['TXT_FILEBROWSER_DIRECTORY_UNSUCCESSFULLY_REMOVED'],
                        $filename
                    )
                    );
                }
            } else {
                if (\Cx\Lib\FileSystem\FileSystem::delete_file(
                    $this->rootPath . '/' . $strPath . '/' . $filename
                )
                ) {
                    return (
                    sprintf(
                        $_ARRAYLANG['TXT_FILEBROWSER_FILE_SUCCESSFULLY_REMOVED'],
                        $filename
                    )
                    );
                } else {
                    return (
                    sprintf(
                        $_ARRAYLANG['TXT_FILEBROWSER_FILE_UNSUCCESSFULLY_REMOVED'],
                        $filename
                    ));
                }
            }
        }
        return (
        sprintf(
            $_ARRAYLANG['TXT_FILEBROWSER_FILE_UNSUCCESSFULLY_REMOVED'],
            $filename
        )
        );
    }

    public function moveFile(
        File $file, $destination
    ) {
        global $_ARRAYLANG;
        if (!empty($destination)) {
            if ($file->getExtension() == ''
                && is_dir(
                    $this->rootPath . ltrim($file->getPath(), '.') . '/'
                    . $file->getName()
                )
            ) {
                $fileName            =
                    $this->rootPath . ltrim($file->getPath(), '.') . '/'
                    . $file->getName();
                $destinationFileName =
                    $this->rootPath . ltrim($file->getPath(), '.') . '/'
                    . $destination;
            } else {
                $fileName            =
                    $this->rootPath . ltrim($file->getPath(), '.') . '/'
                    . $file->getName()
                    . '.' . $file->getExtension();
                $destinationFileName =
                    $this->rootPath . ltrim($file->getPath(), '.') . '/'
                    . $destination
                    . '.'
                    . $file->getExtension();
            }
            if (!\Cx\Lib\FileSystem\FileSystem::move(
                $fileName, $destinationFileName
                , false
            )
            ) {
                return sprintf(
                    $_ARRAYLANG['TXT_FILEBROWSER_FILE_UNSUCCESSFULLY_RENAMED'],
                    $file->getName()
                );
            }
            return sprintf(
                $_ARRAYLANG['TXT_FILEBROWSER_FILE_SUCCESSFULLY_RENAMED'],
                $file->getName()
            );
        }
        else {
            return sprintf(
                $_ARRAYLANG['TXT_FILEBROWSER_FILE_UNSUCCESSFULLY_RENAMED'],
                $file->getName()
            );
        }
    }

    public function writeFile(
        File $file, $content
    ) {
        file_put_contents(
            $this->rootPath . '/' . $file->__toString(), $content
        );
    }

    public function readFile(
        File $file
    ) {
        return file_get_contents($this->rootPath . '/' . $file->__toString());
    }

    public function isDirectory(
        File $file
    ) {
        return is_dir($this->rootPath . '/' . $file->__toString());
    }

    public function isFile(
        File $file
    ) {
        return is_file($this->rootPath . '/' . $file->__toString());
    }

    public function getLink(
        File $file
    ) {
        // TODO: Implement getLink() method.
    }

    public function createDirectory(
        $path, $directory
    ) {
        global $_ARRAYLANG;
        \Env::get('init')->loadLanguageData('MediaBrowser');
        if (preg_match('#^[0-9a-zA-Z_\-\/]+$#', $directory)) {
            if (!\Cx\Lib\FileSystem\FileSystem::make_folder(
                $path . '/' . $directory
            )
            ) {
                return sprintf(
                    $_ARRAYLANG['TXT_FILEBROWSER_UNABLE_TO_CREATE_FOLDER'],
                    $directory
                );
            } else {
                return
                    sprintf(
                        $_ARRAYLANG['TXT_FILEBROWSER_DIRECTORY_SUCCESSFULLY_CREATED'],
                        $directory
                    );
            }
        } else {
            if (!empty($directory)) {
                return (
                $_ARRAYLANG['TXT_FILEBROWSER_INVALID_CHARACTERS']
                );

            }
        }
        return sprintf(
            $_ARRAYLANG['TXT_FILEBROWSER_UNABLE_TO_CREATE_FOLDER'], $directory
        );
    }
}