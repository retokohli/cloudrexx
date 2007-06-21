<?PHP
/**
 * Captcha
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Kaelin <thomas.kaelin@astalvista.ch>
 * @version     1.1.0
 * @package     contrexx
 * @subpackage  lib_spamprotection
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Captcha
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Kaelin <thomas.kaelin@astalvista.ch>
 * @access      public
 * @version     1.1.0
 * @package     contrexx
 * @subpackage  lib_spamprotection
 */
class Captcha {
	
	var $strRandomString;
	var $strFontDir;
    var $strBackgroundDir;
    var $strAbsPath;
    var $strWebPath;
    var $strFilename;

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
    	$this->strFontDir		= ASCMS_DOCUMENT_ROOT.'/lib/spamprotection/fonts/';
	    $this->strBackgroundDir = ASCMS_DOCUMENT_ROOT.'/lib/spamprotection/backgrounds/';
	    $this->strAbsPath 		= ASCMS_DOCUMENT_ROOT.'/images/spamprotection/';
	    $this->strWebPath 		= ASCMS_PATH_OFFSET.'/images/spamprotection/';
	    
	    $this->cleanDirectory();
	    $this->strRandomString 	= $this->createRandomString();
	    $this->strFilename		= $this->createFilename($this->strRandomString);
	    $this->createImage();
    }

    
	/**
	 * Creates an random string with $intDigits digits.
	 *
	 * @param	integer		$intDigits: How many digits should the created string have?
	 * @return	string		A new random string
	 */
    function createRandomString($intDigits=5) {
    	if ($intDigits > 5) {
    		$intDigits = 5;
    	}
    	
		$strReturn = '';	    	
        for ($i=1; $i <= $intDigits; ++$i) {
        	$intRand = rand(0,1);
        	$strReturn .= ($intRand %2 == 0) ? chr(rand(65,90)) : sprintf("%d", rand(2,9));
        }
        
        return  $strReturn;
    }
    
    
    /**
     * Creates a new filename for the image.
     *
     * @param	integer		$strRandomString: This string will be combined with the actual time to create a unique md5()
     * @return	string		Created filename with the format ".jpg"
     */
    function createFilename($strRandomString) {
    	
    	do {
    		$strFileName = md5(time().$strRandomString).'.jpg';
    	} while (is_file($this->strAbsPath.$strFileName));
    	 
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
    		$intColor 	= rand(0,count($arrColors)-1);
    		$intFont	= rand(0,count($arrFonts)-1);
    		$intAngel 	= rand(-7,7);
    		$intYMove 	= rand(-4,+4);
    		
	        imagettftext($handleImage, 11, $intAngel,(1+$i*12), 20 + $intYMove, $arrColors[$intColor], $arrFonts[$intFont],substr($this->strRandomString,$i,1));
    	}
        
        imagejpeg($handleImage, $this->strAbsPath.$this->strFilename,85);
        imagedestroy($handleImage);
    }

    
    /**
     * Removes all images from the temporary folder, which are older than an hour.
     *
     */
    function cleanDirectory() {
		$handleDir = opendir($this->strAbsPath);
		if ($handleDir) {
			while ($strFile = readdir($handleDir)) {
				if ($strFile != '.' && $strFile != '..' && (filemtime($this->strAbsPath.$strFile) < time()-3600)) {
					unlink($this->strAbsPath.$strFile);
				}
			}
			closedir($handleDir);
		}    	
    }
    
    
	/**
	 * Creates an string which can be used as an alt tag for the protection-image.
	 *
	 * @return	string		Alt tag for the image
	 */
    function getAlt() {
    	$strReturn = '';
    	$intLen = strlen($this->strRandomString);
    	
    	for ($i = 0; $i < $intLen; ++$i) {
    		$strReturn .= substr($this->strRandomString,$i,1).'-';
    	}
    	
        return substr($strReturn,0,-1);
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
     * Returns the md5-hash of the random number shown on the image. This function is received its name
     * getOffset() for compatibility-reasons with older versions, which were offset-based. 
     *
     * @return	string		md5-hash of the random number
     */
    function getOffset() {
    	return md5($this->strRandomString);
    }
    
    
	/**
	 * This function is used to validate the entered values of a user.
	 *
	 * @param	integer		$intEnteredNumber: The number entered by the user
	 * @param	string		$strHashvalue: The hashvalue which were passed by an hidden-field in the html-source
	 * @return	boolean		true, if the md5-hash of the number was equals to the hashvalue of the hidden field
	 */
    function compare($intEnteredNumber, $strHashvalue) {
    	return ($strHashvalue == md5($intEnteredNumber)) ? true : false;
    }
}
?>
