<?php

namespace Cx\Core_Modules\TemplateEditor\Model\Entity;
use Cx\Core\Html\Sigma;
use Cx\Core_Modules\MediaBrowser\Model\MediaBrowser;

/**
 * 
 */
class ImageOption extends Option {
    protected $url;

    /**
     * @param String $name
     * @param array  $data
     */
    public function __construct($name,$humanname, $data)
    {
        parent::__construct($name,$humanname, $data);
        $this->url = $data['url'];
        // TODO: Implement _construct() method.
    }

    /**
     * @param Sigma $template
     */
    public function renderBackend($template)
    {
        $subTemplate = new Sigma();
        $subTemplate->loadTemplateFile('core_modules/TemplateEditor/View/Template/Backend/ImageOption.html');
        $subTemplate->setVariable('TEMPLATEEDITOR_OPTION_VALUE', $this->url);
        $subTemplate->setVariable('TEMPLATEEDITOR_OPTION_NAME', $this->name);
        $subTemplate->setVariable('TEMPLATEEDITOR_OPTION_HUMAN_NAME', $this->humanName);
        $mediaBrowser = new MediaBrowser();
        $mediaBrowser->setCallback('callback_'.$this->name);
        $subTemplate->setVariable('MEDIABROWSER_BUTTON', $mediaBrowser->getXHtml('Bild auswählen'));
        $template->setVariable('TEMPLATEEDITOR_OPTION', $subTemplate->get());
        $template->setVariable('TEMPLATEEDITOR_OPTION_TYPE', 'img');
        $template->parse('option');
        // TODO: Implement renderBackend() method.
    }

    /**
     * @param Sigma $template
     */
    public function renderFrontend($template)
    {
        $template->setVariable('TEMPLATE_EDITOR_'.strtoupper($this->name), $this->url);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function handleChange($data)
    {
        $this->url = $data;
        return array('url' => $data);
    }

    /**
     * @return string
     */
    public function yamlSerialize()
    {
        $option = parent::yamlSerialize();
        $option['specific'] = array(
            'url' => $this->url
        );
        return $option;
    }
}