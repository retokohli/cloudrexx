<?php

/**
 * MultiSiteTestCase
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     contrexx
 * @subpackage  core_test
 */

namespace Cx\Core\Test\Model\Entity;

/**
 * MultiSiteTestCase
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     contrexx
 * @subpackage  core_test
 */
abstract class MultiSiteTestCase extends ContrexxTestCase
{
    
    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass() 
    {        
        \Cx\Core_Modules\MultiSite\Controller\ComponentController::$cxMainDomain = \Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain');
        $_SERVER['HTTPS'] = 'https://';
    }    
}
