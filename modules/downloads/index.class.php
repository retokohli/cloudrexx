<?php
/**
 * Downloadmodul
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Damir Beciragic <damie.beciragic@comvation.com>
 * @version        $Id: index.inc.php,v 1.00 $
 * @package     contrexx
 * @subpackage  downloads
 */

/**
 * Includes
 */
require_once dirname(__FILE__).'/lib/downloadsLib.class.php';


/*

ARRAYLANG['TXT_DOWNLOADS_FILTERS']    = "Suche";

*/


//error_reporting(E_ALL);
//ini_set('display_errors', 1);

class downloads extends DownloadsLibrary
{
    private $htmlLinkTemplate = '<a href="%s" title="%s">%s</a>';
    private $htmlImgTemplate = '<img src="%s" alt="%s" />';

    private $moduleParamsHtml = '?section=downloads';
    private $moduleParamsJs = '?section=downloads';

    private $userId;

    /**
     * @var HTML_Template_Sigma
     */
    private $objTemplate;


    /**
     * Contains the info messages about done operations
     *
     * @var array
     * @access private
     */
    private $arrStatusMsg = array('ok' => array(), 'error' => array());



    /**
    * Constructor    -> Call parent-constructor, set language id and create local template-object
    * @global    integer        $_LANGID
    */
    function __construct($strPageContent)
    {
global $_ARRAYLANG;

$_ARRAYLANG['TXT_DOWNLOADS_START'] = 'Start';
$_ARRAYLANG['TXT_DOWNLOADS_MORE'] = 'mehr';

        global $_LANGID;

        parent::__construct();

        $this->_intLanguageId = intval($_LANGID);

        $objFWUser = FWUser::getFWUserObject();
        $this->userId = $objFWUser->objUser->login() ? $objFWUser->objUser->getId() : 0;

        $this->objTemplate = new HTML_Template_Sigma('.');
        $this->objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $this->objTemplate->setTemplate($strPageContent);
    }


    /**
    * Reads $_GET['cmd'] and selects (depending on the value) an action
    *
    */
    public function getPage()
    {
        if (!isset($_GET['cmd'])) {
            $_GET['cmd'] = '';
        }

        if (isset($_GET['download'])) {
            $_GET['cmd'] = 'download_file';
        }
        if (isset($_GET['delete_file'])) {
            $_GET['cmd'] = 'delete_file';
        }

        // check if the cmd is a number
        if (!empty($_REQUEST['cmd']) && intval($_REQUEST['cmd'])) {
            $this->moduleParamsHtml .= '&amp;cmd='.intval($_REQUEST['cmd']);
            $this->moduleParamsJs .= '&cmd='.intval($_REQUEST['cmd']);
        }

        switch ($_GET['cmd']) {
            case 'download_file':
                $this->download();
                exit;
                break;

            case 'delete_file':
                $this->deleteDownload();
                $this->overview();
                break;

            case "file":
                $this->GetFile();
                break;
            /*case 'user';
                $this->showUserInfo();
                break;
            case 'check';
                $this->checkUser();
                break;*/
            default:
                //$this->listDownloads();
                $this->overview();
                break;
        }

        $this->parseMessages();
error_reporting(0);ini_set('display_errors', 0);
        return $this->objTemplate->get();
    }

    private function parseMessages()
    {
        $this->objTemplate->setVariable(array(
            'DOWNLOADS_MSG_OK'      => count($this->arrStatusMsg['ok']) ? implode('<br />', $this->arrStatusMsg['ok']) : '',
            'DOWNLOADS_MSG_ERROR'   => count($this->arrStatusMsg['error']) ? implode('<br />', $this->arrStatusMsg['error']) : ''
        ));
    }

    private function deleteDownload()
    {
        global $_LANGID, $_ARRAYLANG;

        $objDownload = new Download();
        $objDownload->load(isset($_GET['delete_file']) ? $_GET['delete_file'] : 0);

        if (!$objDownload->EOF) {
            $name = '<strong>'.htmlentities($objDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET).'</strong>';
            if ($objDownload->delete()) {
                $this->arrStatusMsg['ok'][] = sprintf($_ARRAYLANG['TXT_DOWNLOADS_DOWNLOAD_DELETE_SUCCESS'], $name);
            } else {
                $this->arrStatusMsg['error'] = array_merge($this->arrStatusMsg['error'], $objDownload->getErrorMsg());
            }
        }
    }

    private function overview()
    {
        global $_LANGID;

        $objDownload = new Download();
        $objCategory = Category::getCategory(!empty($_REQUEST['category']) ? intval($_REQUEST['category']) : 0);
        if ($objCategory->getId()) {
            // check access permissions to selected category
            if (!Permission::checkAccess(142, 'static', true)
                && $objCategory->getReadAccessId()
                && !Permission::checkAccess($objCategory->getReadAccessId(), 'dynamic', true)
                && $objCategory->getOwnerId() != $this->userId
            ) {
                Permission::noAccess(base64_encode(CONTREXX_SCRIPT_PATH.$this->moduleParamsJs.'&category='.$objCategory->getId()));
            }


            // parse crumbtrail
            $this->parseCrumbtrail($objCategory);

            if ($objDownload->load(!empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0)) {

                $this->parseRelatedDownloads($objDownload, $objCategory->getId());

                $this->parseDownload($objDownload);



                // hide unwanted blocks on the detail page
                if ($this->objTemplate->blockExists('downloads_category')) {
                    $this->objTemplate->hideBlock('downloads_category');
                }
                if ($this->objTemplate->blockExists('downloads_subcategory_list')) {
                    $this->objTemplate->hideBlock('downloads_subcategory_list');
                }
                if ($this->objTemplate->blockExists('downloads_file_list')) {
                    $this->objTemplate->hideBlock('downloads_file_list');
                }
            } else {
                // parse selected category
                $this->parseCategory($objCategory);

                // parse subcategories
                $this->parseCategories($objCategory, array('downloads_subcategory_list', 'downloads_subcategory'), null, 'SUB');

                // parse downloads of selected category
                $this->parseDownloads($objCategory);

                // hide unwanted blocks on the category page
                if ($this->objTemplate->blockExists('downloads_download')) {
                    $this->objTemplate->hideBlock('downloads_download');
                }
            }

            // hide unwanted blocks on the category/detail page
            if ($this->objTemplate->blockExists('downloads_overview')) {
                $this->objTemplate->hideBlock('downloads_overview');
            }
        } else {
            // parse category overview
            $this->parseCategories($objCategory, array('downloads_overview', 'downloads_overview_category'), null, null, 'downloads_overview_row', array('downloads_overview_subcategory_list', 'downloads_overview_subcategory'), $this->arrConfig['overview_max_subcats']);

            // hide unwanted blocks on the overview page
            if ($this->objTemplate->blockExists('downloads_category')) {
                $this->objTemplate->hideBlock('downloads_category');
            }
            if ($this->objTemplate->blockExists('downloads_crumbtrail')) {
                $this->objTemplate->hideBlock('downloads_crumbtrail');
            }
            if ($this->objTemplate->blockExists('downloads_subcategory_list')) {
                $this->objTemplate->hideBlock('downloads_subcategory_list');
            }

            if (!empty($this->searchKeyword)) {
                $this->parseDownloads($objCategory);
            } else {
                if ($this->objTemplate->blockExists('downloads_file_list')) {
                    $this->objTemplate->hideBlock('downloads_file_list');
                }
            }

        }


        $this->parseGlobalStuff($objCategory);
    }

