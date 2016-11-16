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

/**
* PDF class
*
* Generate PDF for pdfview
* @copyright    CLOUDREXX CMS - CLOUDREXX AG
* @author       Cloudrexx Development Team <info@cloudrexx.com>
* @package      cloudrexx
* @subpackage   core
* @version      1.1.0
*/

//Load the library file functions.php
require ASCMS_LIBRARY_PATH . '/Mpdf/src/functions.php';

/**
* PDF class
*
* Generate PDF for pdfview
* @copyright    CLOUDREXX CMS - CLOUDREXX AG
* @author       Cloudrexx Development Team <info@cloudrexx.com>
* @package      cloudrexx
* @subpackage   core
* @version      1.1.0
*/
class PDF extends \Mpdf\Mpdf
{
    /**
    * string $content
    * Content for insert
    */
    private $content;

    /**
     * @var string
     */
    private $destination = \Mpdf\Output\Destination::INLINE;

    /**
     * Constructor
     */
    public function __construct($orientation = 'P', $format = 'A4')
    {
        parent::__construct(array(
            'orientation' => $orientation,
            'format'      => $format
        ));
    }

    /**
     * Set the content
     *
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Set the output destination
     *
     * @param type $destination
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    /**
     * Create PDF
     */
    public function Create()
    {
        global $_CONFIG;

        $libPath = \Cx\Core\Core\Controller\Cx::instanciate()
            ->getCodeBaseLibraryPath();
        $this->noImageFile = $libPath . '/Mpdf/data/no_image.gif';
        $this->content = utf8_decode($this->_ParseHTML($this->content));
        if (empty($this->author)) {
            $this->SetAuthor($_CONFIG['coreCmsName']);
        }
        $this->SetDisplayPreferences('HideWindowUI');
        $this->AddPage();
        $this->WriteHTML($this->content);
        $this->Output(
            \Cx\Lib\FileSystem\FileSystem::replaceCharacters($this->title),
            $this->destination
        );
    }

    /**
     * Parse the HTML
     *
     * @param string $source
     *
     * @return string
     */
    function _ParseHTML($source)
    {
        // H1
        // ----------------
        $source = str_replace('<h1>', '<div class="h1">', $source);
        $source = str_replace('</h1>', '</div>', $source);
        // H2
        // ----------------
        $source = str_replace('<h2>', '<div class="h2">', $source);
        $source = str_replace('</h2>', '</div>', $source);
        // H3
        // ----------------
        $source = str_replace('<h3>', '<div class="h3">', $source);
        $source = str_replace('</h3>', '</div>', $source);
        // H4
        // ----------------
        $source = str_replace('<h3>', '<div class="h3">', $source);
        $source = str_replace('</h3>', '</div>', $source);

        // body
        // ----------------
        $source = str_replace('<body>', '<body><div class="body">', $source);
        $source = str_replace('</body>', '</div></body>', $source);

        // p
        // ----------------
        $source = str_replace('<p>', '<div class="p">', $source);
        $source = str_replace('</p>', '</div>', $source);

        // image to relative path
        // ----------------
        $source = str_replace('src="/images', 'src="images', $source);
        $source = str_replace("src='/images", "src='images", $source);

        return $source;
    }
}
