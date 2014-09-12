<?php
/**
 * Created by PhpStorm.
 * User: robin
 * Date: 18.08.14
 * Time: 16:09
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
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
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
     * @param $path      String Path to the directory
     * @param $directory String Name of the directory
     *
     * @throws CreateDirectoryException
     */
    public static function createDirectory($path, $directory)
    {
        if (!\Cx\Lib\FileSystem\FileSystem::make_folder(self::getAbsolutePath($path) . '/' . $directory)) {
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
     * @param String $path      Path to the directory
     * @param String $directory Name of the directory
     * @param bool   $force     Delete directory if it isn't empty
     *
     * @throws RemoveDirectoryException
     */
    public static function removeDirectory($path, $directory, $force = true)
    {
        if (!\Cx\Lib\FileSystem\FileSystem::delete_folder(self::getAbsolutePath($path) . '/' . $directory, $force)) {
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
     * @param $sourcePath      String
     * @param $destinationPath String
     * @param $directory       String
     * @param $force
     *
     * @throws MoveDirectoryException
     */
    public static function moveDirectory($sourcePath, $destinationPath, $directory, $force)
    {
        try {
            $objFile = new File(self::getAbsolutePath($sourcePath) . '/' . $directory);
            $objFile->move(self::getAbsolutePath($destinationPath) . '/' . $directory, $force);
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
     * @param $sourcePath      String
     * @param $destinationPath String
     * @param $file            String
     * @param $force           bool Overwrite existing file
     *
     * @throws MoveFileException
     */
    public static function moveFile($sourcePath, $destinationPath, $file, $force)
    {
        try {
            $objFile = new File(self::getAbsolutePath($sourcePath) . '/' . $file);
            $objFile->move(self::getAbsolutePath($destinationPath) . '/' . $file, $force);
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
     * @param $path String
     * @param $file String
     *
     * @throws RemoveFileException
     */
    public static function removeFile($path, $file)
    {
        if (!\Cx\Lib\FileSystem\FileSystem::delete_file(self::getAbsolutePath($path) . '/' . $file)) {
            throw new RemoveFileException("Couldn't remove File.");
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
        return !strpos($path, '/') === 0;
    }

    /**
     * Get the absolute path from the virtual path.
     * If the path is already absolute nothing will happen to it.
     *
     * @param $virtualPath String The virtual Path
     *
     * @return String The absolute Path
     */
    public static function getAbsolutePath($virtualPath)
    {
        if (self::isVirtualPath($virtualPath)) {
            $pathArray = explode('/', $virtualPath);
            return MediaBrowserConfiguration::getInstance()->mediaTypePaths[array_shift($pathArray)][0] . '/' . join(
                '/', $pathArray
            );
        }
        return $virtualPath;
    }

    /**
     * Checks if a file exists either in the actual filesystem or in the virtual filesystem.
     *
     * @param $path
     * @param $file
     *
     * @return bool
     */
    public static function fileExists($path, $file){
        return \Cx\Lib\FileSystem\FileSystem::exists(self::getAbsolutePath($path) . '/' . $file);
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