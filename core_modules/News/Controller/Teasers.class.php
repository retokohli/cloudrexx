<?php

/**
 * Teasers
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @version 1.0.0
 * @package     contrexx
 * @subpackage  coremodule_news
 * @todo        Edit PHP DocBlocks!
 */

namespace Cx\Core_Modules\News\Controller;

/**
 * Teasers
 *
 * class to show the news teasers
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @access public
 * @version 1.0.0
 * @package     contrexx
 * @subpackage  coremodule_news
 */
class Teasers extends \Cx\Core_Modules\News\Controller\NewsLibrary
{
    public $_pageTitle;
    public $_objTpl;
    public $administrate;
    public $arrTeaserTemplates = array();
    public $arrTeaserFrameTemplates = array();

    public $arrTeaserFrames;
    public $arrTeaserFrameNames;
    public $arrTeasers;

    public $arrFrameTeaserIds;

    public $arrNewsTeasers = array();
    public $arrNewsCategories = array();

    public $_currentXMLElementId;
    public $_currentXMLElement;
    public $_currentXMLArrayToFill;


    /**
    * PHP5 constructor
    * @global \Cx\Core\Html\Sigma
    * @see \Cx\Core\Html\Sigma::setErrorHandling, \Cx\Core\Html\Sigma::setVariable, initialize()
    */
    function __construct($administrate = false)
    {
        parent::__construct();
        $this->administrate = $administrate;

        $this->_objTpl = new \Cx\Core\Html\Sigma('.');
        \Cx\Core\Csrf\Controller\Csrf::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
        $this->_initialize();
    }


    function _initialize()
    {
        $this->initializeTeasers();
        $this->initializeTeaserFrames();
        //$this->_initializeTeaserTemplates();
        $this->initializeTeaserFrameTemplates();
    }


    function initializeTeasers()
    {
        global $objDatabase, $_CORELANG;

        $this->arrTeasers = array();
        $this->getSettings();

        $objResult = $objDatabase->Execute("
            SELECT tblN.id,
                   tblN.date,
                   tblN.userid,
                   tblN.teaser_frames,
                   tblN.redirect,
                   tblN.teaser_show_link,
                   tblN.teaser_image_path,
                   tblN.teaser_image_thumbnail_path,
                   tblL.title,
                   tblL.text AS teaser_full_text,
                   tblL.teaser_text
              FROM ".DBPREFIX."module_news AS tblN
             INNER JOIN ".DBPREFIX."module_news_locale AS tblL ON tblL.news_id=tblN.id
             WHERE tblL.lang_id=".FRONTEND_LANG_ID.
              ($this->administrate == false
                ? " AND tblN.validated='1'
                    AND tblN.status='1'
                    AND tblL.is_active=1
                    AND (tblN.startdate<='".date('Y-m-d H:i:s').
                    "' OR tblN.startdate='0000-00-00 00:00:00') AND (tblN.enddate>='".
                    date('Y-m-d H:i:s')."' OR tblN.enddate='0000-00-00 00:00:00')"
                : "" ).
              ($this->arrSettings['news_message_protection'] == '1' && !\Permission::hasAllAccess()
                ? (($objFWUser = \FWUser::getFWUserObject()) && $objFWUser->objUser->login()
                    ? " AND (tblN.frontend_access_id IN (".implode(',',
                          array_merge(array(0), $objFWUser->objUser->getDynamicPermissionIds())).
                        ") OR userid = ".$objFWUser->objUser->getId().") "
                    : " AND tblN.frontend_access_id=0 ")
                : '')."
             ORDER BY date DESC");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $arrFrames = explode(';', $objResult->fields['teaser_frames']);
                foreach ($arrFrames as $frameId) {
                    if (!isset($this->arrFrameTeaserIds[$frameId])) {
                        $this->arrFrameTeaserIds[$frameId] = array();
                    }
                    array_push($this->arrFrameTeaserIds[$frameId], $objResult->fields['id']);
                }
                if (!empty($objResult->fields['redirect'])) {
                    $extUrl = substr($objResult->fields['redirect'], 7);
                    $tmp    = explode('/', $extUrl);
                    $extUrl = "(".$tmp[0].")";
                } else {
                    $extUrl = "";
                }
                if ($this->administrate == false) {
                    $objFWUser = \FWUser::getFWUserObject();
                    $objUser = $objFWUser->objUser->getUser($objResult->fields['userid']);
                    if ($objUser) {
                        $firstname = $objUser->getProfileAttribute('firstname');
                        $lastname = $objUser->getProfileAttribute('lastname');
                        if (!empty($firstname) && !empty($lastname)) {
                            $author = contrexx_raw2xhtml($firstname.' '.$lastname);
                        } else {
                            $author = contrexx_raw2xhtml($objUser->getUsername());
                        }
                    } else {
                        $author = $_CORELANG['TXT_ANONYMOUS'];
                    }
                } else {
                    $author = '';
                }
                
                //Get the news categories, by news id
                $newsCategories = $this->getCategoriesByNewsId($objResult->fields['id']);
                $this->arrTeasers[$objResult->fields['id']] = array(
                    'id'                => $objResult->fields['id'],
                    'date'              => $objResult->fields['date'],
                    'title'             => $objResult->fields['title'],
                    'teaser_frames'     => $objResult->fields['teaser_frames'],
                    'redirect'          => $objResult->fields['redirect'],
                    'ext_url'           => $extUrl,
                    'category'          => implode(', ', contrexx_raw2xhtml($newsCategories)),
                    'category_id'       => array_keys($newsCategories), 
                    'teaser_full_text'  => $objResult->fields['teaser_full_text'],
                    'teaser_text'       => $objResult->fields['teaser_text'],
                    'teaser_show_link'  => $objResult->fields['teaser_show_link'],
                    'author'            => $author,
                    'teaser_image_path' => $objResult->fields['teaser_image_path'],
                    'teaser_image_thumbnail_path' => $objResult->fields['teaser_image_thumbnail_path'],
                );
                $objResult->MoveNext();
            }
        }
    }


