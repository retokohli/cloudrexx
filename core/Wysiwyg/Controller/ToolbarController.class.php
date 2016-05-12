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
     * @var array $defaultFull
     * @access protected
     */
    protected $defaultFull = array(
        array('Source','-','NewPage','Templates'),
        array('Cut','Copy','Paste','PasteText','PasteFromWord','-','Scayt'),
        array('Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'),
        array('Bold','Italic','Underline','Strike','-','Subscript','Superscript'),
        array('NumberedList','BulletedList','-','Outdent','Indent', 'Blockquote'),
        array('JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'),
        array('Link','Unlink','Anchor'),
        array('Image','Flash','Table','HorizontalRule','SpecialChar'),
        array('Format'),
        array('TextColor','BGColor'),
        array('ShowBlocks'),
        array('Maximize'),
        array('Div','CreateDiv'),
    );
    protected $defaultFrontendEditingContent;
    protected $defaultFrontendEditingTitle;
    protected $defaultBbcode;
    /**
     * These are the buttons that are removed by default and are not accessible
     * @var string
     * @access protected
     */
    protected $defaultRemovedButtons = 'autoFormat,CommentSelectedRange,UncommentSelectedRange,AutoComplete,Preview,Smiley,Iframe,Styles';
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
        // remove the buttons which shall be removed
        foreach ($oldSyntax as $key => $functionGroup) {
            foreach ($removedButtons as $toRemove) {
                if (in_array($toRemove, $functionGroup)) {
                    $keyToRemove = array_search($toRemove, $functionGroup);
                    unset($oldSyntax[$key][$keyToRemove]);
                    if (count($oldSyntax[$key]) == 1) {
                        unset($oldSyntax[$key]);
                    }
                }
            }
        }
        $oldSyntax = array_values($oldSyntax);
        // create a json array once again
        return json_encode($oldSyntax);
    }

    /**
     * Get the template for the toolbar configurator
     *
     * Also registers the necessary css and js files
     * @param   string              $componentRoot          The Path to the template
     * @param   bool                $isDefaultConfiguration Wraps the template
     *                                                      in a form tag
     *                                                      defaults to false
     * @return \Cx\Core\Html\Sigma  $template               The toolbar
     *                                                      configurator template
     */
    public function getToolbarConfiguratorTemplate($componentRoot = '/', $isDefaultConfiguration = false) {
        // load the toolbarconfigurator template
        $template = new \Cx\Core\Html\Sigma($componentRoot . '/View/Template/Backend');
        if ($isDefaultConfiguration) {
            $template->loadTemplateFile('DefaultToolbarConfiguration.html');
            $template->addBlockfile('WYSIWYG_TOOLBAR_CONFIGURATOR', 'ToolbarConfigurator', 'ToolbarConfigurator.html');
        } else {
            $template->loadTemplateFile('ToolbarConfigurator.html');
            $template->touchBlock('ToolbarConfigurator');
        }
        // prepare the js and css files which are needed
        $requiredJsFiles = array(
            'lib/codemirror/codemirror',
            'lib/codemirror/javascript',
            'lib/codemirror/show-hint',
            'js/fulltoolbareditor',
            'js/abstracttoolbarmodifier',
            'js/toolbarmodifier',
            'js/toolbartextmodifier',
            'js/sf',
        );
        $requiredCssFiles = array(
            'CodeMirror',
            'ShowHint',
            'Neo',
            'Fontello',
            'Samples',
        );
        \JS::registerJS($componentRoot . '/ckeditor.config.js.php');
        \JS::registerJS($componentRoot . '/View/Script/Backend.js');
        \JS::registerCSS($componentRoot . '/View/Style/Backend.css');
        if ($componentRoot[0] = '/') {
            $componentRoot = substr($componentRoot, 1);
        }
        // register js and css files for the toolbarconfigurator
        foreach ($requiredCssFiles as $filename) {
            \JS::registerCSS(
                $componentRoot . '/View/Style/' . $filename . '.css'
            );
        }
        foreach ($requiredJsFiles as $filename) {
            \JS::registerJS(
                $componentRoot . '/View/Script/toolbarconfigurator/' . $filename
                . '.js'
            );
        }
        \JS::activate('ckeditor');
        \JS::activate('jquery');
        // get the init object to change to te proper language file
        $init = \Env::get('init');
        // get the language file of the Wysiwyg component (this one btw.)
        $_ARRAYLANG = $init->getComponentSpecificLanguageData('Wysiwyg', false, FRONTEND_LANG_ID);
        // replace language variables
        $template->setVariable(array(
            'TXT_WYSIWYG_TOOLBAR_CONFIGURATOR'  => $_ARRAYLANG['TXT_WYSIWYG_TOOLBAR_CONFIGURATOR'],
            'TXT_WYSIWYG_TOOLBAR_SAVE'          => $_ARRAYLANG['TXT_WYSIWYG_TOOLBAR_SAVE'],
        ));

        // Check if the toolbar configurator needs to be wrapped in a form tag
        if (!$isDefaultConfiguration) {
            $template->hideBlock('wysiwyg_toolbar_store_button');
        }

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
        $pdo = $this->cx->getDb()->getPdoConnection();
        if (in_array(lcfirst($type), $this->types)) {
            $functionName = 'get' . ucfirst($type) . 'Toolbar';
            // Call the function to merge the toolbars
            return call_user_func_array(
                array($this, $functionName),
                array($pdo)
            );
        } else {
            $functionsResult = $pdo->query("
                SELECT `available_functions`
                  FROM `" . DBPREFIX . "core_wysiwyg_toolbar`
                WHERE `is_default` = 1
                LIMIT 1"
            );
            // Assure that the query did not fail
            if ($functionsResult === false) {
                // In case of failure return the default full
                return $this->defaultFull;
            }
            // Verify that we could fetch the data
            $availableFunctions = $functionsResult->fetch(\PDO::FETCH_ASSOC);
            if ($availableFunctions === false) {
                // In case of failure return the default full
                return $this->defaultFull;
            }
            return $availableFunctions['available_functions'];
        }
    }

    /**
     * Get the toolbar of the type full
     * @return string $mergedToolbar    The merged toolbar of all assigned user
     */
    protected function getFullToolbar() {
        // Get all the toolbar ids of every user group assigned to the user
        $toolbarIds = $this->getToolbarIdsOfUserGroup();
        // Make sure that we do not have any redundant toolbar ids
        $toolbarIds = array_unique($toolbarIds);
        // Load the removedButtons of the given toolbars
        $removedButtons = $this->loadRemovedButtons($toolbarIds);
        // Add removed buttons of the default full toolbar to the list
        $pdo = $this->cx->getDb()->getPdoConnection();
        $defaultFullRes = $pdo->query("
                SELECT `removed_buttons`
                  FROM `" . DBPREFIX . "core_wysiwyg_toolbar`
                WHERE `is_default` = 1
                LIMIT 1");
        $defaultFull = $defaultFullRes->fetch(\PDO::FETCH_ASSOC);
        $removedButtons['default'] = $defaultFull['removed_buttons'];
        // return the merged toolbars
        $mergedButtons = $this->mergeRemovedButtons($removedButtons);
        $fullToolbar = $this->getAsOldSyntax($mergedButtons, 'full');
        return $fullToolbar;
    }

    /**
     * Get all toolbar ids of the assigned user groups of the current user
     * @return array    All the available toolbar ids. Be wary though, as this
     *                  array might be empty.
     */
    protected function getToolbarIdsOfUserGroup() {
        // Get the database connection
        $pdo = $this->cx->getDb()->getPdoConnection();
        // Load the current user
        $user = \FWUser::getFWUserObject();
        // Login user
        $user->objUser->login(true);
        // Get all assigned user group ids
        $groupIds = $user->objUser->getAssociatedGroupIds();
        // Initiate an empty array of toolbarIds
        $toolbarIds = array();
        // Loop through all user group ids
        foreach ($groupIds as $groupId) {
            // Load user group information
            $groupResult = $pdo->query("
                SELECT `toolbar` FROM `" . DBPREFIX . "access_user_groups`
                WHERE `group_id` = " . intval($groupId) . "
                LIMIT 1");
            // Assure that the query did not fail
            if ($groupResult !== false) {
                $hasToolbar = $groupResult->fetch(\PDO::FETCH_ASSOC);
                // Verify that we could fetch the result
                if ($hasToolbar !== false) {
                    // Check if the user group has a toolbar assigned
                    if (!empty($hasToolbar['toolbar'])) {
                        // Store the assigned toolbar id
                        $toolbarIds[] = $hasToolbar['toolbar'];
                    }
                }
            }
        }
        return $toolbarIds;
    }

    /**
     * Load the removed button of the given toolbar ids
     * @param   array   $toolbarIds Array containing all ids of the toolbars
     *                              that shall be loaded
     * @return  array               Array containing the removed buttons of
     *                              the given toolbar ids
     */
    protected function loadRemovedButtons(array $toolbarIds) {
        // Get the database connection
        $pdo = $this->cx->getDb()->getPdoConnection();
        // Initiate an empty removedButtons array
        $removedButtons = array();
        // Loop through each toolbar id
        foreach ($toolbarIds as $toolbarId) {
            // Load the removed buttons from the database
            $removedButtonRes = $pdo->query("
                SELECT `removed_buttons`
                  FROM `" . DBPREFIX . "core_wysiwyg_toolbar`
                WHERE `id` = " . intval($toolbarId). "
                LIMIT 1 ");
            if ($removedButtonRes !== false) {
                // Fetch the removed buttons
                $removedButton = $removedButtonRes->fetch(\PDO::FETCH_ASSOC);
                // Verify that the toolbar has any removed buttons
                if (!empty($removedButton)) {
                    // Store the available functions for the current toolbar
                    $removedButtons[] = $removedButton['removed_buttons'];
                }
            }
        }
        return $removedButtons;
    }

    /**
     * Merge the given removed buttons in $removedButtons together
     * @param   array   $removedButtons The removed buttons which shall be
     *                                  merged together
     * @return  string                  The merged buttons. Be wary that this
     *                                  string might be empty
     */
    protected function mergeRemovedButtons(array $removedButtons) {
        $mergedButtons = array();
        // Verify that there is anything to merge at all
        if (empty($removedButtons)) {
            return '';
        }
        // Check if there is more than one list of buttons
        if (count($removedButtons) < 2) {
            // Merging only one list of buttons would be pointless
            if (array_key_exists('default', $removedButtons)) {
                $mergedButtons = $removedButtons['default'];
            } else {
                $mergedButtons = $removedButtons[0];
            }
        } else {
            $defaultButtons = array();
            if (array_key_exists('default', $removedButtons)) {
                $defaultButtons = $removedButtons['default'];
            }
            // Loop through all removed buttons
            for ($i = 0; $i < count($removedButtons); ++$i) {
                $buttons = explode(',', $removedButtons[$i]);
                $mergedButtons = array_diff($defaultButtons, $buttons, $mergedButtons);
            }
        }
        // Combine the merged buttons into a string
        return $mergedButtons;
    }

    /**
     * Get the buttons that shall be removed
     * @param bool|false    $buttonsOnly    Only buttons no config.removeButtons
     *                                      prefix
     * @return string       Either the list of removed buttons or the proper
     *                      config string
     * @TODO implement merge for user groups
     */
    public function getRemovedButtons($buttonsOnly = false) {
        if ($this->cx->getMode() == 'frontend') {
            return '';
        }
        // Initiate default buttons with the buttons that are not allowed
        $buttons = $this->defaultRemovedButtons;
        // Prepare the query to load the default configuration
        $query = 'SELECT `removed_buttons` FROM `' . DBPREFIX . 'core_wysiwyg_toolbar`
                  WHERE `is_default` = 1
                  LIMIT 1';
        $defaultButtonsRes = $this->cx->getDb()->getPdoConnection()->query($query);
        // Verify that the query could be executed
        if ($defaultButtonsRes !== false) {
            // Fetch the data
            $defaultButtons = $defaultButtonsRes->fetch(\PDO::FETCH_ASSOC);
            // Check that a default toolbar has been configured
            if (!empty($defaultButtons)) {
                $buttons= $defaultButtons['removed_buttons'];
            }
        }
        // Used to hide functions that are not allowed to be enable
        if ($buttonsOnly) {
            $buttons =  '\'' . $this->defaultRemovedButtons . '\'';
        } else {
            $buttons = 'config.removeButtons = \'' . $buttons . '\'';
        }
        return $buttons;
    }
}