<?php
declare(strict_types = 1);

/**
 * Cloudrexx App by Comvation AG
 *
 * PHP Version 7.2
 *
 * @category  CloudrexxApp
 * @package   IndexerPdf
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

namespace Cx\Core_Modules\IndexerPdf\Model\Entity;

/**
 * Index PDF (.pdf) documents
 * @copyright   Comvation AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_module_indexerpdf
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
