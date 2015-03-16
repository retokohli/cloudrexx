<?php

namespace Cx\Core_Modules\TemplateEditor\Model\Entity;

use Cx\Core\Core\Controller\Cx;
use Cx\Core\Html\Sigma;
use Cx\Core_Modules\MediaBrowser\Model\MediaBrowser;

/**
 * Class ImageOption
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class ImageOption extends Option
{

    /**
     * Url to image
     *
     * @var String
     */
    protected $url;

    /**
     * @param String $name
     * @param array  $translations
     * @param array  $data
     */
    public function __construct($name, $translations, $data) {
        parent::__construct($name, $translations, $data);
        $this->url = $data['url'];
    }

    /**
     * @param Sigma $template
     */
    public function renderBackend($template) {
        global $_ARRAYLANG;
        $subTemplate = new Sigma();
        $subTemplate->loadTemplateFile(
            'core_modules/TemplateEditor/View/Template/Backend/ImageOption.html'
        );
        $subTemplate->setGlobalVariable($_ARRAYLANG);
        $subTemplate->setVariable('TEMPLATEEDITOR_OPTION_VALUE', $this->url);
        $subTemplate->setVariable('TEMPLATEEDITOR_OPTION_NAME', $this->name);
        $subTemplate->setVariable(
            'TEMPLATEEDITOR_OPTION_HUMAN_NAME', $this->humanName
        );
        $mediaBrowser = new MediaBrowser();
        $mediaBrowser->setCallback('callback_' . $this->name);
        $subTemplate->setVariable(
            'MEDIABROWSER_BUTTON', $mediaBrowser->getXHtml(
            $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_CHOOSE_PICTURE']
        )
        );
        $template->setVariable('TEMPLATEEDITOR_OPTION', $subTemplate->get());
        $template->setVariable('TEMPLATEEDITOR_OPTION_TYPE', 'img');
        $template->parse('option');
    }

    /**
     * @param Sigma $template
     */
    public function renderFrontend($template) {
        $template->setVariable(
            'TEMPLATE_EDITOR_' . strtoupper($this->name),
            htmlentities($this->url)
        );
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws OptionValueNotValidException
     */
    public function handleChange($data) {
        global $_ARRAYLANG;
        $url = parse_url($data);
        if (!isset($url['host'])) {
            if (!file_exists(
                Cx::instanciate()->getWebsitePath() . $url['path']
            )
            ) {
                if (!file_exists(
                    Cx::instanciate()->getCodeBasePath() . $url['path']
                )
                ) {
                    throw new OptionValueNotValidException(
                        sprintf(
                            $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_IMAGE_FILE_NOT_FOUND'],
                            $url['path']
                        )
                    );
                }
            }
        }
        $this->url = $data;
        return array('url' => $data);
    }

    /**
     * @return string
     */
    public function yamlSerialize() {
        $option             = parent::yamlSerialize();
        $option['specific'] = array(
            'url' => $this->url
        );
        return $option;
    }

    /**
     * @return mixed
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url) {
        $this->url = $url;
    }
}