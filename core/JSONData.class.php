<?php

/**
 * JSON Interface to Contrexx Doctrine Database
 *
 * @copyright   Comvation AG
 * @author      Comvation Engineering Team
 * @package     contrexx
 * @subpackage  admin
 */

use Doctrine\Common\Util\Debug as DoctrineDebug;

class JSONData {
	
	var $em = null;

	function __construct() {
		$this->em = Env::em();
	}

    // A couple of Stub methods to feed data to/from Doctrine.
    // TODO: We should probably move all of this to a central place for JSON access (through 
    // js:cx) not limited to our current doctrine entities
    // With most generic entities, js leaves a bit of room as to how the data is to be formatted.
    // get_children will probably have to stick with the json format from renderTree, for reasonable
    // jsTree compat.
	function jsondata() {
		if ($_GET['operation'] == 'get_children') {
			return $this->renderTree();
		}
    // Data source is in /lib/javascript/jquery/jstree/contrexx.js
    // data in $_POST:
    //  id = id of the moved node
    //  ref = id of the new parent node
    //  position = new position of id as ref's Nth child
        elseif ($_GET['operation'] == 'move_node') {

            $nodeRepo = $this->em->getRepository('Cx\Model\ContentManager\Node');
            $moved_node = $nodeRepo->find($_POST['id']);
            $parent_node = $nodeRepo->find($_POST['ref']);

            $moved_node->setParent($parent_node);
            $this->em->persist($parent_node);
            $this->em->persist($moved_node);
            $this->em->flush();

/* following block is for changes in nestedset ordering. needs to be fixed and tested with a known good tree.
$this->em->getConfiguration()->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());

//$nodeRepo->verify();
//$em->clear();

            $nodeRepo->moveDown($moved_node, true);
            $nodeRepo->moveDown($moved_node, $_POST['position']);

    // TODO: Changes in ordering seemingly aren't persisted
            $this->em->persist($moved_node);
            $this->em->persist($parent_node);

            $this->em->flush();
*/
            die();
        }
		elseif ($_GET['class'] == 'page' && $_GET['action'] == 'get') {
    		$pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');
		    $page = $pageRepo->find($_GET['id']);

            $page = Array(
                'id'            =>  $page->getId(),
                'title'         =>  $page->getTitle(),
                'content'       =>  $page->getContent(),
                'customContent' =>  $page->getCustomContent(),
                'cssName'       =>  $page->getCssName(),
                'metatitle'     =>  $page->getMetatitle(),
                'metadesc'      =>  $page->getMetadesc(),
                'metakeys'      =>  $page->getMetakeys(),
                'metarobots'    =>  $page->getMetarobots(),
                'start'         =>  $page->getStart()->format('d.m.Y H:i:s'),
                'end'           =>  $page->getEnd()->format('d.m.Y H:i:s'),
                'editingStatus' =>  $page->getEditingStatus(),
                'display'       =>  $page->getDisplay(),
                'active'        =>  $page->getActive(),
                'target'        =>  $page->getTarget(),
                'module'        =>  $page->getModule(),
                'cm_cmd'        =>  $page->getCmd(),
                'node'          =>  $page->getNode()->getId(),
                'skin'          =>  $page->getSkin(),
                'caching'       =>  $page->getCaching(),
                'user'          =>  $page->getUser(),
                'type'          =>  $page->getType(),
                'username'      =>  $page->getUsername(),
                'updatedAt'     =>  $page->getUpdatedAt(),
                'protection'    =>  $page->getProtection(),
                'slug'          =>  $page->getSlug()
            );
                    
            // browsers will pass rendering of application/* MIMEs to other applications, usually.
            // Skip the following line for debugging, if so desired
            header('Content-Type: application/json');

            // CSRF protection adds CSRF info to anything it's able to find. Disable it whenever
            // outputting json
            $csrf_tags = ini_get('url_rewriter.tags');
            ini_set('url_rewriter.tags', '');

            die(json_encode($page));

            // Just a reminder to switch csrf prot back on after being done outputting json. This
            // will never get called
            ini_set('url_rewriter.tags', $csrf_tags);
		}
        elseif ($_POST['id'] == 'new') {

error_reporting(E_ALL);
ini_set('display_errors', true);

    		$nodeRepo = $this->em->getRepository('Cx\Model\ContentManager\Node');
            $node = new \Cx\Model\ContentManager\Node();
            $node->setParent($nodeRepo->getRoot());

            $this->em->persist($node);

            $page = new \Cx\Model\ContentManager\Page();
            $page->setNode($node);

            //$page->setType($_POST['type']);
            $page->setTitle($_POST['title']);
            // Start/End
            $page->setMetakeys($_POST['metakeys']);
            $page->setMetadesc($_POST['metadesc']);
            $page->setContent($_POST['content']);
            //$page->setModule($_POST['module']);
            //$page->setCmd($_POST['cm_cmd']);
            $page->setTarget($_POST['target']);
            $page->setSlug($_POST['slug']);

            $this->em->persist($page);
            $this->em->flush();

            DoctrineDebug::dump($page);
            die();
        }
        elseif (intval($_POST['id'])) {

error_reporting(E_ALL);
ini_set('display_errors', true);

    		$pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');
            $page = $pageRepo->find($_POST['id']);

            $page->setType($_POST['type']);
            $page->setTitle($_POST['title']);
            // Start/End
            $page->setMetakeys($_POST['metakeys']);
            $page->setMetadesc($_POST['metadesc']);
            $page->setContent($_POST['content']);
            $page->setModule($_POST['module']);
            $page->setCmd($_POST['cm_cmd']);
            $page->setTarget($_POST['target']);
            $page->setSlug($_POST['slug']);

            $this->em->persist($page);
            $this->em->flush();

            DoctrineDebug::dump($page);
            die();
        }
        elseif ($_GET['class'] == 'node') {
            $nodeRepo = $this->em->getRepository('Cx\Model\ContentManager\Node');
            $node = $nodeRepo->find($_GET['id']);

error_reporting(E_ALL);
ini_set('display_errors', true);


            $node->setStatus($_POST['status']);
            die('done');
        }
	}

