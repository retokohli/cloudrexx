<?php
/**
 * Main controller for Error
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_error
 */

namespace Cx\Core\Error\Controller;

/**
 * Main controller for Error
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_error
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
     /**
     * HTML Template
     * @access private
     */
    private $objTemplate;
    
    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array();
    }

     /**
     * Load your component.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                $this->objTemplate = new \Cx\Core\Html\Sigma();
                \Cx\Core\Csrf\Controller\ComponentController::add_placeholder($this->objTemplate);
                
                \Env::get('cx')->getPage()->setContent($this->getErrorPage(\Env::get('cx')->getPage()->getContent()));
                break;
        }
    }
    
    /**
     * Set up page containing the error message.
     *
     * This is a synonym for {@link errorHandling()}.
     * @todo Is this deprecated?
     * @return string  Page content
     */
    private function getErrorPage($pageContent)
    {
        return $this->errorHandling($pageContent);
    }


    /**
     * Set up page containing the error message.
     *
     * Sets up an error page determined by the error ID found in the 'id'
     * field of the $_REQUEST array.
     * @return string  Page content
     * @todo Handle more errors.
     */
    private function errorHandling($pageContent)
    {
        $this->objTemplate->setTemplate($pageContent);

        if (!isset($_REQUEST['id'])) {
            $_REQUEST['id'] = "";
        }

        switch ($_REQUEST['id']){
        case '404':
            $errorNo = 404;
            $errorMsg = "Not found";
            break;

        default:
            $errorNo = 404;
            $errorMsg = "Not found";
            break;
        }

        \header(\Env::get('cx')->getRequest()->getUrl()->getProtocol() . ' ' . $errorNo . ' ' . $errorMsg);

        /*
        100 Continue
        101 Switching Protocols
        200 OK
        201 Created
        202 Accepted
        203 Non-Authoritative Information
        204 No Content
        205 Reset Content
        206 Partial Content
        300 Multiple Choices
        301 Moved Permanently
        302 Moved Temporarily
        303 See Other
        304 Not Modified
        305 Use Proxy
        400 Bad Request
        401 Unauthorised
        402 Payment Required
        403 Forbidden
        404 Not Found
        405 Method Not Allowed
        406 Not Acceptable
        407 Proxy Authentication Required
        408 Request Time-Out
        409 Conflict
        410 Gone
        411 Length Required
        412 Precondition Failed
        413 Request Entity Too Large
        414 Request-URL Too Large
        415 Unsupported Media Type
        500 Server Error
        501 Not Implemented
        502 Bad Gateway
        503 Out of Resources
        504 Gateway Time-Out
        505 HTTP Version not supported
        */

        $this->objTemplate->setVariable(array(
            'ERROR_NUMBER'    => $errorNo,
            'ERROR_MESSAGE'    => $errorMsg
        ));
        return $this->objTemplate->get();
    }
}