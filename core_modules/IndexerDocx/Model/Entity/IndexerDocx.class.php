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
        if (!extension_loaded('zip')) {
            return false;
        }
// TODO: Assuming \ZipArchive is present
        $zip = new \ZipArchive();
// TODO: Check result or catch?
        $zip->open($filepath);
        $tmpPath = tempnam($this->cx->getWebsiteTempPath(), 'idx-docx-');
\DBG::dump($tmpPath);
        $zip->extractTo($tmpPath);
        $zip->close();
        try {
            $tmpFile = new \Cx\Lib\FileSystem\File($tmpPath);
            $content = $tmpFile->getData();
            $tmpFile->delete();
        } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
            \DBG::msg($e->getMessage());
        }
        // Old DocxIndex::getText() implementation, for reference:
        //$string = preg_replace("/\t*/", '', strip_tags($string));
        //$string = trim(preg_replace(
        //    "/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $string));
        //$string = preg_replace("/\n/", " ", $string);
        // Semantically identical with:
        $content = preg_replace('/\\s+/', ' ', strip_tags($content));
\DBG::dump($content);
        return $content;
    }

}
