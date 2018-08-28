<?php
declare(strict_types = 1);

/**
 * Cloudrexx App by Comvation AG
 *
 * PHP Version 7.2
 *
 * @category  CloudrexxApp
 * @package   IndexerDocx
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

namespace Cx\Core_Modules\IndexerDocx\Controller;

/**
 * ComponentController
 * @copyright   Comvation AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_module_indexerdocx
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController
{
    /**
     * Register this Indexer
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function postComponentLoad()
    {
        $this->getComponent('MediaSource')->registerIndexer(
            new \Cx\Core_Modules\IndexerDocx\Model\Entity\IndexerDocx());
    }

}
