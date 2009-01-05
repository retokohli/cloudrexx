<?php

/**
 * News
 *
 * This module will get all the news pages
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @version 1.0.0
 * @package     contrexx
 * @subpackage  core_module_news
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_CORE_MODULE_PATH . '/news/lib/newsLib.class.php';

/**
 * News
 *
 * This module will get all the news pages
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @access public
 * @version 1.0.0
 * @package     contrexx
 * @subpackage  core_module_news
 */
class news extends newsLibrary
{
    public $newsTitle;
    public $arrSettings = array();
    public $_objTpl;
    public $_submitMessage;

    /**
     * PHP5 constructor
     * @param  string  $pageContent
     * @global integer
     * @access public
     */
    function __construct($pageContent)
    {
        $this->getSettings();
        $this->pageContent = $pageContent;
        $this->_objTpl = new HTML_Template_Sigma();
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
    }

    /**
    * Get page
    *
    * @access public
    * @return string content
    */
    function getNewsPage()
    {
        if (!isset($_REQUEST['cmd'])) {
            $_REQUEST['cmd'] = '';
        }

        switch($_REQUEST['cmd']) {
        case 'details':
            return $this->getDetails();
            break;

        case 'submit':
            return $this->_submit();
            break;
        case 'feed':
            return $this->_showFeed();
            break;

        default:
            return $this->getHeadlines();
            break;
        }
    }

    /**
    * Gets the news details
    *
    * @global    array
    * @global    ADONewConnection
    * @global    array
    * @return    string    parsed content
    */
    function getDetails()
    {
        global $objDatabase, $_ARRAYLANG, $_PATHCONFIG;

        $this->_objTpl->setTemplate($this->pageContent);
        $newsid = intval($_GET['newsid']);

        if ($newsid!=0) {
            $objResult = $objDatabase->SelectLimit('SELECT  news.id                 AS id,
                                                            news.userid             AS userid,
                                                            news.source             AS source,
                                                            news.changelog          AS changelog,
                                                            news.url1               AS url1,
                                                            news.url2               AS url2,
                                                            news.text               AS text,
                                                            news.date               AS date,
                                                            news.changelog          AS changelog,
                                                            news.title              AS title,
                                                            news.teaser_image_path  AS newsimage,
                                                            news.teaser_text        AS teasertext
                                                    FROM    '.DBPREFIX.'module_news AS news
                                                    WHERE   news.status = 1 AND
                                                            news.id = '.$newsid.' AND
                                                            news.lang ='.FRONTEND_LANG_ID.' AND
                                                            (news.startdate <= CURDATE() OR news.startdate="0000-00-00") AND
                                                            (news.enddate >= CURDATE() OR news.enddate="0000-00-00")'
                                                    , 1);

            if ($objResult !== false) {
                while (!$objResult->EOF) {
                    $lastUpdate     = $objResult->fields['changelog'];
                    $source         = htmlspecialchars($objResult->fields['source'], ENT_QUOTES, CONTREXX_CHARSET);
                    $url1           = htmlspecialchars($objResult->fields['url1'], ENT_QUOTES, CONTREXX_CHARSET);
                    $url2           = htmlspecialchars($objResult->fields['url2'], ENT_QUOTES, CONTREXX_CHARSET);
                    $newsUrl        = '';
                    $newsSource     = '';
                    $newsLastUpdate = '';

                    if (!empty($url1)) {
                        $strUrl1 = $objResult->fields['url1'];
                        if (strlen($strUrl1) > 40) {
                            $strUrl1 = substr($strUrl1,0,26).'...'.substr($strUrl1,(strrpos($strUrl1,'.')));
                        }
                        $newsUrl = $_ARRAYLANG['TXT_IMPORTANT_HYPERLINKS'].'<br /><a target="_blank" href="'.$url1.'" title="'.$url1.'">'.$strUrl1.'</a><br />';
                    }
                    if (!empty($url2)) {
                        $strUrl2 = $objResult->fields['url2'];
                        if (strlen($strUrl2) > 40) {
                            $strUrl2 = substr($strUrl2,0,26).'...'.substr($strUrl2,(strrpos($strUrl2,'.')));
                        }
                        $newsUrl .= '<a target="_blank" href="'.$url2.'" title="'.$url2.'">'.$strUrl2.'</a><br />';
                    }
                    if (!empty($source)) {
                        $strSource = $objResult->fields['source'];
                        if (strlen($strSource) > 40) {
                            $strSource = substr($strSource,0,26).'...'.substr($strSource,(strrpos($strSource,'.')));
                        }
                        $newsSource = $_ARRAYLANG['TXT_NEWS_SOURCE'].'<br /><a target="_blank" href="'.$source.'" title="'.$source.'">'.$strSource.'</a><br />';
                    }
                    if (!empty($lastUpdate)) {
                        $newsLastUpdate = $_ARRAYLANG['TXT_LAST_UPDATE'].'<br />'.date(ASCMS_DATE_FORMAT,$objResult->fields['changelog']);
                    }

                    if ($objResult->fields['userid'] && ($objFWUser = FWUser::getFWUserObject()) && ($objUser = $objFWUser->objUser->getUser($objResult->fields['userid']))) {
                        $firstname = $objUser->getProfileAttribute('firstname');
                        $lastname = $objUser->getProfileAttribute('lastname');
                        if(!empty($firstname) && !empty($lastname)) {
                            $author = htmlentities($firstname.' '.$lastname, ENT_QUOTES, CONTREXX_CHARSET);
                        } else {
                            $author = htmlentities($objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET);
                        }
                    } else {
                        $author = $_ARRAYLANG['TXT_ANONYMOUS'];
                    }

                    $newstitle = htmlspecialchars(stripslashes($objResult->fields['title']), ENT_QUOTES, CONTREXX_CHARSET);
                    $this->_objTpl->setVariable(array(
                       'NEWS_DATE'          => date(ASCMS_DATE_FORMAT,$objResult->fields['date']),
                       'NEWS_TITLE'         => $newstitle,
                       'NEWS_TEXT'          => stripslashes($objResult->fields['text']),
                       'NEWS_TEASER_TEXT'   => nl2br($objResult->fields['teasertext']),
                       'NEWS_LASTUPDATE'    => $newsLastUpdate,
                       'NEWS_SOURCE'        => $newsSource,
                       'NEWS_URL'           => $newsUrl,
                       'NEWS_AUTHOR'        => $author,
                       'NEWS_IMAGE'         => (empty($objResult->fields['newsimage'])) ? '' : '<img src="'.$_PATHCONFIG['ascms_root_offset'].$objResult->fields['newsimage'].'" alt="'.$newstitle.'" title="'.$newstitle.'" />'
                    ));

                    $objResult->MoveNext();
                }
            }
        } else {
            header("Location: ?section=news");
            exit;
        }

        $this->newsTitle = strip_tags($newstitle);
        return $this->_objTpl->get();
    }

