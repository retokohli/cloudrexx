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

                $this->parseRelatedCategories($objDownload);
                $this->parseRelatedDownloads($objDownload, $objCategory->getId());

                $this->parseDownload($objDownload, $objCategory->getId());



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
                if ($this->objTemplate->blockExists('downloads_file_detail')) {
                    $this->objTemplate->hideBlock('downloads_file_detail');
                }
            }

            // hide unwanted blocks on the category/detail page
            if ($this->objTemplate->blockExists('downloads_overview')) {
                $this->objTemplate->hideBlock('downloads_overview');
            }
            if ($this->objTemplate->blockExists('downloads_most_viewed_file_list')) {
                $this->objTemplate->hideBlock('downloads_most_viewed_file_list');
            }
            if ($this->objTemplate->blockExists('downloads_most_downloaded_file_list')) {
                $this->objTemplate->hideBlock('downloads_most_downloaded_file_list');
            }
            if ($this->objTemplate->blockExists('downloads_most_popular_file_list')) {
                $this->objTemplate->hideBlock('downloads_most_popular_file_list');
            }
            if ($this->objTemplate->blockExists('downloads_newest_file_list')) {
                $this->objTemplate->hideBlock('downloads_newest_file_list');
            }
            if ($this->objTemplate->blockExists('downloads_updated_file_list')) {
                $this->objTemplate->hideBlock('downloads_updated_file_list');
            }
        } else {
            // parse category overview
            $this->parseCategories($objCategory, array('downloads_overview', 'downloads_overview_category'), null, null, 'downloads_overview_row', array('downloads_overview_subcategory_list', 'downloads_overview_subcategory'), $this->arrConfig['overview_max_subcats']);

            if (!empty($this->searchKeyword)) {
                $this->parseDownloads($objCategory);
            } else {
                if ($this->objTemplate->blockExists('downloads_file_list')) {
                    $this->objTemplate->hideBlock('downloads_file_list');
                }
            }

            // parse most viewed downloads
            $this->parseSpecialDownloads(array('downloads_most_viewed_file_list', 'downloads_most_viewed_file'), null, array('views' => 'desc'), $this->arrConfig['most_viewed_file_count']);

            // parse most downloaded downloads
            $this->parseSpecialDownloads(array('downloads_most_downloaded_file_list', 'downloads_most_downloaded_file'), null, array('download_count' => 'desc'), $this->arrConfig['most_downloaded_file_count']);

            // parse most popular downloads
            // TODO: Rating system has to be implemented first!
            //$this->parseSpecialDownloads(array('downloads_most_popular_file_list', 'downloads_most_popular_file'), null, array('rating' => 'desc'), $this->arrConfig['most_popular_file_count']);

            // parse newest downloads
            $filter = array(
                'ctime' => array(
                    '>=' => time() - $this->arrConfig['new_file_time_limit']
                )
            );
            $this->parseSpecialDownloads(array('downloads_newest_file_list', 'downloads_newest_file'), $filter, array('ctime' => 'desc'), $this->arrConfig['newest_file_count']);

            // parse recently updated downloads
            $filter = array(
                'mtime' => array(
                    '>=' => time() - $this->arrConfig['updated_file_time_limit']
                ),
                // exclude newest downloads
                'ctime' => array(
                    '<' => time() - $this->arrConfig['new_file_time_limit']
                ),
            );
            $this->parseSpecialDownloads(array('downloads_updated_file_list', 'downloads_updated_file'), $filter, array('mtime' => 'desc'), $this->arrConfig['updated_file_count']);


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
            if ($this->objTemplate->blockExists('downloads_file_detail')) {
                $this->objTemplate->hideBlock('downloads_file_detail');
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

    private function parseCategories($objCategory, $arrCategoryBlocks, $categoryLimit = null, $variablePrefix = '', $rowBlock = null, $arrSubCategoryBlocks = null, $subCategoryLimit = null)
    {
        global $_ARRAYLANG;

        if (!$this->objTemplate->blockExists($arrCategoryBlocks[0])) {
            return;
        }

        $objSubcategory = Category::getCategories(array('parent_id' => $objCategory->getId(), 'is_active' => true), null, null, null, $categoryLimit);

        if ($objSubcategory->EOF) {
            $this->objTemplate->hideBlock($arrCategoryBlocks[0]);
        } else {
            $row = 1;
            while (!$objSubcategory->EOF) {
                // set category attributes
                $this->parseCategoryAttributes($objSubcategory, $row++, $variablePrefix);

                // parse subcategories
                if (isset($arrSubCategoryBlocks)) {
                    $this->parseCategories($objSubcategory, array('downloads_overview_subcategory_list', 'downloads_overview_subcategory'), $subCategoryLimit, 'SUB');
                }

                // parse category
                $this->objTemplate->parse($arrCategoryBlocks[1]);

                // parse row
                if (isset($rowBlock) && $this->objTemplate->blockExists($rowBlock) && $row % $this->arrConfig['overview_cols_count'] == 0) {
                    $this->objTemplate->parse($rowBlock);
                }

                $objSubcategory->next();
            }

            $this->objTemplate->setVariable('TXT_DOWNLOADS_CATEGORIES', $_ARRAYLANG['TXT_DOWNLOADS_CATEGORIES']);
            $this->objTemplate->parse($arrCategoryBlocks[0]);
        }
    }

    private function parseRelatedCategories($objDownload)
    {
        global $_ARRAYLANG;

        if (!$this->objTemplate->blockExists('downloads_file_category_list')) {
            return;
        }

        $arrCategoryIds = $objDownload->getAssociatedCategoryIds();
        if (count($arrCategoryIds)) {
            $row = 1;
            foreach ($arrCategoryIds as $categoryId) {
                $objCategory = Category::getCategory($categoryId);

                if (!$objCategory->EOF) {
                    // set category attributes
                    $this->parseCategoryAttributes($objCategory, $row++, 'FILE_');

                    // parse category
                    $this->objTemplate->parse('downloads_file_category');
                }
            }

            $this->objTemplate->setVariable('TXT_DOWNLOADS_RELATED_CATEGORIES', $_ARRAYLANG['TXT_DOWNLOADS_RELATED_CATEGORIES']);
            $this->objTemplate->parse('downloads_file_category_list');
        } else {
            $this->objTemplate->hideBlock('downloads_file_category_list');
        }
    }

    private function parseCategoryAttributes($objCategory, $row, $variablePrefix)
    {
        global $_LANGID, $_ARRAYLANG;

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
            'DOWNLOADS_'.$variablePrefix.'CATEGORY_ID'                 => $objCategory->getId(),
            'DOWNLOADS_'.$variablePrefix.'CATEGORY_NAME'               => htmlentities($objCategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET),
            'DOWNLOADS_'.$variablePrefix.'CATEGORY_NAME_LINK'          => $this->getHtmlLinkTag(CONTREXX_SCRIPT_PATH.$this->moduleParamsHtml.'&amp;category='.$objCategory->getId(), sprintf($_ARRAYLANG['TXT_DOWNLOADS_SHOW_CATEGORY_CONTENT'], htmlentities($objCategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)), htmlentities($objCategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)),
            'DOWNLOADS_'.$variablePrefix.'CATEGORY_FOLDER_LINK'        => $this->getHtmlFolderLinkTag(CONTREXX_SCRIPT_PATH.$this->moduleParamsHtml.'&amp;category='.$objCategory->getId(), sprintf($_ARRAYLANG['TXT_DOWNLOADS_SHOW_CATEGORY_CONTENT'], htmlentities($objCategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)), htmlentities($objCategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)),
            'DOWNLOADS_'.$variablePrefix.'CATEGORY_DESCRIPTION'        => htmlentities($description, ENT_QUOTES, CONTREXX_CHARSET),
            'DOWNLOADS_'.$variablePrefix.'CATEGORY_SHORT_DESCRIPTION'  => htmlentities($shortDescription, ENT_QUOTES, CONTREXX_CHARSET),
            'DOWNLOADS_'.$variablePrefix.'CATEGORY_IMAGE'              => $this->getHtmlImageTag($imageSrc, htmlentities($objCategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)),
            'DOWNLOADS_'.$variablePrefix.'CATEGORY_IMAGE_SRC'          => $imageSrc,
            'DOWNLOADS_'.$variablePrefix.'CATEGORY_THUMBNAIL'          => $this->getHtmlImageTag($thumbnailSrc, htmlentities($objCategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)),
            'DOWNLOADS_'.$variablePrefix.'CATEGORY_THUMBNAIL_SRC'      => $thumbnailSrc,
            'DOWNLOADS_'.$variablePrefix.'CATEGORY_DOWNLOADS_COUNT'    => intval($objCategory->getAssociatedDownloadsCount()),
            'DOWNLOADS_'.$variablePrefix.'CATEGORY_DELETE_ICON'        => '',
            'DOWNLOADS_'.$variablePrefix.'CATEGORY_ROW_CLASS'          => 'row'.($row % 2 + 1),
            'TXT_DOWNLOADS_MORE'                                       => $_ARRAYLANG['TXT_DOWNLOADS_MORE']
        ));
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
        global $_CONFIG, $_ARRAYLANG;

        if (!$this->objTemplate->blockExists('downloads_file_list')) {
            return;
        }

        $limitOffset = isset($_GET['pos']) ? intval($_GET['pos']) : 0;
        $objDownload = new Download();
        $objDownload->loadDownloads(array('category_id' => $objCategory->getId()), $this->searchKeyword, null, null, $_CONFIG['corePagingLimit'], $limitOffset);
        $categoryId = $objCategory->getId();

        if ($objDownload->EOF) {
            $this->objTemplate->hideBlock('downloads_file_list');
        } else {
            $row = 1;
            while (!$objDownload->EOF) {
                // select category
                if ($objCategory->EOF) {
                    $arrAssociatedCategories = $objDownload->getAssociatedCategoryIds();
                    $categoryId = $arrAssociatedCategories[0];
                }


                // parse download info
                $this->parseDownloadAttributes($objDownload, $categoryId);
                $this->objTemplate->setVariable('DOWNLOADS_FILE_ROW_CLASS', 'row'.($row++ % 2 + 1));
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

    private function parseSpecialDownloads($arrBlocks, $arrFilter, $arrSort, $limit)
    {
        global $_ARRAYLANG;

        if (!$this->objTemplate->blockExists($arrBlocks[0])) {
            return;
        }

        $objDownload = new Download();
        $objDownload->loadDownloads($arrFilter, null, $arrSort, null, $limit);

        if ($objDownload->EOF) {
            $this->objTemplate->hideBlock($arrBlocks[0]);
        } else {
            $row = 1;
            while (!$objDownload->EOF) {
                // select category
                $arrAssociatedCategories = $objDownload->getAssociatedCategoryIds();
                $categoryId = $arrAssociatedCategories[0];

                // parse download info
                $this->parseDownloadAttributes($objDownload, $categoryId);
                $this->objTemplate->setVariable('DOWNLOADS_FILE_ROW_CLASS', 'row'.($row++ % 2 + 1));
                $this->objTemplate->parse($arrBlocks[1]);

                $objDownload->next();
            }

            $this->objTemplate->setVariable(array(
                'TXT_DOWNLOADS_MOST_VIEWED'         => $_ARRAYLANG['TXT_DOWNLOADS_MOST_VIEWED'],
                'TXT_DOWNLOADS_MOST_DOWNLOADED'     => $_ARRAYLANG['TXT_DOWNLOADS_MOST_DOWNLOADED'],
                'TXT_DOWNLOADS_NEW'                 => $_ARRAYLANG['TXT_DOWNLOADS_NEW'],
                'TXT_DOWNLOADS_RECENTLY_UPDATED'    => $_ARRAYLANG['TXT_DOWNLOADS_RECENTLY_UPDATED']
            ));

            $this->objTemplate->touchBlock($arrBlocks[0]);
        }
    }

    private function parseDownloadAttributes($objDownload, $categoryId)
    {
        global $_ARRAYLANG, $_LANGID;

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
            $image = $this->getHtmlImageTag($this->defaultCategoryImage['src'], htmlentities($objDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET));;
            $thumbnail = $this->getHtmlImageTag($this->defaultCategoryImage['src'].'.thumb', htmlentities($objDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET));
        }

        $this->objTemplate->setVariable(array(
            'TXT_DOWNLOADS_ADDED_BY'            => $_ARRAYLANG['TXT_DOWNLOADS_ADDED_BY'],
            'TXT_DOWNLOADS_LAST_UPDATED'        => $_ARRAYLANG['TXT_DOWNLOADS_LAST_UPDATED'],
            'TXT_DOWNLOADS_DOWNLOADED'          => $_ARRAYLANG['TXT_DOWNLOADS_DOWNLOADED'],
            'TXT_DOWNLOADS_VIEWED'              => $_ARRAYLANG['TXT_DOWNLOADS_VIEWED'],
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
            'DOWNLOADS_FILE_DOWNLOAD_LINK_SRC'  => CONTREXX_SCRIPT_PATH.$this->moduleParamsHtml.'&amp;download='.$objDownload->getId(),
            'DOWNLOADS_FILE_OWNER'              => $this->getParsedUsername($objDownload->getOwnerId()),
            'DOWNLOADS_FILE_OWNER_ID'           => $objDownload->getOwnerId(),
            'DOWNLOADS_FILE_SRC'                => htmlentities($objDownload->getType() == 'file' ? basename($objDownload->getSource()) : $objDownload->getSource(), ENT_QUOTES, CONTREXX_CHARSET),
            'DOWNLOADS_FILE_LAST_UPDATED'       => date(ASCMS_DATE_FORMAT, $objDownload->getMTime()),
            'DOWNLOADS_FILE_VIEWS'              => $objDownload->getViewCount(),
            'DOWNLOADS_FILE_DOWNLOAD_COUNT'     => $objDownload->getDownloadCount()
        ));

        // parse size
        if ($this->arrConfig['use_attr_size']) {
            $this->objTemplate->setVariable(array(
                'TXT_DOWNLOADS_SIZE'                => $_ARRAYLANG['TXT_DOWNLOADS_SIZE'],
                'DOWNLOADS_FILE_SIZE'               => $this->getFormatedFileSize($objDownload->getSize())
            ));
        }

        // parse license
        if ($this->arrConfig['use_attr_license']) {
            $this->objTemplate->setVariable(array(
                'TXT_DOWNLOADS_LICENSE'             => $_ARRAYLANG['TXT_DOWNLOADS_LICENSE'],
                'DOWNLOADS_FILE_LICENSE'            => htmlentities($objDownload->getLicense(), ENT_QUOTES, CONTREXX_CHARSET),
            ));
        }

        // parse version
        if ($this->arrConfig['use_attr_version']) {
            $this->objTemplate->setVariable(array(
                'TXT_DOWNLOADS_VERSION'             => $_ARRAYLANG['TXT_DOWNLOADS_VERSION'],
                'DOWNLOADS_FILE_VERSION'            => htmlentities($objDownload->getVersion(), ENT_QUOTES, CONTREXX_CHARSET),
            ));
        }

        // parse author
        if ($this->arrConfig['use_attr_author']) {
            $this->objTemplate->setVariable(array(
                'TXT_DOWNLOADS_AUTHOR'              => $_ARRAYLANG['TXT_DOWNLOADS_AUTHOR'],
                'DOWNLOADS_FILE_AUTHOR'             => htmlentities($objDownload->getAuthor(), ENT_QUOTES, CONTREXX_CHARSET),
            ));
        }

        // parse website
        if ($this->arrConfig['use_attr_website']) {
            $this->objTemplate->setVariable(array(
                'TXT_DOWNLOADS_WEBSITE'             => $_ARRAYLANG['TXT_DOWNLOADS_WEBSITE'],
                'DOWNLOADS_FILE_WEBSITE'            => $this->getHtmlLinkTag(htmlentities($objDownload->getWebsite(), ENT_QUOTES, CONTREXX_CHARSET), htmlentities($objDownload->getWebsite(), ENT_QUOTES, CONTREXX_CHARSET), htmlentities($objDownload->getWebsite(), ENT_QUOTES, CONTREXX_CHARSET)),
                'DOWNLOADS_FILE_WEBSITE_SRC'        => htmlentities($objDownload->getWebsite(), ENT_QUOTES, CONTREXX_CHARSET),
            ));
        }
    }

    private function parseRelatedDownloads($objDownload, $currentCategoryId)
    {
        global $_LANGID, $_ARRAYLANG;

        if (!$this->objTemplate->blockExists('downloads_related_file_list')) {
            return;
        }

        $objRelatedDownload = $objDownload->getDownloads(array('download_id' => $objDownload->getId()), null, array('order' => 'ASC', 'name' => 'ASC', 'id' => 'ASC'));

        if ($objRelatedDownload) {
            $row = 1;
            while (!$objRelatedDownload->EOF) {
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
                        if (!$objCategory->EOF) {
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
                    'DOWNLOADS_RELATED_FILE_ROW_CLASS'          => 'row'.($row++ % 2 + 1)
                ));
                $this->objTemplate->parse('downloads_related_file');


                $objRelatedDownload->next();
            }

            $this->objTemplate->setVariable('TXT_DOWNLOADS_RELATED_DOWNLOADS', $_ARRAYLANG['TXT_DOWNLOADS_RELATED_DOWNLOADS']);
            $this->objTemplate->parse('downloads_related_file_list');
        } else {
            $this->objTemplate->hideBlock('downloads_related_file_list');
        }
    }

    private function parseDownload($objDownload, $categoryId)
    {
        global $_LANGID, $_ARRAYLANG;

        if (!$this->objTemplate->blockExists('downloads_file_detail')) {
            return;
        }


        $this->parseDownloadAttributes($objDownload, $categoryId);
        $this->objTemplate->parse('downloads_file_detail');

        $objDownload->incrementViewCount();
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

            $objDownload->incrementDownloadCount();

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

    private function getFormatedFileSize($bytes)
    {
        global $_ARRAYLANG;

        if (!$bytes) {
            return $_ARRAYLANG['TXT_DOWNLOADS_UNKNOWN'];
        }

        $exp = log($bytes, 1024);

        if ($exp < 1) {
            return $bytes.' '.$_ARRAYLANG['TXT_DOWNLOADS_BYTES'];
        } elseif ($exp < 2) {
            return round($bytes/1024, 2).' '.$_ARRAYLANG['TXT_DOWNLOADS_KBYTE'];
        } elseif ($exp < 3) {
            return round($bytes/pow(1024, 2), 2).' '.$_ARRAYLANG['TXT_DOWNLOADS_MBYTE'];
        } else {
            return round($bytes/pow(1024, 3), 2).' '.$_ARRAYLANG['TXT_DOWNLOADS_GBYTE'];
        }
    }
}
?>
