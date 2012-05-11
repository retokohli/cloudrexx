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
        
    private $em = null;
    private $pageRepo = null;
    private $nodeRepo = null;
    private $logRepo = null;
    private $fallbacks;
    private $messages;

    function __construct() {
        $this->em = Env::em();
        $this->pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');
        $this->nodeRepo = $this->em->getRepository('Cx\Model\ContentManager\Node');
        $this->logRepo = $this->em->getRepository('Gedmo\Loggable\Entity\LogEntry');
        $this->messages = array();
        $this->tz = new DateTimeZone('Europe/Berlin');

        $fallback_lang_codes = FWLanguage::getFallbackLanguageArray();
        $active_langs = FWLanguage::getActiveFrontendLanguages();

        // get all active languages and their fallbacks
        foreach ($active_langs as $lang) {
            $this->fallbacks[FWLanguage::getLanguageCodeById($lang['id'])] = ((array_key_exists($lang['id'], $fallback_lang_codes)) ? FWLanguage::getLanguageCodeById($fallback_lang_codes[$lang['id']]) : null);
        }
    }

   public function getMessagesAsString() {
        return implode("<br />", $this->messages);
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
        $moved_node = $this->nodeRepo->find($_POST['id']);
        $parent_node = $this->nodeRepo->find($_POST['ref']);

        $moved_node->setParent($parent_node);
        $this->em->persist($parent_node);
        $this->em->persist($moved_node);
        $this->em->flush();


        $this->nodeRepo->moveUp($moved_node, true);
        if ($_POST['position'])
            $this->nodeRepo->moveDown($moved_node, $_POST['position']);

        $this->em->persist($moved_node);
        $this->em->persist($parent_node);

        $this->em->flush();
    }

    public function delete() {
        $node = $this->nodeRepo->find($_POST['id']);

        $this->em->remove($node);
        $this->em->flush();
    }

    // Renders a jsTree friendly representation of the Node tree (in json)
        private function renderTree() {
                $root = $this->nodeRepo->getRoot();
                $logs = $this->logRepo->getLatestLogsOfAllPages();

                $jsondata = $this->tree_to_jstree_array($root, $logs);

                return $jsondata;
        }

    private function tree_to_jstree_array($root, $logs) {
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
            $children = $this->tree_to_jstree_array($node, $logs);
            $last_resort = 0;

            foreach ($node->getPages() as $page) {
                // don't display aliases in cm's tree
                if ($page->getType() == "alias") continue 2;

                $data[FWLanguage::getLanguageCodeById($page->getLang())] = array(
                    'language'  => FWLanguage::getLanguageCodeById($page->getLang()),
                    'title'     => $page->getTitle(),
                    'attr'      => array(
                                         'id'        => $page->getId(),
                                         'data-href' => json_encode(
                                                            array(
                                                                'module'     => $page->getModule().' '.$page->getCmd(),
                                                                'lastupdate' => $page->getUpdatedAt()->format('d.m.Y H:i'),
                                                                'user'       => $this->logRepo->getUsernameByLog($logs[$page->getId()]),
                                                            )
                                                        ),
                                         )
                );

                $editingStatus = $page->getEditingStatus();
                if ($page->isActive()) {
                    if ($editingStatus == 'hasDraft') {
                        $publishingStatus = 'publishedwait';
                    }
                    else if ($editingStatus == 'hasDraftWaiting') {
                        $publishingStatus = 'publishedwait';
                    }
                    else {
                        $publishingStatus = 'published';
                    }
                }
                else {
                    if ($editingStatus == 'hasDraft') {
                        $publishingStatus = 'draft';
                    }
                    else if ($editingStatus == 'hasDraftWaiting') {
                        $publishingStatus = 'draftwait';
                    }
                    else {
                        $publishingStatus = 'unpublished';
                    }
                }

                $metadata[$page->getId()] = array(
                    "visibility"=> $page->getStatus(),
                    "publishing"=> $publishingStatus,
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
                        "title"     => array_key_exists($last_resort, $data) ? $data[$last_resort]["title"] : "No Title",
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

