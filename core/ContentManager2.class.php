<?php

use Doctrine\Common\Util\Debug as DoctrineDebug;

class ContentManager {
	
	var $em = null;

	function __construct() {
		$this->em = Env::em();
	}

	function renderTree() {
		$pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');           
		$nodeRepo = $this->em->getRepository('Cx\Model\ContentManager\Node');

		$root = $nodeRepo->getRoot();

		$jsondata = $this->tree_to_json($root);

		global $objTemplate;
		$objTemplate->addBlockfile('ADMIN_CONTENT', 'content_manager', 'content_manager.html');
		$objTemplate->touchBlock('content_manager');

		return $jsondata;
	}

	function tree_to_json($tree, $level=0) {
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

			$output .= $indent." {\"attr\" : { \"id\" : \"node_".$node->getId()."\" },\n";

			$output .= $indent."  \"data\" : [\n";

			$languages = array();
			foreach ($node->getPages() as $page) {
				if (in_array($page->getLang(), $languages)) continue;

				if (!empty($languages))	$output .= ",\n";
// TODO: do langs right (affects next 2 lines)
$langs = array("", "de", "en");
				$output .= $indent."    { \"language\" : \"".$langs[$page->getLang()]."\", \"title\" : \"".addslashes($page->getTitle())."\" }";
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
