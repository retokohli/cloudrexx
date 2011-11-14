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
        self::STATUS_OK => 'ok',
        self::STATUS_WARNING => 'warning',
        self::STATUS_ERROR => 'error',
        self::STATUS_INFO => 'info'
    );

    protected $uploadFinished = false;

    public function uploadFinished() {
        $this->uploadFinished = true;
    }
    public function isUploadFinished() {
        return $this->uploadFinished;
    }

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

    /**
     * @param integer $by
     */
    public function decreaseUploadedFilesCount($by = 1) {
        $this->uploadedFilesCount -= $by;
    }

    public static function fromSession($data) {
        $r = new UploadResponse();
        $data = json_decode($data, true);
        $r->initFromSession($data['logs'], $data['uploadedFilesCount'], $data['worstStatus'], $data['uploadFinished']);
        return $r;
    }

    public function __construct() {
    }

    protected function initFromSession($logs, $uploadedFilesCount, $worstStatus, $uploadFinished) {
        $this->logs = $logs;
        $this->uploadedFilesCount = $uploadedFilesCount;
        $this->worstStatus = $worstStatus;
        $this->uploadFinished = $uploadFinished;
    }

    public function toSessionValue() {
        return json_encode(array(
            'logs' => $this->logs,
            'uploadedFilesCount' => $this->uploadedFilesCount,
            'worstStatus' => $this->worstStatus,
            'uploadFinished' => $this->uploadFinished
        ));
    }    
}