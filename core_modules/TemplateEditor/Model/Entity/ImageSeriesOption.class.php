<?php

namespace Cx\Core_Modules\TemplateEditor\Model\Entity;
use Cx\Core\Html\Sigma;
use Cx\Core_Modules\MediaBrowser\Model\MediaBrowser;

/**
 * 
 */
class ImageSeriesOption extends Option {

    /**
     * @var Option[]
     */
    protected $urls;

    /**
     * @param String $name
     * @param        $humanname
     * @param array  $data
     */
    public function __construct($name, $humanname, $data)
    {
        parent::__construct($name,$humanname, $data);
        $this->urls = $data['urls'];
        // TODO: Implement _construct() method.
    }

    /**
     * @param Sigma $template
     */
    public function renderBackend($template)
    {
        $subTemplate = new Sigma();
        $subTemplate->loadTemplateFile('core_modules/TemplateEditor/View/Template/Backend/ImagesSeriesOption.html');
        foreach ($this->urls as $url) {
            $subTemplate->setVariable('TEMPLATEEDITOR_OPTION_VALUE', $url);
            $subTemplate->parse('images');
        }
        $mediaBrowser = new MediaBrowser();
        $mediaBrowser->setCallback('callback_'.$this->name);
        $subTemplate->setVariable('MEDIABROWSER_BUTTON', $mediaBrowser->getXHtml('Bild hinzufügen'));
        $subTemplate->setVariable('TEMPLATEEDITOR_OPTION_NAME', $this->name);
        $subTemplate->setVariable('TEMPLATEEDITOR_OPTION_HUMAN_NAME', $this->humanName);
        $template->setVariable('TEMPLATEEDITOR_OPTION', $subTemplate->get());
        $template->setVariable('TEMPLATEEDITOR_OPTION_TYPE', 'img series');
        $template->parse('option');
    }

    /**
     * @param Sigma $template
     */
    public function renderFrontend($template)
    {
        // TODO: Implement renderFrontend() method.
    }

    /**
     * @param array $data
     */
    public function handleChange($data)
    {
        // TODO: Implement handleChange() method.
    }
}