<?php
/**
 * JSON Adapter for Cx\Model\ContentManager\Node
 * @copyright   Comvation AG
 * @author      Florian Schuetz <florian.schuetz@comvation.com>
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  core/json
 */

namespace Cx\Core\Json\Adapter\ContentManager;
use \Cx\Core\Json\JsonAdapter;
use \Cx\Core\ContentManager\ContentManagerException;

/**
 * JSON Adapter for Cx\Model\ContentManager\Node
 * @copyright   Comvation AG
 * @author      Florian Schuetz <florian.schuetz@comvation.com>
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  core/json
 */
class JsonNode implements JsonAdapter {

    /**
     * Reference to the Doctine EntityManager
     * @var \Doctrine\ORM\EntityManager 
     */
    private $em = null;
    
    /**
     * Reference to the Doctrine NodeRepo
     * @var \Cx\Model\ContentManager\Repository\NodeRepository
     */
    private $nodeRepo = null;
    
    /**
     * Reference to the Doctrine PageRepo
     * @var \Cx\Model\ContentManager\Repository\PageRepository
     */
    private $pageRepo = null;
    
    /**
     * Reference to the Doctring LogRepository
     * @var \Gedmo\Loggable\Entity\Repository\CxLogEntryRepository
     */
    private $logRepo = null;
    
    /**
     * List of fallback languages
     * @var Array lang=>fallback lang
     */
    private $fallbacks = array();
    
    /**
     * List of messages
     * @var Array 
     */
    private $messages;

    /**
     * Constructor
     */
    public function __construct() {
        $this->em = \Env::em();
        $this->nodeRepo = $this->em->getRepository('\Cx\Model\ContentManager\Node');
        $this->pageRepo = $this->em->getRepository('\Cx\Model\ContentManager\Page');
        $this->logRepo  = $this->em->getRepository('\Gedmo\Loggable\Entity\LogEntry');
        $this->messages = array();

        $fallback_lang_codes = \FWLanguage::getFallbackLanguageArray();
        $active_langs        = \FWLanguage::getActiveFrontendLanguages();

        // get all active languages and their fallbacks
        foreach ($active_langs as $lang) {
            $this->fallbacks[\FWLanguage::getLanguageCodeById($lang['id'])] = ((array_key_exists($lang['id'], $fallback_lang_codes)) ? \FWLanguage::getLanguageCodeById($fallback_lang_codes[$lang['id']]) : null);
        }
    }
    
    /**
     * Returns the internal name used as identifier for this adapter
     * @return String Name of this adapter
     */
    public function getName() {
        return 'node';
    }
    
