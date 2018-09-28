<?php
declare(strict_types = 1);

/**
 * Cloudrexx App by Comvation AG
 *
 * PHP Version 7.2
 *
 * @category  CloudrexxApp
 * @package   C7NIndexerPdf
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

namespace Cx\Core_Modules\C7NIndexerPdf\Model\Entity;

/**
 * Index PDF (.pdf) documents
 * @copyright   Comvation AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_module_c7nindexerpdf
 */
class IndexerPdf extends \Cx\Core\MediaSource\Model\Entity\Indexer
{
    /**
     * Define known/supported file extensions
     *
     * Note: At the time of writing, this is case sensitive.
     */
    protected $extensions = ['pdf'];

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
        \Cx\Core\Setting\Controller\Setting::init(
            $this->getName(), 'config', 'FileSystem'
        );
        $url = \Cx\Core\Setting\Controller\Setting::getValue('url_pdftotext');
        // URL must at least be "::1", or some (local) path.
        // Also catch null values.
        if (!$url || strlen($url) < 3) {
            return '';
        }
        $content = '';
        $request = new \HTTP_Request2($url, \HTTP_Request2::METHOD_POST);
        $request->addUpload(
            // For other endpoints, it may be necessary to supply
            // arguments 3 and 4.
            'pdffile', $filepath/*, $filepath, 'application/pdf'*/);
        try {
            $content = $request->send()->getBody();
        } catch(\Exception $e) {
        }
        $content = preg_replace('/\\s\\s+/', ' ', $content);
        return $content;
    }

}
