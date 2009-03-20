<?php
/**
 * Find and display requested XML file
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module
 * @todo        Edit PHP DocBlocks!
 */

$strXML='';
if(isset($_GET['mod']) && !empty($_GET['mod'])){
    $_GET['mod'] = str_replace(array('\0', '..', '/'), '', $_GET['mod']);
	$strXMLfile='xmlinfo/'.$_GET['mod'].'.xml';
	if(file_exists($strXMLfile)){
		$hXML=@fopen($strXMLfile,'r');
		if($hXML){
			while(!feof($hXML)){
				$strXML.=fread($hXML,1024);
			}
			header('Content-Type: text/xml');
			die($strXML);
		}
	}
}
echo "notfound";

?>
