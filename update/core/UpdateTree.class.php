<?php

class UpdateContentTree extends ContentTree {
    function buildTree($node = null, $level = 0, $pathSoFar = '') {
        if (!$node) {
            $node = $this->em->getRepository('Cx\Core\ContentManager\Model\Entity\Node')->getRoot();
        }
        $nodes = $node->getChildren();
        foreach ($nodes as $node) {//$title => $entry) {
            $page = $node->getPage($this->langId);
            if (!$page) {
                continue;
            }
            $alias = $pathSoFar . $page->getSlug();
            $this->tree[$node->getLft()] = $this->convert($page, $alias);
            $this->tree[$node->getLft()]['level'] = $level;
            ksort($this->tree);

            $this->buildTree($node, $level + 1, $alias . '/');
        }
    }

}
