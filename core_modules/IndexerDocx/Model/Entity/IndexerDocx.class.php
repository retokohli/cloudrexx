<?php
/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
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
//\DBG::log("filepath $filepath");
        if (!extension_loaded('zip')) {
            return false;
        }
// TODO: Assuming \ZipArchive is present
        $zip = new \ZipArchive();
// TODO: Caller SHOULD check the return value: Empty results need not be stored.
// TODO: Should it throw() instead?
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
//\DBG::log("read tmpfile");
            $tmpFile = new \Cx\Lib\FileSystem\File(
                $tmpFolderPath . '/'. $uniqFolderName . '/'
                . $fileFolderName . '/' . $fileName);
            $content = $tmpFile->getData();
// TODO: Should not be necessary, as delDir() operates recursively:
//\DBG::log("delete tmpfile");
//            $tmpFile->delete();
//\DBG::log("delete filefolder");
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
//\DBG::log("delete done.");
        } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
            \DBG::msg($e->getMessage());
        }
        $content = trim(preg_replace('/\\s\\s+/', ' ',
            strip_tags(str_replace('<', ' <', $content))));
//\DBG::log("content: $content");
        return $content;
    }

}
