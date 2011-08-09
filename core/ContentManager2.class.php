<?php

use Doctrine\Common\Util\Debug as DoctrineDebug;

class ContentManager {
	
	var $em = null;

	function __construct() {
		$this->em = Env::em();
	}

	function renderTree() { /*echo '<pre>';
		$pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');           
		$nodeRepo = $this->em->getRepository('Cx\Model\ContentManager\Node');

		$tree = $pageRepo->getTree();

		$previousLevel = 1;
		$indent = "";

		echo "[{\r";

		foreach ($tree as $leaf) {
			$indent = str_repeat("  ", $leaf->getLvl());

			// close child element container
			if ($previousLevel > $leaf->getLvl()) {
				echo "]--\r";
			}
			// open child element container
			if ($previousLevel < $leaf->getLvl()) {
				echo ", \"children\": [\r";
			}

			$previousLevel = $leaf->getLvl();

			echo $indent."\"attr\": { \"id\" : \"node_12
			echo $indent."\"data\": {\r";
			echo $indent."  \"data\": ".$leaf->getId().",\r";
			echo $indent."  \"metadata\": \"...\"";
			
			$children = $leaf->getChildren();
			if ($children[0]) {
				echo ",\r";
				echo $indent."  \"children\": [\r";
			}
			else {
				echo "\r";
			}

			echo $indent."}\r";


			// $pages = $leaf->getPages();
			
			//echo '- '.$leaf->getId().'['.$leaf->getParent()->getId().', '.$leaf->getLvl()."]\r";
		}

		echo "}]\r";
echo '</pre>';
*/
		include('JsonPageTree.class.php');
		$pt = new JsonPageTree($this->em);
		echo $pt->render();
	}
}

?>
