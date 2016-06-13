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
        foreach (array_keys($oldSyntax) as $key) {
            foreach ($removedButtons as $toRemove) {
                if (in_array($toRemove, $oldSyntax[$key])) {
                    $keyToRemove = array_search($toRemove, $oldSyntax[$key]);
                    unset($oldSyntax[$key][$keyToRemove]);
                    $oldSyntax[$key] = array_reverse(array_reverse($oldSyntax[$key]));
                    if (count($oldSyntax[$key]) == 1) {
                        $tmp = array_reverse(array_reverse($oldSyntax[$key]));
                        if ($tmp[0] == '-') {
                            unset($oldSyntax[$key]);
                        }
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
            'TXT_WYSIWYG_TOOLBAR_SAVE'  => $_ARRAYLANG['TXT_WYSIWYG_TOOLBAR_SAVE'],
        ));

        // Check if the toolbar configurator needs to be wrapped in a form tag
        if (!$isDefaultConfiguration) {
            $template->hideBlock('wysiwyg_toolbar_store_button');
        }
        $template->setVariable(array(
            'TXT_WYSIWYG_TOOLBAR_CONFIGURATOR'  => $_ARRAYLANG['TXT_WYSIWYG_TOOLBAR_CONFIGURATOR'],
        ));

        $this->getToolbarTranslations();

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
        // return the merged toolbars
        $mergedButtons = $this->mergeRemovedButtons($removedButtons);
        $fullToolbar = $this->getAsOldSyntax($mergedButtons, 'full');
        return $fullToolbar;
    }

    /**
     * Get all toolbar ids of the assigned user groups or by the given user groups
     *
     * If the parameter $userGroup is empty the assigned user groups of the
     * current user are used
     * @param   array $userGroups   Array containing the user group ids
     * @return  array $toolbarIds   All the available toolbar ids.
     *                              Be wary though, as this array might be empty
     */
    protected function getToolbarIdsOfUserGroup($userGroups = array()) {
        // Get the database connection
        $pdo = $this->cx->getDb()->getPdoConnection();
        // populate $groupIds with the given user groups
        $groupIds = $userGroups;
        if (empty($groupIds)) {
            // Load the current user
            $user = \FWUser::getFWUserObject();
            // Login user
            $user->objUser->login(true);
            // Get all assigned user group ids
            $groupIds = $user->objUser->getAssociatedGroupIds();
        }
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
            $mergedButtons = $removedButtons[0];
        } else {
            // Create arrays out of the removed button strings
            foreach($removedButtons as $key => $removedButtonsList) {
                $removedButtons[$key] = explode(',', $removedButtonsList);
            }
            // Initiate tmpMerged with the array containing the least amount of
            // removed buttons
            $tmpMerged = min($removedButtons);
            // Unset the index
            unset($removedButtons[array_search($tmpMerged, $removedButtons)]);
            // Renumber the array
            $removedButtons = array_reverse($removedButtons);
            // Loop through all remaining lists of removed buttons
            for ($i = 0; $i <= count($removedButtons) - 1; $i += 2) {
                $buttonsOne = $removedButtons[$i];
                // Check if we have more than one list of removed buttons
                // remaining
                if (array_key_exists($i + 1, $removedButtons)) {
                    $buttonsTwo = $removedButtons[$i + 1];
                    // Get the buttons that are definitely removed
                    $mergedButtons = array_intersect($tmpMerged, $buttonsOne, $buttonsTwo);
                } else {
                    // Only one list of removed buttons left
                    $mergedButtons = array_intersect($tmpMerged, $buttonsOne);
                }
            }
        }
        // Combine the merged buttons into a string
        if (is_array($mergedButtons)) {
            $mergedButtons = join(',', $mergedButtons);
        }
        return $mergedButtons;
    }

    /**
     * Get the buttons that shall be removed
     * @param   bool    $buttonsOnly    Only buttons no config.removeButtons
     *                                  prefix
     * @param   bool    $isAccess       If set remove buttons that are disabled
     *                                  by the default configuration
     * @return  string                  Either the list of removed buttons or
     *                                  the proper config string
     */
    public function getRemovedButtons($buttonsOnly = false, $isAccess = false) {
        if ($this->cx->getMode() == 'frontend') {
            return '';
        }
        // Initiate default buttons with the buttons that are not allowed
        $buttons = $this->defaultRemovedButtons;
        // Initiate the default removed buttons as empty string
        $defaultButtons = '';
        // Get the database connection
        $pdo = $this->cx->getDb()->getPdoConnection();
        // Prepare the query to load the default configuration
        $query = 'SELECT `removed_buttons` FROM `' . DBPREFIX .
                    'core_wysiwyg_toolbar`
                  WHERE `is_default` = 1
                  LIMIT 1';
        $defaultButtonsRes = $pdo->query($query);
        // Verify that the query could be executed
        if ($defaultButtonsRes !== false) {
            // Fetch the data
            $defaultButtons = $defaultButtonsRes->fetch(\PDO::FETCH_ASSOC);
            // Check that a default toolbar has been configured
            if (!empty($defaultButtons)) {
                // Rewrite the buttons with the default removed buttons
                $buttons = $defaultButtons['removed_buttons'];
            }
        }
        // Check if a user group is edited
        if (!empty($_GET['groupId'])) {
            // Get the group id
            $groupId = intval($_GET['groupId']);
            // Prepare the query to load the user group toolbar
            $query = 'SELECT `toolbar` FROM `' . DBPREFIX .
                        'access_user_groups`
                      WHERE `group_id` = ' . $groupId . '
                      LIMIT 1';
            $toolbarIdRes = $pdo->query($query);
            // Verify that the query could be executed
            if ($toolbarIdRes !== false) {
                // Fetch the toolbar id
                $toolbarId = $toolbarIdRes->fetch(\PDO::FETCH_ASSOC);
                if (!empty($toolbarId['toolbar'])) {
                    // Prepare the query to get the removed button of the toolbar
                    $query = 'SELECT `removed_buttons` FROM `' . DBPREFIX .
                        'core_wysiwyg_toolbar`
                          WHERE `id` = ' . intval($toolbarId['toolbar']) . '
                          LIMIT 1';
                    $toolbarButtonsRes = $pdo->query($query);
                    // Verify that the query could be executed
                    if ($toolbarButtonsRes !== false) {
                        // Fetch the removed buttons
                        $toolbarButtons = $toolbarButtonsRes->fetch(
                            \PDO::FETCH_ASSOC
                        );
                        // Verify that there are any removed buttons
                        if (!empty($toolbarButtons)) {
                            // Rewrite the buttons with the removed buttons of
                            // the user group
                            $buttons = $toolbarButtons['removed_buttons'];
                        }
                    }
                }
            }
        }
        // Used to hide functions that are disabled by the default config
        if ($isAccess && $defaultButtons) {
            $buttons = $defaultButtons['removed_buttons'];
        }
        // Used to hide functions that are not allowed to be enable
        if ($buttonsOnly) {
            $buttons =  '\'' . $this->defaultRemovedButtons . '\'';
        } else {
            $buttons = 'config.removeButtons = \'' . $buttons . '\'';
        }
        return $buttons;
    }

    /**
     * Get the translations for the toolbar and store it as a variable
     * 
     * This variable is available through the cx javascript framework. 
     * @return void
     */
    protected function getToolbarTranslations() {
        // Get the init object to change to te proper language file
        $init = \Env::get('init');
        $init->_initBackendLanguage();
        // Check if current language is english or not
        if ($init->getBackendLangId() === 2) {
            // Current language is english no need to translate anything
            return;
        }
        // Initiate $translations as empty stdClass object
        $translations = new \stdClass();
        // Get the language file of the Wysiwyg component (this one btw.)
        $_ARRAYLANG = $init->getComponentSpecificLanguageData('Wysiwyg', false, 1);
        // Populate the std object with the translations
        $translations->mode = $_ARRAYLANG['TXT_WYSIWYG_TOOLBAR_MODE'];
        $translations->document = $_ARRAYLANG['TXT_WYSIWYG_TOOLBAR_DOCUMENT'];
        $translations->doctools = $_ARRAYLANG['TXT_WYSIWYG_TOOLBAR_DOCUMENT_TOOLS'];
        $translations->clipboard = $_ARRAYLANG['TXT_WYSIWYG_TOOLBAR_CLIPBOARD'];
        $translations->undo = $_ARRAYLANG['TXT_WYSIWYG_TOOLBAR_UNDO'];
        $translations->find = $_ARRAYLANG['TXT_WYSIWYG_TOOLBAR_FIND'];
        $translations->selection = $_ARRAYLANG['TXT_WYSIWYG_TOOLBAR_SELECTION'];
        $translations->spellchecker = $_ARRAYLANG['TXT_WYSIWYG_TOOLBAR_SPELLCHECKER'];
        $translations->basicstyles = $_ARRAYLANG['TXT_WYSIWYG_TOOLBAR_BASICSTYLES'];
        $translations->cleanup = $_ARRAYLANG['TXT_WYSIWYG_TOOLBAR_CLEANUP'];
        $translations->list = $_ARRAYLANG['TXT_WYSIWYG_TOOLBAR_LIST'];
        $translations->indent = $_ARRAYLANG['TXT_WYSIWYG_TOOLBAR_INDENT'];
        $translations->blocks = $_ARRAYLANG['TXT_WYSIWYG_TOOLBAR_BLOCKS'];
        $translations->align = $_ARRAYLANG['TXT_WYSIWYG_TOOLBAR_ALIGN'];
        $translations->links = $_ARRAYLANG['TXT_WYSIWYG_TOOLBAR_LINKS'];
        $translations->insert = $_ARRAYLANG['TXT_WYSIWYG_TOOLBAR_INSERT'];
        $translations->styles = $_ARRAYLANG['TXT_WYSIWYG_TOOLBAR_STYLES'];
        $translations->colors = $_ARRAYLANG['TXT_WYSIWYG_TOOLBAR_COLORS'];
        $translations->tools = $_ARRAYLANG['TXT_WYSIWYG_TOOLBAR_TOOLS'];
        \ContrexxJavascript::getInstance()->setVariable(
            'toolbarTranslations',
            $translations,
            'toolbarConfigurator'
        );
    }
}