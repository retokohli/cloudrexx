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

namespace Cx\Core_Modules\IndexerPdf\Model\Entity;

/**
 * Index PDF (.pdf) documents
 *
 * Notes:
 *
 * Events:
 *  'MediaSource:Remove'
 *  'MediaSource:Add'
 *  'MediaSource:Edit'
 *
 * @copyright   Cloudrexx AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @package     MediaSource
 */
class IndexerPdf extends \Cx\Core\MediaSource\Model\Entity\Indexer

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
    protected $extensions = ['pdf'];

    /**
     * Return the text to be indexed for the given path
     * @param   $filepath
     * @return  string
     */
    protected function getText($filepath)
    {
// TODO: Assuming an empty path to binary
// TODO: Assuming pdftext is present
        $output = null;
        exec('pdftotext ' . $filepath . ' -', $output);
        $content = join(' ', $output);
        $content = preg_replace('/\\s+/', ' ', $content);
        return $content;
    }

}