    private function parseCategory($objCategory)
    {
        global $_LANGID;

        if (!$this->objTemplate->blockExists('downloads_category')) {
            return;
        }

        $description = $objCategory->getDescription($_LANGID);
        if (strlen($description) > 100) {
            $shortDescription = substr($description, 0, 97).'...';
        } else {
            $shortDescription = $description;
        }

        $imageSrc = $objCategory->getImage();
        if (!empty($imageSrc) && file_exists(ASCMS_PATH.$imageSrc)) {
            if (file_exists(ASCMS_PATH.$imageSrc.'.thumb')) {
                $thumbnailSrc = $imageSrc.'.thumb';
            } else {
                $thumbnailSrc = $this->defaultCategoryImage['src'].'.thumb';
            }
        } else {
            $imageSrc = $this->defaultCategoryImage['src'];
            $thumbnailSrc = $this->defaultCategoryImage['src'].'.thumb';
        }

        $this->objTemplate->setVariable(array(
            'DOWNLOADS_CATEGORY_ID'                 =>  $objCategory->getId(),
            'DOWNLOADS_CATEGORY_NAME'               => htmlentities($objCategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET),
            'DOWNLOADS_CATEGORY_DESCRIPTION'        => htmlentities($description, ENT_QUOTES, CONTREXX_CHARSET),
            'DOWNLOADS_CATEGORY_SHORT_DESCRIPTION'  => htmlentities($shortDescription, ENT_QUOTES, CONTREXX_CHARSET),
            //'DOWNLOADS_CATEGORY_BREADCRUMB'         => $this->getCategoryBreadcrumb($objCategory),
            'DOWNLOADS_CATEGORY_IMAGE'              => $this->getHtmlImageTag($imageSrc, htmlentities($objCategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)),
            'DOWNLOADS_CATEGORY_IMAGE_SRC'          => $imageSrc,
            'DOWNLOADS_CATEGORY_THUMBNAIL'          => $this->getHtmlImageTag($thumbnailSrc, htmlentities($objCategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)),
            'DOWNLOADS_CATEGORY_THUMBNAIL_SRC'      => $thumbnailSrc
        ));
        $this->objTemplate->parse('downloads_category');
    }



    private function parseCrumbtrail($objParentCategory)
    {
        global $_ARRAYLANG, $_LANGID;

        if (!$this->objTemplate->blockExists('downloads_crumbtrail')) {
            return;
        }

        $arrCategories = array();

        do {
            $arrCategories[] = array(
                'id'    => $objParentCategory->getId(),
                'name'  => htmlentities($objParentCategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)
            );
            $objParentCategory = Category::getCategory($objParentCategory->getParentId());
        } while ($objParentCategory->getId());

        krsort($arrCategories);

        foreach ($arrCategories as $arrCategory) {
            $this->objTemplate->setVariable(array(
                'DOWNLOADS_CRUMB_ID'    => $arrCategory['id'],
                'DOWNLOADS_CRUMB_NAME'  => $arrCategory['name']
            ));
            $this->objTemplate->parse('downloads_crumb');
        }

        $this->objTemplate->setVariable('TXT_DOWNLOADS_START', $_ARRAYLANG['TXT_DOWNLOADS_START']);

        $this->objTemplate->parse('downloads_crumbtrail');
    }

    private function parseGlobalStuff($objCategory)
    {
        $this->objTemplate->setVariable(array(
            'DOWNLOADS_JS'  => $this->getJavaScriptCode($objCategory)
        ));

        $this->parseSearchForm($objCategory);
    }

    private function getJavaScriptCode($objCategory)
    {
        global $_ARRAYLANG;

        $fileDeleteTxt = preg_replace('#\n#', '\\n', addslashes($_ARRAYLANG['TXT_DOWNLOADS_CONFIRM_DELETE_DOWNLOAD']));
        $fileDeleteLink = CONTREXX_SCRIPT_PATH.$this->moduleParamsJs.'&category='.$objCategory->getId().'&delete_file=';

        $javascript = <<<JS_CODE
<script type="text/javascript">
// <![CDATA[
function downloadsDeleteFile(id,name)
{
    msg = '$fileDeleteTxt'
    if (confirm(msg.replace('%s',name))) {
        window.location.href='$fileDeleteLink'+id;
    }
}

function downloadsDeleteCategory(id,name)
{

}

// ]]>
</script>
JS_CODE;

        return $javascript;
    }

//    private function getCategoryBreadcrumb($objParentCategory)
//    {
//        global $_ARRAYLANG, $_LANGID;
//
//        $arrCategories = array();
//
//        do {
//            $objParentCategory = Category::getCategory($objParentCategory->getParentId());
//            $arrCategories[] = $this->getHtmlFolderLinkTag(
//                CONTREXX_SCRIPT_PATH.$this->moduleParamsHtml.'&amp;category='.$objParentCategory->getId(),
//                sprintf($_ARRAYLANG['TXT_DOWNLOADS_SHOW_CATEGORY_CONTENT'], htmlentities($objParentCategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)),
//                htmlentities($objParentCategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)
//            );
//        } while ($objParentCategory->getId());
//
//        krsort($arrCategories);
//        return implode(' / ', $arrCategories);
//    }

