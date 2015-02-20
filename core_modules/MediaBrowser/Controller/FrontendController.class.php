<?php
/**
 * Specific FrontendController for this Component. Use this to easily create a frontent view
 *
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 */

namespace Cx\Core_Modules\MediaBrowser\Controller;

/**
 * Specific FrontendController for this Component. Use this to easily create a frontent view
 *
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  modules_skeleton
 */
class FrontendController extends
    \Cx\Core\Core\Model\Entity\SystemComponentFrontendController
{

    /**
     * Use this to parse your frontend page
     *
     * You will get a template based on the content of the resolved page
     * You can access Cx class using $this->cx
     * To show messages, use \Message class
     *
     * @param \Cx\Core\Html\Sigma $template Template containing content of resolved page
     * @param                     $cmd
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, $cmd)
    {
        // this class inherits from Controller, therefore you can get access to
        // Cx like this:
        $this->cx;

        // Controller routes all calls to undeclared methods to your
        // ComponentController. So you can do things like
        $this->getName();


    }
}
