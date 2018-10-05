<?php declare(strict_types=1);
/**
 * Cloudrexx App by Comvation AG
 *
 * PHP Version 7.1 - 7.2
 *
 * @category  CloudrexxApp
 * @package   CHDIRTravelLog
 * @author    Comvation AG <info@comvation.com>
 * @copyright 2018 ch-direct
 * @link      https://www.comvation.com/
 *
 * Unauthorized copying, changing or deleting
 * of any file from this app is strictly prohibited
 *
 * Authorized copying, changing or deleting
 * can only be allowed by a separate contract
 */

namespace Cx\Modules\CHDIRTravelLog\Controller;

/**
 * ComponentController
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @package     cloudrexx
 * @subpackage  module_chdirtravellog
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {

    /**
     * Return the Controller class names for this component (except this)
     * @return  array
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function getControllerClasses()
    {
        return ['Frontend', 'Backend'];
    }

}
