<?php
declare(strict_types = 1);

/**
 * Cloudrexx App by Comvation AG
 *
 * PHP Version 7.2
 *
 * @category  CloudrexxApp
 * @package   C7NIndexerDocx
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

namespace Cx\Core_Modules\C7NIndexerDocx\Model\Entity;

/**
 * Index Word OpenDocument (.docx) documents
 * @copyright   Comvation AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_module_c7nindexerdocx
 */
class IndexerDocx extends \Cx\Core\MediaSource\Model\Entity\Indexer
{
    /**
     * Define known/supported file extensions
     *
     * Note: At the time of writing, this is case sensitive.
     */
    protected $extensions = [
        'docx',
        'odt',
    ];

    /**
     * Return the text to be indexed for the given path
     *
     * Returns the empty string on error (see todo).
     * @param   string  $filepath
     * @return  string
     * @todo    On error, throw an Exception as defined by the caller
     */
    protected function getText($filepath)
    {
        if (!extension_loaded('zip')) {
            return '';
        }
        // Assuming \ZipArchive is present
        $zip = new \ZipArchive();
        // TODO: Caller should define and catch an Exception
        if ($zip->open($filepath) !== true) {
            return '';
        }
        // The file is extracted to its original folder and file name,
        // thus a folder with a unique name is required.
        $tmpFolderPath = $this->cx->getWebsiteTempPath();
        $uniqFolderName = uniqid('idx-docx-');
        // Default: .docx
        $contentFilePath = 'word/document.xml';
        if (preg_match('/\\.odt$/i', $filepath)) {
            $contentFilePath = 'content.xml';
        }
        \Cx\Lib\FileSystem\FileSystem::make_folder(
            $tmpFolderPath . '/' . $uniqFolderName, true);
        $isExtracted = $zip->extractTo(
            $tmpFolderPath . '/' . $uniqFolderName, $contentFilePath
        );
        $zip->close();
        if (!$isExtracted) {
            return '';
        }
        $content = '';
        try {
            $tmpFile = new \Cx\Lib\FileSystem\File(
                $tmpFolderPath . '/'. $uniqFolderName . '/' . $contentFilePath
            );
            $content = $tmpFile->getData();
        } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
            \DBG::msg($e->getMessage());
        }
        \Cx\Lib\FileSystem\FileSystem::delete_folder(
            $tmpFolderPath . '/' . $uniqFolderName, true
        );
        $content = trim(preg_replace('/\\s\\s+/', ' ',
            strip_tags(str_replace('<', ' <', $content))
        ));
        return $content;
    }

}