    private function parseCategories($objCategory, $arrCategoryBlocks, $categoryLimit = null, $variablePrefix = '', $rowBlock = null, $arrSubCategoryBlocks = null, $subCategoryLimit = null)
    {
        global $_ARRAYLANG, $_LANGID;

        if (!$this->objTemplate->blockExists($arrCategoryBlocks[0])) {
            return;
        }

        $objSubcategory = Category::getCategories(array('parent_id' => $objCategory->getId(), 'is_active' => true), null, null, null, $categoryLimit);

        if ($objSubcategory->EOF) {
            $this->objTemplate->hideBlock($arrCategoryBlocks[0]);
        } else {
            $row = 1;
            while (!$objSubcategory->EOF) {
                if (// subcategory is hidden -> check if the user is allowed to see it listed anyways
                    !$objSubcategory->getVisibility()
                    // non managers are not allowed to see hidden subcategories
                    && !Permission::checkAccess(142, 'static', true)
                    // those who have read access permission to the subcategory are allowed to see it listed
                    && !Permission::checkAccess($objSubcategory->getReadAccessId(), 'dynamic', true)
                    // the owner is allowed to see its own categories
                    && (!$objSubcategory->getOwnerId() || $objSubcategory->getOwnerId() != $this->userId)
                ) {
                    $objSubcategory->next();
                    continue;
                }

                $description = $objSubcategory->getDescription($_LANGID);
                if (strlen($description) > 100) {
                    $shortDescription = substr($description, 0, 97).'...';
                } else {
                    $shortDescription = $description;
                }

                $imageSrc = $objSubcategory->getImage();
                if (!empty($imageSrc) && file_exists(ASCMS_PATH.$imageSrc)) {
                    if (file_exists(ASCMS_PATH.$imageSrc.'.thumb')) {
                        $thumbnailSrc = $imageSrc.'.thumb';
                    } else {
                        $thumbnailSrc = $this->defaultCategoryImage['src'].'.thumb';
                    }
                } else {
                    $imageSrc = $this->defaultCategoryImage['src'];
                    $thumbnailSrc = $this->defaultCategoryImage['src'].'.thumb';
                }

                $this->objTemplate->setVariable(array(
                    'DOWNLOADS_'.$variablePrefix.'CATEGORY_ID'                 => $objSubcategory->getId(),
                    'DOWNLOADS_'.$variablePrefix.'CATEGORY_NAME'               => htmlentities($objSubcategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET),
                    'DOWNLOADS_'.$variablePrefix.'CATEGORY_NAME_LINK'          => $this->getHtmlLinkTag(CONTREXX_SCRIPT_PATH.$this->moduleParamsHtml.'&amp;category='.$objSubcategory->getId(), sprintf($_ARRAYLANG['TXT_DOWNLOADS_SHOW_CATEGORY_CONTENT'], htmlentities($objSubcategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)), htmlentities($objSubcategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)),
                    'DOWNLOADS_'.$variablePrefix.'CATEGORY_FOLDER_LINK'        => $this->getHtmlFolderLinkTag(CONTREXX_SCRIPT_PATH.$this->moduleParamsHtml.'&amp;category='.$objSubcategory->getId(), sprintf($_ARRAYLANG['TXT_DOWNLOADS_SHOW_CATEGORY_CONTENT'], htmlentities($objSubcategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)), htmlentities($objSubcategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)),
                    'DOWNLOADS_'.$variablePrefix.'CATEGORY_DESCRIPTION'        => htmlentities($description, ENT_QUOTES, CONTREXX_CHARSET),
                    'DOWNLOADS_'.$variablePrefix.'CATEGORY_SHORT_DESCRIPTION'  => htmlentities($shortDescription, ENT_QUOTES, CONTREXX_CHARSET),
                    'DOWNLOADS_'.$variablePrefix.'CATEGORY_IMAGE'              => $this->getHtmlImageTag($imageSrc, htmlentities($objSubcategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)),
                    'DOWNLOADS_'.$variablePrefix.'CATEGORY_IMAGE_SRC'          => $imageSrc,
                    'DOWNLOADS_'.$variablePrefix.'CATEGORY_THUMBNAIL'          => $this->getHtmlImageTag($thumbnailSrc, htmlentities($objSubcategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)),
                    'DOWNLOADS_'.$variablePrefix.'CATEGORY_THUMBNAIL_SRC'      => $thumbnailSrc,
                    'DOWNLOADS_'.$variablePrefix.'CATEGORY_DELETE_ICON'        => '',
                    'DOWNLOADS_'.$variablePrefix.'CATEGORY_ROW_CLASS'          => 'row'.($row++ % 2 + 1),
                    'TXT_DOWNLOADS_MORE'                       => $_ARRAYLANG['TXT_DOWNLOADS_MORE']
                ));

                // parse subcategories
                if (isset($arrSubCategoryBlocks)) {
                    $this->parseCategories($objSubcategory, array('downloads_overview_subcategory_list', 'downloads_overview_subcategory'), $subCategoryLimit, 'SUB');
                }

                $this->objTemplate->parse($arrCategoryBlocks[1]);

                // parse row
                if (isset($rowBlock) && $this->objTemplate->blockExists($rowBlock) && $row % $this->arrConfig['overview_cols_count'] == 0) {
                    $this->objTemplate->parse($rowBlock);
                }

                $objSubcategory->next();
            }

            $this->objTemplate->touchBlock($arrCategoryBlocks[0]);
        }
    }

    private function getHtmlDeleteLinkIcon($id, $name)
    {
        global $_ARRAYLANG;

        return sprintf($this->htmlLinkTemplate, "javascript:void(0)\" onclick=\"downloadsDeleteFile($id,'$name')", $_ARRAYLANG['TXT_DOWNLOADS_DELETE'], sprintf($this->htmlImgTemplate, 'cadmin/images/icons/delete.gif', $_ARRAYLANG['TXT_DOWNLOADS_DELETE']));
    }
    private function getHtmlLinkTag($href, $title, $value)
    {
        return sprintf($this->htmlLinkTemplate, $href, $title, $value);
    }

    private function getHtmlImageTag($src, $alt)
    {
        return sprintf($this->htmlImgTemplate, $src, $alt);
    }

    private function getHtmlFolderLinkTag($href, $title, $value)
    {
        return sprintf($this->htmlLinkTemplate, $href, $title, sprintf($this->htmlImgTemplate, 'images/modules/downloads/folder_front.gif', $title).' '.$value);
    }

