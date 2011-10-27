<?php
/**
 * UploadResponses result from an upload request.
 * They carry information about problems concerning uploaded files.
 */
class UploadResponse {
    /**
     * @var array array( array( 'status' => 'ok'|'error'..., 'message' => string, 'file' => string ) )
     */
    protected $logs = array();

    /**
     * Stores the count of successfully uploaded files.
     * @var integer
     */
    protected $uploadedFilesCount = 0;

    const STATUS_OK = 0;
    const STATUS_WARNING = 1;
    const STATUS_ERROR = 2;
    const STATUS_INFO = 3;

    protected $worstStatus = 0;

    protected $statusTexts = array(
        STATUS_OK => 'ok',
        STATUS_OK => 'warning',
        STATUS_OK => 'error',
        STATUS_OK => 'info'
    );

    /**
     * Adds a log message concerning a file to the response.
     * @param string status one of UploadResponse::STATUS_(OK|WARNING|ERROR|INFO)
     * @param string message
     * @param string file filename, without path.
     */
    public function addMessage($status, $message, $file) {
        $this->logs[] = array(
            'status' => $this->statusTexts[$status],
            'message' => $message,
            'file' => $file                        
        );

        if($status > $this->worstStatus)
            $this->worstStatus = $status;
    }

    /**
     * @return string
     */
    public function getJSON() {
        return json_encode(array(
            'status' => $this->statusTexts[$this->worstStatus],
            'messages' => $this->logs,
            'fileCount' => $this->uploadedFilesCount
        ));
    }

    /**
     * @param integer $by
     */
    public function increaseUploadedFilesCount($by = 1) {
        $this->uploadedFilesCount += $by;
    }
}