<?php
/**
* Class Search
*/

class  Search {
	var $objTpl;
	
	
	/**
	* Constructor
	* @param  string  $pageContent
	*/
	function __construct($pageContent) {
		$this->pageContent = $pageContent;
		$this->objTpl = new HTML_Template_Sigma('.');
		$this->objTpl->setErrorHandling(PEAR_ERROR_DIE);
	}
	
	
	/**
	* calls search functions and output the results
	*
	*/
	function getSearchPage() {
		global $_CONFIG, $_ARRAYLANG,$objDatabase;
		$this->objTpl->setTemplate($this->pageContent);
			
		$_GET['search'] = !empty($_GET['search']) ? $_GET['search'] : "";

		if ($_GET['search']) {
			global $sortOrder;
			
			$searchTerm 		= contrexx_addslashes(trim($_GET['st']));
			$searchOptions  	= $_GET['sop'];
			$arrSearchIn 		= $_GET['si'];
			$sortOrder 			= $_GET['so'];
			$searchInAllModules = false;
			
				
			//if search in "all" is selected
			if (in_array('all', $arrSearchIn)) {
				$searchInAllModules = true;
			}
			
						
			require_once ASCMS_CORE_PATH.'/Modulechecker.class.php';
			$objModulChecker  = &new ModuleChecker();
			$arrActiveModules = array_keys($objModulChecker->arrActiveModulesByName);
			
			//NEWS AND CONTENT pages get searched by default
			$queryNews    = $this->buildQuery("news", $searchTerm, $searchOptions);
			$queryContent = $this->buildQuery("content", $searchTerm, $searchOptions);
			
			//DOCSYS
			if (in_array('docsys', $arrSearchIn) || $searchInAllModules) {
				if (in_array('docsys', $arrActiveModules)) {
					$querydocsys = $this->buildQuery("docsys", $searchTerm, $searchOptions);
				}
			}
			
			//PODCAST
			if (in_array('podcast', $arrSearchIn) || $searchInAllModules) {
				if (in_array('podcast', $arrActiveModules)) {
					$queryPodcast = $this->buildQuery("podcast", $searchTerm, $searchOptions);
					$queryPodcastCategory = $this->buildQuery("podcastCategory",  $searchTerm, $searchOptions);
				}
			}
			
			//SHOP
			if (in_array('shop', $arrSearchIn) || $searchInAllModules) {
				if (in_array('shop', $arrActiveModules)) {
					$queryshop = $this->buildQuery("shop", $searchTerm, $searchOptions);
				}
			}
			
			//GALLERY
			if (in_array('gallery', $arrSearchIn) || $searchInAllModules) {
				if (in_array('gallery', $arrActiveModules)) {
					$queryGalleryCats = $this->buildQuery("gallery_cats", $searchTerm, $searchOptions);
					$queryGalleryPics = $this->buildQuery("gallery_pics", $searchTerm, $searchOptions);
				}
			}
			
			//MEMBERDIR
			if (in_array('memberdir', $arrSearchIn) || $searchInAllModules) {
				if (in_array('memberdir', $arrActiveModules)) {
					$queryMemberdir = $this->buildQuery("memberdir",  $searchTerm, $searchOptions);
					$queryMemberdirCats = $this->buildQuery("memberdir_cats",  $searchTerm, $searchOptions);
				}
			}
			
			//DIRECTORY
			if (in_array('directory', $arrSearchIn) || $searchInAllModules) {
				if (in_array('directory', $arrActiveModules)) {
					$queryDirectory = $this->buildQuery("directory",  $searchTerm, $searchOptions);
					$queryDirectoryCats = $this->buildQuery("directory_cats",  $searchTerm, $searchOptions);
				}
			}
			
			//CALENDAR
			if (in_array('calendar', $arrSearchIn) || $searchInAllModules) {
				if (in_array('calendar', $arrActiveModules)) {
					$queryCalendar = $this->buildQuery("calendar",  $searchTerm, $searchOptions);
					$queryCalendarCats = $this->buildQuery("calendar_cats",  $searchTerm, $searchOptions);
				}
			}
			
			//FORUM
			if (in_array('forum', $arrSearchIn) || $searchInAllModules) {
				if (in_array('forum', $arrActiveModules)) {					
					$queryForum = $this->buildQuery("forum",  $searchTerm, $searchOptions);
				}
			}
			
			
			//Prm: Query,Section,Cmd,PageVar
			$arrContent			= $this->getResultArray($queryContent,"","","page=",$searchTerm);		
			$arrNews			= $this->getResultArray($queryNews,"news","details","newsid=",$searchTerm);
			$arrDocsys 			= array();
			$arrShopProducts	= array();
			$arrPodcastMedia	= array();
			$arrPodcastCategory	= array();
			$arrGalleryCats 	= array();
			$arrGalleryPics 	= array();
			$arrMemberdir 		= array();
			$arrMemberdirCats 	= array();
			$arrDirectory 		= array();
			$arrDirectoryCats 	= array();
			$arrCalendar 		= array();
			$arrCalendarCats 	= array();
			$arrForum 			= array();
			
			//DOCSYS
			if (!empty($querydocsys)) {
				$arrDocsys = $this->getResultArray($querydocsys,"docsys","details","id=",$searchTerm);
			}
			
			//PODCAST
			if (!empty($queryPodcast)) {
				$arrPodcastMedia	= $this->getResultArray($queryPodcast,"podcast","","id=",$searchTerm);
				$arrPodcastCategory = $this->getResultArray($queryPodcastCategory,"podcast","","cid=",$searchTerm);
			}
			
			//SHOP
			if (!empty($queryshop)) {
				$arrShopProducts = $this->getResultArray($queryshop,"shop","","productId=",$searchTerm);
			}
			
			//GALLERY
			if (!empty($queryGalleryCats)) {
				$arrGalleryCats = $this->getResultArray($queryGalleryCats,"gallery","showCat","cid=",$searchTerm);
				$arrGalleryPics = $this->getResultArray($queryGalleryPics,"gallery","showCat","cid=",$searchTerm);
			}
			
			//MEMBERDIR
			if (!empty($queryMemberdir)) {
				$arrMemberdir 	  = $this->getResultArray($queryMemberdir, "memberdir", "", "mid=", $searchTerm);
				$arrMemberdirCats = $this->getResultArray($queryMemberdirCats, "memberdir", "", "id=", $searchTerm);
			}
			
			//DIRECTORY
			if (!empty($queryDirectory)) {
				$arrDirectory     = $this->getResultArray($queryDirectory, "directory", "detail", "id=", $searchTerm);
				$arrDirectoryCats = $this->getResultArray($queryDirectoryCats, "directory", "", "lid=", $searchTerm);
			}
			
			//CALENDAR
			if (!empty($queryCalendar)) {
				$arrCalendar 	 = $this->getResultArray($queryCalendar, "calendar", "event", "id=", $searchTerm);
				$arrCalendarCats = $this->getResultArray($queryCalendarCats, "calendar", "", "catid=", $searchTerm);
			}
			
			//FORUM
			if (!empty($queryForum)) {
				$arrForum = $this->getResultArray($queryForum, "forum", "thread", "id=", $searchTerm);
			}
			
					
			function comparison($a,$b) {
				global $sortOrder;
				$key = $sortOrder == "date" ? "date" : "score";
				if ($a[$key] > $b[$key]) {
					return -1;
				}
				
				return 1;
			}
			
			
	
			//**************************************
			//paging start
			//**************************************
			$arrSearchResults = array_merge(	$arrContent,
												$arrNews,
												$arrDocsys,
												$arrPodcastMedia,
												$arrPodcastCategory,
												$arrShopProducts,
												$arrGalleryCats,
												$arrGalleryPics,
												$arrMemberdir,
												$arrMemberdirCats,
												$arrDirectory,
												$arrDirectoryCats,
												$arrCalendar,
												$arrCalendarCats,
												$arrForum
											);
			
											
			if(is_array($arrSearchResults)){
				usort($arrSearchResults, "comparison");
			}
			$countResults = sizeof($arrSearchResults);

			$pos = !empty($_GET['pos']) ? $_GET['pos'] : 0;
			
			
			$paging = getPaging($countResults, $pos, '&amp;section=search&amp;sop='.$searchOptions.'&amp;so='.$sortOrder.'&amp;si[]='.implode("&amp;si[]=", $arrSearchIn).'&amp;search=suchen&amp;st='.htmlentities($searchTerm, ENT_QUOTES, CONTREXX_CHARSET), '<b>'.$_ARRAYLANG['TXT_SEARCH_RESULTS'].'</b>', true);
			$this->objTpl->setVariable("SEARCH_PAGING", "$paging");
			//**************************************
			//  paging end
			//**************************************
			
			
			
			
			//*************************************
			//  parsing start
			//*************************************
			
			if ($countResults > 0){
				$searchComment=sprintf($_ARRAYLANG['TXT_SEARCH_RESULTS_ORDER_BY_HITS'].", ".$_ARRAYLANG['TXT_SEARCH_RESULTS_SORT_BY']." ".($sortOrder == "date" ? $_ARRAYLANG['TXT_DATE'] : $_ARRAYLANG['TXT_HITS']),$searchTerm,$countResults);
				$this->objTpl->setVariable("SEARCH_TITLE", $searchComment."<br />");
				
				$arrSearchOut=array_slice($arrSearchResults,$pos,$_CONFIG['corePagingLimit']);
				
				foreach($arrSearchOut as $kk=>$details){
					$this->objTpl->setVariable(array(	'ORDER_BY' 	=> $sortOrder == "date" ? !empty($details['date']) ? date("d.m.Y", $details['date']) : "" : $_ARRAYLANG['TXT_HITS'].' '.$details['score'].'%',
														'LINK' 			=> '<b><a href="'.$details['link'].'" title="'.$details['title'].'">'.$details['title'].'</a></b>',
														'SHORT_CONTENT' => $details['content'].' ..<br />',
														'MODULE' 		=> $details['module'],
												));
					$this->objTpl->parse("search_result_row");
				}
				
				$url = str_replace("&so=date", "", urldecode($_SERVER['QUERY_STRING']));
				$url = str_replace("&so=hits", "", $url);
				$this->objTpl->setVariable("SEARCH_URL", "index.php?".$url.'&amp;so=');
				
			} else {
				//no result
				$noresult= ($searchTerm <>'') ? sprintf($_ARRAYLANG['TXT_NO_SEARCH_RESULTS'],$searchTerm) : sprintf($_ARRAYLANG['TXT_PLEASE_ENTER_SEARCHTERM'],$searchTerm);
				$this->objTpl->setVariable("LINK", $noresult);
				$this->objTpl->setVariable("SHORT_CONTENT","");
				$this->objTpl->setVariable("COUNT_MATCH", "");
				$this->objTpl->setVariable("SEARCH_TITLE", "");
				$this->objTpl->parse("search_result_row");
			}
		}
		
		
		//generate module list
		$arrActiveModules = $this->getActiveModules();
		foreach ($arrActiveModules as $module) {
			$arrActiveModulesTranslated[] = array("module_name" => $module['name'], "modul_name_translated" => $this->getModuleNameTranslated($module['name']));
		}
		
		function comparison2($a,$b) {
			global $sortOrder;
			if ($a['modul_name_translated'] < $b['modul_name_translated']) {
				return -1;
			}
			
			return 1;
		}
		
		usort($arrActiveModulesTranslated, "comparison2");
		
		foreach ($arrActiveModulesTranslated as $module) {
			$this->objTpl->setVariable(array(	'MODULE_NAME'			 => $module['module_name'],
												'MODULE_NAME_TRANSLATED' => $module['modul_name_translated'],
										));
			$this->objTpl->parse("module_row");		
		}
				
		$this->objTpl->setVariable("TXT_WHAT_ARE_YOU_SEARCHING_FOR", $_ARRAYLANG['TXT_WHAT_ARE_YOU_SEARCHING_FOR']);
		$this->objTpl->setVariable("TXT_SEARCH", $_ARRAYLANG['TXT_SEARCH']);
		$this->objTpl->setVariable("ST_POST", !empty($_GET['st']) ? $_GET['st'] : "");
		
		return $this->objTpl->get();
	}
	
	
	
	
		