    /**
    * Gets the list with the headlines
    *
    * @global    array
    * @global    ADONewConnection
    * @global    array
    * @return    string    parsed content
    */
    function getHeadlines()
    {
        global $_CONFIG, $objDatabase, $_ARRAYLANG, $_PATHCONFIG;

        $selected   = '';
        $newsfilter = '';
        $paging     = '';
        $pos        = 0;
        $i          = 0;

        if (isset($_GET['pos'])) {
            $pos = intval($_GET['pos']);
        }

        $this->_objTpl->setTemplate($this->pageContent);

        if (!empty($_REQUEST['category']) || (($_REQUEST['category'] = intval($_REQUEST['cmd'])) > 0)) {
            $newsfilter = ' AND ';
            $boolFirst = true;

            $arrCategories = explode(',',$_REQUEST['category']);

            if (count($arrCategories) == 1) {
                $selected = $arrCategories[0];
        }

            foreach ($arrCategories as $intCategoryId) {
                if (!$boolFirst) {
                    $newsfilter .= 'OR ';
                }

                $newsfilter .= 'n.catid='.intval($intCategoryId).' ';
                $boolFirst = false;
            }
        }

        $catMenu    =  '<select onchange="this.form.submit()" name="category">'."\n";
        $catMenu    .= '<option value="" selected="selected">'.$_ARRAYLANG['TXT_CATEGORY'].'</option>'."\n";
        $catMenu    .= $this->getCategoryMenu(FRONTEND_LANG_ID, $selected)."\n";
        $catMenu    .= '</select>'."\n";

        $this->_objTpl->setVariable(array(
            'NEWS_CAT_DROPDOWNMENU' => $catMenu,
            'TXT_PERFORM'           => $_ARRAYLANG['TXT_PERFORM'],
            'TXT_CATEGORY'          => $_ARRAYLANG['TXT_CATEGORY'],
            'TXT_DATE'              => $_ARRAYLANG['TXT_DATE'],
            'TXT_TITLE'                => $_ARRAYLANG['TXT_TITLE'],
            'TXT_NEWS_MESSAGE'          => $_ARRAYLANG['TXT_NEWS_MESSAGE']
        ));

        $query = '  SELECT      n.id                AS newsid,
                                n.userid            AS newsuid,
                                n.date              AS newsdate,
                                n.title             AS newstitle,
                                n.teaser_image_path AS newsimage,
                                n.redirect          AS newsredirect,
                                nc.name             AS name
                    FROM        '.DBPREFIX.'module_news AS n
                    INNER JOIN  '.DBPREFIX.'module_news_categories AS nc
                    ON          n.catid=nc.catid
                    WHERE       status = 1
                                AND n.lang='.FRONTEND_LANG_ID.'
                                AND (n.startdate<=CURDATE() OR n.startdate="0000-00-00")
                                AND (n.enddate>=CURDATE() OR n.enddate="0000-00-00")
                                '.$newsfilter.'
                    ORDER BY    newsdate DESC';

        /***start paging ****/
        $objResult = $objDatabase->Execute($query);
        $count = $objResult->RecordCount();
        if (isset($_REQUEST['category'])) {
			$category = "&amp;category=".$selected;
		}

		if (isset($_REQUEST['cmd'])) {
			if ($_REQUEST['cmd'] == $selected) {
        		$category = "&amp;cmd=".$_REQUEST['cmd'];
			}else {
				$category .= "&amp;cmd=".$_REQUEST['cmd'];
			}
		}


        if ($count>intval($_CONFIG['corePagingLimit'])) {
            $paging = getPaging($count, $pos, "&amp;section=news".$category, $_ARRAYLANG['TXT_NEWS_MESSAGES'], true);
        }
        $this->_objTpl->setVariable("NEWS_PAGING", $paging);
        $objResult = $objDatabase->SelectLimit($query, $_CONFIG['corePagingLimit'], $pos);
        /*** end paging ***/

        if ($count>=1) {
            while (!$objResult->EOF) {
                ($i % 2) ? $class  = 'row1' : $class  = 'row2';

                if ($objResult->fields['newsuid'] && ($objFWUser = FWUser::getFWUserObject()) && ($objUser = $objFWUser->objUser->getUser($objResult->fields['newsuid']))) {
                    $firstname = $objUser->getProfileAttribute('firstname');
                    $lastname = $objUser->getProfileAttribute('lastname');
                    if(!empty($firstname) && !empty($lastname)) {
                        $author = htmlentities($firstname.' '.$lastname, ENT_QUOTES, CONTREXX_CHARSET);
                    } else {
                        $author = htmlentities($objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET);
                    }
                } else {
                    $author = $_ARRAYLANG['TXT_ANONYMOUS'];
                }

                $newstitle = htmlspecialchars(stripslashes($objResult->fields['newstitle']), ENT_QUOTES, CONTREXX_CHARSET);
                $this->_objTpl->setVariable(array(
                           'NEWS_CSS'           => $class,
                           'NEWS_LONG_DATE'     => date(ASCMS_DATE_FORMAT,$objResult->fields['newsdate']),
                           'NEWS_DATE'          => date(ASCMS_DATE_SHORT_FORMAT, $objResult->fields['newsdate']),
                           'NEWS_LINK'          => (empty($objResult->fields['newsredirect'])) ? '<a href="?section=news&amp;cmd=details&amp;newsid='.$objResult->fields['newsid'].'" title="'.$newstitle.'">'.$newstitle.'</a>' : '<a href="'.$objResult->fields['newsredirect'].'" title="'.$newstitle.'">'.$newstitle.'</a>',
                           'NEWS_CATEGORY'      => stripslashes($objResult->fields['name']),
                           'NEWS_AUTHOR'        => $author,
                           'NEWS_IMAGE'         => (empty($objResult->fields['newsimage'])) ? '' : '<img src="'.$_PATHCONFIG['ascms_root_offset'].$objResult->fields['newsimage'].'" alt="'.$newstitle.'" title="'.$newstitle.'" />'
                        ));

                $this->_objTpl->parse('newsrow');
                $i++;
                $objResult->MoveNext();
            }
        } else {
            $this->_objTpl->setVariable(array(
                'NEWS_DATE'     => '',
                'NEWS_LINK'     => $_ARRAYLANG['TXT_NO_NEWS_FOUND'],
                'NEWS_CATEGORY' => '',
            ));
            $this->_objTpl->parse('newsrow');
        }
        return $this->_objTpl->get();
    }

