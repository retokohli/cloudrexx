<?php

/**
 * Search
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core_module_search
 * @todo        Edit PHP DocBlocks!
 */

namespace Cx\Core\Search;
require ASCMS_CORE_PATH . '/Module.class.php';

/**
 * Search manager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core_module_search
 */
class SearchManager extends \Module
{
    private $em       = null;
    private $db       = null;
    private $init     = null;
    private $pageRepo = null;
    private $nodeRepo = null;
    private $logRepo  = null;
    private $term     = '';
    
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
    }
    
    /**
     * Gets the search results.
     */
    public function getSearchResults()
    {
        $this->template->addBlockfile('ADMIN_CONTENT', 'search', 'search.html');
        
        $this->template->setVariable(array(
            'SEARCH_RESULT_NAME'    => '',
        ));
    }
}

?>
