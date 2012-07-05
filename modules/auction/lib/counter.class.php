<?php
/**
 * Auction-Counter
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_auction
 * @todo        Edit PHP DocBlocks!
 */



class AuctionCounter{

	var $mode 		= '';
	var $auction_id = 0;
	var $content 	= '';

	function AuctionCounter()
    {
        $this->__construct();
    }

	function __construct(){

		$this->mode 		= isset($_REQUEST["mode"]) ? $_REQUEST['mode'] : '';
		$this->auction_id 	= isset($_REQUEST['id']) ? intval($_REQUEST["id"]) : 0;

		if($this->auction_id>0){
			if($this->mode == 'countdown'){
				$this->content = $this->GetCountdown($this->auction_id);
			}
		}
		echo($this->content);

	}

	function GetCountdown($id){
		include_once('../../../config/configuration.php');
		$content = '';
		mysql_connect ($_DBCONFIG['host'],$_DBCONFIG['user'],$_DBCONFIG['password']);
		$sql_query		= "select enddate from ".DBPREFIX."module_auction where id='".$id."'";
		$result			= mysql_db_query($_DBCONFIG['database'],$sql_query);
		$row			= mysql_fetch_array($result);
		$endtime 		= $row["enddate"];
		$Differenz		= $endtime-time();
		if($Differenz>0){
			$Tage 			= floor($Differenz/86400);
			$Rest 			= $Differenz-($Tage*86400);
			$Stunden 		= floor($Rest/3600);
			$Rest 			= $Rest-($Stunden*3600);
			$Minuten 		= floor($Rest/60);
			$Rest 			= $Rest-($Minuten*60);
			if($Tage>0){
				$content = $Tage.' T - '.$Stunden.' Std - '.$Minuten.' Min - '.$Rest.' Sek';
			}else{
				$content = $Stunden.' Std - '.$Minuten.' Min - '.$Rest.' Sek';
			}
			$content		= '<div class="time">'.$content.'</div>';
		}else{
			$content = '<div class="closed">Auktion ist geschlossen</div>';
		}
		mysql_close();
		return $content;
	}

}

$Ajax = new AuctionCounter();


?>
