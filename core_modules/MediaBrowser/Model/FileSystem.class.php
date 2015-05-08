<?php

/**
 * Class FileSystem
 *
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 */

namespace Cx\Core_Modules\MediaBrowser\Model;

use Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserConfiguration;
use Cx\Core_Modules\Uploader\Controller\UploaderConfiguration;
use Cx\Lib\FileSystem\File;
use Cx\Lib\FileSystem\FileSystemException as OldFileSystemException;

/**
 * Class FileSystem
 *
 * Creates, moves and removes directories and files within the Contrexx spectrum.
 * This class also resolves the virtual path's from the MediaBrowser.
 *
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 */
class FileSystem
{

    /**
     * Creates a directory in the specified path.
     *
     * ``` php
     * \Cx\Core_Modules\MediaBrowser\Model\FileSystem::createDirectory('files/Movies','PulpFiction');
     * ```
     *
     * @param $path      string Path to the directory
     * @param $directory string Name of the directory
     *
     * @throws CreateDirectoryException
     */
    public static function createDirectory($path, $directory)
    {
        if (!self::checkPermissions($path)) {
            throw new CreateDirectoryException('No rights to remove file.');
        }
        if (!\Cx\Lib\FileSystem\FileSystem::make_folder(
            self::getAbsolutePath($path) . '/' . $directory
        )
        ) {
            throw new CreateDirectoryException('Can\' create directory.');
        }
    }

    /**
     * Removes the directory in the specified path.
     *
     * ``` php
     * \Cx\Core_Modules\MediaBrowser\Model\FileSystem::removeDirectory('files/Movies','StarWarsVII');
     * ```
     *
     * @param string $path      Path to the directory
     * @param string $directory Name of the directory
     * @param bool   $force     Delete directory if it isn't empty
     *
     * @throws RemoveDirectoryException
     */
    public static function removeDirectory($path, $directory, $force = true)
    {
        if (!self::checkPermissions($path)) {
            throw new RemoveDirectoryException('No rights to remove file.');
        }
        if (!\Cx\Lib\FileSystem\FileSystem::delete_folder(
            self::getAbsolutePath($path) . '/' . $directory, $force
        )
        ) {
            throw new RemoveDirectoryException('Can\' remove directory.');
        }
    }

    /**
     * Moves the directory in the specified path.
     *
     * ``` php
     * \Cx\Core_Modules\MediaBrowser\Model\FileSystem::moveDirectory('files/Movies','files/Movies/Tarantino','PulpFiction');
     * ```
     *
     * @param $sourcePath      string
     * @param $destinationPath string
     * @param $directory       string
     * @param $force
     *
     * @throws MoveDirectoryException
     */
    public static function moveDirectory(
        $sourcePath, $destinationPath, $directory, $force
    )
    {
        if (!self::checkPermissions($sourcePath) || !self::checkPermissions($destinationPath)) {
            throw new MoveDirectoryException("No rights to remove file");
        }
        try {
            $objFile = new File(
                self::getAbsolutePath($sourcePath) . '/' . $directory
            );
            $objFile->move(
                self::getAbsolutePath($destinationPath) . '/' . $directory,
                $force
            );
        } catch (OldFileSystemException $e) {
            throw new MoveDirectoryException($e->getMessage());
        }
    }

    /**
     * Moves file from source path to destination path
     *
     * ``` php
     * \Cx\Core_Modules\MediaBrowser\Model\FileSystem::moveFile('files/Movies','files/Movies/Tarantino','ReservoirDogs.jpg');
     * ```
     *
     * @param $sourcePath             string
     * @param $destinationPath        string
     * @param $file                   string
     * @param $newfileName            string
     * @param $force                  bool Overwrite existing file
     *
     * @throws MoveFileException
     */
    public static function moveFile(
        $sourcePath, $destinationPath, $file, $newfileName, $force
    )
    {
        if (!self::checkPermissions($sourcePath) || !self::checkPermissions($destinationPath)){
            throw new MoveFileException("No rights to remove file");
        }
        $sourceAbsolutePath = self::getAbsolutePath($sourcePath);
        $destinationAbsolutePath = self::getAbsolutePath($destinationPath);
        try {
            $filePathinfo = pathinfo($sourceAbsolutePath . '/' . $file);
            $fileExtension = isset($filePathinfo['extension'])
                ? $filePathinfo['extension'] : '';
            $File = new File($sourceAbsolutePath . '/' . $file);
            $File->move(
                $destinationAbsolutePath . '/' . $newfileName . '.'
                . $fileExtension, $force
            );

            foreach (
                UploaderConfiguration::getInstance()->getThumbnails() as
                $thumbnail
            ) {
                if (FileSystem::fileExists(
                    $sourceAbsolutePath,
                    $filePathinfo['filename'] . $thumbnail['value'] . '.'
                    . $fileExtension
                )
                ) {
                    $File = new File(
                        $sourceAbsolutePath . '/' . $filePathinfo['filename']
                        . $thumbnail['value'] . '.' . $fileExtension
                    );
                    $File->move(
                        $destinationAbsolutePath . '/' . $newfileName
                        . $thumbnail['value'] . '.' . $fileExtension, $force
                    );
                }
            }
        } catch (OldFileSystemException $e) {
            throw new MoveFileException($e->getMessage());
        }
    }