    /**
    * Gets the global page title
    *
    * @param     string    (optional)$pageTitle
    */
    function getPageTitle($pageTitle="")
    {
        if (empty($this->newsTitle)) {
            $this->newsTitle = htmlspecialchars(strip_tags(stripslashes($pageTitle)), ENT_QUOTES, CONTREXX_CHARSET);
        }
    }

    function _notify_by_email($news_id)
    {
        $user_id  = intval($this->arrSettings['news_notify_user']);
        $group_id = intval($this->arrSettings['news_notify_group']);
        $users_in_group = array();
        if ($group_id > 0) {
            $objFWUser = FWUser::getFWUserObject();
            $objGroup = $objFWUser->objGroup->getGroup($group_id);
            if ($objGroup) {
                $users_in_group = $objGroup->getAssociatedUserIds();
            }
        }
        if ($user_id > 0) {
            $users_in_group[] = $user_id;
        }
        // Now we have fetched all user IDs that
        // are to be notified. Now send those emails!
        foreach ($users_in_group as $user_id) {
            $this->_notify_user_by_email($user_id, $news_id);
        }
    }


    function _notify_user_by_email($user_id, $news_id)
    {
        global $_ARRAYLANG, $_CONFIG;

        // First, load recipient infos.
        try {
            $objFWUser = FWUser::getFWUserObject();
            $objUser = $objFWUser->objUser->getUser($user_id);
        } catch (Exception $e) {}
        if (!$objUser) {
            return false;
        }
        $user  = $objUser->getUsername();
        $first = $objUser->getProfileAttribute('firstname');
        $last  = $objUser->getProfileAttribute('lastname');
        $email = $objUser->getEmail();

        // Adress user with username if no first, lastname is defined.
        $name = ($first && $last) ? "$first $last" : $user;

        $msg  = $_ARRAYLANG['TXT_NOTIFY_ADDRESS'] . " $name\n\n";
        // Split the message text into lines
        $words = preg_split('/\s+/s', $_ARRAYLANG['TXT_NOTIFY_MESSAGE']);
        $line = '';
        for ($idx = 0; $idx < sizeof($words); $idx++) {
            if (strlen($line . ' ' . $words[$idx]) < 80) {
                // Line not full yet
                if ($line) $line .= ' ' . $words[$idx];
                else       $line =        $words[$idx];
            } else {
                // Line is full. add to message and empty.
                $msg .= "$line\n";
                $line = $words[$idx];
            }
        }
        $serverPort = '';
        $msg .= "$line\n";
        $msg .= "  http://".$_SERVER['SERVER_NAME'].  $serverPort.ASCMS_PATH_OFFSET . "/cadmin/index.php?cmd=news"
            . "&act=edit&newsId=$news_id&validate=true";
        $msg .= "\n\n";
        $msg .= $_CONFIG['coreAdminName'];

        if (@include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') {
            $objMail = new phpmailer();

            if ($_CONFIG['coreSmtpServer'] > 0 && @include_once ASCMS_CORE_PATH.'/SmtpSettings.class.php') {
                if (($arrSmtp = SmtpSettings::getSmtpAccount($_CONFIG['coreSmtpServer'])) !== false) {
                    $objMail->IsSMTP();
                    $objMail->Host = $arrSmtp['hostname'];
                    $objMail->Port = $arrSmtp['port'];
                    $objMail->SMTPAuth = true;
                    $objMail->Username = $arrSmtp['username'];
                    $objMail->Password = $arrSmtp['password'];
                }
            }

            $objMail->CharSet = CONTREXX_CHARSET;
            $objMail->From = $_CONFIG['coreAdminEmail'];
            $objMail->FromName = $_CONFIG['coreAdminName'];
            $objMail->Subject = $_ARRAYLANG['TXT_NOTIFY_SUBJECT'];
            $objMail->IsHTML(false);
            $objMail->Body = $msg;

            $objMail->AddAddress($email, $name);
            $objMail->Send();
        }
        return true;
    }

