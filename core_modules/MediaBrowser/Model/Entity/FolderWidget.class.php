<?php

/**
 * Class FolderWidget
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 */

namespace Cx\Core_Modules\MediaBrowser\Model\Entity;

/**
 * Class FolderWidget
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 */
class FolderWidget extends \Cx\Model\Base\EntityBase
{
    /**
     * mediabrowser mode - view only
     */
    const MODE_VIEW_ONLY = 1;

    /**
     * The folder we are monitoring
     */
    protected $folder;
    
    /**
     * The unique widget identifier
     */
    protected $id;
    
    /**
     * The Curren mode of folder widget
     */
    protected $mode;

    /**
     * Init the folder widget
     * 
     * @param string  $folder
     * @param boolean $viewOnly
     */
    public function __construct($folder, $viewOnly = false)
    {
        if (!isset($_SESSION['MediaBrowser'])) {
            $_SESSION['MediaBrowser'] = array();
        }
        if (!isset($_SESSION['MediaBrowser']['FolderWidget'])) {
            $_SESSION['MediaBrowser']['FolderWidget'] = array();
        }
        $lastKey  = count($_SESSION['MediaBrowser']['FolderWidget']);
        $widgetId = $lastKey++;
        
        $this->id = $widgetId;
        
        $this->folder = $folder;
        
        if ($viewOnly) {
            $this->mode = self::MODE_VIEW_ONLY;
        }
        
        $_SESSION['MediaBrowser']['FolderWidget'][$this->id] = array(
            'folder' => $this->folder,
            'mode'   => $this->mode
        );
    }
    
    /**
     * Set the folder widget id
     * 
     * @param integer $id
     */
    public function setId($id) {
        $this->id = $id;
    }
    
    /**
     * Get the folder widget id
     * 
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set the folder path
     * 
     * @param string $folder
     */
    public function setFolder($folder) {
        $this->folder = $folder;
    }
    
    /**
     * Get the XHTML to display the widget.
     */
    public function getXhtml()
    {
        \JS::activate('mediabrowser');
        \JS::registerJS('core_modules/MediaBrowser/View/Script/FolderWidget.js');

        $tpl = new \Cx\Core\Html\Sigma(\Cx\Core\Core\Controller\Cx::instanciate()->getCoreModuleFolderName().'/MediaBrowser/View/Template/');
        
        $tpl->loadTemplateFile('FolderWidget.html');
        $tpl->setVariable(array(
            'MEDIABROWSER_FOLDER_WIDGET_ID'          => $this->id,
            'MEDIABROWSER_FOLDER_WIDGET_IS_EDITABLE' => ($this->mode != self::MODE_VIEW_ONLY) ? 'true' : 'false',
        ));

        return $tpl->get();
    }
    
    /**
     * Php magic method. calls the $this->getXhtml()
     * 
     * @return string 
     */
    public function __toString()
    {
        return $this->getXhtml();
    }
}