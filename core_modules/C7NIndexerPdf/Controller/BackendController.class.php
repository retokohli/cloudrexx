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

namespace Cx\Core_Modules\C7NIndexerPdf\Controller;

/**
 * ComponentController
 *
 * Integrate a call like this in your code in order to trigger indexing:
 *  $this->cx->getEvents()->triggerEvent(
 *      'MediaSource:Edit',
 *      ['path' => $this->cx->getWebsiteDocumentRootPath() . '/sample.pdf']
 *  );
 * @copyright   Comvation AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_module_c7nindexerpdf
 */
class BackendController extends \Cx\Core\Core\Model\Entity\SystemComponentBackendController
{
    public function getCommands()
    {
        return ['Settings' => []];
    }

    public function showOverviewPage()
    {
        return false;
    }

}