    /**
    * Get the submit page
    *
    * Get the submit, login or the noaccess page depending on the configuration
    *
    * @access private
    * @global array
    * @global ADONewConnection
    * @see HTML_Template_Sigma::setTemplate(), modulemanager::getModules(), Permission::checkAccess()
    * @return string content
    */
    function _submit()
    {
        global $_ARRAYLANG, $objDatabase;

        $this->_objTpl->setTemplate($this->pageContent);

        require_once ASCMS_CORE_PATH.'/modulemanager.class.php';
        $objModulManager = new modulemanager();
        $arrInstalledModules = $objModulManager->getModules();
        if (in_array(23, $arrInstalledModules)) {
            $communityModul = true;
        } else {
            $communityModul = false;
        }

        if (!$this->arrSettings['news_submit_news'] == '1' || (!$communityModul && $this->arrSettings['news_submit_only_community'] == '1')) {
            header('Location: index.php?section=news');
            exit;
        } elseif ($this->arrSettings['news_submit_only_community'] == '1') {
            $objFWUser = FWUser::getFWUserObject();
            if ($objFWUser->objUser->login()) {
                if (!Permission::checkAccess(61, 'static')) {
                    header('Location: '.CONTREXX_DIRECTORY_INDEX.'?section=login&cmd=noaccess');
                    exit;
                }
            } else {
                $link = base64_encode(CONTREXX_DIRECTORY_INDEX.'?'.$_SERVER['QUERY_STRING']);
                header('Location: '.CONTREXX_DIRECTORY_INDEX.'?section=login&redirect='.$link);
                exit;
            }
        }

        $newsCat = "";
        $newsText = "";
        $newsTeaserText = '';
        $newsTitle = "";
        $newsRedirect = 'http://';
        $newsSource = "http://";
        $newsUrl1 = "http://";
        $newsUrl2 = "http://";
        $insertStatus = false;

        if (isset($_POST['submitNews'])) {
            $objValidator = new FWValidator();

            $_POST['newsTitle'] = contrexx_strip_tags(html_entity_decode($_POST['newsTitle']));
            $_POST['newsTeaserText'] = contrexx_stripslashes($_POST['newsTeaserText']);
            $_POST['newsRedirect'] = $objValidator->getUrl(contrexx_strip_tags(html_entity_decode($_POST['newsRedirect'])));
            $_POST['newsText'] = get_magic_quotes_gpc() ? stripslashes($_POST['newsText']) : $_POST['newsText'];
            $_POST['newsText'] = $this->filterBodyTag($_POST['newsText']);
            $_POST['newsSource'] = $objValidator->getUrl(contrexx_strip_tags(html_entity_decode($_POST['newsSource'])));
            $_POST['newsUrl1'] = $objValidator->getUrl(contrexx_strip_tags(html_entity_decode($_POST['newsUrl1'])));
            $_POST['newsUrl2'] = $objValidator->getUrl(contrexx_strip_tags(html_entity_decode($_POST['newsUrl2'])));
            $_POST['newsCat'] = intval($_POST['newsCat']);

            if (!empty($_POST['newsTitle']) && (!empty($_POST['newsText']) || (!empty($_POST['newsRedirect']) && $_POST['newsRedirect'] != 'http://'))) {
                $insertStatus = $this->_insert();
                if (!$insertStatus) {
                    $newsTitle = $_POST['newsTitle'];
                    $newsTeaserText = $_POST['newsTeaserText'];
                    $newsRedirect = $_POST['newsRedirect'];
                    $newsSource = $_POST['newsSource'];
                    $newsUrl1 = $_POST['newsUrl1'];
                    $newsUrl2 = $_POST['newsUrl2'];
                    $newsCat = $_POST['newsCat'];
                    $newsText = $_POST['newsText'];
                }
                else {
                    $this->_notify_by_email($insertStatus);
                }
            } else {
                $newsTitle = $_POST['newsTitle'];
                $newsTeaserText = $_POST['newsTeaserText'];
                $newsRedirect = $_POST['newsRedirect'];
                $newsSource = $_POST['newsSource'];
                $newsUrl1 = $_POST['newsUrl1'];
                $newsUrl2 = $_POST['newsUrl2'];
                $newsCat = $_POST['newsCat'];
                $newsText = $_POST['newsText'];

                $this->_submitMessage = $_ARRAYLANG['TXT_SET_NEWS_TITLE_AND_TEXT_OR_REDIRECT']."<br /><br />";
            }
        }

        if ($insertStatus) {
            if ($this->_objTpl->blockExists('news_submit_form')) {
                $this->_objTpl->hideBlock('news_submit_form');
            }
            if ($this->_objTpl->blockExists('news_submitted')) {
                $this->_objTpl->touchBlock('news_submitted');
            }
        } else {
            require ASCMS_CORE_PATH.'/wysiwyg.class.php';
            global $wysiwygEditor, $FCKeditorBasePath;
            $wysiwygEditor = "FCKeditor";
            $FCKeditorBasePath = "/editor/fckeditor/";

            $this->_objTpl->setVariable(array(
                'TXT_NEWS_MESSAGE'          => $_ARRAYLANG['TXT_NEWS_MESSAGE'],
                'TXT_TITLE'                 => $_ARRAYLANG['TXT_TITLE'],
                'TXT_CATEGORY'              => $_ARRAYLANG['TXT_CATEGORY'],
                'TXT_HYPERLINKS'            => $_ARRAYLANG['TXT_HYPERLINKS'],
                'TXT_EXTERNAL_SOURCE'       => $_ARRAYLANG['TXT_EXTERNAL_SOURCE'],
                'TXT_LINK'                  => $_ARRAYLANG['TXT_LINK'],
                'TXT_NEWS_NEWS_CONTENT'     => $_ARRAYLANG['TXT_NEWS_NEWS_CONTENT'],
                'TXT_NEWS_TEASER_TEXT'      => $_ARRAYLANG['TXT_NEWS_TEASER_TEXT'],
                'TXT_SUBMIT_NEWS'           => $_ARRAYLANG['TXT_SUBMIT_NEWS'],
                'TXT_NEWS_REDIRECT'         => $_ARRAYLANG['TXT_NEWS_REDIRECT'],
                'TXT_NEWS_NEWS_URL'         => $_ARRAYLANG['TXT_NEWS_NEWS_URL'],
                'NEWS_TEXT'                 => get_wysiwyg_editor('newsText', $newsText, 'news'),
                'NEWS_CAT_MENU'             => $this->getCategoryMenu(FRONTEND_LANG_ID, $newsCat),
                'NEWS_TITLE'                => $newsTitle,
                'NEWS_SOURCE'               => $newsSource,
                'NEWS_URL1'                 => $newsUrl1,
                'NEWS_URL2'                 => $newsUrl2,
                'NEWS_TEASER_TEXT'          => $newsTeaserText,
                'NEWS_REDIRECT'             => $newsRedirect
            ));

            if ($this->_objTpl->blockExists('news_category_menu')) {
                $objResult = $objDatabase->Execute('SELECT catid, name FROM '.DBPREFIX.'module_news_categories WHERE lang='.FRONTEND_LANG_ID.' ORDER BY catid asc');

                if ($objResult !== false) {
                    while (!$objResult->EOF) {
                        $this->_objTpl->setVariable(array(
                            'NEWS_CATEGORY_ID'      => $objResult->fields['catid'],
                            'NEWS_CATEGORY_TITLE'   => htmlentities($objResult->fields['name'], ENT_QUOTES, CONTREXX_CHARSET)
                        ));
                        $this->_objTpl->parse('news_category_menu');
                        $objResult->MoveNext();
                    }
                }
            }

            if ($this->_objTpl->blockExists('news_submit_form')) {
                $this->_objTpl->parse('news_submit_form');
            }
        }

        $this->_objTpl->setVariable('NEWS_STATUS_MESSAGE', $this->_submitMessage);
        return $this->_objTpl->get();
    }



