<?php

/**
 * Sigma
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_html
 */

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cx\Core\Html;

/**
 * Description of Sigma
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  core_html
 */
class Sigma extends \HTML_Template_Sigma {
    
    public function __construct($root = '', $cacheRoot = '') {
        parent::__construct($root, $cacheRoot);
        $this->setErrorHandling(PEAR_ERROR_DIE);
    }
    
    /**
     * Reads the file and returns its content
     *
     * @param    string    filename
     * @return   string    file content (or error object)
     * @access   private
     */
    function _getFile($filename)
    {
        $filename = \Env::get('ClassLoader')->getFilePath($filename);
        if (!($fh = @fopen($filename, 'r'))) {
            return $this->raiseError($this->errorMessage(SIGMA_TPL_NOT_FOUND, $filename), SIGMA_TPL_NOT_FOUND);
        }
        $content = fread($fh, filesize($filename));
        fclose($fh);
        return $content;
    }
    
    function getRoot() {
        return $this->fileRoot;
    }
}
