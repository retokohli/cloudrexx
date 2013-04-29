<?php
/**
 * Class FrontendController
 *
 * This class renders the frontend for this component. The method getPage()
 * will be moved to an abstract superclass in later releases of Contrexx.
 *
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  module_demo
 * @version     1.0.0
 */

namespace Cx\Core_Modules\FrontendEditing\Controller;

/**
 * Class FrontendController
 *
 * This class renders the frontend for this component. The method getPage()
 * will be moved to an abstract superclass in later releases of Contrexx.
 *
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  module_demo
 * @version     1.0.0
 */
class FrontendController
{
    public function initFrontendEditing() {
        global $objInit, $_ARRAYLANG, $page;
        // add css and javascript file
        $jsFilesRoot = substr(ASCMS_CORE_MODULE_FOLDER.'/' . \Cx\Core_Modules\FrontendEditing\Controller\ComponentController::getName() . '/View/Script', 1);

        \JS::registerCSS(substr(ASCMS_CORE_MODULE_FOLDER.'/' . \Cx\Core_Modules\FrontendEditing\Controller\ComponentController::getName() . '/View/Style' . '/Main.css', 1));
        \JS::registerJS($jsFilesRoot . '/Main.js');
        \JS::registerJS($jsFilesRoot . '/CKEditorPlugins.js');

        // activate ckeditor
        \JS::activate('ckeditor');
        \JS::activate('jquery-cookie');

        // load language data
        $_ARRAYLANG = $objInit->loadLanguageData('FrontendEditing');
        $langVariables = array(
            'TXT_FRONTEND_EDITING_SHOW_TOOLBAR' => $_ARRAYLANG['TXT_FRONTEND_EDITING_SHOW_TOOLBAR'],
            'TXT_FRONTEND_EDITING_HIDE_TOOLBAR' => $_ARRAYLANG['TXT_FRONTEND_EDITING_HIDE_TOOLBAR'],
            'TXT_FRONTEND_EDITING_PUBLISH'      => $_ARRAYLANG['TXT_FRONTEND_EDITING_PUBLISH'],
            'TXT_FRONTEND_EDITING_SAVE'         => $_ARRAYLANG['TXT_FRONTEND_EDITING_SAVE'],
            'TXT_FRONTEND_EDITING_EDIT'         => $_ARRAYLANG['TXT_FRONTEND_EDITING_EDIT'],
            'TXT_FRONTEND_EDITING_STOP_EDIT'    => $_ARRAYLANG['TXT_FRONTEND_EDITING_STOP_EDIT'],
            'TXT_FRONTEND_EDITING_HAS_DRAFT'    => sprintf($_ARRAYLANG['TXT_FRONTEND_EDITING_HAS_DRAFT'], '<a id="fe_toolbar_load_draft">' . $_ARRAYLANG['TXT_FRONTEND_EDITING_HAS_DRAFT_LOAD'] . '</a>'),
        );

        // add toolbar to html
        $this->prepareTemplate();

        // assign js variables
        $ContrexxJavascript = \ContrexxJavascript::getInstance();
        $ContrexxJavascript->setVariable('langVars', $langVariables, 'frontendEditing');
        $ContrexxJavascript->setVariable('pageId', $page->getId(), 'frontendEditing');

        $configPath = ASCMS_PATH_OFFSET.substr(\Env::get('ClassLoader')->getFilePath(ASCMS_CORE_PATH.'/Wysiwyg/ckeditor.config.js.php'), strlen(ASCMS_DOCUMENT_ROOT));
        $ContrexxJavascript->setVariable('configPath', $configPath."?langId=".FRONTEND_LANG_ID, 'frontendEditing');
    }

    private function prepareTemplate() {
        global $_ARRAYLANG, $license, $objInit, $objTemplate, $page;

        $componentTemplate = new \Cx\Core\Html\Sigma(ASCMS_CORE_MODULE_PATH.'/' . \Cx\Core_Modules\FrontendEditing\Controller\ComponentController::getName() . '/View/Template');
        $componentTemplate->setErrorHandling(PEAR_ERROR_DIE);

        // add div for toolbar after starting body tag
        $componentTemplate->loadTemplateFile('Toolbar.html');
        $componentTemplate->setVariable(array(
            'TXT_UPGRADE' => $_ARRAYLANG['TXT_FRONTEND_EDITING_TOOLBAR_UPGRADE'],
            'LINK_LICENSE' => ASCMS_PATH_OFFSET . '/cadmin/index.php?cmd=license',
        ));

        $objUser = \FWUser::getFWUserObject()->objUser;
        $firstname = $objUser->getProfileAttribute('firstname');
        $lastname = $objUser->getProfileAttribute('lastname');
        $componentTemplate->setGlobalVariable(array(
            'LOGGED_IN_USER'                        => !empty($firstname) && !empty($lastname) ? $firstname . ' ' . $lastname : $objUser->getUsername(),
            'TXT_LOGOUT'                            => $_ARRAYLANG['TXT_FRONTEND_EDITING_TOOLBAR_LOGOUT'],
            'TXT_FRONTEND_EDITING_TOOLBAR_OPEN_CM'  => $_ARRAYLANG['TXT_FRONTEND_EDITING_TOOLBAR_OPEN_CM'],
            'LINK_LOGOUT'                           => $objInit->getUriBy('section', 'logout'),
            'LINK_PROFILE'                          => ASCMS_PATH_OFFSET . '/cadmin/index.php?cmd=access&amp;act=user&amp;tpl=modify&amp;id=' . $objUser->getId(),
            'LINK_CM'                               => ASCMS_PATH_OFFSET . '/cadmin/index.php?cmd=content&amp;page=' . $page->getId() . '&amp;tab=content',
        ));

        if ($componentTemplate->blockExists('upgradable')) {
            if ($license->isUpgradable()) {
                $componentTemplate->parse('upgradable');
            } else {
                $componentTemplate->hideBlock('upgradable');
            }
        }
        $objTemplate->_blocks['__global__'] = preg_replace('/<body[^>]*>/', '\\0' . $componentTemplate->get(), $objTemplate->_blocks['__global__']);
    }

    /**
     * Checks whether the frontend editing is active or not
     * @return boolean
     */
    public static function frontendEditingIsActive() {
        global $_CONFIG, $page;
        // check permission and frontend editing status
        if (   \FWUser::getFWUserObject()->objUser->getAdminStatus()
            || (   $_CONFIG['frontendEditingStatus'] == 'on'
                && \Permission::checkAccess(6, 'static', true)
                && \Permission::checkAccess(35, 'static', true)
                && (   !$page->isBackendProtected()
                    || Permission::checkAccess($page->getId(), 'page_backend', true)))
        ) {
            return true;
        }
        return false;
    }
}
