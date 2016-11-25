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
 * Generate PDF for pdfview
 *
 * @copyright   Cloudrexx AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_pdf
 * @version     1.0.0
 */

namespace Cx\Core_Modules\Pdf\Model\Entity;

/**
 * PDF class
 * Generate PDF for pdfview
 *
 * @copyright   Cloudrexx AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_pdf
 * @version     1.0.0
 */
class PdfDocument extends \HTML2FPDF
{
    /**
    * string $content
    * Content for insert
    */
    public $content;

    /**
    * string $title
    * File name
    */
    public $title;

    /**
    * string $orientation
    * pageorientation
    */
    public $pdf_orientation;

    /**
    * string $unit
    * Unit-format
    */
    public $pdf_unit;

    /**
    * string $format
    * Page-format
    */
    public $pdf_format;

    /**
    * string $pdf_creator
    * PDF author
    */
    public $pdf_autor;

    /**
     * @var string $destination
     */
    public $destination = '';

    /**
     * @var string $filePath
     */
    public $filePath = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        global $_CONFIG;

        $this->pdf_orientation  = 'P';
        $this->pdf_unit         = 'mm';
        $this->pdf_format       = 'A4';
        $this->pdf_autor        = $_CONFIG['coreCmsName'];
    }

    /**
     * Create PDF Document
     */
    public function Create()
    {
        $this->content = utf8_decode($this->_ParseHTML($this->content));

        $pdf = new \HTML2FPDF();
        $pdf->ShowNOIMG_GIF();
        $pdf->DisplayPreferences('HideWindowUI');
        $pdf->AddPage();
        $pdf->WriteHTML($this->content);
        if (empty($this->filePath)) {
            $this->filePath = \Cx\Lib\FileSystem\FileSystem::replaceCharacters(
                $this->title
            );
        }
        $pdf->Output($this->filePath, $this->destination);
    }

    /**
     * Parse the html
     *
     * @param string $source
     *
     * @return string
     */
    public function _ParseHTML($source)
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