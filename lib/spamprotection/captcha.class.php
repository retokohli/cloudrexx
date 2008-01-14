<?PHP
/**
 * Captcha
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Kaelin <thomas.kaelin@astalvista.ch>
 * @access      public
 * @version     1.2.0
 * @package     contrexx
 * @subpackage  lib_spamprotection
 */
class Captcha {
	var $strRandomString;
	var $strSalt;
	
	var $strFilename;
	var $strFontDir;
    var $strBackgroundDir;
    var $strAbsolutePath;
    var $strWebPath;
    
    var $intRandomLength = 5;
    var $intSaltLength = 20;
    var $intMaximumCharacters = 20;

	/**
	* Constructor-Fix for non PHP5-Servers
    *
    */
    function Captcha() {
    	$this->__construct();
    }    
    
    
	/**
	* Constructor: Initializes base-state of object
    *
    */
    function __construct() {
    	srand ((double)microtime()*1000000);
	    
	    $this->cleanDirectory();
	    
	    $this->strRandomString 	= $this->createRandomString();
	    $this->strSalt 			= $this->createRandomString($this->intSaltLength);
	    
	    $this->strFilename 		= $this->createFilename();
	    $this->strFontDir 		= ASCMS_DOCUMENT_ROOT.'/lib/spamprotection/fonts/';
	    $this->strBackgroundDir = ASCMS_DOCUMENT_ROOT.'/lib/spamprotection/backgrounds/';
	    $this->strAbsolutePath 	= ASCMS_DOCUMENT_ROOT.'/images/spamprotection/';
	    $this->strWebPath 		= ASCMS_PATH_OFFSET.'/images/spamprotection/';
	    
	     $this->createImage();
    }

    /**
     * Removes all images from the temporary folder, which are older than an hour.
     *
     */
    function cleanDirectory() {
		$handleDir = opendir($this->strAbsolutePath);
		if ($handleDir) {
			while ($strFile = readdir($handleDir)) {
				if ($strFile != '.' && $strFile != '..' && (filemtime($this->strAbsolutePath.$strFile) < time()-3600)) {
					unlink($this->strAbsolutePath.$strFile);
				}
			}
			closedir($handleDir);
		}    	
    }
    
	/**
	 * Creates an random string with $intDigits digits.
	 *
	 * @param	integer		$intDigits: How many digits should the created string have?
	 * @return	string		A new random string
	 */
    function createRandomString($intDigits=0) {
    	if ($intDigits > $this->intMaximumCharacters || $intDigits == 0) {
    		$intDigits = $this->intRandomLength;
    	}
    	
		$strReturn = '';	    	
        for ($i=1; $i <= $intDigits; ++$i) {
        	switch (rand(0,1)) {
        		case 0:
        			$strReturn .= chr(rand(65,90));
        			break;
        		case 1:
        			$strReturn .= sprintf("%d", rand(2,9));
        			break;
        		default:
        	}
        }
        
        return  $strReturn;
    }
    
    
    /**
     * Creates a new filename for the image.
     *
     * @return	string		Created filename with the format ".jpg"
     */
    function createFilename() {
    	
    	do {
    		$strFileName = md5(time().$this->strRandomString).'.jpg';
    	} while (is_file($this->strAbsolutePath.$strFileName));
    	 
    	return $strFileName;
    }
    
    
	/**
	 * Creates the image into the temporary folder.
	 *
	 */
    function createImage() {
        $intBackground = rand(1, 7);
        $handleImage = imagecreatefromjpeg($this->strBackgroundDir.$intBackground.'.jpg');
        
        $arrColors 	= array(	imagecolorallocate($handleImage, 0, 0, 0),		//black
        						imagecolorallocate($handleImage, 255, 0, 0), 	//red
        						imagecolorallocate($handleImage, 0, 180, 0), 	//darkgreen
        						imagecolorallocate($handleImage, 0, 105, 172),	//blue
        						imagecolorallocate($handleImage, 145, 19, 120)	//purple
        				);
        				
        $arrFonts	= array(	$this->strFontDir.'coprgtb.ttf',
        						$this->strFontDir.'ltypeb.ttf',
        				);
        
    	for ($i = 0; $i < strlen($this->strRandomString); ++$i) {
    		$intColor 	= rand(0, count($arrColors)-1);
    		$intFont	= rand(0, count($arrFonts)-1);
    		$intAngel 	= rand(-10, 10);
    		$intYMove 	= rand(-6, +6);
    		
	        imagettftext($handleImage, 11, $intAngel, (1+$i*12), 20 + $intYMove, $arrColors[$intColor], $arrFonts[$intFont], substr($this->strRandomString,$i,1));
    	}
        
        imagejpeg($handleImage, $this->strAbsolutePath.$this->strFilename, 90);
        imagedestroy($handleImage);
    }
    
    
	/**
	 * Because of security-problems: returns an empty string.
	 *
	 * @return	string		Alt tag for the image
	 */
    function getAlt() {
    	/*
    	$strReturn = '';
    	$intLen = strlen($this->strRandomString);
    	
    	for ($i = 0; $i < $intLen; ++$i) {
    		$strReturn .= substr($this->strRandomString,$i,1).'-';
    	}
    	
       return substr($strReturn,0,-1);
       */
    	
       return 'Spamprotection';
	}

	
    /**
     * Return the relative URL to the created image.
     *
     * @return	string		Relative URL to the created image
     */
    function getUrl() {
        return $this->strWebPath.$this->strFilename;
    }
    
    
    /**
     * Returns an salt-value and an md5-hash (random-string concatenated with salt) divided by a semicolon. 
     * This function received its name getOffset() for compatibility-reasons with older versions, which were offset-based. 
     *
     * @return	string		md5-hash of the random number
     */
    function getOffset() {
    	return $this->strSalt.';'.md5($this->strRandomString.$this->strSalt);
    }
    
    
	/**
	 * This function is used to validate the entered values of a user.
	 *
	 * @param	string		$strEnteredString: The string entered by the user. Should be the random-number of the picture.
	 * @param	string		$strOffset: The offset-value which was passed by an hidden-field in the html-source (Salt;MD5[Random+Salt])
	 * @return	boolean		true, if the md5-hash of the number was equals to the hashvalue of the hidden field
	 */
    function compare($strEnteredString, $strOffset) {
    	$arrOffsetParts = explode(';', $strOffset, 2);
    	
    	return ($arrOffsetParts[1] == md5($strEnteredString.$arrOffsetParts[0])) ? true : false;
    }
}
?>