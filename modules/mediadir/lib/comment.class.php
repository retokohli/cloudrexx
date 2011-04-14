<?php
/**
 * Media  Directory Comments Class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_marketplace
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH . '/mediadir/lib/lib.class.php';

class mediaDirectoryComment extends mediaDirectoryLibrary
{
    public $strOkMessage;
    public $strErrMessage;

    var $tmpPageSection;
    var $tmpPageCmd;

    /**
     * Constructor
     */
    function __construct()
    {
        parent::getSettings();
    }



    function getCommentJavascript(){
        global $_ARRAYLANG;

        $strOkMessage = $_ARRAYLANG['TXT_MEDIADIR_COMMENT_ADD_SUCCESSFULL'];
        $strErrMessage = $_ARRAYLANG['TXT_MEDIADIR_COMMENT_ADD_CORRUPT'];
        
        $strFunctionComment = $this->moduleName.'Comment';
        $strFunctionRefreshComment = $this->moduleName.'RefreshComments';
        $strFunctionCheckCommentForm = $this->moduleName.'CheckCommentForm';
        $strSection = $this->moduleName;
        $strNewComment = $this->moduleName.'NewComment_';
        $strNewAddedComment = $this->moduleName.'NewAddedComment_';
        $strCommentOk = $this->moduleName.'CommentOk';
        $strCommentErr = $this->moduleName.'CommentErr';
        $strCommentErrMessage = $this->moduleName.'ErrorMessage';

        $strCommentsJavascript  =  <<<EOF
        
var $strFunctionComment = function(entry)
{
    var postParameters = $('commentFormInputs_'+entry).serialize(true);

    var elEntry = $('commentForm_'+entry);
    var oldChilds = elEntry.childElements();
    elEntry.hide();
    var elLoadingImg = new Element('img', {src: 'images/modules/$strSection/loading.gif', border: '0', alt:'loading...'});
    elEntry.insert(elLoadingImg,'after');

    new Ajax.Request('index.php?section=$strSection&comment=add&eid='+entry, {
        method: 'post',
        parameters: postParameters,
        onSuccess: function (transport){
            var response = transport.responseText;
            var arrResponse = response.split("-");
            var status = arrResponse[0];
            var section = arrResponse[1];
            var cmd = arrResponse[2];

            if(status == 'success') {
                $strFunctionRefreshComment(entry,section,cmd);
            } else if (status == 'captcha') {
                elLoadingImg.remove();
                $$('#commentForm_'+entry+' #commentCaptcha')[0].style.border = "#ff0000 1px solid";
                elEntry.show();
            }
            else {
                $('commentForm_'+entry).className = '$strCommentErr';
                $('commentForm_'+entry).update('$strErrMessage');
            }
        },
        onFailure: function(){
            $('commentForm_'+entry).className = '$strCommentErr';
            $('commentForm_'+entry).update('$strErrMessage');
        }
    });

}

var $strFunctionRefreshComment = function(entry,section,cmd)
{
    new Ajax.Request('index.php', {
        method: 'get',
        parameters: {section : "$strSection", comment : "refresh", eid : entry, pageSection : section, pageCmd : cmd},
        onSuccess: function (transport){
            var response = transport.responseText;

            $('$strNewAddedComment'+entry).className = '$strNewComment';
            $('$strNewAddedComment'+entry).update(response);
            $('$strNewAddedComment'+entry).setStyle({display: 'block'});

            $('commentForm_'+entry).className = '$strCommentOk';
            $('commentForm_'+entry).update('$strOkMessage');
        },
        onFailure: function(){
        }
    });
}

var $strFunctionCheckCommentForm = function(entry)
{
    var isOk = true;
    var commentName = $('commentName').value;
    var commentComment = $('commentComment').value;

    if(commentName == "") {
    	isOk = false;
    	$('commentName').style.border = "#ff0000 1px solid";
    } else {
        $('commentName').style.borderColor = '';
    }

    if(commentComment == "") {
    	isOk = false;
    	$('commentComment').style.border = "#ff0000 1px solid";
    } else {
        $('commentComment').style.borderColor = '';
    }

    if (!isOk) {
		$('$strCommentErrMessage').style.display = "block";
	} else {
	   $strFunctionComment(entry);
	}
}

EOF;
        return $strCommentsJavascript;
    }



    function getCommentForm($objTpl, $intEnrtyId) {
        global $_ARRAYLANG, $_CORELANG, $objDatabase;

        if($this->arrSettings['settingsAllowComments'] == 1) {
            $bolGenerateCommentForm = false;

            $objFWUser  = FWUser::getFWUserObject();
            $objUser    = $objFWUser->objUser;

            if($this->arrSettings['settingsCommentOnlyCommunity'] == 1) {
                if($objUser->login()) {
                    $bolGenerateCommentForm = true;
                }
            } else {
                $bolGenerateCommentForm = true;
            }

            if($bolGenerateCommentForm) {
                if($objUser->login()) {
                    $strCommentFormName = htmlspecialchars($objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET);
                    $strCommentFormMail = htmlspecialchars($objUser->getEmail(), ENT_QUOTES, CONTREXX_CHARSET);
                    $strCommentFormUrl = htmlspecialchars($objUser->getProfileAttribute('website'), ENT_QUOTES, CONTREXX_CHARSET);
                } else {
                    $strCaptchaCode = $this->getCaptcha();
                }

                $strCommentForm  = '<div class="'.$this->moduleName.'CommentForm" id="commentForm_'.$intEnrtyId.'">';
                $strCommentForm .= '<form action="'.$_SERVER['REQUEST_URI'].'" name="commentFormInputs_'.$intEnrtyId.'" id="commentFormInputs_'.$intEnrtyId.'" method="post" >';
                $strCommentForm .= '<input name="commentPageSection" value="'.$_GET['section'].'" type="hidden" />';
                $strCommentForm .= '<input name="commentPageCmd" value="'.$_GET['cmd'].'" type="hidden" />';
                $strCommentForm .= '<p><label>'.$_CORELANG['TXT_NAME'].'<font color="#ff0000"> *</font></label><input name="commentName" id="commentName" class="'.$this->moduleName.'InputfieldComment" value="'.$strCommentFormName.'" type="text" /></p>';
                $strCommentForm .= '<p><label>'.$_CORELANG['TXT_ACCESS_EMAIL'].'</label><input name="commentMail" class="'.$this->moduleName.'InputfieldComment" id="commentMail" value="'.$strCommentFormMail.'" type="text" /></p>';
                $strCommentForm .= '<p><label>'.$_CORELANG['TXT_ACCESS_WEBSITE'].'</label><input name="commentUrl" class="'.$this->moduleName.'InputfieldComment" id="commentUrl" value="'.$strCommentFormUrl.'" type="text" /></p>';
                $strCommentForm .= '<p><label>'.$_ARRAYLANG['TXT_MEDIADIR_COMMENT'].'<font color="#ff0000"> *</font></label><textarea name="commentComment" id="commentComment" class="'.$this->moduleName.'TextareaComment"></textarea></p>';
                $strCommentForm .= $strCaptchaCode;
                $strCommentForm .= '<p><input class="'.$this->moduleName.'ButtonComment" value="'.$_ARRAYLANG['TXT_MEDIADIR_ADD'].'" onclick="'.$this->moduleName.'CheckCommentForm('.$intEnrtyId.');" name="add" type="button"></p>';
                $strCommentForm .= '</form>';
                $strCommentForm .= '<div style="display: none; color: rgb(255, 0, 0);" id="'.$this->moduleName.'ErrorMessage"><p>'.$_ARRAYLANG['TXT_MEDIADIR_PLEASE_CHECK_INPUT'].'</p></div>';
                $strCommentForm .= '</div>';

                $objTpl->setVariable(array(
                    $this->moduleLangVar.'_ENTRY_COMMENT_FORM' => $strCommentForm,
                    'TXT_'.$this->moduleLangVar.'_COMMENTS' => $_ARRAYLANG['TXT_MEDIADIR_COMMENTS']
                ));
            }
        }
    }



    function getCaptcha() {
        global $_ARRAYLANG;

        include_once ASCMS_LIBRARY_PATH.'/spamprotection/captcha.class.php';
        $captcha = new Captcha();

        $strCode = '<p><label>CAPTCHA</label><img alt="'.$captcha->getAlt().'" src="'.$captcha->getUrl().'" class="captcha" /> <input type="text" name="commentCaptcha" id="commentCaptcha" /><br /></p>';

        return $strCode;
    }



    function getComments($objTpl, $intEnrtyId) {
        global $_ARRAYLANG, $objDatabase;

        if($this->arrSettings['settingsAllowComments'] == 1) {
            $objRSGetComments = $objDatabase->Execute("
                SELECT
                    `id`, `added_by`, `date`, `ip`, `name`, `mail`, `url`, `notification`, `comment`
                FROM
                    ".DBPREFIX."module_".$this->moduleTablePrefix."_comments
                WHERE
                    `entry_id` = '".intval($intEnrtyId)."'
                ORDER BY
                    `id` ASC
            ");

            $intCountComments = $objRSGetComments->RecordCount();

            if(intval($objTpl->blockExists($this->moduleName.'EntryComments')) != 0) {
                if ($objRSGetComments !== false) {
                    $i=0;
                    while (!$objRSGetComments->EOF) {
                        if(intval($objRSGetComments->fields['added_by']) != 0) {
                            $objFWUser  = FWUser::getFWUserObject();
                            $objUser = $objFWUser->objUser;
                            $objUser = $objUser->getUser(intval($objRSGetComments->fields['added_by']));
                            $strAddedBy = $objUser->getUsername();
                        } else {
                            $strAddedBy = "unknown";
                        }

                        if(!empty($objRSGetComments->fields['url'])) {
                            if(substr($objRSGetComments->fields['url'], 0,7) != 'http://') {
                                $strUrl = '<a href="http://'.strip_tags($objRSGetComments->fields['url']).'" class="'.$this->moduleName.'CommentUrl">'.strip_tags($objRSGetComments->fields['url']).'</a>';
                            } else {
                                $strUrl = '<a href="'.strip_tags($objRSGetComments->fields['url']).'" class="'.$this->moduleName.'CommentUrl">'.strip_tags($objRSGetComments->fields['url']).'</a>';
                            }
                        }

                        if(!empty($objRSGetComments->fields['mail'])) {
                            $strMail = '<a href="mailto:'.$objRSGetComments->fields['mail'].'" class="'.$this->moduleName.'CommentMail">'.$objRSGetComments->fields['mail'].'</a>';
                        }

                        $objTpl->setVariable(array(
                            $this->moduleLangVar.'_ENTRY_COMMENT_ROW_CLASS' => $i%2==0 ? 'row1' : 'row2',
                            $this->moduleLangVar.'_ENTRY_COMMENT_ENTRY_ID' => intval($intEnrtyId),
                            $this->moduleLangVar.'_ENTRY_COMMENT_ID' => intval($objRSGetComments->fields['id']),
                            $this->moduleLangVar.'_ENTRY_COMMENT_ADDED_BY' => $strAddedBy,
                            $this->moduleLangVar.'_ENTRY_COMMENT_NAME' => strip_tags(htmlspecialchars($objRSGetComments->fields['name'], ENT_QUOTES, CONTREXX_CHARSET)),
                            $this->moduleLangVar.'_ENTRY_COMMENT_MAIL' => $strMail,
                            $this->moduleLangVar.'_ENTRY_COMMENT_MAIL_SRC' => strip_tags(htmlspecialchars($objRSGetComments->fields['mail'], ENT_QUOTES, CONTREXX_CHARSET)),
                            $this->moduleLangVar.'_ENTRY_COMMENT_URL' => $strUrl,
                            $this->moduleLangVar.'_ENTRY_COMMENT_URL_SRC' => strip_tags(htmlspecialchars($objRSGetComments->fields['url'], ENT_QUOTES, CONTREXX_CHARSET)),
                            $this->moduleLangVar.'_ENTRY_COMMENT_COMMENT' => strip_tags(htmlspecialchars($objRSGetComments->fields['comment'], ENT_QUOTES, CONTREXX_CHARSET)),
                            $this->moduleLangVar.'_ENTRY_COMMENT_IP' => strip_tags(htmlspecialchars($objRSGetComments->fields['ip'], ENT_QUOTES, CONTREXX_CHARSET)),
                            $this->moduleLangVar.'_ENTRY_COMMENT_DATE' => date("d. M Y",$objRSGetComments->fields['date'])."  ".$_ARRAYLANG['TXT_MEDIADIR_AT']." ".date("H:i:s",$objRSGetComments->fields['date']),
                        ));

                        $i++;
                        $objTpl->parse($this->moduleName.'EntryComments');

                        $objRSGetComments->MoveNext();
                    }
                }
            }

            $objTpl->setVariable(array(
                $this->moduleLangVar.'_ENTRY_COMMENTS' => intval($intCountComments).' '.$_ARRAYLANG['TXT_MEDIADIR_COMMENTS'],
                'TXT_'.$this->moduleLangVar.'_COMMENTS' => $_ARRAYLANG['TXT_MEDIADIR_COMMENTS'],
                $this->moduleLangVar.'_ENTRY_NEW_ADDED_COMMENT' => '<div id="'.$this->moduleName.'NewAddedComment_'.$intEnrtyId.'" style="display: none;">hier erscheint der gerade eben hinzugefügte Kommentar.</div>',
            ));
        }
    }



    function saveComment($intEntryId, $arrCommentData) {
        global $_ARRAYLANG, $objDatabase;

        $strRemoteAddress = contrexx_addslashes($_SERVER['REMOTE_ADDR']);

        $objFWUser  = FWUser::getFWUserObject();
        $objUser    = $objFWUser->objUser;

        if($objUser->login()) {
            $intAddedBy = $objUser->getId();
        } else {
            $intAddedBy = 0;

            //captcha check
            include_once ASCMS_LIBRARY_PATH.'/spamprotection/captcha.class.php';
            $captcha = new Captcha();
            if(!$captcha->check($arrCommentData['commentCaptcha']))
                die('captcha');
        }

        $objInsertComment = $objDatabase->Execute("
            INSERT INTO
                ".DBPREFIX."module_".$this->moduleTablePrefix."_comments
            SET
                `entry_id`='".intval($intEntryId)."',
                `added_by`='".intval($intAddedBy)."',
                `date`='".mktime()."',
                `ip`='".$strRemoteAddress."',
                `name`='".contrexx_addslashes($arrCommentData['commentName'])."',
                `mail`='".contrexx_addslashes($arrCommentData['commentMail'])."',
                `url`='".contrexx_addslashes($arrCommentData['commentUrl'])."',
                `notification`='0',
                `comment`='".contrexx_addslashes($arrCommentData['commentComment'])."'
        ");

        if($objInsertComment !== false) {
            echo 'success-'.$arrCommentData['commentPageSection']."-".$arrCommentData['commentPageCmd'];
        } else {
            echo 'fail';
        }

        die();
    }



    function refreshComments($intEnrtyId, $strPageSection, $strPageCmd) {
        global $_ARRAYLANG, $objDatabase, $_LANGID;

        $arrComment = $this->getLastComment($intEnrtyId);

        $objRSGetContentPage = $objDatabase->SelectLimit("
            SELECT
                content.`content` AS content
            FROM
                ".DBPREFIX."content_navigation AS navigation,
                ".DBPREFIX."modules AS modules,
                ".DBPREFIX."content AS content
            WHERE
                modules.`name` = '".contrexx_addslashes($strPageSection)."'
            AND
                navigation.`module` = modules.`id`
            AND
                navigation.`cmd` = '".contrexx_addslashes($strPageCmd)."'
            AND
                navigation.`lang` = '".intval($_LANGID)."'
            AND
                navigation.`catid` = content.`id`
        ", 1);


        if ($objRSGetContentPage !== false) {
            $strPageContent = $objRSGetContentPage->fields['content'];
            $regexBlock = '<!-- BEGIN '.$this->moduleName.'EntryComments -->(.*?)<!-- END '.$this->moduleName.'EntryComments -->';

            if(preg_match("/".$regexBlock."/is", $strPageContent, $matchBlock)){
                $strComment = $matchBlock[1];

                $arrPlacholders = array_keys($arrComment);
                $intNumPlaceholders = intval(count($arrPlacholders));

                for ($x = 0; $x < $intNumPlaceholders; $x++) {
                    $strComment = str_replace($arrPlacholders[$x], $arrComment[$arrPlacholders[$x]], $strComment);
                }

                echo $strComment;
            }
        }

        die();
    }



    function getLastComment($intEnrtyId) {
        global $_ARRAYLANG, $objDatabase;

        $arrComment = array();

        $objRSGetComment = $objDatabase->SelectLimit("
            SELECT
                `id`, `added_by`, `date`, `ip`, `name`, `mail`, `url`, `notification`, `comment`
            FROM
                ".DBPREFIX."module_".$this->moduleTablePrefix."_comments
            WHERE
                `entry_id` = '".intval($intEnrtyId)."'
            ORDER BY
                `id` DESC
        ", 1);

        if ($objRSGetComment !== false) {
            if(intval($objRSGetComment->fields['added_by']) != 0) {
                $objFWUser  = FWUser::getFWUserObject();
                $objUser = $objFWUser->objUser;
                $objUser = $objUser->getUser(intval($objRSGetComment->fields['added_by']));
                $strAddedBy = $objUser->getUsername();
            } else {
                $strAddedBy = "unknown";
            }

            if(!empty($objRSGetComment->fields['url'])) {
                if(substr($objRSGetComments->fields['url'], 0,7) != 'http://') {
                    $strUrl = '<a href="http://'.strip_tags($objRSGetComments->fields['url']).'" class="'.$this->moduleName.'CommentUrl">'.strip_tags($objRSGetComment->fields['url']).'</a>';
                } else {
                    $strUrl = '<a href="'.strip_tags($objRSGetComment->fields['url']).'" class="'.$this->moduleName.'CommentUrl">'.strip_tags($objRSGetComment->fields['url']).'</a>';
                }
            }

            if(!empty($objRSGetComment->fields['mail'])) {
                $strMail = '<a href="mailto:'.$objRSGetComment->fields['mail'].'" class="'.$this->moduleName.'CommentMail">'.$objRSGetComment->fields['mail'].'</a>';
            }

            $arrComment['{'.$this->moduleLangVar.'_ENTRY_COMMENT_ENTRY_ID}'] = intval($intEnrtyId);
            $arrComment['{'.$this->moduleLangVar.'_ENTRY_COMMENT_ID}'] = intval($objRSGetComment->fields['id']);
            $arrComment['{'.$this->moduleLangVar.'_ENTRY_COMMENT_ADDED_BY}'] = $strAddedBy;
            $arrComment['{'.$this->moduleLangVar.'_ENTRY_COMMENT_NAME}'] = strip_tags(htmlspecialchars($objRSGetComment->fields['name'], ENT_QUOTES, CONTREXX_CHARSET));
            $arrComment['{'.$this->moduleLangVar.'_ENTRY_COMMENT_MAIL}'] = $strMail;
            $arrComment['{'.$this->moduleLangVar.'_ENTRY_COMMENT_MAIL_SRC}'] = strip_tags(htmlspecialchars($objRSGetComment->fields['mail'], ENT_QUOTES, CONTREXX_CHARSET));
            $arrComment['{'.$this->moduleLangVar.'_ENTRY_COMMENT_URL}'] = $strUrl;
            $arrComment['{'.$this->moduleLangVar.'_ENTRY_COMMENT_URL_SRC}'] = strip_tags(htmlspecialchars($objRSGetComment->fields['url'], ENT_QUOTES, CONTREXX_CHARSET));
            $arrComment['{'.$this->moduleLangVar.'_ENTRY_COMMENT_COMMENT}'] = strip_tags(htmlspecialchars($objRSGetComment->fields['comment'], ENT_QUOTES, CONTREXX_CHARSET));
            $arrComment['{'.$this->moduleLangVar.'_ENTRY_COMMENT_IP}'] = strip_tags(htmlspecialchars($objRSGetComment->fields['ip'], ENT_QUOTES, CONTREXX_CHARSET));
            $arrComment['{'.$this->moduleLangVar.'_ENTRY_COMMENT_DATE}'] = date("d. M Y",$objRSGetComment->fields['date'])."  ".$_ARRAYLANG['TXT_MEDIADIR_AT']." ".date("H:i:s",$objRSGetComment->fields['date']);

            return $arrComment;
        }
    }



    function restoreComments($intEnrtyId) {
        global $_ARRAYLANG, $objDatabase;

        $objRestoreComments = $objDatabase->Execute("
            DELETE FROM
                ".DBPREFIX."module_".$this->moduleTablePrefix."_comments
            WHERE
                `entry_id`='".intval($intEnrtyId)."'
        ");

        if($objRestoreComments !== false) {
            return true;
        } else {
            return false;
        }
    }



    function deleteComment($intCommentId) {
        global $_ARRAYLANG, $objDatabase;

        $objDeleteComments = $objDatabase->Execute("
            DELETE FROM
                ".DBPREFIX."module_".$this->moduleTablePrefix."_comments
            WHERE
                `id`='".intval($intCommentId)."'
        ");

        if($objDeleteComments !== false) {
            return true;
        } else {
            return false;
        }
    }
}