    function initializeTeaserFrames($id = 0)
    {
        global $objDatabase;

        $this->arrTeaserFrames = array();
        $this->arrTeaserFrameNames = array();

        if ($id != 0) {
            $objResult = $objDatabase->SelectLimit("SELECT id, frame_template_id, name FROM ".DBPREFIX."module_news_teaser_frame WHERE id=".$id, 1);
        } else {
            $objResult = $objDatabase->Execute("SELECT id, frame_template_id, name FROM ".DBPREFIX."module_news_teaser_frame ORDER BY name");
        }
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $this->arrTeaserFrames[$objResult->fields['id']] = array(
                    'id'                => $objResult->fields['id'],
                    'frame_template_id' => $objResult->fields['frame_template_id'],
                    'name'              => $objResult->fields['name']
                );

                $this->arrTeaserFrameNames[$objResult->fields['name']] = $objResult->fields['id'];
                $objResult->MoveNext();
            }
        }
    }


    /**
    * Inizialize teaser frame templates
    *
    * @access private
    */
    function initializeTeaserFrameTemplates($id = 0)
    {
        global $objDatabase;

        if ($id == 0) {
            $objResult = $objDatabase->Execute("SELECT id, description, html, source_code_mode FROM ".DBPREFIX."module_news_teaser_frame_templates");
        } else {
            $objResult = $objDatabase->Execute("SELECT id, description, html, source_code_mode FROM ".DBPREFIX."module_news_teaser_frame_templates WHERE id=".$id);
        }
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $this->arrTeaserFrameTemplates[$objResult->fields['id']] = array(
                    'id'                => $objResult->fields['id'],
                    'description'       => $objResult->fields['description'],
                    'html'              => $objResult->fields['html'],
                    'source_code_mode'  => $objResult->fields['source_code_mode']
                );
                $objResult->MoveNext();
            }
        }
    }


    function getTeaserFrame($teaserFrameId, $templateId)
    {
        return $this->_getTeaserFrame($teaserFrameId, $templateId);
    }


    function setTeaserFrames($arrTeaserFrames, &$code)
    {
        global $objDatabase;

        $arrTeaserFramesNames = array_flip($this->arrTeaserFrameNames);

        foreach ($arrTeaserFrames as $teaserFrameName) {
            $arrMatches = preg_grep('/^'.$teaserFrameName.'$/i', $arrTeaserFramesNames);

            if (count($arrMatches)>0) {
                $frameId = array_keys($arrMatches);
                $id = $frameId[0];
                $templateId = $this->arrTeaserFrames[$id]['frame_template_id'];
                $code = str_replace("{TEASERS_".$teaserFrameName."}", $this->_getTeaserFrame($id, $templateId), $code);

            }
        }
    }


    /**
    * Get teaser frame
    *
    * Returns the selected teaser frame by $id with its teaserboxes
    *
    * @access private
    * @return string
    */
    function _getTeaserFrame($id, $templateId)
    {
        $teaserFrame = "";

        $arrTeaserBlocks = array();
        if (isset($this->arrTeaserFrameTemplates[$templateId]['html'])) {
            $teaserFrame = $this->arrTeaserFrameTemplates[$templateId]['html'];
            if (preg_match_all('/<!-- BEGIN (teaser_[0-9]+) -->/ms', $teaserFrame, $arrTeaserBlocks)) {
                $funcSort = create_function('$a, $b', '{$aNr = preg_replace("/^[^_]+_/", "", $a);$bNr = preg_replace("/^[^_]+_/", "", $b);if ($aNr == $bNr) {return 0;} return ($aNr < $bNr) ? -1 : 1;}');
                usort($arrTeaserBlocks[0], $funcSort);
                usort($arrTeaserBlocks[1], $funcSort);
                $arrMatch = array();                
                foreach ($arrTeaserBlocks[1] as $nr => $teaserBlock) {                    
                    if (preg_match('/<!-- BEGIN '.$teaserBlock.' -->(.*)<!-- END '.$teaserBlock.' -->/s', $teaserFrame, $arrMatch)) {
                        $teaserBlockCode = $arrMatch[1];
                    } else {
                        $teaserBlockCode = '';
                    }

                    $objTpl = new \Cx\Core\Html\Sigma();
                    $objTpl->setTemplate($teaserBlockCode); 
                    
                    if (isset ($this->arrFrameTeaserIds[$id][$nr])) {
                        $imagePath     = $this->arrTeasers[$this->arrFrameTeaserIds[$id][$nr]]['teaser_image_path'];
                        $thumbnailPath = $this->arrTeasers[$this->arrFrameTeaserIds[$id][$nr]]['teaser_image_thumbnail_path'];
                        $title         =  $this->arrTeasers[$this->arrFrameTeaserIds[$id][$nr]]['title'];
                        //Sets the news url link for the details page of the news message
                        $newsUrl       = empty($this->arrTeasers[$this->arrFrameTeaserIds[$id][$nr]]['redirect'])
                                         ? \Cx\Core\Routing\Url::fromModuleAndCmd('News', $this->findCmdById('details', $this->arrTeasers[$this->arrFrameTeaserIds[$id][$nr]]['category_id']), FRONTEND_LANG_ID, array('newsid' => $this->arrTeasers[$this->arrFrameTeaserIds[$id][$nr]]['id'], 'teaserId' => $this->arrTeaserFrames[$id]['id']))
                                         : $this->arrTeasers[$this->arrFrameTeaserIds[$id][$nr]]['redirect'];
                        
                        //Image or thumbnail Placeholders parsing
                        list($image, $htmlLinkImage, $imageSource) = self::parseImageThumbnail($imagePath, $thumbnailPath, $title, $newsUrl);
                        
                        if (!empty($image)) {
                            $objTpl->setVariable(array(
                                'TEASER_IMAGE'               => $image,
                                'TEASER_IMAGE_SRC'           => contrexx_raw2xhtml($imageSource),
                                'TEASER_IMAGE_ALT'           => contrexx_raw2xhtml($title),
                                'TEASER_IMAGE_LINK'          => $htmlLinkImage,
                            ));

                            if ($objTpl->blockExists('news_teaser_image')) {
                                $objTpl->parse('news_teaser_image');
                            }
                        } else {
                            if ($objTpl->blockExists('news_teaser_image')) {
                                $objTpl->hideBlock('news_teaser_image');
                            }
                        }
                        
                        //Parsing Images and Thumbnails Blocks
                        self::parseImageBlock($objTpl, $imagePath, $title, $newsUrl, 'teaser_image_detail');
                        self::parseImageBlock($objTpl, $thumbnailPath, $title, $newsUrl, 'teaser_image_thumbnail');
                        
                        if ($this->arrTeasers[$this->arrFrameTeaserIds[$id][$nr]]['teaser_show_link']) {
                            $objTpl->setVariable(array(
                                'TEASER_URL'          => $newsUrl,
                                'TEASER_URL_TARGET'   => empty($this->arrTeasers[$this->arrFrameTeaserIds[$id][$nr]]['redirect']) ? '_self' : '_blank',
                                
                            ));
                            if ($objTpl->blockExists('teaser_link')) {
                                $objTpl->parse('teaser_link');
                            }
                        } else {
                            if ($objTpl->blockExists('teaser_link')) { 
                                $objTpl->hideBlock('teaser_link');
                            }
                        }
                        
                        $objTpl->setVariable(array(
                            'TEASER_CATEGORY'    => contrexx_raw2xhtml($this->arrTeasers[$this->arrFrameTeaserIds[$id][$nr]]['category']),
                            'TEASER_LONG_DATE'   => date(ASCMS_DATE_FORMAT, $this->arrTeasers[$this->arrFrameTeaserIds[$id][$nr]]['date']),
                            'TEASER_DATE'        => date(ASCMS_DATE_FORMAT_DATE, $this->arrTeasers[$this->arrFrameTeaserIds[$id][$nr]]['date']),
                            'TEASER_TIME'        => date(ASCMS_DATE_FORMAT_TIME, $this->arrTeasers[$this->arrFrameTeaserIds[$id][$nr]]['date']),
                            'TEASER_TITLE'       => contrexx_raw2xhtml($title),
                            'TEASER_IMAGE_PATH'  => contrexx_raw2xhtml($imageSource),
                            'TEASER_TEXT'        => contrexx_raw2xhtml(nl2br($this->arrTeasers[$this->arrFrameTeaserIds[$id][$nr]]['teaser_text'])),
                            'TEASER_FULL_TEXT'   => contrexx_raw2xhtml($this->arrTeasers[$this->arrFrameTeaserIds[$id][$nr]]['teaser_full_text']),
                            'TEASER_AUTHOR'      => contrexx_raw2xhtml($this->arrTeasers[$this->arrFrameTeaserIds[$id][$nr]]['author']),
                            'TEASER_EXT_URL'     => contrexx_raw2xhtml($this->arrTeasers[$this->arrFrameTeaserIds[$id][$nr]]['ext_url']),
                            
                        ));
                    } elseif ($this->administrate) {
                        $objTpl->setVariable(array(
                            'TEASER_CATEGORY'     => 'TXT_CATEGORY',
                            'TEASER_DATE'         => 'TXT_DATE',
                            'TEASER_TIME'         => 'TXT_TIME',
                            'TEASER_LONG_DATE'    => 'TXT_LONG_DATE',
                            'TEASER_TITLE'        => 'TXT_TITLE',
                            'TEASER_URL'          => 'TXT_URL',
                            'TEASER_URL_TARGET'   => 'TXT_URL_TARGET',
                            'TEASER_IMAGE_PATH'   => 'TXT_IMAGE_PATH',
                            'TEASER_TEXT'         => 'TXT_TEXT',
                            'TEASER_FULL_TEXT'    => 'TXT_FULL_TEXT',
                            'TEASER_AUTHOR'       => 'TEASER_AUTHOR',
                            'TEASER_EXT_URL'      => 'TEASER_EXT_URL',
                            'TEASER_IMAGE'        => 'TEASER_IMAGE',
                            'TEASER_IMAGE_SRC'    => 'TEASER_IMAGE_SRC',
                            'TEASER_IMAGE_ALT'    => 'TEASER_IMAGE_ALT',
                            'TEASER_IMAGE_LINK'   => 'TEASER_IMAGE_LINK',
                            'NEWS_TEASER_IMAGE_THUMBNAIL'      => 'NEWS_TEASER_IMAGE_THUMBNAIL',
                            'NEWS_TEASER_IMAGE_THUMBNAIL_SRC'  => 'NEWS_TEASER_IMAGE_THUMBNAIL_SRC',
                            'NEWS_TEASER_IMAGE_THUMBNAIL_ALT'  => 'NEWS_TEASER_IMAGE_THUMBNAIL_ALT',
                            'NEWS_TEASER_IMAGE_THUMBNAIL_LINK' => 'NEWS_TEASER_IMAGE_THUMBNAIL_LINK',
                            'NEWS_TEASER_IMAGE_DETAIL'         => 'NEWS_TEASER_IMAGE_DETAIL',
                            'NEWS_TEASER_IMAGE_DETAIL_SRC'     => 'NEWS_TEASER_IMAGE_DETAIL_SRC',
                            'NEWS_TEASER_IMAGE_DETAIL_ALT'     => 'NEWS_TEASER_IMAGE_DETAIL_ALT',
                            'NEWS_TEASER_IMAGE_DETAIL_LINK'    => 'NEWS_TEASER_IMAGE_DETAIL_LINK',
                        ));
                    }

                    if (!$this->administrate) {
                        $teaserFrame = preg_replace('/<!-- BEGIN '.$teaserBlock.' -->[\S\s]*<!-- END '.$teaserBlock.' -->/', $objTpl->get(), $teaserFrame);                        
                    } else {
                        $teaserFrame = preg_replace('/(<!-- BEGIN '.$teaserBlock.' -->)[\S\s]*(<!-- END '.$teaserBlock.' -->)/', '<table cellspacing="0" cellpadding="0" style="border:1px dotted #aaaaaa;"><tr><td>'. $objTpl->get() .'</td></tr></table>', $teaserFrame);                        
                    }
                }
            }
        }
        return $teaserFrame;
    }


    function getFirstTeaserFrameTemplateId()
    {
        reset($this->arrTeaserFrameTemplates);
        $arrFrameTeamplte = current($this->arrTeaserFrameTemplates);
        return $arrFrameTeamplte['id'];
    }


    function getTeaserFrameTemplateMenu($selectedId)
    {
        $menu = "";
        foreach ($this->arrTeaserFrameTemplates as $teaserFrameTemplateId => $teaserFrameTemplate) {
            if ($selectedId == $teaserFrameTemplateId) {
                $selected = "selected=\"selected\"";
            } else {
                $selected = "";
            }
            $menu .= "<option value=\"".$teaserFrameTemplateId."\" ".$selected.">".$teaserFrameTemplate['description']."</option>\n";
        }
        return $menu;
    }


    function updateTeaserFrame($id, $templateId, $name)
    {
        global $objDatabase;

        if ($objDatabase->Execute("UPDATE ".DBPREFIX."module_news_teaser_frame SET frame_template_id=".$templateId.", name='".$name."' WHERE id=".$id) !== false) {
            return true;
        } else {
            return false;
        }
    }


    function addTeaserFrame($id, $templateId, $name)
    {
        global $objDatabase;

        if ($objDatabase->Execute("INSERT INTO ".DBPREFIX."module_news_teaser_frame (`frame_template_id`, `name`) VALUES (".$templateId.", '".$name."')") !== false) {
            return true;
        } else {
            return false;
        }
    }


    function updateTeaserFrameTemplate($id, $description, $html, $sourceCodeMode)
    {
        global $objDatabase;

        if ($objDatabase->Execute("UPDATE ".DBPREFIX."module_news_teaser_frame_templates SET description='".$description."', html='".$html."', source_code_mode='".$sourceCodeMode."' WHERE id=".$id) !== false) {
            return true;
        } else {
            return false;
        }
    }


    function addTeaserFrameTemplate($description, $html, $sourceCodeMode)
    {
        global $objDatabase;

        if ($objDatabase->Execute("INSERT INTO ".DBPREFIX."module_news_teaser_frame_templates (`description`, `html`, `source_code_mode`) VALUES ('".$description."', '".$html."', '".$sourceCodeMode."')") !== false) {
            return true;
        } else {
            return false;
        }
    }


    function deleteTeaserFrame($frameId)
    {
        global $objDatabase;

        if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_news_teaser_frame WHERE id=".$frameId) !== false) {
            return true;
        } else {
            return false;
        }
    }


    function deleteTeaserFrameTeamplte($templateId)
    {
        global $objDatabase, $_ARRAYLANG;

        foreach ($this->arrTeaserFrames as $arrTeaserFrame) {
            if ($arrTeaserFrame['frame_template_id'] == $templateId) {
                return $_ARRAYLANG['TXT_COULD_NOT_DELETE_TEMPLATE_TEXT'];
            }
        }

        if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_news_teaser_frame_templates WHERE id=".$templateId) !== false) {
            return true;
        } else {
            return false;
        }
    }


    function isUniqueFrameName($frameId, $frameName)
    {
        $arrFrameNames = array_flip($this->arrTeaserFrameNames);
        $arrEqualFrameNames = preg_grep('/^'.$frameName.'$/i', $arrFrameNames);

        if (count($arrEqualFrameNames) == 0 || array_key_exists($frameId, $arrEqualFrameNames)) {
            return true;
        } else {
            return false;
        }
    }

}

?>