    /**
    * Insert a new news message
    *
    * @access private
    * @global ADONewConnection
    * @global array
    * @return boolean true on success - false on failure
    */
    function _insert()
    {
        global $objDatabase, $_ARRAYLANG;

        $objFWUser = FWUser::getFWUserObject();

        $date = time();
        $newstitle = $_POST['newsTitle'];
        $newstext = addslashes($_POST['newsText']);
        $newsRedirect = $_POST['newsRedirect'];
        if ($newsRedirect == 'http://') {
            $newsRedirect = '';
        }
        $newsTeaserText = addslashes($_POST['newsTeaserText']);
        $newssource = $_POST['newsSource'];
        $newsurl1 = $_POST['newsUrl1'];
        $newsurl2 = $_POST['newsUrl2'];
        $newscat = $_POST['newsCat'];
        $userid = $objFWUser->objUser->getId();

        $objResult = $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_news (
            id,
            date,
            title,
            text,
            redirect,
            source,
            url1,
            url2,
            catid,
            lang,
            startdate,
            enddate,
            status,
            validated,
            userid,
            teaser_text,
            changelog
            ) VALUES (
            '',
            '$date',
            '$newstitle',
            '$newstext',
            '".$newsRedirect."',
            '$newssource',
            '$newsurl1',
            '$newsurl2',
            '$newscat',
            '".FRONTEND_LANG_ID."',
            '',
            '',
            '".($this->arrSettings['news_activate_submitted_news'] == '1' ? "1" : "0")."',
            '".($this->arrSettings['news_activate_submitted_news'] == '1' ? "1" : "0")."',
            '$userid',
            '".$newsTeaserText."',
            '$date')"
        );

