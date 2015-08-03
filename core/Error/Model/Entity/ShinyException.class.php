<?php

/**
 * Create error pages
 *
 * @copyright	CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @package	    contrexx
 * @subpackage  core_error
 * @version		1.0.0
 */

namespace Cx\Core\Error\Model\Entity;

/**
 * Display a exception as a human readable error message
 *
 * @copyright	CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @package	    contrexx
 * @subpackage  core_error
 * @version		1.0.0
 */
class ShinyException extends \Exception
{
    protected $templateFile = null;
    protected $templatePlaceholders = array();

    public function __construct($message = null, $code = 0, \Exception $previous = null) {
        $this->templateFile = \Env::get('cx')->getCodeBaseCorePath() . '/Error/View/Template/Backend/ShinyException.html';
        parent::__construct($message, $code, $previous);

        $this->templatePlaceholders = array(
            'ERROR_EXCEPTION_MESSAGE'           => $this->getMessage(),
            'ERROR_EXCEPTION_CODE'              => $this->getCode(),
            'ERROR_EXCEPTION_FILE'              => $this->getFile(),
            'ERROR_EXCEPTION_LINE'              => $this->getLine(),
            'ERROR_EXCEPTION_TRACE'             => $this->getTraceAsString(),
            'ERROR_EXCEPTION_PREVIOUS'          => $this->getPrevious(),
        );
    }

    public function setTemplateFile($templateFile) {
        $this->templateFile = $templateFile;
    }

    public function registerPlaceholders($placeholders) {
        $this->templatePlaceholders = array_merge($this->templatePlaceholders, $placeholders);
    }

    public function getBackendViewMessage() {
        $template = new \Cx\Core\Html\Sigma();
        $template->setErrorHandling(PEAR_ERROR_DIE);
        $template->loadTemplateFile($this->templateFile);
        $template->setVariable($this->templatePlaceholders);
        \Cx\Core\Csrf\Controller\Csrf::add_placeholder($template);
        return $template->get();
    }
}

