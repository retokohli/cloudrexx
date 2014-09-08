<?php
/**
 * Sigma
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_html
 */

namespace Cx\Core\Html;

/**
 * Description of Sigma
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Michael Ritter <michael.ritter@comvation.com>
 * @author Thomas Däppen <thomas.daeppen@comvation.com>
 * @package     contrexx
 * @subpackage  core_html
 */
class Sigma extends \HTML_Template_Sigma {

    protected $restoreFileRoot = null;
    
    public function __construct($root = '', $cacheRoot = '') {
        parent::__construct($root, $cacheRoot);
        $this->removeVariablesRegExp = '@' . $this->openingDelimiter . '(' . $this->variablenameRegExp . ')\s*'
            . $this->closingDelimiter . '@sm';
        $this->setErrorHandling(PEAR_ERROR_DIE);
    }
    
    function getRoot() {
        return $this->fileRoot;
    }

    function loadTemplateFile($filename, $removeUnknownVariables = true, $removeEmptyBlocks = true) {
        $this->mapCustomizing($filename);
        $return = parent::loadTemplateFile($filename, $removeUnknownVariables, $removeEmptyBlocks);
        $this->unmapCustomizing();
        return $return;
    }

    function addBlockfile($placeholder, $block, $filename) {
        $this->mapCustomizing($filename);
        $return = parent::addBlockfile($placeholder, $block, $filename);
        $this->unmapCustomizing();
        return $return;
    }

    function replaceBlockfile($block, $filename, $keepContent = false) {
        $this->mapCustomizing($filename);
        $return = parent::replaceBlockfile($block, $filename, $keepContent);
        $this->unmapCustomizing();
        return $return;
    }

    /**
     * The customizing mechanism does not apply to method _getCached().
     * Therefore it is not overwritten.
     */
    /** function _getCached($filename, $block = '__global__', $placeholder = '') {} */

    /**
     * Detects if $filename is customized.
     * If so, it causes \HTML_Template_Sigma to load the customized version
     * of the file.
     * @param   string $filename    The filename passed by the overwritten methods of \HTML_Template_Sigma
     */
    protected function mapCustomizing($filename) {
        // check if template is customized
        $filePath = \Env::get('ClassLoader')->getFilePath($this->fileRoot . $filename);
        if ($filePath != $this->fileRoot . $filename) {
            // backup original fileRoot
            $this->restoreFileRoot = $this->fileRoot;

            // point fileRoot to customizing path
            $newFileRoot = substr($filePath, 0, -strlen($filename));
            $this->fileRoot = $newFileRoot;
        }
    }

    /**
     * In case a customized version of a file has been loaded.
     * This method does revert \HTML_Template_Sigma so that is will
     * continue to load regular files without customizings.
     */
    protected function unmapCustomizing() {
        if ($this->restoreFileRoot) {
            $this->fileRoot = $this->restoreFileRoot;
            $this->restoreFileRoot = null;
        }
    }
}
