<?php

/**
 * Search
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core_module_search
 */

namespace Cx\Core\Search;
require ASCMS_CORE_PATH . '/Module.class.php';

/**
 * Search manager
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core_module_search
 */
class SearchManager extends \Module
{
    /**
     * Doctrine entity manager
     * @var    object
     * @access private
     */
    private $em = null;
    /**
     * Database connection
     * @var    object
     * @access private
     */
    private $db = null;
    /**
     * InitCMS
     * @var    object
     * @access private
     */
    private $init = null;
    /**
     * Page repository
     * @var    object
     * @access private
     */
    private $pageRepo = null;
    /**
     * Node repository
     * @var    object
     * @access private
     */
    private $nodeRepo = null;
    /**
     * Log repository
     * @var    object
     * @access private
     */
    private $logRepo = null;
    /**
     * Search term
     * @var    string
     * @access private
     */
    private $term = '';
    /**
     * Position for paging
     * @var    int
     * @access private
     */
    private $pos = 0;
    
    /**
     * Constructor
     */
    function __construct($act, $tpl, $db, $init)
    {
        parent::__construct($act, $tpl);
        $this->defaultAct = 'getSearchResults';
        
        $this->em       = \Env::em();
        $this->db       = $db;
        $this->act      = $act;
        $this->tpl      = $tpl;
        $this->init     = $init;
        
        $this->pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');
        $this->nodeRepo = $this->em->getRepository('Cx\Model\ContentManager\Node');
        $this->logRepo  = $this->em->getRepository('Gedmo\Loggable\Entity\LogEntry');
        
        $this->term     = !empty($_GET['term']) ? contrexx_input2raw($_GET['term']) : '';
        $this->pos      = !empty($_GET['pos'])  ? contrexx_input2raw($_GET['pos'])  : 0;
    }
    
    /**
     * Gets the search results.
     * 
     * @return  mixed  Parsed content.
     */
    public function getSearchResults()
    {
        global $_ARRAYLANG;
        
        $this->template->addBlockfile('ADMIN_CONTENT', 'search', 'search.html');
        
        if (!empty($this->term)) {
            $pages      = $this->getSearchedPages();
            $countPages = $this->countSearchedPages();
            
            if ($countPages > 0) {
                $paging = getPaging($countPages, $this->pos, '&amp;cmd=search', '', true);
                
                $this->template->setVariable(array(
                    'TXT_SEARCH_RESULTS_COMMENT' => sprintf($_ARRAYLANG['TXT_SEARCH_RESULTS_COMMENT'], $this->term, $countPages),
                    'TXT_SEARCH_TITLE'           => $_ARRAYLANG['TXT_NAVIGATION_TITLE'],
                    'TXT_SEARCH_CONTENT_TITLE'   => $_ARRAYLANG['TXT_PAGETITLE'],
                    'TXT_SEARCH_SLUG'            => $_ARRAYLANG['TXT_CORE_CM_SLUG'],
                    'TXT_SEARCH_LANG'            => $_ARRAYLANG['TXT_LANGUAGE'],
                    'SEARCH_PAGING'              => $paging,
                ));
                
                foreach ($pages as $page) {
                    $this->template->setVariable(array(
                        'SEARCH_RESULT_ID'            => $page->getId(),
                        'SEARCH_RESULT_TITLE'         => $page->getTitle(),
                        'SEARCH_RESULT_CONTENT_TITLE' => $page->getContentTitle(),
                        'SEARCH_RESULT_SLUG'          => $page->getSlug(),
                        'SEARCH_RESULT_LANG'          => \FWLanguage::getLanguageCodeById($page->getLang()),
                    ));
                    
                    $this->template->parse('search_result_row');
                }
            } else {
                $this->template->setVariable(array(
                    'TXT_SEARCH_NO_RESULTS' => sprintf($_ARRAYLANG['TXT_SEARCH_NO_RESULTS'], $this->term),
                ));
            }
        } else {
            $this->template->setVariable(array(
                'TXT_SEARCH_NO_TERM' => $_ARRAYLANG['TXT_SEARCH_NO_TERM'],
            ));
        }
    }
    
    /**
     * Gets the search query builder.
     * Searches for slug, title and content title by the given search term.
     * 
     * @return  QueryBuilder  $qb
     */
    private function getSearchQueryBuilder()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
           ->from('Cx\Model\ContentManager\Page', 'p')
           ->where(
               $qb->expr()->orx(
                   $qb->expr()->like('p.slug', ':searchTerm'),
                   $qb->expr()->like('p.title', ':searchTerm'),
                   $qb->expr()->like('p.contentTitle', ':searchTerm')
               )
           )
           ->setParameter('searchTerm', '%'.$this->term.'%');
        
        return $qb;
    }
    
    /**
     * Gets the searched pages as array.
     * 
     * @return  array  $pages  \Cx\Model\ContentManager\Page
     */
    private function getSearchedPages()
    {
        global $_CONFIG;
        
        $qb = $this->getSearchQueryBuilder();
        $qb->setFirstResult($this->pos)->setMaxResults($_CONFIG['corePagingLimit']);
        $pages = $qb->getQuery()->getResult();
        
        return $pages;
    }
    
    /**
     * Counts the searched pages.
     * 
     * @return  int  $countPages
     */
    private function countSearchedPages()
    {
        $qb = $this->getSearchQueryBuilder();
        $pages = $qb->getQuery()->getResult();
        $countPages = count($pages);
        
        return $countPages;
    }
}

?>
