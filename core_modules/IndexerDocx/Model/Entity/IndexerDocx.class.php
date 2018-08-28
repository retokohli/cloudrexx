<?php
declare(strict_types = 1);

/**
 * Cloudrexx App by Comvation AG
 *
 * PHP Version 7.2
 *
 * @category  CloudrexxApp
 * @package   IndexerDocx
 * @author    Comvation AG <info@comvation.com>
 * @copyright 2018 Comvation AG
 * @link      https://www.comvation.com
 *
 * Unauthorized copying, changing or deleting
 * of any file from this app is strictly prohibited
 *
 * Authorized copying, changing or deleting
 * can only be allowed by a separate contract
 */

namespace Cx\Core_Modules\IndexerDocx\Model\Entity;

/**
 * Index Word OpenDocument (.docx) documents
 * @copyright   Comvation AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_module_indexerdocx
 */
class IndexerDocx extends \Cx\Core\MediaSource\Model\Entity\Indexer
{
    /**
     * TODO: What is that?
     * @var $type string
     */
    protected $type;
    /**
     * TODO: Presumably, this defines the file extensions
     * accepted by this Indexer?
     * TODO: This is case insensitive, right?
     */
    protected $extensions = ['docx'];

    /**
     * Return the text to be indexed for the given path
     * @param   $filepath
     * @return  string
     */
    protected function getText($filepath)
    {
        if (!extension_loaded('zip')) {
            return false;
        }
// TODO: Assuming \ZipArchive is present
        $zip = new \ZipArchive();
// TODO: Caller SHOULD check the return value: Empty results need not be stored.
// (in fact, existing records SHOULD be deleted)
        if ($zip->open($filepath) !== true) {
            return '';
        }
        $fileFolderName = 'word';
        $fileName = 'document.xml';
        // NOTE: The file is extracted to its original folder and file name,
        // thus a folder with a unique name is required.
        $uniqFolderName = uniqid('idx-docx-');
        $tmpFolderPath = $this->cx->getWebsiteTempPath();
        $tmpFolderWebPath = $this->cx->getWebsiteTempWebPath();
        $filesystem = new \Cx\Lib\FileSystem\FileSystem();
        $filesystem->mkDir($tmpFolderPath, $tmpFolderWebPath, $uniqFolderName);
        $isExtracted = $zip->extractTo(
            $tmpFolderPath . '/' . $uniqFolderName,
            $fileFolderName . '/' . $fileName);
        $zip->close();
        if (!$isExtracted) {
            return '';
        }
        $content = '';
        try {
            $tmpFile = new \Cx\Lib\FileSystem\File(
                $tmpFolderPath . '/'. $uniqFolderName . '/'
                . $fileFolderName . '/' . $fileName);
            $content = $tmpFile->getData();
// TODO: Should not be necessary, as delDir() operates recursively:
//            $tmpFile->delete();
//            $filesystem->delDir(
//                $tmpFolderPath, $tmpFolderWebPath,
//                $uniqFolderName . '/' . $fileFolderName);
// TODO: This does not work for unknown reasons.
// It doesn't return "error", either:
//\DBG::log("delete uniqfolder: ".
//            $filesystem->delDir(
//                $tmpFolderPath, $tmpFolderWebPath,
//                $uniqFolderName)
//);
        } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
            \DBG::msg($e->getMessage());
        }
        $content = trim(preg_replace('/\\s\\s+/', ' ',
            strip_tags(str_replace('<', ' <', $content))));
        return $content;
    }

}
