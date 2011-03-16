<?php
/**
 * Content Tree
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @version		1.0.0
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
*   1 getTree()               Retrieves an indexed array of the nodes from top to bottom, left to right
*   1 getThisNode()          Retrieves an array of the current nodes
*   0 getPrevious()           Returns the previous Tree_Node object if any
*   0 getNext()               Returns the next Tree_Node object if any
*   0 getParent()             Returns the parent Tree_Node object if any
*   0 hasChildren()           Returns whether this node has child nodes or not
*   0 depth()                 Returns the depth of this node in the tree (zero based)
*   0 isChildOf()             Returns whether this node is a direct child of the given node/tree
*   1 getNodeCount()          Retreives the number of nodes in the collection, optionally recursing
*   0 getThisTree()           Retreives an indexed array of the current node
*/


  var $table   = array();
  var $node   = array();
  var $tree   = array();
  var $index = 0;



    /**
    * Constructor
    *
    */
	function __construct($langId=null)
	{
		global $objDatabase, $_FRONTEND_LANGID;

		if (!isset($langId)) {
			$langId = $_FRONTEND_LANGID;
		}

		$modules = array();
		$objResult = $objDatabase->Execute("SELECT id, name FROM ".DBPREFIX."modules");
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				$modules[$objResult->fields['id']]=$objResult->fields['name'];
				$objResult->MoveNext();
			}
		}


		$sql =  "
			SELECT          n.catid                                  AS catid         ,
							n.parcat                                 AS parcat        ,
							n.catname                                AS catname       ,
							n.displayorder                           AS displayorder  ,
							n.displaystatus                          AS displaystatus ,
							n.username                               AS username      ,
							FROM_UNIXTIME(n.changelog,'%d.%m.%Y %T') AS changelog,
							n.cmd                                    AS cmd               ,
							n.lang                                   AS lang              ,
							n.module                                 AS module            ,
							n.startdate                              AS startdate         ,
							n.enddate                                AS enddate           ,
							n.protected                              AS protected         ,
							n.frontend_access_id                     AS frontend_access_id,
							n.backend_access_id                      AS backend_access_id,
                            a_s.url                                  AS alias_url,
						    settings.setvalue                        AS alias_enable
		               FROM ".DBPREFIX."content_navigation AS n
							LEFT OUTER JOIN ".DBPREFIX."module_alias_target AS a_t ON a_t.url = n.catid
							LEFT OUTER JOIN ".DBPREFIX."module_alias_source AS a_s
								ON  a_t.id        = a_s.target_id
								AND a_s.isdefault = 1
						    LEFT OUTER JOIN ".DBPREFIX."settings            AS settings 
						        ON settings.setmodule = 41
						       AND settings.setname   = 'aliasStatus'

		              WHERE lang=".$langId."
		           ORDER BY parcat ASC, displayorder ASC";
		$objResult = $objDatabase->Execute($sql);
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				$this->node[$objResult->fields['catid']]= array(
					'catid' => $objResult->fields['catid'],
				    'parcat' => stripslashes($objResult->fields['parcat']),
				    'catname' => stripslashes($objResult->fields['catname']),
				    'displayorder' => $objResult->fields['displayorder'],
				    'displaystatus' => $objResult->fields['displaystatus'],
				    'changelog' => $objResult->fields['changelog'],
				    'cmd' => $objResult->fields['cmd'],
				    'modulename' => $modules[$objResult->fields['module']],
				    'moduleid' => $objResult->fields['module'],
				    'lang' => $objResult->fields['lang'],
				    'startdate' => $objResult->fields['startdate'],
				    'enddate' => $objResult->fields['enddate'],
				    'protected' => $objResult->fields['protected'],
				    'frontend_access_id' => $objResult->fields['frontend_access_id'],
				    'backend_access_id' => $objResult->fields['backend_access_id'],
                    'alias'             => $objResult->fields['alias_enable'] ? $objResult->fields['alias_url'] : '' 
				    );

				$this->table[$objResult->fields['parcat']][$objResult->fields['catid']]= array(
					'catid' => $objResult->fields['catid'],
				    'parcat' => stripslashes($objResult->fields['parcat']),
				    'catname' => stripslashes($objResult->fields['catname']),
				    'displayorder' => $objResult->fields['displayorder'],
				    'displaystatus' => $objResult->fields['displaystatus'],
				    'changelog' => $objResult->fields['changelog'],
				    'cmd' => $objResult->fields['cmd'],
				    'modulename' => $modules[$objResult->fields['module']],
				    'moduleid' => $objResult->fields['module'],
				    'lang' => $objResult->fields['lang'],
				    'startdate' => $objResult->fields['startdate'],
				    'enddate' => $objResult->fields['enddate'],
				    'protected' => $objResult->fields['protected'],
				    'frontend_access_id' => $objResult->fields['frontend_access_id'],
				    'backend_access_id' => $objResult->fields['backend_access_id'],
                    'alias'             => $objResult->fields['alias_enable'] ? $objResult->fields['alias_url'] : '',
				    'level' => '0'
				    );
				$objResult->MoveNext();
			}
		}
		// $parcat is the starting parent id
		// optional $maxLevel is the maximum level, set to 0 to show all levels
		$this->buildTree($parcat=0,$maxlevel=0,$level=0);
	}


	function buildTree($parcat=0,$maxlevel=0,$level=0)
	{
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
	}


	function getNodeCount()
	{
		return count($this->table);
	}



	function getThisNode($nodeId)
	{
		if(!empty($nodeId))
		return $this->node[$nodeId];
	}


	function getTree()
	{
		// $parcat is the starting parent id
		// optional $maxLevel is the maximum level, set to 0 to show all levels

		return $this->tree;
	}
}
?>
