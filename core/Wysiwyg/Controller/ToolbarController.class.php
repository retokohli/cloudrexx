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
 * Specific ToolbarContoller for this Component.
 *
 * Use this to easily create a toolbar configurator
 * @copyright   Cloudrexx AG
 * @author      Nick Brönnimann <nick.broennimann@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_wysiwyg
 * @version     1.0.0
 */

namespace Cx\Core\Wysiwyg\Controller;

/**
 * Specific ToolbarContoller for this Component.
 *
 * Use this to easily create a toolbar configurator
 * @copyright   Cloudrexx AG
 * @author      Nick Brönnimann <nick.broennimann@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_wysiwyg
 * @version     1.0.0
 */
class ToolbarController { // extends \Cx\Core\Core\Model\Entity\SystemComponentBackendController {
    /**
     * This is the default toolbar for either full or small as json
     * @var string $defaultFull
     * @access protected
     */
    protected $defaultFull = "[
        ['Source','-','NewPage','Templates'],
        ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Scayt'],
        ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
        ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
        ['NumberedList','BulletedList','-','Outdent','Indent', 'Blockquote'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
        ['Link','Unlink','Anchor'],
        ['Image','Flash','Table','HorizontalRule','SpecialChar'],
        ['Format'],
        ['TextColor','BGColor'],
        ['ShowBlocks'],
        ['Maximize'],
        ['Div','CreateDiv']
    ]";
    protected $defaultFrontendEditingContent;
    protected $defaultFrontendEditingTitle;
    protected $defaultBbcode;
    protected $cx;
    protected $types = array(
        'small',
        'full',
        'frontendEditingContent',
        'frontendEditingTitle',
        'bbcode'
    );

    public function __construct(\Cx\Core\Core\Controller\Cx $cx) {
        $this->cx = $cx;
    }

    /**
     * Compile the removed buttons into available functions
     *
     * Compiles the removed buttons into available function based on the given
     * type of toolbar (either small, full, bbcode, frontendEditingContent or
     * frontendEditingTitle) and returns these available functions as a json-
     * array
     * @param string $removedButtons List of functinos which shall be removed
     * @param string $type Type of toolbar (either small, full, bbcode, frontendEditingContent or frontendEditingTitle)
     * @return string $oldSyntax json-array of the available functions
     */
    public function getAsOldSyntax($removedButtons, $type) {
        // create array of removed buttons from comma separated functions
        $removedButtons = explode(',', $removedButtons);
        // load old syntax of given type
        switch ($type) {
            case 'frontendEditingContent':
                $oldSyntax = $this->defaultFrontendEditingContent;
                break;
            case 'frontendEditingTitle':
                $oldSyntax = $this->defaultFrontendEditingTitle;
                break;
            case 'bbcode':
                $oldSyntax = $this->defaultBbcode;
                break;
            case 'small':
            case 'full':
            default:
                $oldSyntax = $this->defaultFull;
                break;
        }
        // create array out of json array
        $oldSyntax = json_decode($oldSyntax);
        // remove the buttons which shall be removed
        array_diff($oldSyntax, $removedButtons);
        // create a json array once again
        return json_encode($oldSyntax);
    }

    /**
     * Get the template for the toolbar configurator
     *
     * Also registers the necessary css and js files
     * @param \Cx\Core\Core\Controller\Cx $cx
     * @return \Cx\Core\Html\Sigma $template The toolbar configurator template
     */
    public function getToolbarConfiguratorTemplate() {
        $componentRoot = $this->cx->getWebsiteDocumentRootPath() . '/core/Wysiwyg';
        // load the toolbarconfigurator template
        $template = new \Cx\Core\Html\Sigma($componentRoot . '/View/Template/Backend');
        $template->loadTemplateFile('ToolbarConfigurator.html');
        // prepare the js and css files which are needed
        $requiredJsFiles = array(
            'CodeMirror',
            'CodeMirrorJavascript',
            'ShowHint',
            'FullToolbarEditor',
            'AbstractToolabrModifier',
            'ToolbarModifier',
            'ToolbarTextModifier',
            'sf',
        );
        $requiredCssFiles = array(
            'CodeMirror',
            'ShowHint',
            'Neo',
            'Fontello',
            'Samples',
        );
        \JS::registerJS($componentRoot . '/ckeditor.config.js.php');
        \JS::registerJS($this->cx->getWebsiteDocumentRootPath() . '/lib/ckeditor/ckeditor.js');
        // register js and css files for the toolbarconfigurator
        foreach ($requiredJsFiles as $filename) {
            \JS::registerJS($componentRoot . '/View/Script/' . $filename . '.js');
        }
        foreach ($requiredCssFiles as $filename) {
            \JS::registerCSS($componentRoot . '/View/Style/' . $filename . '.css');
        }
        // get the init object to change to te proper language file
        $init = \Env::get('init');
        // get the language file of the Wysiwyg component (this one btw.)
        $_ARRAYLANG = $init->getComponentSpecificLanguageData('Wysiwyg', false, FRONTEND_LANG_ID);
        // replace language variables
        $template->setVariable(array(
            'TXT_WYSIWYG_TOOLBAR_CONFIGURATOR' => $_ARRAYLANG['TXT_WYSIWYG_TOOLBAR_CONFIGURATOR'],

        ));

        return $template;
    }