    private function parseDownloads($objCategory)
    {
        global $_LANGID, $_CONFIG, $_ARRAYLANG;

        if (!$this->objTemplate->blockExists('downloads_file_list')) {
            return;
        }

        $limitOffset = isset($_GET['pos']) ? intval($_GET['pos']) : 0;
        $objDownload = new Download();
        $objDownload->loadDownloads(array('category_id' => $objCategory->getId(), 'is_active' => true), $this->searchKeyword, null, null, $_CONFIG['corePagingLimit'], $limitOffset);
        $categoryId = $objCategory->getId();

        if ($objDownload->EOF) {
            $this->objTemplate->hideBlock('downloads_file_list');
        } else {
            $row = 1;
            while (!$objDownload->EOF) {
//                if (// download is protected
//                    $objDownload->getAccessId()
//                    // download is not visible for unauthorized users
//                    && !$objDownload->getVisibility()
//                    // the user isn't a admin
//                    && !Permission::checkAccess(142, 'static', true)
//                    // the user doesn't has access to this download
//                    && !Permission::checkAccess($objDownload->getAccessId(), 'dynamic', true)
//                    // the user isn't the owner of the download
//                    && $objDownload->getOwnerId() != $this->userId
//                ) {
//                    $objDownload->next();
//                    continue;
//                }

                if ($objCategory->EOF) {
                    $arrAssociatedCategories = $objDownload->getAssociatedCategoryIds();
                    $categoryId = $arrAssociatedCategories[0];
                }

                $description = $objDownload->getDescription($_LANGID);
                if (strlen($description) > 100) {
                    $shortDescription = substr($description, 0, 97).'...';
                } else {
                    $shortDescription = $description;
                }

                $imageSrc = $objDownload->getImage();
                if (!empty($imageSrc) && file_exists(ASCMS_PATH.$imageSrc)) {
                    if (file_exists(ASCMS_PATH.$imageSrc.'.thumb')) {
                        $thumbnailSrc = $imageSrc.'.thumb';
                    } else {
                        $thumbnailSrc = $this->defaultCategoryImage['src'].'.thumb';
                    }

                    $image = $this->getHtmlImageTag($imageSrc, htmlentities($objDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET));
                    $thumbnail = $this->getHtmlImageTag($thumbnailSrc, htmlentities($objDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET));
                } else {
                    $imageSrc = $this->defaultCategoryImage['src'];
                    $thumbnailSrc = $this->defaultCategoryImage['src'].'.thumb';
                    $image = '';
                    $thumbnail = '';
                }

                $this->objTemplate->setVariable(array(
                    'DOWNLOADS_FILE_ID'                 => $objDownload->getId(),
                    'DOWNLOADS_FILE_DETAIL_SRC'         => CONTREXX_SCRIPT_PATH.$this->moduleParamsHtml.'&amp;category='.$categoryId.'&amp;id='.$objDownload->getId(),
                    'DOWNLOADS_FILE_NAME'               => htmlentities($objDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET),
                    'DOWNLOADS_FILE_DESCRIPTION'        => htmlentities($description, ENT_QUOTES, CONTREXX_CHARSET),
                    'DOWNLOADS_FILE_SHORT_DESCRIPTION'  => htmlentities($shortDescription, ENT_QUOTES, CONTREXX_CHARSET),
                    'DOWNLOADS_FILE_IMAGE'              => $image,
                    'DOWNLOADS_FILE_IMAGE_SRC'          => $imageSrc,
                    'DOWNLOADS_FILE_THUMBNAIL'          => $thumbnail,
                    'DOWNLOADS_FILE_THUMBNAIL_SRC'      => $thumbnailSrc,
                    'DOWNLOADS_FILE_ICON'               => $this->getHtmlImageTag($objDownload->getIcon(), htmlentities($objDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)),
                    'DOWNLOADS_FILE_DELETE_ICON'        => $this->getHtmlDeleteLinkIcon($objDownload->getId(), htmlspecialchars($objDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)),
                    'DOWNLOADS_FILE_ROW_CLASS'          => 'row'.($row++ % 2 + 1)
                ));
                $this->objTemplate->parse('downloads_file');


                $objDownload->next();
            }

            $downloadCount = $objDownload->getFilteredSearchDownloadCount();
            if ($downloadCount > $_CONFIG['corePagingLimit']) {
                $this->objTemplate->setVariable('DOWNLOADS_FILE_PAGING', getPaging($downloadCount, $limitOffset, '&amp;'.substr($this->moduleParamsHtml, 1).'&amp;category='.$objCategory->getId().'&amp;downloads_search_keyword='.htmlspecialchars($this->searchKeyword), "<b>".$_ARRAYLANG['TXT_DOWNLOADS_DOWNLOADS']."</b>"));
            }

            $this->objTemplate->touchBlock('downloads_file_list');
        }
    }

    private function parseRelatedDownloads($objDownload, $currentCategoryId)
    {
        global $_LANGID;

        if (!$this->objTemplate->blockExists('downloads_related_file_list')) {
            return;
        }

        $objRelatedDownload = $objDownload->getDownloads(array('download_id' => $objDownload->getId()), null, array('order' => 'ASC', 'name' => 'ASC', 'id' => 'ASC'));

        if ($objRelatedDownload->EOF) {
            $this->objTemplate->hideBlock('downloads_related_file');
        } else {
            $row = 1;
            while (!$objRelatedDownload->EOF) {
                if (// download is protected
                    $objRelatedDownload->getAccessId()
                    // download is not visible for unauthorized users
                    && !$objRelatedDownload->getVisibility()
                    // the user isn't a admin
                    && !Permission::checkAccess(142, 'static', true)
                    // the user doesn't has access to this download
                    && !Permission::checkAccess($objRelatedDownload->getAccessId(), 'dynamic', true)
                    // the user isn't the owner of the download
                    && $objRelatedDownload->getOwnerId() != $this->userId
                ) {
                    $objRelatedDownload->next();
                    continue;
                }


                $description = $objRelatedDownload->getDescription($_LANGID);
                if (strlen($description) > 100) {
                    $shortDescription = substr($description, 0, 97).'...';
                } else {
                    $shortDescription = $description;
                }

                $imageSrc = $objRelatedDownload->getImage();
                if (!empty($imageSrc) && file_exists(ASCMS_PATH.$imageSrc)) {
                    if (file_exists(ASCMS_PATH.$imageSrc.'.thumb')) {
                        $thumbnailSrc = $imageSrc.'.thumb';
                    } else {
                        $thumbnailSrc = $this->defaultCategoryImage['src'].'.thumb';
                    }

                    $image = $this->getHtmlImageTag($imageSrc, htmlentities($objRelatedDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET));
                    $thumbnail = $this->getHtmlImageTag($thumbnailSrc, htmlentities($objRelatedDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET));
                } else {
                    $imageSrc = $this->defaultCategoryImage['src'];
                    $thumbnailSrc = $this->defaultCategoryImage['src'].'.thumb';
                    $image = '';
                    $thumbnail = '';
                }

                $arrAssociatedCategories = $objRelatedDownload->getAssociatedCategoryIds();
                if (in_array($currentCategoryId, $arrAssociatedCategories)) {
                    $categoryId = $currentCategoryId;
                } else {
                    $arrPublicCategories = array();
                    $arrProtectedCategories = array();

                    foreach ($arrAssociatedCategories as $categoryId) {
                        $objCategory = Category::getCategory($categoryId);
                        if (!$objCategory->EOF && $objCategory->getActiveStatus()) {
                            if ($objCategory->getVisibility()
                                || Permission::checkAccess($objCategory->getReadAccessId(), 'dynamic', true)
                                || $objCategory->getOwnerId() == $this->userId
                               ) {
                                $arrPublicCategories[] = $categoryId;
                                break;
                            } else {
                                $arrProtectedCategories[] = $categoryId;
                            }
                        }
                    }

                    if (count($arrPublicCategories)) {
                        $categoryId = $arrPublicCategories[0];
                    } elseif (count($arrProtectedCategories)) {
                        $categoryId = $arrProtectedCategories[0];
                    } else {
                        $objRelatedDownload->next();
                        continue;
                    }
                }

                $this->objTemplate->setVariable(array(
                    'DOWNLOADS_RELATED_FILE_ID'                 => $objRelatedDownload->getId(),
                    'DOWNLOADS_RELATED_FILE_DETAIL_SRC'         => CONTREXX_SCRIPT_PATH.$this->moduleParamsHtml.'&amp;category='.$categoryId.'&amp;id='.$objRelatedDownload->getId(),
                    'DOWNLOADS_RELATED_FILE_NAME'               => htmlentities($objRelatedDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET),
                    'DOWNLOADS_RELATED_FILE_DESCRIPTION'        => htmlentities($description, ENT_QUOTES, CONTREXX_CHARSET),
                    'DOWNLOADS_RELATED_FILE_SHORT_DESCRIPTION'  => htmlentities($shortDescription, ENT_QUOTES, CONTREXX_CHARSET),
                    'DOWNLOADS_RELATED_FILE_IMAGE'              => $image,
                    'DOWNLOADS_RELATED_FILE_IMAGE_SRC'          => $imageSrc,
                    'DOWNLOADS_RELATED_FILE_THUMBNAIL'          => $thumbnail,
                    'DOWNLOADS_RELATED_FILE_THUMBNAIL_SRC'      => $thumbnailSrc,
                    'DOWNLOADS_RELATED_FILE_ICON'               => $this->getHtmlImageTag($objRelatedDownload->getIcon(), htmlentities($objRelatedDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)),
                    'DOWNLOADS_RELATED_FILE_DELETE_ICON'        => $this->getHtmlDeleteLinkIcon($objRelatedDownload->getId(), htmlspecialchars($objRelatedDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)),
                    'DOWNLOADS_RELATED_FILE_ROW_CLASS'          => 'row'.($row++ % 2 + 1)
                ));
                $this->objTemplate->parse('downloads_related_file');


                $objRelatedDownload->next();
            }

            $this->objTemplate->touchBlock('downloads_related_file_list');
        }
    }

