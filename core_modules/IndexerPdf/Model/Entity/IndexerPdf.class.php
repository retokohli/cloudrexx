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
// TODO: This is supposed to be a string, right?
    protected function getText($filepath)
    {
        $content = '';
        \Cx\Core\Setting\Controller\Setting::init($this->getName(), 'config');
        $url = \Cx\Core\Setting\Controller\Setting::getValue('url_pdftotext');
        // URL should at least be "localhost"
        if (strlen($url) > 8) {
            $request = new \HTTP_Request2($url, \HTTP_Request2::METHOD_POST);
            $request->addUpload(
                'pdffile', $filepath/*, $filepath, 'application/pdf'*/);
            try {
                $content = $request->send()->getBody();
            } catch(\Exception $e) {
            }
        } else {
// TODO: Assuming pdftext is present
// TODO: Assuming an empty path to binary
            $status = null;
            exec('pdftotext ' . $filepath . ' -', $content, $status);
            if ($status === 0) {
                $content = join(' ', $content);
            }
        }
        $content = preg_replace('/\\s+/', ' ', $content);
        return $content;
    }

}