        if ($objResult !== false){
            $this->_submitMessage = $_ARRAYLANG['TXT_NEWS_SUCCESSFULLY_SUBMITED']."<br /><br />";
            $ins_id = $objDatabase->Insert_ID();
            return $ins_id;
        } else{
            $this->_submitMessage = $_ARRAYLANG['TXT_NEWS_SUBMIT_ERROR']."<br /><br />";
            return false;
        }
    }

    /**
    * Show feed page
    *
    * @access private
    * @global array
    * @global integer
    * @return string Template output
    */
    function _showFeed()
    {
        global $_ARRAYLANG;

        $objLanguage = new FWLanguage();
        $this->_objTpl->setTemplate($this->pageContent);
        $serverPort = $_SERVER['SERVER_PORT'] == 80 ? "" : ":".intval($_SERVER['SERVER_PORT']);
        $rssFeedUrl = "http://".$_SERVER['SERVER_NAME'].$serverPort.ASCMS_PATH_OFFSET."/feed/news_headlines_".$objLanguage->getLanguageParameter(FRONTEND_LANG_ID, 'lang').".xml";
        $jsFeedUrl = "http://".$_SERVER['SERVER_NAME'].$serverPort.ASCMS_PATH_OFFSET."/feed/news_".$objLanguage->getLanguageParameter(FRONTEND_LANG_ID, 'lang').".js";
        $hostname = addslashes(htmlspecialchars($_SERVER['SERVER_NAME'], ENT_QUOTES, CONTREXX_CHARSET));

        $rss2jsCode = <<<RSS2JSCODE
&lt;script language="JavaScript" type="text/javascript"&gt;
&lt;!--
// {$_ARRAYLANG['TXT_NEWS_OPTIONAL_VARS']}
var rssFeedFontColor = "#000000"; // {$_ARRAYLANG['TXT_NEWS_FONT_COLOR']}
var rssFeedFontSize = 8; // {$_ARRAYLANG['TXT_NEWS_FONT_SIZE']}
var rssFeedFont = "Arial, Verdana"; // {$_ARRAYLANG['TXT_NEWS_FONT']}
var rssFeedLimit = 10; // {$_ARRAYLANG['TXT_NEWS_DISPLAY_LIMIT']}
var rssFeedShowDate = true; // {$_ARRAYLANG['TXT_NEWS_SHOW_NEWS_DATE']}
var rssFeedTarget = "_blank"; // _blank | _parent | _self | _top
// --&gt;
&lt;/script&gt;
&lt;script type="text/javascript" language="JavaScript" src="$jsFeedUrl"&gt;&lt;/script&gt;
&lt;noscript&gt;
&lt;a href="$rssFeedUrl"&gt;$hostname - {$_ARRAYLANG['TXT_NEWS_SHOW_NEWS']}&lt;/a&gt;
&lt;/noscript&gt;
RSS2JSCODE;

        $this->_objTpl->setVariable(array(
            'NEWS_HOSTNAME'     => $hostname,
            'NEWS_RSS2JS_CODE'  => $rss2jsCode,
            'NEWS_RSS2JS_URL'   => $jsFeedUrl,
            'NEWS_RSS_FEED_URL' => $rssFeedUrl
        ));
        return $this->_objTpl->get();
    }
}

?>