	/**
	 * builds sql statement
	 *
	 * @param  string $section
	 * @param  string $searchTerm
	 * @return string $query
	 */
	function buildQuery($section, $searchTerm, $searchOption) {
		global $_LANGID, $_CONFIG;
		
		$objFWUser = FWUser::getFWUserObject();
		$query="";
		
		switch($section) {
			//NEWS
			case "news":
				if($_GET['si'][0] == "all") {
					$where = $this->getWhereSnippet($searchTerm, $searchOption, array("text", "title", "teaser_text"));
					$query ="	SELECT
									id AS id,
									text AS content,
									title AS title,
									date AS date,
									redirect
								FROM
									".DBPREFIX."module_news
								WHERE
									".$where."
								AND
									lang=".$_LANGID."
								AND
									status=1
								AND
									(startdate<=CURDATE() OR startdate='0000-00-00')
								AND
									(enddate>=CURDATE() OR enddate='0000-00-00')";
				}
				break;
			//CONTENT
			case "content":
//				$where = $this->getWhereSnippet($searchTerm, $_GET['sop'], array("content"));
				$sectionClause = $_GET['si'][0] !== "all" ? "AND n.section = ''" : "";
				
				$query="SELECT n.catid AS id,
		                    m.name AS section,
		                    n.cmd AS cmd,
		                    n.changelog AS date,
		                    c.id AS contentid,
		                    c.content AS content,
		                    c.title AS title,		                    
                      MATCH (content,title) AGAINST ('%.".htmlentities($searchTerm, ENT_QUOTES, CONTREXX_CHARSET)."%') AS score
                       FROM ".DBPREFIX."content AS c,
                            ".DBPREFIX."content_navigation AS n,
                            ".DBPREFIX."modules AS m
                      WHERE (content LIKE ('%".htmlentities($searchTerm, ENT_QUOTES, CONTREXX_CHARSET)."%')
                      	OR title LIKE ('%$searchTerm%'))
                        ".(($_CONFIG['searchVisibleContentOnly'] == "on") ? "AND n.displaystatus = 'on'" : "")."
                        AND activestatus='1'
                        AND is_validated='1'
                        ".(
						!$objFWUser->objUser->login() ?
							// user is not authenticated
							($_CONFIG['coreListProtectedPages'] == 'off' ? 'AND n.protected=0' : '') :
							// user is authenticated
							(
								!$objFWUser->objUser->getAdminStatus() ?
									 // user is not administrator
									'AND (n.protected=0'.(count($objFWUser->objUser->getDynamicPermissionIds()) ? ' OR n.frontend_access_id IN ('.implode(', ', $objFWUser->objUser->getDynamicPermissionIds()).')' : '').')' :
									// user is administrator
									''
							)
						)."
						AND (n.startdate<=CURDATE() OR n.startdate='0000-00-00')
						AND (n.enddate>=CURDATE() OR n.enddate='0000-00-00')
                        AND n.module =m.id
                        AND n.catid = c.id
                        ".$sectionClause ."
                        AND n.lang=".$_LANGID;
				break;
			//DOCSYS
			case "docsys":
				$where = $this->getWhereSnippet($searchTerm, $searchOption, array("text", "title"));
				$query="	SELECT
								id,
								text AS content,
								title AS title,
								date AS date,
							MATCH
								(text,title) AGAINST ('%".htmlentities($searchTerm, ENT_QUOTES, CONTREXX_CHARSET)."%') AS score
							FROM
								".DBPREFIX."module_docsys
							WHERE
								".$where." 
							AND
								lang=".$_LANGID."
							AND
								status=1
							AND
								(startdate<=CURDATE() OR startdate='0000-00-00')
							AND
								(enddate>=CURDATE() OR enddate='0000-00-00')";
				break;
			//PODCAST
			case "podcast":
				$where = $this->getWhereSnippet($searchTerm, $searchOption, array("description", "title"));
				$query = "	SELECT
								id,
								title,
								description AS content,
								date_added AS date,
							MATCH
								(description,title) AGAINST ('%$searchTerm%') AS score
							FROM
								".DBPREFIX."module_podcast_medium
							WHERE
								".$where."
							AND
								status=1";
				break;
			//PODCAST DIRECTORY
			case "podcastCategory":
				$where = $this->getWhereSnippet($searchTerm, $searchOption, array("description", "title"));
				$query = "	SELECT
								tblCat.id,
								tblCat.title,
								tblCat.description as content,
							MATCH
								(title,description) AGAINST ('%$searchTerm%') AS score
							FROM 
								".DBPREFIX."module_podcast_category AS tblCat,
								".DBPREFIX."module_podcast_rel_category_lang AS tblLang
							WHERE
								".$where."
							AND
								tblCat.status=1
							AND
								tblLang.category_id=tblCat.id
							AND
								tblLang.lang_id=".$_LANGID;
				break;
			//SHOP
			case "shop":
				$where = $this->getWhereSnippet($searchTerm, $searchOption, array("description", "title"));
				$query="	SELECT
								id,
								title,
								description AS content,
							MATCH
								(description,title) AGAINST ('%".htmlentities($searchTerm, ENT_QUOTES, CONTREXX_CHARSET)."%') AS score
							FROM
								".DBPREFIX."module_shop_products
							WHERE
								".$where." 
							AND
								status =1";
				break;
			//GALLERY CATS
			case "gallery_cats":
				$query = "	SELECT
								tblLang.gallery_id,
								tblLang.value AS title,
							MATCH
								(tblLang.value) AGAINST ('%$searchTerm%') AS score
							FROM
								".DBPREFIX."module_gallery_language AS tblLang,
								".DBPREFIX."module_gallery_categories AS tblCat
							WHERE
								tblLang.value LIKE ('%$searchTerm%')
							AND
								tblLang.lang_id=".$_LANGID."
							AND
								tblLang.gallery_id=tblCat.id
							AND
								tblCat.status=1";
				break;
			//GALLERY PICS
			case "gallery_pics":
				$query = "	SELECT
								tblPic.catid AS id,
								tblLang.name AS title,
								tblLang.desc AS content,
							MATCH
								(tblLang.name,tblLang.desc) AGAINST ('%$searchTerm%') AS score
							FROM
								".DBPREFIX."module_gallery_pictures AS tblPic,
								".DBPREFIX."module_gallery_language_pics AS tblLang,
								".DBPREFIX."module_gallery_categories AS tblCat
							WHERE
								(tblLang.name LIKE ('%$searchTerm%') OR tblLang.desc LIKE ('%$searchTerm%'))
							AND
								tblLang.lang_id=".$_LANGID."
							AND
								tblLang.picture_id=tblPic.id
							AND
								tblPic.status=1
							AND
								tblCat.id=tblPic.catid
							AND
								tblCat.status=1";
				break;
			//MEMBERDIR
			case "memberdir":
				$query = "	SELECT
								tblValue.id,
								tblDir.name AS title,
								tblDir.date AS date,
								CONCAT_WS(' ', `1`, `2`, '') AS content
							FROM
								".DBPREFIX."module_memberdir_values AS tblValue,
								".DBPREFIX."module_memberdir_directories AS tblDir
							WHERE
								tblDir.dirid = tblValue.dirid
							AND
								tblValue.`lang_id` = ".$_LANGID."
							AND (
								tblValue.`1` LIKE '%$searchTerm%' OR
								tblValue.`2` LIKE '%$searchTerm%' OR
								tblValue.`3` LIKE '%$searchTerm%' OR
								tblValue.`4` LIKE '%$searchTerm%' OR
								tblValue.`5` LIKE '%$searchTerm%' OR
								tblValue.`6` LIKE '%$searchTerm%' OR
								tblValue.`7` LIKE '%$searchTerm%' OR
								tblValue.`8` LIKE '%$searchTerm%' OR
								tblValue.`9` LIKE '%$searchTerm%' OR
								tblValue.`10` LIKE '%$searchTerm%' OR
								tblValue.`11` LIKE '%$searchTerm%' OR
								tblValue.`12` LIKE '%$searchTerm%' OR
								tblValue.`13` LIKE '%$searchTerm%' OR
								tblValue.`14` LIKE '%$searchTerm%' OR
								tblValue.`15` LIKE '%$searchTerm%' OR
								tblValue.`16` LIKE '%$searchTerm%' OR
								tblValue.`17` LIKE '%$searchTerm%' OR
								tblValue.`18` LIKE '%$searchTerm%'
								)";
				break;
			//MEMBERDIR CATS
			case "memberdir_cats":
				$query = "	SELECT
								dirid AS id,
								name AS title,
								description AS content,
							MATCH
								(name, description) AGAINST ('%$searchTerm%') AS score
							FROM
								".DBPREFIX."module_memberdir_directories
							WHERE
								active = '1' AND lang_id=".$_LANGID."
							AND
								(name LIKE ('%$searchTerm%') OR description LIKE ('%$searchTerm%'))";
				break;
			//DIRECTORY
			case "directory":
				$where = $this->getWhereSnippet($searchTerm, $searchOption, array("title", "description", "searchkeys", "company_name"));
				$query = "	SELECT
								id AS id,
								title AS title,
								description AS content,
								date as date,
							MATCH
								(title, description) AGAINST ('%$searchTerm%') AS score
							FROM
								".DBPREFIX."module_directory_dir
							WHERE
								".$where."
							AND
								status = '1'";
				break;
			//DIRECTORY CATS
			case "directory_cats":
				$where = $this->getWhereSnippet($searchTerm, $searchOption, array("name", "description"));
				$query = "	SELECT
								id AS id,
								name AS title,
								description AS content,
							MATCH
								(name, description) AGAINST ('%$searchTerm%') AS score
							FROM
								".DBPREFIX."module_directory_categories
							WHERE
								".$where."
							AND
								status = '1'";
				break;
			//CALENDAR
			case "calendar":
				$where = $this->getWhereSnippet($searchTerm, $searchOption, array("name", "comment", "placeName"));
				$query = "	SELECT
								`id` AS id,
								`name` AS title,
								`comment` AS content
							FROM
								".DBPREFIX."module_calendar`
							WHERE
								`active` = '1'
							AND
								".$where;
				break;
			//CALENDAR CATS
			case "calendar_cats":
				$query = "	SELECT
								id AS id,
							name
								AS title
							FROM
								".DBPREFIX."module_calendar_categories
							WHERE
								status = '1'
							AND
								(name LIKE ('%$searchTerm%'))";
				break;
			//FORUM
			case "forum":
				$where = $this->getWhereSnippet($searchTerm, $searchOption, array("subject", "content", "keywords"));
				$query = "	SELECT
								thread_id AS id,
								subject AS title,
								content AS content,
								time_created AS date,
							MATCH
								(subject, keywords, content) AGAINST ('%$searchTerm%') AS score
							FROM
								".DBPREFIX."module_forum_postings
							WHERE
								".$where;
				break;
		}
		
		return $query;
	}
	
	
	/**
	 * returns where clause
	 *
	 * @param  string $term
	 * @param  string $options
	 * @param  arr $arrField
	 * @return string $arrReplacedField
	 */
	function getWhereSnippet($term, $options, $arrField) {
		$arrTerm = preg_split("/\s+/", $term);
		
		switch ($options) {
			case "all":
				$arrWhereParts = array();
				foreach ($arrTerm as $word) {
					$arrWhereParts[] = "___FIELDPLACHEOLDER___ LIKE '%".htmlentities(addslashes($word))."%'";
				}
				$template = join(" AND ", $arrWhereParts);
				break;
			case "any":
				$arrWhereParts = array();
				foreach ($arrTerm as $word) {
					$arrWhereParts[] = "___FIELDPLACHEOLDER___ LIKE '%".htmlentities(addslashes($word))."%'";
				}
				$template = join(" OR ", $arrWhereParts);
				break;
			case "exact":
				$template = "___FIELDPLACHEOLDER___ LIKE '%".htmlentities(addslashes($term))."%'";
				break;
		}
		
		$arrReplacedField = array();
		foreach ($arrField as $field) {
			$arrReplacedField[] = str_replace("___FIELDPLACHEOLDER___", $field, $template);
		}
		
		return join(" OR ", $arrReplacedField);
	}
	

	/**
	* Get resultarray
	*
	* Gets the results in an array
	* @author  Christian Wehrli <christian.wehrli@astalavista.ch>
	* @global array
	* @global ADONewConnection
	* @global array
	* @param  string    $query            the searching query
	* @param  string    $section_var    needed for section
	* @param  string    $cmd_var        needed for cmd
	* @param  string    $pagevar
	* @param  string    $searchTerm            search term
	* @return array                    search results
	*/
	function getResultArray($query,$section_var,$cmd_var,$pagevar,$searchTerm) {
		global $_CONFIG, $objDatabase, $_ARRAYLANG;

		$arrSearchResults = array();
		$objResult          = $objDatabase->Execute($query);
		$i=0;
		
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				$i++;
				$cmd	 = !empty($objResult->fields['cmd'])     ? $objResult->fields['cmd']     : "";
				$section = !empty($objResult->fields['section']) ? $objResult->fields['section'] : "";
				
				if ($section == "" && $section_var <> ""){
					$section = $section_var;
				}
				if ($cmd == "" && $cmd_var <> ""){
					$cmd = $cmd_var;
				}
				
				$temp_section	= (($section == "") ? "" : "&amp;section=$section");
				$temp_cmd		= (($cmd == "")     ? "" : "&amp;cmd=$cmd");
				
				switch ($section) {
					case '':
					case 'docsys':
					case 'podcast':
					case 'shop':
					case 'gallery':
					case 'memberdir':
					case 'directory':
					case 'calendar':
					case 'forum':
						$temp_pagelink  = '?'.$pagevar.$objResult->fields['id'].$temp_section.$temp_cmd;
						break;
					case 'news':
						if (empty($objResult->fields['redirect'])) {
							$temp_pagelink = '?'.$pagevar.$objResult->fields['id'].$temp_section.$temp_cmd;
						} else {
							$temp_pagelink = $objResult->fields['redirect'];
						}
						break;
					default:
						$temp_pagelink  = '?'.$temp_section.$temp_cmd;
				}
				
				
				$searchcontent = eregi_replace("\{[a-z0-9_]+\}","",strip_tags($objResult->fields['content']));
				$searchcontent = stripslashes($searchcontent);
				$searchcontent = preg_replace("#\[[^\]]+\]#", "", $searchcontent);
				$arrSearchTerm = preg_split("/\s+/", $searchTerm);
				$shortcontent  = substr(ltrim($searchcontent), strripos($searchcontent, $arrSearchTerm[0])-25,intval($_CONFIG['searchDescriptionLength']));
//				$shortcontent  = substr(ltrim($searchcontent), 0,intval($_CONFIG['searchDescriptionLength']));
				$shortcontent  = preg_replace("/".$arrSearchTerm[0]."/i", '<font class="searchModuleSTFounded">'.$arrSearchTerm[0].'</font>', $shortcontent);
				$arrShortContent = explode(" ",$shortcontent);
				
				$arrelem= array_pop($arrShortContent);
				
				$shortcontent = str_replace("&nbsp;","",join(" ",$arrShortContent));
				$score =!empty($objResult->fields['score']) ? $objResult->fields['score'] : 0;
				$score>=1 ? $scorePercent=100 : $scorePercent=intval($score*100);
				//Muss noch ge�ndert werden, sobald das Ranking bei News funktioniert!!!
				$score==0 ? $scorePercent=25 : $scorePercent=$scorePercent;
				$searchtitle           = !empty($objResult->fields['title']) ? $objResult->fields['title'] : $_ARRAYLANG['TXT_UNTITLED'];
				$date           = !empty($objResult->fields['date']) ? $objResult->fields['date'] : "";
											
				$arrSearchResults[$i] = array(	"score"   => $scorePercent,
												"title"	  => $searchtitle,
												"content" => $shortcontent,
												"link"    => $temp_pagelink,
												"date"    => $date,
												"module"  => !empty($section) ? $this->getModuleNameTranslated($section) : "Content"
											);
				$objResult->MoveNext();
			}
		}
		
		return $arrSearchResults;
	}
	
	
	
	/**
	 * returns translated modul name
	 *
	 * @param  string $modulename
	 * @return string $_ARRAYLANG[$modulename]
	 */
	function getModuleNameTranslated($modulename) {
		global $objDatabase, $_ARRAYLANG, $objInit;
		$langfile = ASCMS_DOCUMENT_ROOT."/lang/".$objInit->arrLang[LANG_ID]['lang'].'/backend.php';
		include_once($langfile);
		$objResult = $objDatabase->Execute("SELECT area_name FROM ".DBPREFIX."backend_areas WHERE uri like '%".$modulename."%'");

		return !empty($_ARRAYLANG[$objResult->fields["area_name"]]) ? $_ARRAYLANG[$objResult->fields["area_name"]] : "";
	}	
	
	
	/**
	 * returns all active modules
	 *
	 * @return array $arrModules
	 */
	function getActiveModules() {
		global $objDatabase;
		//those are not really modules, so we dont need them...
		$arrDontUse = array("search", "media1", "media2", "media3", "media4", "sitemap", "ids", "home", "login", "error","fileUploader", "imprint", "agb", "privacy");

		$objResult = $objDatabase->Execute("SELECT * FROM ".DBPREFIX."modules WHERE status = 'y'");
		while (!$objResult->EOF) {
			if (!in_array($objResult->fields["name"], $arrDontUse)) {
				$arrModules[] = array("id" => $objResult->fields["id"],
									  "name" => $objResult->fields["name"]);
			}
			$objResult->MoveNext();	
		}
		
		return !empty($arrModules) ? $arrModules : "";
	}
}


?>
