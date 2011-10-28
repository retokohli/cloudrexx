<?php
/**
 * Search
 *
 * Gets the Search results from the DB
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @version 2.0.0
 * @package     contrexx
 * @subpackage  core_module_search
 * @todo        Edit PHP DocBlocks!
 */

//Security-Check
if (eregi("index.class.php",$_SERVER['PHP_SELF'])) {
    Header("Location: ../../index.php");
    die();
}

/**
* Gets the searchpage
*
* @global     array
* @global     array
* @global     ADONewConnection
* @param     integer    $pos  get the results from pos
* @param     string        $page_content get the content from index.php
* @return    string        Result page content
*/
function search_getSearchPage($pos, $page_content)
{
    global $_CONFIG, $_ARRAYLANG,$objDatabase;

    $objTpl = new HTML_Template_Sigma('.');
    CSRF::add_placeholder($objTpl);
    $objTpl->setErrorHandling(PEAR_ERROR_DIE);
    $objTpl->setTemplate($page_content);
	$objTpl->setVariable("TXT_SEARCH", $_ARRAYLANG['TXT_SEARCH']);

    $term = ""; //$_SERVER['HTTP_HOST'];
    if (isset($_REQUEST['term'])&& strlen($_REQUEST['term'])>=3) {
		$term = contrexx_addslashes(trim($_REQUEST['term']));

		if (contrexx_isModuleActive('news')) {
			$querynews = search_searchQuery("news", $term);
		}
        if (contrexx_isModuleActive('docsys')) {
			$querydocsys = search_searchQuery("docsys",$term);
		}
        if (contrexx_isModuleActive('podcast')) {
			$queryPodcast = search_searchQuery("podcast",$term);
			$queryPodcastCategory = search_searchQuery("podcastCategory", $term);
		}
		if (contrexx_isModuleActive('shop')) {
			$queryshop = search_searchQuery("shop",$term);
		}
		if (contrexx_isModuleActive('gallery')) {
			$queryGalleryCats = search_searchQuery("gallery_cats",$term);
			$queryGalleryPics = search_searchQuery("gallery_pics",$term);
		}
		if (contrexx_isModuleActive('memberdir')) {
			$queryMemberdir = search_searchQuery("memberdir", $term);
			$queryMemberdirCats = search_searchQuery("memberdir_cats", $term);
		}
		if (contrexx_isModuleActive('directory')) {
			$queryDirectory = search_searchQuery("directory", $term);
			$queryDirectoryCats = search_searchQuery("directory_cats", $term);
		}
		if (contrexx_isModuleActive('calendar')) {
			$queryCalendar = search_searchQuery("calendar", $term);
		}
		if (contrexx_isModuleActive('forum')) {
			$queryForum = search_searchQuery("forum", $term);
		}
    }

    //Prm: Query,Section,Cmd,PageVar
    //$arrayContent=search_getResultArray($query,"","","page=",$term);
    $pageRepo = Env::em()->getRepository('Cx\Model\ContentManager\Page');
    $arrayContent = $pageRepo->searchResultsForSearchModule($term);
    
    $arrayNews=search_getResultArray($querynews,"news","details","newsid=",$term);
    $arrayDocsys = array();
    $arrayShopProducts = array();
    $arrayPodcastMedia = array();
    $arrayPodcastCategory = array();
    $arrayGalleryCats = array();
    $arrayGalleryPics = array();
    $arrayMemberdir = array();
    $arrayMemberdirCats = array();
    $arrayDirectory = array();
    $arrayDirectoryCats = array();
    $arrayCalendar = array();
    $arrayCalendarCats = array();
    $arrayForum = array();

    if (!empty($querydocsys)) {
       	$arrayDocsys=search_getResultArray($querydocsys,"docsys","details","id=",$term);
    }
    if (!empty($queryPodcast)) {
       	$arrayPodcastMedia=search_getResultArray($queryPodcast,"podcast","","id=",$term);
       	$arrayPodcastCategory = search_getResultArray($queryPodcastCategory,"podcast","","cid=",$term);
    }
    if (!empty($queryshop)) {
        $arrayShopProducts=search_getResultArray($queryshop,"shop","","productId=",$term);
    }
    if (!empty($queryGalleryCats)) {
    	$arrayGalleryCats = search_getResultArray($queryGalleryCats,"gallery","showCat","cid=",$term);
    	$arrayGalleryPics = search_getResultArray($queryGalleryPics,"gallery","showCat","cid=",$term);
    }
	if (!empty($queryMemberdir)) {
    	$arrayMemberdir = search_getResultArray($queryMemberdir, "memberdir", "", "mid=", $term);
    	$arrayMemberdirCats = search_getResultArray($queryMemberdirCats, "memberdir", "", "id=", $term);
    }
    if (!empty($queryDirectory)) {
    	$arrayDirectory = search_getResultArray($queryDirectory, "directory", "detail", "id=", $term);
    	$arrayDirectoryCats = search_getResultArray($queryDirectoryCats, "directory", "", "lid=", $term);
    }
	if (!empty($queryCalendar)) {
    	$arrayCalendar = search_getResultArray($queryCalendar, "calendar", "event", "id=0", $term);
    }
    if (!empty($queryForum)) {
    	$arrayForum = search_getResultArray($queryForum, "forum", "thread", "id=", $term);
    }


    //**************************************
	//paging start
    //**************************************

    $arraySearchResults=array_merge($arrayContent,$arrayNews,$arrayDocsys,$arrayPodcastMedia,$arrayPodcastCategory,$arrayShopProducts,
    								$arrayGalleryCats,$arrayGalleryPics,$arrayMemberdir,$arrayMemberdirCats,$arrayDirectory,$arrayDirectoryCats,
    								$arrayCalendar,$arrayCalendarCats,$arrayForum);
    if(is_array($arraySearchResults)){
        usort($arraySearchResults, "search_comparison");
    }
    $countResults=sizeof($arraySearchResults);


    if(!is_numeric($pos)){
        $pos = 0;
    }


    $paging = getPaging($countResults, $pos, "&amp;section=search&amp;term=".htmlentities($term, ENT_QUOTES, CONTREXX_CHARSET), "<b>".$_ARRAYLANG['TXT_SEARCH_RESULTS']."</b>", true);
    $objTpl->setVariable("SEARCH_PAGING", "$paging");
    $term=htmlentities(stripslashes($term), ENT_QUOTES, CONTREXX_CHARSET);
    $objTpl->setVariable("SEARCH_TERM",$term);

    //**************************************
    //  paging end
    //**************************************
    //*************************************
    //  parsing start
    //*************************************

    if ($countResults > 0){
    	$searchComment=sprintf($_ARRAYLANG['TXT_SEARCH_RESULTS_ORDER_BY_RELEVANCE'],$term,$countResults);
        $objTpl->setVariable("SEARCH_TITLE",$searchComment."<br />");

        $arraySearchOut=array_slice($arraySearchResults,$pos,$_CONFIG['corePagingLimit']);

        foreach($arraySearchOut as $kk=>$details){
            $objTpl->setVariable("COUNT_MATCH",$_ARRAYLANG['TXT_RELEVANCE']." $details[Score]%");
            $objTpl->setVariable("LINK", "<b><a href=\"".contrexx_raw2xhtml($details['Link'])."\" title=\"".contrexx_raw2xhtml($details['Title'])."\">".contrexx_raw2xhtml($details['Title'])."</a></b>");
            $objTpl->setVariable("SHORT_CONTENT", $details['Content']." ..<br />");
            $objTpl->parse("searchrow");
        }
    }
    else
    {
		$noresult= ($term <>'') ? sprintf($_ARRAYLANG['TXT_NO_SEARCH_RESULTS'],$term) : sprintf($_ARRAYLANG['TXT_PLEASE_ENTER_SEARCHTERM'],$term);
		$objTpl->setVariable("LINK", $noresult);
		$objTpl->setVariable("SHORT_CONTENT","");
		$objTpl->setVariable("COUNT_MATCH", "");
		$objTpl->setVariable("SEARCH_TITLE", "");
		$objTpl->parse("searchrow");
    }
    return $objTpl->get();
}