    /**
     * Removes file from directory
     *
     *
     * ``` php
     * \Cx\Core_Modules\MediaBrowser\Model\FileSystem::moveFile('files/Movies','ReservoirDogs.jpg');
     * ```
     *
     * @param $path string
     * @param $file string
     *
     * @throws RemoveFileException
     */
    public static function removeFile($path, $file)
    {
        if (!self::checkPermissions($path)){
            throw new RemoveFileException("No rights to remove file");
        }
        $absolutePath = self::getAbsolutePath($path);
        if (!\Cx\Lib\FileSystem\FileSystem::delete_file(
            $absolutePath . $file
        )
        ) {
            throw new RemoveFileException("Couldn't remove file.");
        }

        $filePathinfo = pathinfo($absolutePath . '/' . $file);
        $fileExtension = isset($filePathinfo['extension'])
            ? $filePathinfo['extension'] : '';
        foreach (
            UploaderConfiguration::getInstance()->getThumbnails() as $thumbnail
        ) {
            if (FileSystem::fileExists(
                $absolutePath,
                $filePathinfo['filename'] . $thumbnail['value'] . '.'
                . $fileExtension
            )
            ) {
                if (!\Cx\Lib\FileSystem\FileSystem::delete_file(
                    $absolutePath . '/' . $filePathinfo['filename']
                    . $thumbnail['value'] . '.' . $fileExtension
                )
                ) {
//                    throw new RemoveFileException("Couldn't remove File.".$absolutePath . '/' . $file . $thumbnail['value'] . '.' . $fileExtension);
                }
            }
        }
    }

    /**
     * Check if a path is virtual or real.
     *
     * ``` php
     * \Cx\Core_Modules\MediaBrowser\Model\FileSystem::isVirtualPath('files/Movies'); // Returns true
     * ```
     *
     * @param $path
     *
     * @return bool
     */
    public static function isVirtualPath($path)
    {
        return !(strpos($path, '/') === 0);
    }

    /**
     * Get the absolute path from the virtual path.
     * If the path is already absolute nothing will happen to it.
     *
     * @param $virtualPath string The virtual Path
     *
     * @return string The absolute Path
     */
    public static function getAbsolutePath($virtualPath)
    {
        if (self::isVirtualPath($virtualPath)) {
            $pathArray = explode('/', $virtualPath);
            return \Cx\Core\Core\Controller\Cx::instanciate()->getMediaSourceManager()->getMediaTypePathsbyNameAndOffset(array_shift($pathArray),0) . '/' . join(
                '/', $pathArray
            );
        }
        return $virtualPath;
    }

    /**
     * Get the web path from the virtual path.
     * If the path is already formed for web nothing will happen to it.
     *
     * @param $virtualPath string The virtual Path
     *
     * @return string The web Path
     */
    public static function getWebPath($virtualPath)
    {
        if($virtualPath) {
            $file = preg_replace('#\\\\#', '/', $virtualPath);
            $file = preg_replace('#'.preg_quote(\Env::get('cx')->getWebsiteDocumentRootPath(), '#').'#', '', $file);
            $file = preg_replace('#'.preg_quote(\Env::get('cx')->getCodeBaseDocumentRootPath(), '#').'#', '', $file);
            return \Env::get('cx')->getWebsiteOffsetPath() . $file;
        }
        return $virtualPath;
    }

    /**
     * Checks if $subdirectory is a subdirectory of $path.
     * You can use a virtual path as a parameter.
     *
     * @param $path
     * @param $subdirectory
     *
     * @return boolean
     */
    public static function isSubdirectory($path, $subdirectory)
    {
        $absolutePath = self::getAbsolutePath($path);
        $absoluteSubdirectory = self::getAbsolutePath($subdirectory);
        return (boolean)preg_match(
            '#^' . preg_quote($absolutePath,'#') . '#', $absoluteSubdirectory
        );
    }

    /**
     * Checks permission
     *
     * @param $path
     *
     * @return bool
     */
    public static function checkPermissions($path)
    {
        $hasAccess = false;
        foreach (
            \Cx\Core\Core\Controller\Cx::instanciate()->getMediaSourceManager()->getMediaTypePaths() as
            $virtualPathName => $mediatype
        ) {
            if (self::isSubdirectory($virtualPathName, $path)) {
                return true;
            }
        }
        return $hasAccess;
    }

    /**
     * Checks if a file exists either in the actual filesystem or in the virtual filesystem.
     *
     * @param $path
     * @param $file
     *
     * @return bool
     */
    public static function fileExists($path, $file)
    {
        return \Cx\Lib\FileSystem\FileSystem::exists(
            self::getAbsolutePath($path) . '/' . $file
        );
    }


}

/**
 * Class RemoveFileException
 *
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 */
class RemoveFileException extends \Exception
{

}

/**
 * Class MoveFileException
 *
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 */
class MoveFileException extends \Exception
{

}

/**
 * Class MoveDirectoryException
 *
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 */
class MoveDirectoryException extends \Exception
{

}

/**
 * Class RemoveDirectoryException
 *
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 */
class RemoveDirectoryException extends \Exception
{

}

/**
 * Class CreateDirectoryException
 *
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 */
class CreateDirectoryException extends \Exception
{

}

/**
 * Class NotVirtualPathException
 *
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 */
class NotVirtualPathException extends \Exception
{

}