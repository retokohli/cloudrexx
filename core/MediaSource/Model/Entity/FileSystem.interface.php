<?php
/**
 * @copyright   Comvation AG 
 * @author Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Core\MediaSource\Model\Entity;

interface FileSystem {
    public function getFileList($directory, $recursive = false);
    public function removeFile(File $file);
    public function moveFile(File $file, $destination);
    public function writeFile(File $file, $content);
    public function readFile(File $file);
    public function isDirectory(File $file);
    public function isFile(File $file);
    public function getLink(File $file);
    public function createDirectory($path, $directory);
}