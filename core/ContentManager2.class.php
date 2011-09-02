<?php

/**
 * Content Manager 2 (Doctrine-based version)
 * @copyright   Comvation AG
 * @author      Comvation Engineering Team
 * @package     contrexx
 * @subpackage  admin
 * @todo        PHPDoc!
 */

use Doctrine\Common\Util\Debug as DoctrineDebug;

/**
 * Content Manager 2 (Doctrine-based version)
 * @copyright   Comvation AG
 * @author      Comvation Engineering Team
 * @package     contrexx
 * @subpackage  admin
 */
class ContentManager {

    var $em = null;


    function __construct()
    {
        $this->em = Env::em();
    }


    function renderCM()
    {
        // Render the Content Manager within our old backend template.
        global $objTemplate;

        $objTemplate->addBlockfile('ADMIN_CONTENT', 'content_manager', 'content_manager.html');
        $objTemplate->touchBlock('content_manager');
        require_once ASCMS_LIBRARY_PATH.'/FRAMEWORK/cxjs/ContrexxJavascript.class.php';
        // TODO: move including of add'l JS dependencies to cx obj from /cadmin/index.html
        $objTemplate->setVariable('CXJS_INIT_JS', ContrexxJavascript::getInstance()->initJs());
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
            $nodeRepo->moveUp($moved_node, true);
            $nodeRepo->moveDown($moved_node, $_POST['position']);

    // TODO: Changes in ordering seemingly aren't persisted
            $this->em->persist($moved_node);
            $this->em->flush();

            die();
        }
        else {

    // TODO: This part should surely be moved to a unified json interface to/fro doctrine.
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
                'start'         =>  $page->getStart(),
                'end'           =>  $page->getEnd(),
                'editingStatus' =>  $page->getEditingStatus(),
                'display'       =>  $page->getDisplay(),
                'active'        =>  $page->getActive(),
                'target'        =>  $page->getTarget(),
                'module'        =>  $page->getModule(),
                'cmd'           =>  $page->getCmd(),
                'node'          =>  $page->getNode()->getId(),
                'skin'          =>  $page->getSkin(),
                'caching'       =>  $page->getCaching(),
                'user'          =>  $page->getUser(),
                'type'          =>  $page->getType(),
                'username'      =>  $page->getUsername(),
                'updatedAt'     =>  $page->getUpdatedAt(),
                'protection'    =>  $page->getProtection(),
                'slug'          =>  $page->getSlug(),
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
    }


    // Renders a jsTree friendly representation of the Node tree (in json)
    function renderTree()
    {
        $pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');
        $nodeRepo = $this->em->getRepository('Cx\Model\ContentManager\Node');

        $root = $nodeRepo->getRoot();

        $jsondata = $this->tree_to_json($root);

    // TODO: I feel we should be able to cerce doctrine to handle utf8 automagically, right?
        return utf8_encode($jsondata);
    }


    function tree_to_json($tree, $level=0)
    {
    // This thing can get quite complicated, json's quaint syntax does that to you. Should
    // produce syntactically correct and correctly indented json, though.
    // If unsure about the json ouput, feed it to jQ's parseJSON or jsonlint.com
        $indent = str_repeat("  ", $level);
        $output = "[\n";
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

                if (!empty($languages))    $output .= ",\n";
    // TODO: do langs right (affects next 2 lines)
    $langs = array("", "de", "en");
                $output .= $indent."    { \"language\" : \"".$langs[$page->getLang()]."\", \"title\" : \"".addslashes($page->getTitle())."\", \"attr\": {\"id\" : \"".$page->getId()."\"} }";
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
