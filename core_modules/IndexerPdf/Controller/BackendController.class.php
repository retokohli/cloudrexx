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

namespace Cx\Core_Modules\IndexerPdf\Controller;

/**
 * BackendController
 * @copyright   Comvation AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_module_indexerpdf
 */
class BackendController extends \Cx\Core\Core\Model\Entity\SystemComponentBackendController
{
    /**
     * Set up the backend view
     * @param   \Cx\Core\Html\Sigma $template
     * @param   array               $cmd
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, array $cmd,
        &$isSingle = false)
    {
// TODO: Should this view be linked in the backend navigation?  Where?
        \Cx\Core\Setting\Controller\Setting::init($this->getName(), 'config');
        \Cx\Core\Setting\Controller\Setting::storeFromPost();
        \Cx\Core\Setting\Controller\Setting::show($template,
            $this->cx->getBackendFolderName() . '/' . $this->getName(), '', '',
            'TXT_CORE_MODULE_INDEXERPDF_');
    }

}