    private function parseDownload($objDownload)
    {
        global $_LANGID;

        if (!$this->objTemplate->blockExists('downloads_file_detail')) {
            return;
        }


//        if (// download is protected
//            $objDownload->getAccessId()
//            // download is not visible for unauthorized users
//            && !$objDownload->getVisibility()
//            // the user isn't a admin
//            && !Permission::checkAccess(142, 'static', true)
//            // the user doesn't has access to this download
//            && !Permission::checkAccess($objDownload->getAccessId(), 'dynamic', true)
//            // the user isn't the owner of the download
//            && $objDownload->getOwnerId() != $this->userId
//        ) {
//            $objDownload->next();
//            return;
//        }


        $description = $objDownload->getDescription($_LANGID);
        if (strlen($description) > 100) {
            $shortDescription = substr($description, 0, 97).'...';
        } else {
            $shortDescription = $description;
        }

        $imageSrc = $objDownload->getImage();
        if (!empty($imageSrc) && file_exists(ASCMS_PATH.$imageSrc)) {
            if (file_exists(ASCMS_PATH.$imageSrc.'.thumb')) {
                $thumbnailSrc = $imageSrc.'.thumb';
            } else {
                $thumbnailSrc = $this->defaultCategoryImage['src'].'.thumb';
            }

            $image = $this->getHtmlImageTag($imageSrc, htmlentities($objDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET));
            $thumbnail = $this->getHtmlImageTag($thumbnailSrc, htmlentities($objDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET));
        } else {
            $imageSrc = $this->defaultCategoryImage['src'];
            $thumbnailSrc = $this->defaultCategoryImage['src'].'.thumb';
            $image = '';
            $thumbnail = '';
        }

        $this->objTemplate->setVariable(array(
            'DOWNLOADS_FILE_ID'                 => $objDownload->getId(),
            'DOWNLOADS_FILE_DETAIL_SRC'         => CONTREXX_SCRIPT_PATH.$this->moduleParamsHtml.'&amp;id='.$objDownload->getId(),
            'DOWNLOADS_FILE_NAME'               => htmlentities($objDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET),
            'DOWNLOADS_FILE_DESCRIPTION'        => htmlentities($description, ENT_QUOTES, CONTREXX_CHARSET),
            'DOWNLOADS_FILE_SHORT_DESCRIPTION'  => htmlentities($shortDescription, ENT_QUOTES, CONTREXX_CHARSET),
            'DOWNLOADS_FILE_IMAGE'              => $image,
            'DOWNLOADS_FILE_IMAGE_SRC'          => $imageSrc,
            'DOWNLOADS_FILE_THUMBNAIL'          => $thumbnail,
            'DOWNLOADS_FILE_THUMBNAIL_SRC'      => $thumbnailSrc,
            'DOWNLOADS_FILE_ICON'               => $this->getHtmlImageTag('images/modules/downloads/'.Download::$arrMimeTypes[$objDownload->getMimeType()]['icon']/*$objDownload->getIcon()*/, htmlentities($objDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)),
            'DOWNLOADS_FILE_DELETE_ICON'        => $this->getHtmlDeleteLinkIcon($objDownload->getId(), htmlspecialchars($objDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)),
            'DOWNLOADS_FILE_DOWNLOAD_LINK_SRC'  => CONTREXX_SCRIPT_PATH.$this->moduleParamsHtml.'&amp;download='.$objDownload->getId()
        ));
        $this->objTemplate->parse('downloads_file_detail');
    }

    private function parseSearchForm($objCategory)
    {
        global $_ARRAYLANG;

        $this->objTemplate->setVariable(array(
            'DOWNLOADS_SEARCH_KEYWORD'  => htmlentities($this->searchKeyword, ENT_QUOTES, CONTREXX_CHARSET),
            'DOWNLOADS_SEARCH_URL'      => CONTREXX_SCRIPT_PATH.$this->moduleParamsHtml.'&amp;category='.$objCategory->getId(),
            'TXT_DOWNLOADS_SEARCH'  => $_ARRAYLANG['TXT_DOWNLOADS_SEARCH']
        ));
    }

    private function download()
    {
        $objDownload = new Download();
        $objDownload->load(!empty($_GET['download']) ? intval($_GET['download']) : 0);
        if (!$objDownload->EOF) {
            if (// download is protected
                $objDownload->getAccessId()
                // the user isn't a admin
                && !Permission::checkAccess(142, 'static', true)
                // the user doesn't has access to this download
                && !Permission::checkAccess($objDownload->getAccessId(), 'dynamic', true)
                // the user isn't the owner of the download
                && $objDownload->getOwnerId() != $this->userId
            ) {
                Permission::noAccess();
            }

            if ($objDownload->getType() == 'file') {
                header("Content-Type: application/force-download");
                header("Content-Disposition: attachment; filename=". htmlspecialchars(basename($objDownload->getSource())));
                header("Content-Length: ".filesize(ASCMS_PATH.$objDownload->getSource()));
                readfile(ASCMS_PATH.$objDownload->getSource());
            } else {
                // add socket -> prevent to hide the source from the customer
                header('Location: '.$objDownload->getSource());
            }
        }
    }