/**
 * Get searchquery
 *
 * Gets the SQL-searching-string (for news or content search)
 *
 * @param  string	for search in news or content
 * @param  string	replace the variable with the search term
 * @return string	SQL-Query with search term
 */
function search_searchQuery($section, $searchTerm)
{
    global $_LANGID, $_CONFIG;

    $objFWUser = FWUser::getFWUserObject();
    $query="";
    switch($section)
    {
        case "news":
            $query ="SELECT id AS id,
                            text AS content,
                            title AS title,
			date,
                            redirect,
                            MATCH (text,title,teaser_text) AGAINST ('%$searchTerm%') AS score
                      FROM ".DBPREFIX."module_news AS tblN
                      INNER JOIN ".DBPREFIX."module_news_locale AS tblL ON tblL.news_id = tblN.id
                      WHERE (text LIKE ('%".htmlentities($searchTerm, ENT_QUOTES, CONTREXX_CHARSET)."%') OR title LIKE ('%$searchTerm%') OR teaser_text LIKE ('%$searchTerm%'))
		                AND lang_id=".FRONTEND_LANG_ID."
                        AND status=1
                        AND is_active=1
                        AND (startdate<=CURDATE() OR startdate='0000-00-00')
                        AND (enddate>=CURDATE() OR enddate='0000-00-00')";
			break;

        case "content":
             $query="SELECT n.catid AS id,
		                    m.name AS section,
		                    n.cmd AS cmd,
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
						AND n.lang=".$_LANGID;
            break;

        case "docsys":
             $query="SELECT id,
		                    text AS content,
                            title AS title,
                      MATCH (text,title) AGAINST ('%".htmlentities($searchTerm, ENT_QUOTES, CONTREXX_CHARSET)."%') AS score
                       FROM ".DBPREFIX."module_docsys
                      WHERE (text LIKE ('%".htmlentities($searchTerm, ENT_QUOTES, CONTREXX_CHARSET)."%') OR title LIKE ('%$searchTerm%'))
                        AND lang=".$_LANGID."
                        AND status=1
                        AND (startdate<=CURDATE() OR startdate='0000-00-00')
                        AND (enddate>=CURDATE() OR enddate='0000-00-00')";
            break;

        case "podcast":
        	$query = "SELECT id,
    						title,
    						description AS content,
    					MATCH (description,title) AGAINST ('%$searchTerm%') AS score
    					FROM ".DBPREFIX."module_podcast_medium
    					WHERE (description LIKE ('%$searchTerm%') OR title LIKE ('%$searchTerm%'))
    					AND status=1";
        	break;

        case "podcastCategory":
        	$query = "SELECT tblCat.id, tblCat.title, tblCat.description,
        				MATCH (title,description) AGAINST ('%$searchTerm%') AS score
        				FROM ".DBPREFIX."module_podcast_category AS tblCat,
        				".DBPREFIX."module_podcast_rel_category_lang AS tblLang
        				WHERE (title LIKE ('%$searchTerm%') OR description LIKE ('%$searchTerm%'))
        				AND tblCat.status=1
        				AND tblLang.category_id=tblCat.id
        				AND tblLang.lang_id=".$_LANGID;
        	break;

        case "shop":
             $query="SELECT id,
                            title,
		                    description AS content,
                      MATCH (description,title) AGAINST ('%".htmlentities($searchTerm, ENT_QUOTES, CONTREXX_CHARSET)."%') AS score
                       FROM ".DBPREFIX."module_shop_products
                      WHERE description LIKE ('%".htmlentities($searchTerm, ENT_QUOTES, CONTREXX_CHARSET)."%')
                         OR title LIKE ('%$searchTerm%')
                        AND status =1";
            break;

		case "gallery_cats":
			$query = "SELECT tblLang.gallery_id, tblLang.value AS title,
						MATCH (tblLang.value) AGAINST ('%$searchTerm%') AS score
						FROM ".DBPREFIX."module_gallery_language AS tblLang,
						".DBPREFIX."module_gallery_categories AS tblCat
						WHERE tblLang.value LIKE ('%$searchTerm%')
						AND tblLang.lang_id=".$_LANGID."
						AND tblLang.gallery_id=tblCat.id
						AND tblCat.status=1";
			break;

		case "gallery_pics":
			$query = "SELECT tblPic.catid AS id, tblLang.name AS title, tblLang.desc AS content,
						MATCH (tblLang.name,tblLang.desc) AGAINST ('%$searchTerm%') AS score
						FROM ".DBPREFIX."module_gallery_pictures AS tblPic,
						".DBPREFIX."module_gallery_language_pics AS tblLang,
						".DBPREFIX."module_gallery_categories AS tblCat
						WHERE (tblLang.name LIKE ('%$searchTerm%') OR tblLang.desc LIKE ('%$searchTerm%'))
						AND tblLang.lang_id=".$_LANGID."
						AND tblLang.picture_id=tblPic.id
						AND tblPic.status=1
						AND tblCat.id=tblPic.catid
						AND tblCat.status=1";
			break;

        case "memberdir":
			$query = "SELECT tblValue.id,
							tblDir.name AS title,
							CONCAT_WS(' ', `1`, `2`, '') AS content
    					  FROM ".DBPREFIX."module_memberdir_values AS tblValue,
    					  ".DBPREFIX."module_memberdir_directories AS tblDir
    					  WHERE tblDir.dirid = tblValue.dirid
    					  AND tblValue.`lang_id` = ".$_LANGID."
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

		case "memberdir_cats":
			$query = "SELECT dirid AS id, name AS title, description AS content,
			MATCH (name, description) AGAINST ('%$searchTerm%') AS score
			FROM ".DBPREFIX."module_memberdir_directories
			WHERE active = '1' AND lang_id=".$_LANGID."
			AND (name LIKE ('%$searchTerm%') OR description LIKE ('%$searchTerm%'))";
			break;

		case "directory":
			$query = "SELECT id AS id,
							 title AS title,
							 description AS content,
			MATCH (title, description) AGAINST ('%$searchTerm%') AS score
			FROM ".DBPREFIX."module_directory_dir
			WHERE status = '1'
			AND (title LIKE ('%$searchTerm%') OR description LIKE ('%$searchTerm%') OR searchkeys LIKE ('%$searchTerm%') OR company_name LIKE ('%$searchTerm%'))";
			break;

		case "directory_cats":
			$query = "SELECT id AS id,
							 name AS title,
							 description AS content,
			MATCH (name, description) AGAINST ('%$searchTerm%') AS score
			FROM ".DBPREFIX."module_directory_categories
			WHERE status = '1'
			AND (name LIKE ('%$searchTerm%') OR description LIKE ('%$searchTerm%'))";
			break;
		case "calendar":
			$query = "	SELECT tblE.`id` AS id,
							 tblE.`name` AS title,
							 tblE.`comment` AS content,
							 tblE.`startdate` AS startdate
						FROM `".DBPREFIX."module_calendar` AS tblE
                    INNER JOIN `".DBPREFIX."module_calendar_categories` AS tblC
                        ON tblC.`id` = tblE.`catid`
						WHERE tblE.`active` = '1'
                        AND tblC.`lang` = ".FRONTEND_LANG_ID."
                        AND tblC.`status` = 1
						AND (
								tblE.`name` LIKE ('%$searchTerm%')
							OR 	tblE.`comment` LIKE ('%$searchTerm%')
							OR 	tblE.`placeName` LIKE ('%$searchTerm%')
						)";
			break;

		default:
            break;
    }
    return $query;
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
 * @param  string    $term            search term
 * @return array                    search results
 */
function search_getResultArray($query,$section,$command,$pagevar,$term)
{
    global $_ARRAYLANG;

    $objDatabase = Env::get('db');
    $config = Env::get('config');

    $arraySearchResults = array();

    $pageRepo = Env::em()->getRepository('Cx\Model\ContentManager\Page');
    $crit = array(
         'module' => $section,
         'lang'   => FRONTEND_LANG_ID,
         'cmd'    => NULL
    );
    if(!empty($command))
        $crit['cmd'] = $command;

    $page = $pageRepo->findOneBy($crit);
    if(!$page || !$page->isActive()) {
        return array();
    }

    $pagePath = $pageRepo->getPath($page);

    $objResult = $objDatabase->Execute($query);
    if (!$objResult || !$objResult->RecordCount()) {
        return array();
    }

    while (!$objResult->EOF) {
        switch ($section) {
            case '':
            case 'docsys':
            case 'podcast':
            case 'shop':
            case 'gallery':
            case 'memberdir':
            case 'directory':
            case 'forum':
                $temp_pagelink  = $pagePath.'?'.$pagevar.$objResult->fields['id'];
                break;

            case 'calendar':
                $day 	=  '&dayID='.date("d", intval($objResult->fields['startdate']));
                $month 	=  '&monthID='.date("m", intval($objResult->fields['startdate']));
                $year 	=  '&yearID='.date("Y", intval($objResult->fields['startdate']));

                $temp_pagelink  = $pagePath.'?'.$pagevar.$day.$month.$year;
                break;

            case 'news':
                if (empty($objResult->fields['redirect'])) {
                    $temp_pagelink = $pagePath.'?'.$pagevar.$objResult->fields['id'];
                } else {
                    $temp_pagelink = $objResult->fields['redirect'];
                }
                break;

            default:
                break;
        }

        $searchcontent = trim(stripslashes(strip_tags($objResult->fields['content'])));
        $searchcontent = preg_replace(
            array(
                '/\{[a-z0-9_]+\}/',
                '/\[\[[a-z0-9_]+\]\]/',
                '/<!--\s+(BEGIN|END)\s+[a-z0-9_]+\s+-->/'
            ),
            '',
            $searchcontent);
        $shortcontent = substr($searchcontent, 0, intval($config['searchDescriptionLength']));
        $arrayShortContent = explode(' ', $shortcontent);
        array_pop($arrayShortContent);
        $shortcontent = str_replace('&nbsp;','',join(' ',$arrayShortContent));
        $score=$objResult->fields['score'];
        $score>=1 ? $scorePercent=100 : $scorePercent=intval($score*100);
//TODO: Muss noch geändert werden, sobald das Ranking bei News funktioniert!!!
        $score==0 ? $scorePercent=25 : $scorePercent=$scorePercent;
        $date = isset($objResult->fields['date']) ? $objResult->fields['date'] : null;
        $searchtitle=!empty($objResult->fields['title']) ? $objResult->fields['title'] : $_ARRAYLANG['TXT_UNTITLED'];
        $arraySearchResults[] = array(
            "Score"=>$scorePercent,
            "Title"=>$searchtitle,
            "Content"=>$shortcontent,
            "Link"=>$temp_pagelink,
            "Date"=>$date
        );
        $objResult->MoveNext();
    }

    return $arraySearchResults;
}

/**
 * compare two elements of the result array by matching percentage.
 *
 * Used for ordering using usort(Array)
 * @author  	Christian Wehrli <christian.wehrli@astalavista.ch>
 * @param  	string	$a
 * @param  	string	$b
 * @return 	integer (-1: $a>$b; 0: $a==$b; +1: $a<$b)
 */
function search_comparison($a,$b)
{
    if ($a['Score']==$b['Score']) {
	if (isset($a['Date'])) {
		if ($a['Date']==$b['Date']) {
			return 0;
		} elseif ($a['Date']>$b['Date']) {
			return -1;
		} else {
			return 1;
		}
	}
        return 0;
    }
    elseif ($a['Score']>$b['Score']){
        return -1;
    } else {
        return 1;
    }
}
