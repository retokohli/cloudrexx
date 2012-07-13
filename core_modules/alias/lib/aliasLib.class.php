<?php
/**
 * Alias library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core_module_alias
 * @todo        Edit PHP DocBlocks!
 */
/**
 * @ignore
 */
require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';
/**
 * @ignore
 */
require_once ASCMS_FRAMEWORK_PATH.'/FWHtAccess.class.php';

/**
 * Alias library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core_module_alias
 * @todo        Edit PHP DocBlocks!
 */
class aliasLib
{
    public $_arrAliasTypes = array(
        'local',
        'url'
    );

    public $langId;

    public $_arrConfig = null;
    
    protected $em = null;
    
    protected $nodeRepository = null;
    
    protected $pageRepository = null;
    

    function __construct($langId = 0)
    {
        $this->langId = intval($langId) > 0 ? $langId : FRONTEND_LANG_ID;
        
        $this->em = Env::em();
        $this->nodeRepository = $this->em->getRepository('Cx\Model\ContentManager\Node');
        $this->pageRepository = $this->em->getRepository('Cx\Model\ContentManager\Page');
    }

    
    function _getAliases($limit = null)
    {
        $pos = isset($_GET['pos']) ? intval($_GET['pos']) : 0;

        $tree = $this->pageRepository->getTree(null, false, \Cx\Model\ContentManager\Repository\PageRepository::SEARCH_MODE_ALIAS_ONLY, true);
        
        $pages = array();
        $i = 0;
        foreach ($tree as $node) {
            $i++;
            if ($i < $pos) {
                continue;
            }
            $page = current($node->getPages(true)->getSnapshot());
            if (!$page) {
                continue;
            }
            $pages[] = $page;
            if ($limit && count($pages) == $limit) {
                break;
            }
        }
        
        return $pages;
    }


    function _getAliasesCount()
    {
        return count($this->_getAliases());
    }
    

    function _getAlias($aliasId)
    {
        $crit = array(
            'node' => $aliasId,
        );
        return current($this->pageRepository->findBy($crit, true));
    }
    
    
    function _fetchTarget($page)
    {
        $crit = array(
            'node' => $page->getTargetNodeId(),
            'lang' => $page->getTargetLangId(),
        );
        return current($this->pageRepository->findBy($crit, true));
    }
    
    
    function _isLocalAliasTarget($page)
    {
        return $page->isTargetInternal();
    }
    
    
    function _getURL($page)
    {
        $lang = FWLanguage::getLanguageCodeById($page->getLang());
        return $page->getUrl('/' . $lang, '');
    }
    
    function _getAliasesWithSameTarget($aliaspage)
    {
        $page = $this->_fetchTarget($aliaspage);
        if (!$page) {
            return array();
        }
        return $page->getAliases();
    }
    

    function _setAliasTarget(&$arrAlias)
    {
        if ($arrAlias['type'] == 'local') {
            $page = new Cx\Model\ContentManager\Page();
            $page->setTarget($arrAlias['url']);
            $target_node_id = $page->getTargetNodeId();
            $target_lang_id = $page->getTargetLangId();
            if (!$target_lang_id) {
                $target_lang_id = $this->langId;
            }
            $crit = array(
                'node' => $target_node_id,
                'lang' => $target_lang_id,
            );
            $page_repo = Env::em()->getRepository('Cx\Model\ContentManager\Page');
            $targetPage = $page_repo->findBy($crit, true);
            $targetPage = $targetPage[0];
            $targetPath = $page_repo->getPath($targetPage);
            $arrAlias['pageUrl'] = "/".$targetPath;
            $arrAlias['title'] = $targetPage->getContentTitle();
        }
    }
    
    
    function _createTemporaryAlias()
    {
        global $objFWUser;
        
        $page = new \Cx\Model\ContentManager\Page();
        $page->setLang(0);
        $page->setType(\Cx\Model\ContentManager\Page::TYPE_ALIAS);
        $page->setCmd('');
        $page->setActive(true);
        //$page->setUsername($objFWUser->objUser->getUsername());
        return $page;
    }
    

    function _saveAlias($slug, $target, $is_local, $id = "")
    {
        if ($slug == "") {
            return false;
        }
        
        // is internal target
        if ($is_local) {
            // get target page
            $temp_page = new \Cx\Model\ContentManager\Page();
            $temp_page->setTarget($target);
            $existing_aliases = $this->_getAliasesWithSameTarget($temp_page);
            
            // if alias exists already -> fail
            foreach ($existing_aliases as $existing_alias) {
                if (($id == '' || $existing_alias->getNode()->getId() != $id) &&
                        $slug == $existing_alias->getSlug()) {
                    return false;
                }
            }
        }
        
        if ($id == "") {
            // create new node
            $node = new \Cx\Model\ContentManager\Node();
            $node->setParent($this->nodeRepository->getRoot());
            $this->em->persist($node);

            // add a page
            $page = $this->_createTemporaryAlias();
            $page->setNode($node);
        } else {
            $node = $this->nodeRepository->find($id);
            if (!$node) {
                return false;
            }
            $pages = $node->getPages(true);
            if (count($pages) != 1) {
                return false;
            }
            $page = $pages->first();
            // we won't change anything on non aliases
            if ($page->getType() != \Cx\Model\ContentManager\Page::TYPE_ALIAS) {
                return false;
            }
        }
        
        // set page attributes
        $page->setSlug($slug);
        $page->setTarget($target);
        $page->setTitle($page->getSlug());
        
        // sanitize slug
        while (file_exists(ASCMS_PATH . '/' . $page->getSlug())) {
            $page->nextSlug();
        }
        
        // save
	$page->validate();
        $this->em->persist($page);
        $this->em->flush();
        $this->em->refresh($node);
        $this->em->refresh($page);
        
        return true;
    }


    function _deleteAlias($aliasId)
    {
        $alias = $this->_getAlias($aliasId);
        if (!is_object($alias)) {
            die ("ALIAS NOT FOUND: ID=".$aliasId);
            return false;
        }
        $this->em->remove($alias->getNode());
        $this->em->remove($alias);
        $this->em->flush();
        return true;
    }
}
