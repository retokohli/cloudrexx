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
require_once ASCMS_CORE_PATH.'/json/JsonAdapter.interface.php';
use \Cx\Core\Json\JsonAdapter;

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
        $this->logRepo = $this->em->getRepository('\Gedmo\Loggable\Entity\LogEntry');
        $this->messages = array();

        $fallback_lang_codes = \FWLanguage::getFallbackLanguageArray();
        $active_langs = \FWLanguage::getActiveFrontendLanguages();

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
        return array('getTree', 'delete', 'multipleDelete', 'move');
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
            $page->setupPath();
            $this->em->persist($page);
        }
        
        $this->em->persist($moved_node);
        $this->em->persist($parent_node);

        $this->em->flush();
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
        $logs = $this->logRepo->getLatestLogsOfAllPages();

        //$actions = array();
        $jsondata = $this->tree_to_jstree_array($root, $logs, !$recursive/*, $actions*/);
        //$jsondata['actions'] = $actions;
        /*print(memory_get_usage());
        die();*/

        return $jsondata;
    }

    /**
     * Converts a tree level to JSON
     * @param Cx\Model\ContentManager\Node $root Root node of the current level
     * @param Array $logs List of all logs (used to get the username)
     * @return String JSON data
     */
    private function tree_to_jstree_array($root, $logs, $flat = false, &$actions = null) {
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
        
        $first = false;
        if ($actions === null) {
            $actions = array();
            $first = true;
        }
        
        $output = array();
        $tree = array();
        foreach ($sorted_tree as $node) {
            $data = array();
            $metadata = array();
            $children = array();
            
            // if this node is expanded (toggled)
            $toggled = (isset($open_nodes[$node->getId()]) &&
                        $open_nodes[$node->getId()]);
            if (!$flat || $toggled) {
                $children = $this->tree_to_jstree_array($node, $logs, $flat, $actions);
            }
            $last_resort = 0;

            foreach ($node->getPages() as $page) {
                // don't display aliases in cm's tree
                if ($page->getType() == \Cx\Model\ContentManager\Page::TYPE_ALIAS)
                    continue 2;

                if (!isset($logs[$page->getId()])) {
                    throw new \Cx\Model\ContentManager\PageException(
                            'Page #' . $page->getId() .
                            ' has no log entries! Please contact your system administrator.'
                    );
                }
                $user = $this->logRepo->getUsernameByLog($logs[$page->getId()]);
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
                                'level'      => $node->getLvl(),
                            )
                        ),
                    ),
                );

                $editingStatus = $page->getEditingStatus();
                if ($page->isActive()) {
                    if ($editingStatus == 'hasDraft') {
                        $publishingStatus = 'publishedwait';
                    } else if ($editingStatus == 'hasDraftWaiting') {
                        $publishingStatus = 'publishedwait';
                    } else {
                        $publishingStatus = 'published';
                    }
                } else {
                    if ($editingStatus == 'hasDraft') {
                        $publishingStatus = 'draft';
                    } else if ($editingStatus == 'hasDraftWaiting') {
                        $publishingStatus = 'draftwait';
                    } else {
                        $publishingStatus = 'unpublished';
                    }
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
                
                $actions[$lang][$node->getId()] = $this->getActions($node->getId(), $lang);
            }
            
            $state = array();
            if (!$node->getChildren()->isEmpty()) {
                $state = array('state' => 'closed');
            } else if ($toggled) {
                $state = array('state' => 'open');
            }

            $tree[] = array_merge(array(
                'attr'     => array(
                    'id'   => 'node_' . $node->getId()
                ),
                'data'     => array_values($data),
                'children' => $children,
                'metadata' => $metadata,
            ), $state);
        }
        
        if ($first) { // only add actions on first (non internal) call
            // moving everything to 'tree' so we can add actions
            $output['tree']    = $tree;
            $output['actions'] = $actions;
        } else {
            $output = $tree;
        }
        
        return($output);
    }
    
    protected function getActions($nodeId, $lang) {
        require_once ASCMS_CORE_PATH . "/ActionsRenderer.class.php";

        $node = $this->nodeRepo->find($nodeId);
        $page = $node->getPage(\FWLanguage::getLanguageIdByCode($lang));
        if ($page != null) {
            return \ActionsRenderer::render($page);
        } else {
            return \ActionsRenderer::renderNew($nodeId, $lang);
        }
    }
}
