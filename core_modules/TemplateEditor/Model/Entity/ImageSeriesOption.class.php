<?php

namespace Cx\Core_Modules\TemplateEditor\Model\Entity;
use Cx\Core\Core\Controller\Cx;
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
        foreach ($data['urls'] as $key => $url){
            if (!empty($url)){
                $this->urls[$key] = $url;
            }
        }
    }

    /**
     * @param Sigma $template
     */
    public function renderBackend($template)
    {
        global $_ARRAYLANG;
        $subTemplate = new Sigma();
        $subTemplate->loadTemplateFile('core_modules/TemplateEditor/View/Template/Backend/ImagesSeriesOption.html');
        $subTemplate->setGlobalVariable($_ARRAYLANG);

        foreach ($this->urls as $id => $url) {
            $subTemplate->setVariable('TEMPLATEEDITOR_OPTION_VALUE', $url);
            $subTemplate->setVariable('TEMPLATEEDITOR_OPTION_ID', $id);
            $subTemplate->parse('images');
        }
        $mediaBrowser = new MediaBrowser();
        $mediaBrowserId = $this->name.'_mediabrowser';
        $mediaBrowser->setOptions(array('id' =>$mediaBrowserId));
        $mediaBrowser->setCallback('callback_'.$this->name);
        $subTemplate->setVariable('MEDIABROWSER_BUTTON', $mediaBrowser->getXHtml($_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_ADD_PICTURE']));
        $subTemplate->setVariable('MEDIABROWSER_ID', $mediaBrowserId);
        $subTemplate->setVariable('TEMPLATEEDITOR_OPTION_NAME', $this->name);
        $subTemplate->setVariable('TEMPLATEEDITOR_OPTION_HUMAN_NAME', $this->humanName);
        //Get last key
        end($this->urls);
        $key = key($this->urls);
        $key = $key != null ? $key : '0';
        $subTemplate->setVariable('TEMPLATEEDITOR_LASTID',$key);
        $template->setVariable('TEMPLATEEDITOR_OPTION', $subTemplate->get());
        $template->setVariable('TEMPLATEEDITOR_OPTION_TYPE', 'img series');

        $template->parse('option');
    }

    /**
     * @param Sigma $template
     */
    public function renderFrontend($template)
    {
        $blockName = strtolower('TEMPLATE_EDITOR_'.$this->name);
        if ($template->blockExists($blockName)){
            foreach ($this->urls as $id => $url) {
                $template->setVariable(strtoupper('TEMPLATE_EDITOR_'.$this->name), htmlentities($url));
                $template->parse($blockName);
            }
        }
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws OptionValueNotValidException
     */
    public function handleChange($data)
    {

        global $_ARRAYLANG;
        if (empty($data['id']) && $data['id'] != 0) {
            throw new OptionValueNotValidException("Needs a id to work");
        }
        if (empty($data['url'])){
            if (isset($data['action']) && $data['action'] == 'remove'){
                unset($this->urls[intval($data['id'])]);
            }
            else {
                throw new OptionValueNotValidException(sprintf($_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_VALUE_EMPTY']));
            }
        }
        $url = parse_url($data['url']);
        if (!isset($url['host'])){
            if (!file_exists(Cx::instanciate()->getWebsitePath().$url['path'])){
                if (!file_exists(Cx::instanciate()->getCodeBasePath().$url['path'])){
                    throw new OptionValueNotValidException(sprintf($_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_IMAGE_FILE_NOT_FOUND'], $url['path']));
                }
            }
        }
        $this->urls[$data['id']] = $data['url'];
        return array('urls' => $this->urls);
    }

    /**
     * @return string
     */
    public function yamlSerialize()
    {
        $option = parent::yamlSerialize();
        $option['specific'] = array(
            'urls' => $this->urls
        );
        return $option;
    }
}