    /**
     * Wrapper for any getTYPEToolbar calls
     *
     * Type must be one of the following: Small, Full, FrontendEditingContent,
     * FrontendEditingTitle or Bbcode
     * @param $type
     * @return string
     */
    public function getToolbar($type) {
        $em = $this->cx->getDb()->getEntityManager();
        $toolbarRepo = $em->getRepository('\\Cx\\Core\\Wysiwyg\\Model\\Entity\\WysiwygToolbar');
        if (in_array(lcfirst($type), $this->types)) {
            $functionName = 'get' . ucfirst($type) . 'Toolbar';
            // Call the function to merge the toolbars
            return call_user_func_array(
                array($this, $functionName),
                array($toolbarRepo, $em)
            );
        } else {
            $defaultToolbar = $toolbarRepo->findOneBy(array('isDefault' => 1));
            return $defaultToolbar->getAvailableFunctions();
        }
    }

    /**
     * @param \Doctrine\ORM\EntityRepository    $toolbarRepo    The toolbar
     *                                                          repository
     * @param \Doctrine\ORM\EntityManager       $em             The entity
     *                                                          manager
     * @return string $mergedToolbar            The merged toolbar of all
     *                                          assigned user groups or the
     *                                          default Full
     */
    protected function getFullToolbar(\Doctrine\ORM\EntityRepository $toolbarRepo, \Doctrine\ORM\EntityManager $em) {
        // Load the group repository
        $groupRepo = $em->getRepository('\\Cx\\Core\\User\\Model\\Entity\\Group');
        // Load the current user
        $user = \FWUser::getFWUserObject();
        // Get all assigned user group ids
        $groupIds = $user->objUser->getAssociatedGroupIds();
        // Initiate an empty array of toolbarIds
        $toolbarIds = array();
        // Loop through all user group ids
        foreach ($groupIds as $groupId) {
            // Load user group entity through group repository
            $group = $groupRepo->findOneBy(array('groupId' => $groupId));
            // Check if the user group has a toolbar assigned
            if (!empty($group->getToolbar())) {
                // Store the assigned toolbar id
                $toolbarIds[] = $group->getToolbar();
            }
        }
        // Make sure that we do not have any redundant toolbar ids
        $toolbarIds = array_unique($toolbarIds);
        // Initiate an empty toolbar array
        $toolbars = array();
        // Loop through each toolbar id
        foreach ($toolbarIds as $toolbarId) {
            // Load the toolbar entity through the toolbar repository
            $toolbar = $toolbarRepo->findOneBy($toolbarId);
            // Store the available functions for the current toolbar
            $toolbars[] = $toolbar->getAvailableFunctions();
        }
        // Initiate a mergedToolbar as an empty array
        $mergedToolbar = array();
        // Loop through all available function constellation
        for ($i = 0; $i < count($toolbars) - 1; $i += 2) {
            // Check if the current index is still in range of the array keys
            if (
                array_key_exists($i, $toolbars) &&
                array_key_exists($i + 1, $toolbars)
            ) {
                // Merge the already merged toolbar, the current one and the
                // next one together
                $mergedToolbar = array_merge(
                    $mergedToolbar,
                    json_decode($toolbars[$i]),
                    json_decode($toolbars[$i + 1])
                );
                // Check if we have reached the last index and the next one
                // would already be out of range of the array keys
            } else if (
                array_key_exists($i, $toolbars) &&
                !array_key_exists($i + 1, $toolbars)
            ) {
                // merge the last toolbar and the product of the previous merges
                $mergedToolbar = array_merge(
                    $mergedToolbar,
                    json_decode($toolbars[$i])
                );
                // leave the loop just to be sure as we should leave anyway
                break;
            }
        }
    }
}