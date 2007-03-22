<?php
/**
 * Find and display requested XML file
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Astalavista Development Team <thun@astalvista.ch>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module
 * @todo        Edit PHP DocBlocks!
 */

$strXML='';
if(isset($_GET['mod']) && !empty($_GET['mod'])){
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
