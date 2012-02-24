<?php

/**
 * JSON Export for the node structure
 *
 * @copyright   Comvation AG
 * @author      Comvation Engineering Team
 * @package     contrexx
 * @subpackage  admin
 */

use Doctrine\Common\Util\Debug as DoctrineDebug;

class JSONNode {
        
        var $em = null;
    var $fallbacks;

        function __construct() {
                $this->em = Env::em();
        $this->tz = new DateTimeZone('Europe/Berlin');

        $fallback_lang_codes = FWLanguage::getFallbackLanguageArray();
        $active_langs = FWLanguage::getActiveFrontendLanguages();

        // get all active languages and their fallbacks
        foreach ($active_langs as $lang) {
            $this->fallbacks[FWLanguage::getLanguageCodeById($lang['id'])] = ((array_key_exists($lang['id'], $fallback_lang_codes)) ? FWLanguage::getLanguageCodeById($fallback_lang_codes[$lang['id']]) : null);
        }
        }

    public function getTree() {
        return $this->renderTree();
    }

    // Data source is in /lib/javascript/jquery/jstree/contrexx.js
    // data in $_POST:
    //  id = id of the moved node
    //  ref = id of the new parent node
    //  position = new position of id as ref's Nth child
    public function move() {
        $nodeRepo = $this->em->getRepository('Cx\Model\ContentManager\Node');
        $moved_node = $nodeRepo->find($_POST['id']);
        $parent_node = $nodeRepo->find($_POST['ref']);

        $moved_node->setParent($parent_node);
        $this->em->persist($parent_node);
        $this->em->persist($moved_node);
        $this->em->flush();


        $nodeRepo->moveUp($moved_node, true);
        if ($_POST['position'])
            $nodeRepo->moveDown($moved_node, $_POST['position']);

        $this->em->persist($moved_node);
        $this->em->persist($parent_node);

        $this->em->flush();
    }

    public function delete() {
        $nodeRepo = $this->em->getRepository('Cx\Model\ContentManager\Node');
        $node = $nodeRepo->find($_POST['id']);

        $this->em->remove($node);
        $this->em->flush();
    }

    // Renders a jsTree friendly representation of the Node tree (in json)
        private function renderTree() {
                $pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');
                $nodeRepo = $this->em->getRepository('Cx\Model\ContentManager\Node');

                $root = $nodeRepo->getRoot();

                $jsondata = $this->tree_to_jstree_array($root);

                return $jsondata;
        }

    private function tree_to_jstree_array($root) {
        $fallback_langs = $this->fallbacks;

        $sorted_tree = array();
        foreach($root->getChildren() as $node) {
            $sorted_tree[$node->getLft()] = $node;
        }
        ksort($sorted_tree);

        $output = array();
        foreach ($sorted_tree as $node) {
            $data = array();
            $metadata = array();
            $children = $this->tree_to_jstree_array($node);
            $last_resort = 0;

            foreach ($node->getPages() as $page) {
                // don't display aliases in cm's tree
                if ($page->getType() == "alias") continue 2;

                $data[FWLanguage::getLanguageCodeById($page->getLang())] = array(
                    "language"  => FWLanguage::getLanguageCodeById($page->getLang()),
                    "title"     => $page->getTitle(),
                    "attr"      => array("id" => $page->getId()) 
                );
                $metadata[$page->getId()] = array(
                    "visibility"=> $page->getStatus(),
                    "publishing"=> $page->isActive() ? 'published' : 'unpublished',
                );
                $last_resort = FWLanguage::getLanguageCodeById($page->getLang());
            }
            foreach ($fallback_langs as $lang => $fallback) {
                if (!array_key_exists($lang, $data) && array_key_exists($fallback, $data)) {
                    $data[$lang] = array(
                        "language"  => $lang,
                        "title"     => $data[$fallback]["title"],
                        "attr"      => array(
                            "id"    => "0"
                        )
                    );
                    $metadata[0] = array(
                        "visibility"=> "active",
                        "publishing"=> "unpublished"
                    );
                }
                elseif (!array_key_exists($lang, $data)) {
                    $data[$lang] = array(
                        "language"  => $lang,
                        "title"     => $data[$last_resort]["title"],
                        "attr"      => array(
                            "id"    => "0"
                        )
                    );
                    $metadata[0] = array(
                        "visibility"=> "active",
                        "publishing"=> "unpublished"
                    );
                }
            }

            $output[] = array(
                "attr"      => array(
                    "id"    =>  "node_".$node->getId()
                ),
                "data"      => array_values($data),
                "children"  => $children,
                "metadata"  => $metadata
            );
        }

        return($output);
    }
}

