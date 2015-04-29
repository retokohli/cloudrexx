<?php

/**
 * Class UploadResponse
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_uploader
 */

namespace Cx\Core_Modules\Uploader\Controller;

/**
 * UploadResponses result from an upload request.
 * They carry information about problems concerning uploaded files.
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_uploader
 */
class UploadResponse {

    /**
     * @var array array( array( 'status' => 'ok'|'error'..., 'message' => string, 'file' => string ) )
     */
    protected $logs = array();

    /**
     * Define the normal status
     */
    const STATUS_OK = 0;

    /**
     * Define the warning status
     */
    const STATUS_WARNING = 1;

    /**
     * Define the error status
     */
    const STATUS_ERROR = 2;

    /**
     * Define the info status
     */
    const STATUS_INFO = 3;

    /**
     * The worstStatus is indentifying status of the upload files.
     * @var integer 
     */
    protected $worstStatus = 0;

    /**
     *
     * @var array 
     */
    protected $statusTexts = array(
        self::STATUS_OK => 'ok',
        self::STATUS_WARNING => 'warning',
        self::STATUS_ERROR => 'error',
        self::STATUS_INFO => 'info'
    );

    public function __construct() {
        
    }

    /**
     * Adds a log message concerning a file to the response.
     * 
     * @param string $status  one of UploadResponse::STATUS_(OK|WARNING|ERROR|INFO)
     * @param string $message message
     */
    public function addMessage($status, $message) {
        $this->logs[] = array(
            'status' => $this->statusTexts[$status],
            'message' => $message,
        );
        if ($status > $this->worstStatus) {
            $this->worstStatus = $status;
        }
    }

    /**
     * get the response
     * 
     * @return array
     */
    public function getResponse() {
        return array(
            'status' => $this->statusTexts[$this->worstStatus],
            'messages' => $this->logs,
        );
    }

    /**
     * get the worst status
     * 
     * @return integer
     */
    public function getWorstStatus() {
        return $this->worstStatus;
    }

}

