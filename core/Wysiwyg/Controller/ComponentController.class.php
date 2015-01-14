<?php
/**
 * @copyright   Comvation AG
 * @author      Sebastian Brand <sebastian.brand@comvation.com>
 * @package     contrexx
 * @subpackage  core_wysiwyg
 */

namespace Cx\Core\Wysiwyg\Controller;

class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController implements \Cx\Core\Event\Model\Entity\EventListener {
    
    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponent $systemComponent, \Cx\Core\Core\Controller\Cx $cx) {
        parent::__construct($systemComponent, $cx);
        $evm = $cx->getEvents();
        $evm->addEventListener('wysiwygCssReload', $this);
    }
    
    /*
     * This function controlls the events from the eventListener
     */
    public function onEvent($eventName, array $eventArgs) {
        switch ($eventName) {
            case 'wysiwygCssReload':
                $themeRepo = new \Cx\Core\View\Model\Repository\ThemeRepository();
                $skinId = $eventArgs[0]['skin'];
                $result = $eventArgs[1];

                $skin = $themeRepo->getDefaultTheme()->getFoldername();
                //0 is default theme so you dont must change the themefolder
                if(!empty($skinId) && $skinId>0){
                    $skin = $themeRepo->findById($skinId)->getFoldername();
                }
                //getThemeFileContent
                $filePath = $skin.'/index.html';
                $content = '';

                if (file_exists($this->cx->getWebsiteThemesPath().'/'.$filePath)) {
                    $content = file_get_contents($this->cx->getWebsiteThemesPath().'/'.$filePath);
                } elseif (file_exists($this->cx->getCodeBaseThemesPath().'/'.$filePath)) {
                    $content = file_get_contents($this->cx->getCodeBaseThemesPath().'/'.$filePath);
                }

                $cssArr = \JS::findCSS($content);
                $result['wysiwygCss'] = $cssArr;

                break;
            default:
                break;
        }
    }
    
    public function getControllerClasses() {
        return array('Backend');
    }
    
    /*
     * find all wysiwyg templates and retrun it in the correct format for the ckeditor
     */
    public function getWysiwygTempaltes() {
        $em = $this->cx->getDb()->getEntityManager();
        $repo = $em->getRepository('Cx\Core\Wysiwyg\Model\Entity\Wysiwyg');
        $allWysiwyg = $repo->findBy(array('inactive'=>'0'));
        $containerArr = array();
        foreach ($allWysiwyg as $wysiwyg) {
            $containerArr[] = array(
                'title' => $wysiwyg->getTitle(),
                'image' => $wysiwyg->getImagePath(),
                'description' => $wysiwyg->getDescription(),
                'html' => $wysiwyg->getHtmlContent(),
            );
        }
        
        return json_encode($containerArr);
    }

}
