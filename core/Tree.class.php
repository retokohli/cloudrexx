<?php
/**
 * Content Tree
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author             Comvation Development Team <info@comvation.com>
 * @version            1.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

/**
 * This class creates a tree structure as an indexed array object
 *
 * content array provider
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @access		public
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  core
 */
class ContentTree
{
/**
* Dev.status / Public methods for Tree class:
*   1 getTree()               Retrieves an indexed array of the nodes from top to bottom*/

  var $table   = array();
  var $node   = array();
  var $tree   = array();
  var $index = 0;

  var $em = null;

    /**
    * Constructor
    *
    */
	function __construct($langId=null)
	{
		global $objDatabase, $_FRONTEND_LANGID;

        $this->em = Env::em();

		if (!isset($langId)) {
			$langId = $_FRONTEND_LANGID;
		}
        $this->srcTree = $this->em->getRepository('Cx\Model\ContentManager\Page')->getTreeBySlug(null, $langId);
		// $parcat is the starting parent id
		$this->buildTree($this->srcTree);
    }

    function convert($page, $alias) {
//TODO: this conversion is a hack. in the final dump, we'll have module names instead of ids in the module attribute.
//TODO: this means we will need to do exactly the opposite conversion (module2id)
        $m2i = Env::get('module2id');
        return array(
            'catname' => $page->getTitle(),
//TODO:
            'catid' => 0,
//TODO:
            'parcat' => 0,
            'node_id' => $page->getNode()->getId(),
            'displaystatus' => $page->getDisplay(),
            'cmd' => $page->getCmd(),
            'modulename' => $page->getModule(),
            'moduleid' => $m2i[$page->getModule()],
            'lang' => $page->getLang(),
            'startdate' => $page->getStart(),
            'enddate' => $page->getEnd(),
            'protected' => $page->getProtection(),
//TODO:
            'frontend_access_id' => 0,
//TODO:
            'backend_access_id' => 0,
            'alias' => $alias
        );
    }

	function buildTree(&$node, $level = 0, $pathSoFar = '')
	{
        foreach($node as $title => $entry) {
            $page = $entry['__data']['page'];
            $alias = $pathSoFar.$page->getSlug();
            $this->tree[$this->index] = $this->convert($page, $alias);
            $this->tree[$this->index]['level']=$level;
            $this->index++;
            
            unset($entry['__data']);
            $this->buildTree($entry, $level+1, $alias.'/');
        }

        /*
		$list=$this->table[$parcat];
		foreach( $list AS $key => $data )
	    {
  	        $this->tree[$this->index] =$list[$key];
  	        $this->tree[$this->index]['level']=$level;
	    	$this->index++;
			if ((isset($this->table[$key])) AND (($maxlevel>=$level+1) OR ($maxlevel==0)))
			{
			  $this->buildTree($key,$maxlevel,$level+1);
			}
	    }
        */
	}

	function getTree()
	{
		// $parcat is the starting parent id
		// optional $maxLevel is the maximum level, set to 0 to show all levels

		return $this->tree;
	}
}
?>