    /**
     * Shows all existing entries of the blog in descending order.
     *
     * @global     array        $_ARRAYLANG
     * @global    object        $objDatabase
     * @global     array        $_CONFIG
     */
//    function listDownloads()
//    {
//        global $_ARRAYLANG, $objDatabase, $_LANGID;
//
//        // Request
//        // ---------------------------------------------------------------------
//        $category = (isset($_REQUEST['category']) ? $_REQUEST['category'] : '');
//        $keyword = (isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : '');
//
//        // Filter-Display
//        // ---------------------------------------------------------------------
//        if ($this->_arrConfig['filter']==1) {
//            $filter_display = 'block';
//        } else {
//            $filter_display = 'none';
//        }
//        $this->objTemplate->setVariable(array('FILTER_DISPLAY' => $filter_display));
//
//        // Icons
//        // ---------------------------------------------------------------------
//        if ($this->_arrConfig['design']>0) {
//            $this->objTemplate->setVariable(array(
//                'ICON_DISPLAY' => 'block',
//                'ICON_FILTERS' => $this->_GetIconImage('filter.gif'),
//                'ICON_INFO'    => $this->_GetIconImage('info.gif'),
//            ));
//        } else {
//            $this->objTemplate->setVariable(array(
//                'ICON_DISPLAY' => 'none',
//                'ICON_FILTERS' => '',
//                'ICON_INFO'    => '',
//            ));
//        }
//
//        $FILTER_CATEGORIES_VALUE = $this->_GetCategoriesOption($category);
//
//        // Categories
//        // ---------------------------------------------------------------------
//        if (intval($category<1)&&$keyword=='') {
//            $this->objTemplate->setCurrentBlock('Categories_Row');
//            $Categories = $this->_GetCategories();
//            for($x=0;$x<count($Categories); $x++) {
//                $CategoryInfo = $this->_CategoryInfo($Categories[$x]);
//                if ($this->_arrConfig['design']>0) {
//                    if ($CategoryInfo['category_img'] != '') {
//                        $CategoryIcon = $this->_GetIconImage($CategoryInfo['category_img'], 1);
//                    } else {
//                        $CategoryIcon = $this->_GetIconImage('category.gif');
//                    }
//                } else {
//                    $CategoryIcon = '';
//                }
//
//                $Categoryname = $CategoryInfo['category_loc']['lang'][$_LANGID]['name'];
//                if ($Categoryname == '') {
//                    $Categoryname = $CategoryInfo['category_loc'][0]['name'];
//                }
//
//                $this->objTemplate->setVariable(array(
//                    'CATEGORY_ID'   => $CategoryInfo['category_id'],
//                    'CATEGORY_NAME' => $Categoryname,
//                    'CATEGORY_DESC' => $CategoryInfo['category_loc']['lang'][$_LANGID]['desc'],
//                    'ICON_CATEGORY' => $CategoryIcon,
//                ));
//
//                $this->objTemplate->parse('Categories_Row');
//            }
//
//            $this->objTemplate->setVariable(array(
//                'CATEGORIES_DISPLAY'             => 'block',
//            ));
//        } else {
//            $this->objTemplate->setVariable(array(
//                'CATEGORIES_DISPLAY'             => 'none',
//            ));
//        }
//
//
//        // Files
//        // ---------------------------------------------------------------------
//        if (intval($category)>0) {
//            $query = "
//                SELECT rel_file, rel_category, file_id, file_name
//                FROM ".DBPREFIX."module_downloads_rel_files_cat
//                JOIN ".DBPREFIX."module_downloads_files ON ".DBPREFIX."module_downloads_rel_files_cat.rel_file=".DBPREFIX."module_downloads_files.file_id
//                WHERE rel_category=".$category." AND file_state=1
//                ORDER BY file_name";
//        } else {
//            $query = "
//                SELECT file_id, file_name, rel_file
//                FROM ".DBPREFIX."module_downloads_files
//                LEFT JOIN ".DBPREFIX."module_downloads_rel_files_cat ON ".DBPREFIX."module_downloads_files.file_id=".DBPREFIX."module_downloads_rel_files_cat.rel_file
//                WHERE rel_file is NULL AND file_state=1
//                ORDER BY file_name";
//        }
//
//        // QUERY FOR SEARCH
//        // ----------------------------------------------------------------------
//        if ($keyword!='') {
//            $query = "
//                SELECT file_id, file_name, rel_file
//                FROM ".DBPREFIX."module_downloads_files
//                LEFT JOIN ".DBPREFIX."module_downloads_rel_files_cat ON ".DBPREFIX."module_downloads_files.file_id=".DBPREFIX."module_downloads_rel_files_cat.rel_file
//                LEFT JOIN ".DBPREFIX."module_downloads_files_locales ON ".DBPREFIX."module_downloads_files.file_id=".DBPREFIX."module_downloads_files_locales.loc_file
//                WHERE (loc_name LIKE '%".$keyword."%' OR loc_desc LIKE '%".$keyword."%') AND file_state=1
//                GROUP BY file_id ORDER BY file_name";
//        }
//
//        $objResult = $objDatabase->Execute($query);
//        if ($objResult && $objResult->RecordCount()) {
//            $objFWUser = FWUser::getFWUserObject();
//            $FilesJS = '';
//            $openendJS = '';
//            while (!$objResult->EOF) {
//
//                $fileInfo = $this->_FileInfo($objResult->fields["file_id"]);
//
//                if ($this->_arrConfig["design"]>0) {
//                    if ($fileInfo['file_type']!='') {
//                        $ImgName = $fileInfo['file_type'].'.gif';
//                    } else {
//                        $ImgName = 'file.gif';
//                    }
//                    $FileIcon = $this->_GetIconImage($ImgName);
//                } else {
//                    $FileIcon = '';
//                }
//
//                $FILE_SCREEN = '';
//                if ($fileInfo['file_img']!='') {
//                    $FILE_SCREEN = '<a href="'.$fileInfo['file_img'].'" target="_blank">'.$_ARRAYLANG['TXT_DOWNLOADS_SCREENSHOT'].'</a>';
//                }
//
//                // Downlaod-Link
//                // --------------------------------------
//                if ($fileInfo["file_protected"]==0) {
//                    $DonwlodLink = '<a href="index.php?section=downloads&cmd=file&id='.$fileInfo['file_source'].'" target="_blank">'.$this->_GetIconImage('download.gif').'</a>';
//                } else {
//                    if ($objFWUser->objUser->login()) {
//                        if (Permission::checkAccess($fileInfo['file_access_id'], 'dynamic', true)) {
//                            $DonwlodLink = '<a href="index.php?section=downloads&cmd=file&id='.$fileInfo['file_source'].'" target="_blank">'.$this->_GetIconImage('download.gif').'</a>';
//                        } else {
//                            $DonwlodLink = '<a href="index.php?section=downloads&cmd=user">'.$this->_GetIconImage('lock.gif').'</a>';
//                        }
//                    } else {
//                        $DonwlodLink = '<a href="index.php?section=login">'.$this->_GetIconImage('lock.gif').'</a>';
//                    }
//                }
//
//                // TXT_DOWNLOADS_DOWNLOAD
//                if ($this->_arrConfig["design"]==0) {
//                if ($objFWUser->objUser->login()) {
//                    if (!Permission::checkAccess($fileInfo['file_access_id'], 'dynamic', true)) {
//                        $DonwlodLink = '<a href="index.php?section=downloads&cmd=file&id='.$fileInfo['file_source'].'" target="_blank">'.$_ARRAYLANG["TXT_DOWNLOADS_DOWNLOAD"].'</a>';
//                    } else {
//                        $DonwlodLink = '<a href="index.php?section=login">'.$_ARRAYLANG["TXT_DOWNLOADS_LOGIN"].'</a>';
//                    }
//                } else {
//                    $DonwlodLink = '<a href="index.php?section=login">'.$_ARRAYLANG["TXT_DOWNLOADS_LOGIN"].'</a>';
//                }
//            }
//
//
//
//            $this->objTemplate->setVariable(array(
//                'FILE_ID'                  => $fileInfo['file_id'],
//                'FILE_NAME'                => $fileInfo['file_loc']['lang'][$_LANGID]["name"],
//                'FILE_DESC'                => str_replace(chr(13), '<br />', $fileInfo['file_loc']['lang'][$_LANGID]["desc"]),
//                'FILE_TYPE'                => $fileInfo['file_type'],
//                'FILE_TYPE'                => $fileInfo['file_type'],
//                'FILE_SIZE'                => ($fileInfo['file_size']/1000)." KB",
//                'FILE_IMG'                 => $fileInfo['file_img'],
//                'FILE_AUTHOR'              => $fileInfo['file_autor'],
//                'FILE_CREATED'             => $fileInfo['file_created'],
//                'FILE_LICENSE'             => $fileInfo['file_license'],
//                'FILE_VERSION'             => $fileInfo['file_version'],
//                'ICON_FILE'                => $FileIcon,
//                'ICON_INFO'                => $this->_GetIconImage('info.gif',0,'info_'.$fileInfo['file_id']),
//                'ICON_DOWNLOAD'            => $DonwlodLink,
//                'TXT_DOWNLOADS_LICENSE'    => $_ARRAYLANG['TXT_DOWNLOADS_LICENSE'],
//                'TXT_DOWNLOADS_VERSION'    => $_ARRAYLANG['TXT_DOWNLOADS_VERSION'],
//                'TXT_DOWNLOADS_SIZE'       => $_ARRAYLANG['TXT_DOWNLOADS_SIZE'],
//                'TXT_DOWNLOADS_SCREENSHOT' => $_ARRAYLANG['TXT_DOWNLOADS_SCREENSHOT'],
//                'FILE_SCREEN'              => $FILE_SCREEN,
//
//            ));
//
//            $openendJS .= "opened[".$fileInfo['file_id']."] = false;
//
//            ";
//            $FilesJS .= "Download[".$fileInfo['file_id']."] = new fx.Height('DownlaodLayer_".$fileInfo['file_id']."',{duration:1000});
//
//            ";
//
//            $this->objTemplate->parse('Files_Row');
//            $objResult->MoveNext();
//        }
//
//        $DOWNLOADS_JS = "
//            <script type=\"text/javascript\" src=\"lib/javascript/prototype.lite.js\"></script>
//            <script type=\"text/javascript\" src=\"lib/javascript/moo.fx.js\"></script>
//            <script type=\"text/javascript\">
//            <!--
//            opened = new Array()
//            Download = new Array();
//            ".$openendJS."
//            window.onload = function() {
//                ".$FilesJS."
//            };
//
//            function toggelopen(Obj) {
//                if (opened[Obj]==false) {
//                    Download[Obj].custom(24,180);
//                    opened[Obj]=true;
//                    document.getElementById('info_'+Obj).src = '".ASCMS_MODULE_WEB_PATH."/downloads/images/icons/".$this->_arrConfig["design"]."/info_act.gif';
//                } else {
//                    Download[Obj].custom(180,24);
//                    opened[Obj]=false;
//                    document.getElementById('info_'+Obj).src = '".ASCMS_MODULE_WEB_PATH."/downloads/images/icons/".$this->_arrConfig["design"]."/info.gif';
//                }
//            }
//            //-->
//            </script>
//        ";
//
//        $this->objTemplate->setVariable(array(
//            'DOWNLOADS_JS'                 => $DOWNLOADS_JS,
//        ));
//    } else {
//        $this->objTemplate->hideBlock('Files_Row');
//    }
//
//    if ($this->_arrConfig["filter"]==0) {
//        $searchdisplay = 'none';
//    } else {
//        $searchdisplay = 'block';
//    }
//
//    $this->objTemplate->setVariable(array(
//        'TXT_DOWNLOADS_DOWNLOADS'  => $_ARRAYLANG['TXT_DOWNLOADS_DOWNLOADS'],
//        'TXT_DOWNLOADS_SEARCH'     => $_ARRAYLANG['TXT_DOWNLOADS_SEARCH'],
//        'TXT_DOWNLOADS_FILTERS'    => $_ARRAYLANG['TXT_DOWNLOADS_FILTERS'],
//        'TXT_DOWNLOADS_CATEGORIES' => $_ARRAYLANG['TXT_DOWNLOADS_CATEGORIES'],
//        'FILTER_CATEGORIES_VALUE'  => $FILTER_CATEGORIES_VALUE,
//        'FILTER_DISPLAY'           => $searchdisplay,
//    ));
//}


//function GetFile()
//{
//    global $objDatabase;
//
//    $code = $_REQUEST["id"];
//
//    if ($code!='') {
//        $objResult = $objDatabase->SelectLimit("SELECT `file_id` FROM ".DBPREFIX."module_downloads_files WHERE file_source='".$code."'", 1);
//        if ($objResult !== false && $objResult->RecordCount() == 1) {
//            $File_ID =  $objResult->fields['file_id'];
//        } else {
//            header('location:index.php?section=downloads');
//            exit();
//        }
//    } else {
//        header('location:index.php?section=downloads');
//        exit();
//    }
//
//    $FileInfo = $this->_FileInfo($File_ID);
//
//    if ($FileInfo["file_protected"]==0) {
//        $StartDownload = true;
//    } else {
//        if (Permission::checkAccess($FileInfo['file_access_id'], 'dynamic', true)) {
//            $StartDownload = true;
//        } else {
//            $StartDownload = false;
//        }
//    }
//
//    if ($StartDownload) {
//        if (substr($FileInfo['file_name'], 0, 7)!='http://' && substr($FileInfo['file_name'], 0, 8)!='https://') {
//            $Dateiname  = basename($FileInfo['file_name']);
//            $Size       = filesize(ASCMS_PATH.'/'.$FileInfo['file_name']);
//            header("Content-Type: application/force-download");
//            header("Content-Disposition: attachment; filename=".$Dateiname."");
//            header("Content-Length: ".$Size."");
//            readfile(ASCMS_PATH.'/'.$FileInfo['file_name']);
//        } else {
//            header('location:'.$FileInfo['file_name']);
//            exit();
//        }
//    } else {
//        header('location:index.php?section=login');
//        exit();
//    }
//}


