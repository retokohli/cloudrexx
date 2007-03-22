<?php
require_once ASCMS_FRAMEWORK_PATH.'/NewsML.class.php';

class NewsML2
{
	var $_objNewsML;
	
	function NewsML2()
	{
		$this->__construct();
	}
	
	function __construct()
	{
		$this->_objNewsML = &new NewsML();
	}
	
	function setNews($arrNewsMLProviders, &$code)
	{
		global $objDatabase;
		
		$arrNewsMLProviderNames = $this->_objNewsML->_arrNewsMLProviderNames;
		
		if (count($arrNewsMLProviderNames)>0) {
			foreach ($arrNewsMLProviders as $newsMLProvider) {
				$arrMatches = preg_grep('/^'.$newsMLProvider.'$/i', $arrNewsMLProviderNames);
				
				if (count($arrMatches)>0) {
					$providerIds = array_keys($arrMatches);
					$providerId = $providerIds[0];
					$this->_objNewsML->readNewsMLDocuments($providerId);
					$code = str_replace("{NEWSML_".$newsMLProvider."}", $this->_objNewsML->parseNewsMLDocuments($providerId), $code);
				}
			}
		}
	}
}
?>