    /**
     * Returns an array of method names accessable from a JSON request
     * @return array List of method names
     */
    public function getAccessableMethods() {
        return array('getTree', 'delete', 'multipleDelete', 'move', 'getPageTitlesTree');
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString() {
        return implode('<br />', $this->messages);
    }

    /**
     * Returns the Node tree rendered for JS
     * @return String JSON data 
     */
    public function getTree($parameters) {
        global $_CORELANG;
        
        // Global access check
        if (!\Permission::checkAccess(6, 'static', true) ||
                !\Permission::checkAccess(35, 'static', true)) {
            throw new \ContentManagerException($_CORELANG['TXT_CORE_CM_USAGE_DENIED']);
        }
        
        $nodeId = 0;
        if (isset($parameters['get']) && isset($parameters['get']['nodeid'])) {
            $nodeId = contrexx_input2raw($parameters['get']['nodeid']);
        }
        if (isset($parameters['get']) && isset($parameters['get']['page'])) {
            $pageId = contrexx_input2raw($parameters['get']['page']);
            $page = $this->pageRepo->findOneBy(array('id' => $pageId));
            $node = $page->getNode()->getParent();
            // #node_{id},#node_{id}
            $openNodes = array();
            while ($node && $node->getId() != $nodeId) {
                $openNodes[] = '#node_' . $node->getId();
                $node = $node->getParent();
            }
            if (!isset($_COOKIE['jstree_open'])) {
                $_COOKIE['jstree_open'] = '';
            }
            $openNodes2 = explode(',', $_COOKIE['jstree_open']);
            if ($openNodes2 == array(0=>'')) {
                $openNodes2 = array();
            }
            $openNodes = array_merge($openNodes, $openNodes2);
            $_COOKIE['jstree_open'] = implode(',', $openNodes);
        }

        $recursive = false;
        if ($nodeId == 0 &&
                isset($parameters['get']) &&
                isset($parameters['get']['recursive']) &&
                $parameters['get']['recursive'] == 'true') {
            $recursive = true;
        }
        return $this->renderTree($nodeId, $recursive);
    }

    /**
     * Moves a node.
     * 
     * The following arguments are used:
     * id = id of the moved node
     * ref = id of the new parent node
     * position = new position of id as ref's Nth child
     * 
     * Data source is in /lib/javascript/jquery/jstree/contrexx.js
     * @param array $arguments Arguments passed from JsonData
     */
    public function move($arguments) {
        global $_CORELANG;
        
        // Global access check
        if (!\Permission::checkAccess(6, 'static', true) ||
                !\Permission::checkAccess(35, 'static', true)) {
            throw new \ContentManagerException($_CORELANG['TXT_CORE_CM_USAGE_DENIED']);
        }
        if (!\Permission::checkAccess(160, 'static', true)) {
            throw new \ContentManagerException($_CORELANG['TXT_CORE_CM_MOVE_DENIED']);
        }
        
        $moved_node = $this->nodeRepo->find($arguments['post']['id']);
        $parent_node = $this->nodeRepo->find($arguments['post']['ref']);

        $moved_node->setParent($parent_node);
        $this->em->persist($parent_node);
        $this->em->persist($moved_node);
        $this->em->flush();


        $this->nodeRepo->moveUp($moved_node, true);
        if ($arguments['post']['position'])
            $this->nodeRepo->moveDown($moved_node, $arguments['post']['position']);

        foreach ($moved_node->getPages() as $page) {
            $page->setupPath($page->getLang());
            $this->em->persist($page);
        }
        
        $this->em->persist($moved_node);
        $this->em->persist($parent_node);

        $this->em->flush();
        
        $nodeLevels[$moved_node->getId()] = $moved_node->getLvl();
        foreach ($moved_node->getChildren() as $node) {
            $nodeLevels[$node->getId()] = $node->getLvl();
        }
        
        return array(
            'nodeLevels' => $nodeLevels,
        );
    }

    /**
     * Deletes a node
     * @param array $arguments Arguments passed from JsonData
     */
    public function delete($arguments) {
        global $_CORELANG;
        
        // Global access check
        if (!\Permission::checkAccess(6, 'static', true) ||
                !\Permission::checkAccess(35, 'static', true)) {
            throw new \ContentManagerException($_CORELANG['TXT_CORE_CM_USAGE_DENIED']);
        }
        if (!\Permission::checkAccess(26, 'static', true)) {
            throw new \ContentManagerException($_CORELANG['TXT_CORE_CM_DELETE_DENIED']);
        }
        
        $node = $this->nodeRepo->find($arguments['post']['id']);
        
        $this->em->getConnection()->executeQuery('SET FOREIGN_KEY_CHECKS = 0');
        
        $this->em->remove($node);
        $this->em->flush();
        
        $this->em->getConnection()->executeQuery('SET FOREIGN_KEY_CHECKS = 1');
    }
    
    /**
     * Deletes multiple nodes.
     * 
     * @param  array  $param  Client parameters.
     */
    public function multipleDelete($params) {
        $post   = $params['post'];
        $return = array();
        
        foreach ($post['nodes'] as $nodeId) {
            $data['post']['id'] = $nodeId;
            $this->delete($data);
            if ($nodeId == $post['currentNodeId']) {
                $return['deletedCurrentPage'] = true;
            }
        }
        
        return $return;
    }

    /**
     * Renders a jsTree friendly representation of the Node tree (in json)
     * @return String JSON data
     */
    private function renderTree($rootNodeId = 0, $recursive = false) {
        if ($rootNodeId == 0) {
            $root = $this->nodeRepo->getRoot();
        } else {
            $root = current($this->nodeRepo->findById($rootNodeId));
        }
        if (!is_object($root)) {
            throw new \Exception('Node not found (#' . $rootNodeId . ')');
        }

        $jsondata = $this->tree_to_jstree_array($root, !$recursive/*, $actions*/);

        return $jsondata;
    }

    /**
     * Converts a tree level to JSON
     * @param Cx\Model\ContentManager\Node $root Root node of the current level
     * @param Array $logs List of all logs (used to get the username)
     * @return String JSON data
     */
    private function tree_to_jstree_array($root, $flat = false, &$actions = null) {
        $fallback_langs = $this->fallbacks;

        $sorted_tree = array();
        foreach ($root->getChildren() as $node) {
            $sorted_tree[$node->getLft()] = $node;
        }
        ksort($sorted_tree);

        // get open nodes
        $open_nodes = array();
        if (isset($_COOKIE['jstree_open'])) {
            $tmp_open_nodes = explode(',', $_COOKIE['jstree_open']);
            foreach ($tmp_open_nodes as $node) {
                $node_id = substr($node, 6);
                $open_nodes[$node_id] = true;
            }
        }
        
        $output     = array();
        $tree       = array();
        $nodeLevels = array();
        foreach ($sorted_tree as $node) {
            $data       = array();
            $metadata   = array();
            $children   = array();
            
            // if this node is expanded (toggled)
            $toggled = (isset($open_nodes[$node->getId()]) &&
                        $open_nodes[$node->getId()]);
            if (!$flat || $toggled) {
                $children = $this->tree_to_jstree_array($node, $flat);
            }
            $last_resort = 0;

            foreach ($node->getPages() as $page) {
                // don't display aliases in cm's tree
                if ($page->getType() == \Cx\Model\ContentManager\Page::TYPE_ALIAS) {
                    continue 2;
                }

                $user = $page->getUpdatedBy();
                $data[\FWLanguage::getLanguageCodeById($page->getLang())] = array(
                    'language' => \FWLanguage::getLanguageCodeById($page->getLang()),
                    'title' => $page->getTitle(),
                    'attr' => array(
                        'id' => $page->getId(),
                        'data-href' => json_encode(
                            array(
                                'slug'       => $page->getSlug(),
                                'module'     => $page->getModule() . ' ' . $page->getCmd(),
                                'lastupdate' => $page->getUpdatedAt()->format('d.m.Y H:i'),
                                'user'       => $user,
                            )
                        ),
                    ),
                );

                $editingStatus = $page->getEditingStatus();
                if ($page->isActive()) {
                    if ($editingStatus == 'hasDraft') {
                        $publishingStatus = 'published draft';
                    } else if ($editingStatus == 'hasDraftWaiting') {
                        $publishingStatus = 'published draft waiting';
                    } else {
                        $publishingStatus = 'published';
                    }
                } else {
                    if ($editingStatus == 'hasDraft') {
                        $publishingStatus = 'unpublished draft';
                    } else if ($editingStatus == 'hasDraftWaiting') {
                        $publishingStatus = 'unpublished draft waiting';
                    } else {
                        $publishingStatus = 'unpublished';
                    }
                }
                if ($page->isBackendProtected() &&
                        !\Permission::checkAccess($page->getBackendAccessId(), 'dynamic', true)) {
                    $publishingStatus .= ' locked';
                }
                
                $metadata[$page->getId()] = array(
                    'visibility' => $page->getStatus(),
                    'publishing' => $publishingStatus,
                );
                $last_resort = \FWLanguage::getLanguageCodeById($page->getLang());
            }
            
            foreach ($fallback_langs as $lang => $fallback) {
                // fallback can be false, array_key_exists does not like booleans
                if (!$fallback) {
                    $fallback = null;
                }
                if (!array_key_exists($lang, $data) && array_key_exists($fallback, $data)) {
                    $data[$lang]['language'] = $lang;
                    $data[$lang]['title'] = $data[$fallback]['title'];

                    if ($data[$fallback]['attr']['id'] == 'broken') {
                        $data[$lang]['attr']['id'] = 'broken';
                    } else {
                        $data[$lang]['attr']['id'] = '0';
                    }
                } else if (!array_key_exists($lang, $data)) {
                    $data[$lang]['language'] = $lang;

                    if (array_key_exists($last_resort, $data)) {
                        $data[$lang]['title']      = $data[$last_resort]['title'];
                        $data[$lang]['attr']['id'] = '0';
                    } else {
                        $data[$lang]['title']      = 'No Title';
                        $data[$lang]['attr']['id'] = 'broken';
                    }
                }

                $metadata[0] = array(
                    'visibility' => 'active',
                    'publishing' => 'unpublished',
                );
                $metadata['broken'] = array(
                    'visibility' => 'broken',
                    'publishing' => 'unpublished',
                );
            }
            
            $state = array();
            if (!$node->getChildren()->isEmpty()) {
                if ($toggled) {
                    $state = array('state' => 'open');
                } else {
                    $state = array('state' => 'closed');
                }
            }

            $nodeLevels[$node->getId()] = $node->getLvl();
            if (isset($children['nodeLevels'])) {
                $nodeLevels = $nodeLevels + $children['nodeLevels'];
            }

            $tree[] = array_merge(array(
                'attr'     => array(
                    'id'   => 'node_' . $node->getId()
                ),
                'data'     => array_values($data),
                'children' => isset($children['tree']) ? $children['tree'] : array(),
                'metadata' => $metadata,
            ), $state);
        }
        $output['tree']       = $tree;
        $output['nodeLevels'] = $nodeLevels;
        
        return($output);
    }
    
    /**
     * Gets the page titles of all languages.
     * 
     * @return  array  $tree
     */
    public function getPageTitlesTree()
    {        
        $root = $this->nodeRepo->getRoot();
        $tree = $this->buildPageTitlesTree($root);
        
        return $tree;
    }
    
    /**
     * Builds a tree with all page titles.
     * 
     * @param   array  $root
     */
    protected function buildPageTitlesTree($root)
    {   
        $sortedTree = array();
        foreach ($root->getChildren() as $node) {
            $sortedTree[$node->getLft()] = $node;
        }
        ksort($sortedTree);
        
        $tree     = array();
        $children = array();
        
        foreach ($sortedTree as $node) {
            $children = $this->buildPageTitlesTree($node);
            
            $nodeId   = $node->getId();
            $langCode = 0;
            
            foreach ($node->getPages() as $page) {
                $langCode = \FWLanguage::getLanguageCodeById($page->getLang());
                
                $tree[$nodeId][$langCode]['title'] = $page->getTitle();
                $tree[$nodeId][$langCode]['id'] = $page->getId();
                $tree[$nodeId][$langCode]['level'] = $node->getLvl();
            }
            
            foreach ($this->fallbacks as $lang => $fallback) {
                $fallback = $fallback ? $fallback : null;
                if (isset($tree[$nodeId]) && !array_key_exists($lang, $tree[$nodeId]) && array_key_exists($fallback, $tree[$nodeId])) {
                    $tree[$nodeId][$lang]['title'] = $tree[$nodeId][$fallback]['title'];
                    $tree[$nodeId][$lang]['level'] = $tree[$nodeId][$fallback]['level'];
                } else if (isset($tree[$nodeId]) && !array_key_exists($lang, $tree[$nodeId])) {
                    if (array_key_exists($langCode, $tree[$nodeId])) {
                        $tree[$nodeId][$lang]['title'] = $tree[$nodeId][$langCode]['title'];
                        $tree[$nodeId][$lang]['level'] = $tree[$nodeId][$langCode]['level'];
                    }
                }
            }
            
            $tree += $children;
        }
        
        return $tree;
    }
}
