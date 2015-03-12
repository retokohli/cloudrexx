<?php

namespace Cx\Core_Modules\TemplateEditor\Controller;
use Cx\Core\Core\Controller\Cx;
use Cx\Core\Json\JsonAdapter;
use Cx\Core\View\Model\Repository\ThemeRepository;
use Cx\Core_Modules\TemplateEditor\Model\FileStorage;
use Cx\Core_Modules\TemplateEditor\Model\Repository\ThemeOptionsRepository;

/**
 * 
 */
class JSONTemplateEditor implements JsonAdapter {

    /**
     * @param array $params
     */
    public function updateOption($params) {
        $fileStorage = new FileStorage(
            Cx::instanciate()->getWebsiteThemesPath()
        );
        $themeOptionRepository = new ThemeOptionsRepository($fileStorage);
        $themeRepository = new ThemeRepository();
        $themeID = isset($_GET['tid']) ? $_GET['tid'] : 1;
        $theme = $themeRepository->findById($themeID);
        $themeOptions = $themeOptionRepository->get(
            $theme
        );
        if (empty($params['post']['optionName']) && !preg_match('/[a-z]+/i',$params['post']['optionName'])){
            throw new \LogicException("This method needs a valid name to work.");
        }
        if (empty($params['post']['optionData'])){
            throw new \LogicException("This method needs data to work.");
        }
        $data = $themeOptions->handleChanges($params['post']['optionName'], $params['post']['optionData']);
        if (!isset($_SESSION['TemplateEditor'])){
            $_SESSION['TemplateEditor'] = array();
        }
        if (!isset($_SESSION['TemplateEditor'][$themeID])){
            $_SESSION['TemplateEditor'][$themeID] = array();
        }

        $_SESSION['TemplateEditor'][$themeID][$params['post']['optionName']] = $data;
        $themeOptionRepository->save($themeOptions);

    }

    /**
     * Returns the internal name used as identifier for this adapter
     *
     * @return String Name of this adapter
     */
    public function getName()
    {
        return 'TemplateEditor';
    }

    /**
     * Returns an array of method names accessable from a JSON request
     *
     * @return array List of method names
     */
    public function getAccessableMethods()
    {
        return array('updateOption');
    }

    /**
     * Returns all messages as string
     *
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString()
    {
        // TODO: Implement getMessagesAsString() method.
    }

    /**
     * Returns default permission as object
     *
     * @return Object
     */
    public function getDefaultPermissions()
    {
        // TODO: Implement getDefaultPermissions() method.
}}