    // Renders a jsTree friendly representation of the Node tree (in json)
	function renderTree() {
		$pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');
		$nodeRepo = $this->em->getRepository('Cx\Model\ContentManager\Node');

		$root = $nodeRepo->getRoot();

		$jsondata = $this->tree_to_json($root);

		return $jsondata;
	}

	private function tree_to_json($tree, $level=0) {
    // This thing can get quite complicated, json's quaint syntax does that to you. Should
    // produce syntactically correct and correctly indented json, though.
    // If unsure about the json ouput, feed it to jQ's parseJSON or jsonlint.com
		$indent = str_repeat("  ", $level);
		$output = "";

		$output .= "[\n";

		$firstrun = true;

		foreach($tree->getChildren() as $node) {
			if ($firstrun) {
				$firstrun = false;
			}
			else {
				$output .= ",\n";
			}

			$output .= $indent." {\"attr\" : { \"id\" : \"node_".$node->getId()."\"},\n";

			$output .= $indent."  \"data\" : [\n";

			$languages = array();
			foreach ($node->getPages() as $page) {
				if (in_array($page->getLang(), $languages)) continue;

				if (!empty($languages))	$output .= ",\n";
				$output .= $indent."    { \"language\" : \"".FWLanguage::getLanguageCodeById($page->getLang())."\", \"title\" : \"".addslashes($page->getTitle())."\", \"attr\": {\"id\" : \"".$page->getId()."\"} }";
				$languages[] = $page->getLang();
			}
			$output .= $indent."\n".$indent."  ],\n";

			if (sizeof($node->getChildren())) {
				$output .= $indent."  \"children\" : ";
				$output .= $this->tree_to_json($node, $level+1);				
			}

			$output .= $indent."  \"icon\" : \"page\",\n";
			$output .= $indent."  \"metadata\" : {\n";
			$output .= $indent."    \"status\" : \"active\",\n";
			$output .= $indent."    \"emblem\" : [\"redirect\"]\n";
			$output .= $indent."  }\n";
			$output .= $indent." }";
		}

		$output .= "\n".$indent."]";

		if ($level > 0) $output .= ",";
		$output .= "\n";

		return $output;
	}
}

?>
