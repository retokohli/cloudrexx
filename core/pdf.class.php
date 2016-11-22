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
class PDF extends mPDF
{
    /**
    * string $content
    * Content for insert
    */
    private $content;

    /**
     * @var string
     */
    private $destination = 'I';

    /**
     * Constructor
     */
    public function __construct($orientation = 'P', $format = 'A4')
    {
        parent::__construct('', $format, 0, '', 15, 15, 16, 16, 9, 9, $orientation);
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
        $this->noImageFile = $libPath . '/Mpdf/includes/no_img.gif';
        $this->content = utf8_decode($this->content);
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
     * Get file full path
     *
     * @param string $path     file path
     * @param string $basepath file base path
     */
    public function GetFullPath(&$path, $basepath = '')
    {
        // When parsing CSS need to pass temporary basepath -
        // so links are relative to current stylesheet
        if (!$basepath) {
            $basepath = $this->basepath;
        }
        //Fix path value
        $path = str_replace("\\", "/", $path); //If on Windows
        // mPDF 5.7.2
        if (substr($path, 0, 2) == "//") {
            $tr = parse_url($basepath);
            $path = $tr['scheme'] . ':' . $path; // mPDF 6
        }

        $regexp = '|^./|'; // Inadvertently corrects "./path/etc"
        //and "//www.domain.com/etc"
        $path = preg_replace($regexp, '', $path);

        if (substr($path, 0, 1) == '#') {
            return;
        }
        if (preg_match('@^(mailto|tel|fax):.*@i', $path)) {
            return;
        }

        if (
            substr($path, 0, 3) == "../" ||
            strpos($path, ":/") === false ||
            strpos($path, ":/") > 10
        ) {
            $objFile = new Cx\Lib\FileSystem\FileSystemFile($path);
            $path    = $objFile->getAbsoluteFilePath();
        }
    }

}