    /**
     * Show some information about the user account
     *
     * Note that this is a noser.com special for the download module.
     * This method redirects to the download module main page if there
     * is no User logged in, or if no User information can be retrieved.
     * @global  array   $_ARRAYLANG         Language array
     * @return  boolean                     True on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
/*    function showUserInfo()
    {
        global $_ARRAYLANG, $objAuth;

        if (empty($objAuth) || !$objAuth->checkAuth()) {
            $redirect = base64_encode("index.php?section=downloads&cmd=user");
            header("Location: index.php?section=login&redirect=$redirect");
            exit;
        }

        $arrInfo = userManagement::getUserInfo();
        if (!$arrInfo) {
            return false;
        }

        if (!empty($_POST['logout'])) {
            header("Location: index.php?section=logout");
            exit;
        }

        if (!empty($_POST['update'])) {
            // User tries to change the password
            $oldPass  = (isset($_POST['oldPass'])  ? contrexx_stripslashes($_POST['oldPass'])  : '');
            $newPass1 = (isset($_POST['newPass1']) ? contrexx_stripslashes($_POST['newPass1']) : '');
            $newPass2 = (isset($_POST['newPass2']) ? contrexx_stripslashes($_POST['newPass2']) : '');

            if ($oldPass == '') {
                $this->objTemplate->setVariable('DOWNLOAD_USER_STATUS', $_ARRAYLANG['TXT_DOWNLOAD_ENTER_CURRENT_PASSWORD']);
            }
            elseif (md5($oldPass) != $arrInfo['password']) {
                $this->objTemplate->setVariable('DOWNLOAD_USER_STATUS', $_ARRAYLANG['TXT_DOWNLOAD_WRONG_CURRENT_PASSWORD']);
            }
            elseif ($newPass1 == '') {
                $this->objTemplate->setVariable('DOWNLOAD_USER_STATUS', $_ARRAYLANG['TXT_DOWNLOAD_SPECIFY_NEW_PASSWORD']);
            }
            elseif ($newPass1 != $newPass2) {
                $this->objTemplate->setVariable('DOWNLOAD_USER_STATUS', $_ARRAYLANG['TXT_DOWNLOAD_PASSWORD_NOT_CONFIRMED']);
            }
            elseif (strlen($newPass1) < 6) {
                $this->objTemplate->setVariable('DOWNLOAD_USER_STATUS', $_ARRAYLANG['TXT_DOWNLOAD_INVALID_PASSWORD']);
            }
            else {
                $result = userManagement::changePasswordById($arrInfo['id'], $newPass1);
                if ($result) {
                    $this->objTemplate->setVariable('DOWNLOAD_USER_STATUS', $_ARRAYLANG['TXT_DOWNLOAD_PASSWORD_CHANGED_SUCCESSFULLY']);
                } else {
                    $this->objTemplate->setVariable('DOWNLOAD_USER_STATUS', $_ARRAYLANG['TXT_DOWNLOAD_UNABLE_SET_NEW_PASSWORD']);
                }
                // Clear POST array and refresh user Info
                unset($_POST);
                return $this->showUserInfo();
            }
        }
//echo("username: ".$_SESSION['shop']['username']."<br />");

        // Determine end date
        $orderUnixTimeStamp = strtotime($arrInfo['regdate']);
        $validity = $arrInfo['validity'] * 24 * 60 * 60;
        $endDate =
            ($validity > 0
                ? date('d.m.Y', ($orderUnixTimeStamp+$validity))
                : $_ARRAYLANG['TXT_DOWNLOAD_VALIDITY_UNLIMITED']
            );

        $this->objTemplate->setVariable(array(
            // Account information
            'TXT_DOWNLOAD_SURNAME' => $_ARRAYLANG['TXT_DOWNLOAD_SURNAME'],
            'TXT_DOWNLOAD_FIRSTNAME' => $_ARRAYLANG['TXT_DOWNLOAD_FIRSTNAME'],
            'TXT_DOWNLOAD_EMAIL' => $_ARRAYLANG['TXT_DOWNLOAD_EMAIL'],
            'TXT_DOWNLOAD_VALID_TIL' => $_ARRAYLANG['TXT_DOWNLOAD_VALID_TIL'],
            'DOWNLOAD_CUSTOMER_SURNAME' => $arrInfo['lastname'],
            'DOWNLOAD_CUSTOMER_FIRSTNAME' => $arrInfo['firstname'],
            'DOWNLOAD_CUSTOMER_EMAIL' => $arrInfo['email'],
            'DOWNLOAD_CUSTOMER_VALID_TIL' => $endDate,
            // Change password
            'TXT_DOWNLOAD_PASSWORD_MIN_CHARS' => $_ARRAYLANG['TXT_DOWNLOAD_PASSWORD_MIN_CHARS'],
            'TXT_DOWNLOAD_PASSWORD_CURRENT' => $_ARRAYLANG['TXT_DOWNLOAD_PASSWORD_CURRENT'],
            'TXT_DOWNLOAD_PASSWORD_NEW' => $_ARRAYLANG['TXT_DOWNLOAD_PASSWORD_NEW'],
            'TXT_DOWNLOAD_PASSWORD_CONFIRM' => $_ARRAYLANG['TXT_DOWNLOAD_PASSWORD_CONFIRM'],
            'TXT_DOWNLOAD_PASSWORD_CHANGE' => $_ARRAYLANG['TXT_DOWNLOAD_PASSWORD_CHANGE'],
            // Logout
            'TXT_DOWNLOAD_LOGOUT' => $_ARRAYLANG['TXT_DOWNLOAD_LOGOUT'],
        ));

        return true;
    }

    function checkUser(){
        global $objDatabase, $objAuth, $objPerm;
        if(isset($_REQUEST["id"])){
            $UserInfo = userManagement::getUserInfoByName($_REQUEST["id"]);

            $validity               = $UserInfo['validity'];
            $regdate                = $UserInfo['regdate'];
            $orderUnixTimeStamp     = strtotime($regdate);
            $endDate = ($validity > 0 ? date('d.m.Y', ($orderUnixTimeStamp+($validity * 24 * 60 * 60))) : '');

            $this->objTemplate->setVariable(array(
            'USER_INFO' => 'Expires: '.$endDate,
        ));
        }
    }
*/

}
?>
