<?php

class InstallerCx {

    /**
     * The absolute path to the Code Base of the Contrexx installation.
     * Formerly known as ASCMS_DOCUMENT_ROOT.
     * @var string
     */
    protected $codeBaseDocumentRootPath = null;

    /**
     * The absolute path to the Code Base of the Contrexx installation.
     * Formerly known as ASCMS_DOCUMENT_ROOT.
     * @var string
     */
    protected $websiteDocumentRootPath = null;

    protected $websitePath = null;
    protected $websiteOffsetPath = null;
    protected $mode = 'minimal';

    /**
     * Initializes the Cx class
     * This does everything related to Contrexx.
     * @param string $mode (optional) Use constants, one of self::MODE_[FRONTEND|BACKEND|CLI|MINIMAL]
     * @param string $configFilePath The absolute path to a Contrexx configuration
     *                               file (configuration.php) that shall be loaded
     *                               instead of the default one.
     */
    public function __construct($basePath) {
        $this->basePath = str_replace('/installer', '', $basePath);
        $this->codeBaseDocumentRootPath = $this->basePath;
        $this->websiteDocumentRootPath = $this->basePath;
        $this->websitePath = $this->basePath;
        $this->websiteOffsetPath = '';
    }

    /**
     * Return the absolute path to the Code Base of the Contrexx installation.
     * Formerly known as ASCMS_DOCUMENT_ROOT.
     * @return string
     */
    public function getCodeBaseDocumentRootPath() {
        return $this->codeBaseDocumentRootPath;
    }

    /**
     * Return the absolute path to the data repository of the Contrexx installation.
     * Formerly known as ASCMS_INSTANCE_DOCUMENT_ROOT.
     * @return string
     */
    public function getWebsiteDocumentRootPath() {
        return $this->websiteDocumentRootPath;
    }

    /**
     * Return the offset path from the website's data repository to the
     * location of the Contrexx installation if it is run in a subdirectory.
     * Formerly known as ASCMS_INSTANCE_OFFSET.
     * @return string
     */
    public function getWebsiteOffsetPath()
    {
        return $this->websiteOffsetPath;
    }

    /**
     * Return the absolute path to the website's data repository.
     * Formerly known as ASCMS_INSTANCE_PATH.
     * @return string
     */
    public function getWebsitePath() {
        return $this->websitePath;
    }

    /**
     * Returns the mode this instance of Cx is in
     * @return string One of 'cli', 'frontend', 'backend', 'minimal'
     */
    public function getMode() {
        return $this->mode;
    }
}
