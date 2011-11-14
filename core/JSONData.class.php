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
    var $fallbacks;
    var $adapters = array();

	function __construct() {
		$this->em = Env::em();
        $this->tz = new DateTimeZone('Europe/Berlin');

        $fallback_lang_codes = FWLanguage::getFallbackLanguageArray();
        $active_langs = FWLanguage::getActiveFrontendLanguages();

        foreach ($active_langs as $lang) {
            $this->fallbacks[FWLanguage::getLanguageCodeById($lang['id'])] = ((array_key_exists($lang['id'], $fallback_lang_codes)) ? FWLanguage::getLanguageCodeById($fallback_lang_codes[$lang['id']]) : null);
        }

        include('JSONNode.class.php');
        $this->adapters['node'] = new JSONNode();
        include('JSONPage.class.php');
        $this->adapters['page'] = new JSONPage();
	}

    // A couple of Stub methods to feed data to/from Doctrine.
    // TODO: We should probably move all of this to a central place for JSON access (through 
    // js:cx) not limited to our current doctrine entities
    // With most generic entities, js leaves a bit of room as to how the data is to be formatted.
    // get_children will probably have to stick with the json format from renderTree, for reasonable
    // jsTree compat.
	function jsondata() {
        if (array_key_exists($_GET['object'], $this->adapters)) {
            // browsers will pass rendering of application/* MIMEs to other applications, usually.
            // Skip the following line for debugging, if so desired
            header('Content-Type: application/json');

            // CSRF protection adds CSRF info to anything it's able to find. Disable it whenever
            // outputting json
            $csrf_tags = ini_get('url_rewriter.tags');
            ini_set('url_rewriter.tags', '');

            return call_user_func(array($this->adapters[$_GET['object']], $_GET['act']));

            // Just a reminder to switch csrf prot back on after being done outputting json. This
            // will never get called
            ini_set('url_rewriter.tags', $csrf_tags);
        }




        elseif ($_POST['page']['id'] == 'new' || $_POST['page']['id'] == 0) {
    		$nodeRepo = $this->em->getRepository('Cx\Model\ContentManager\Node');
      		$pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');

            if ($_POST['page']['node']) {
                $node = $nodeRepo->find($_POST['page']['node']);                
            }

            $updated_page = array_map('contrexx_input2raw', $_POST['page']);

            if (!$node) {
                $node = new \Cx\Model\ContentManager\Node();
                $node->setParent($nodeRepo->getRoot());

                $this->em->persist($node);

                $page = new \Cx\Model\ContentManager\Page();
                $page->setNode($node);
            }
            else {
                foreach ($node->getPages() as $pageCandidate) {
                    $source_page = $pageCandidate;
                    if ($source_page->getLang() == FWLanguage::getLanguageIdByCode($_GET['page']['lang'])) break;
                }
                $page = $pageRepo->translate($source_page, FWLanguage::getLanguageIdByCode($updated_page['lang']), true, true, true);
            }

            $page->setType($updated_page['type'] ? $updated_page['type'] : 'fallback');

            $page->setUpdatedAtToNow();
            $page->setLang(FWLanguage::getLanguageIdByCode($updated_page['lang']));
            $page->setUsername('system');
            $page->setStart(null);
            $page->setEnd(null);
            $page->setContentTitle($updated_page['title']);
            $page->setTitle($updated_page['name']);
            $page->setContentTitle($updated_page['title']);
            $page->setMetatitle($updated_page['metatitle']);
            $page->setMetakeys($updated_page['metakeys']);
            $page->setMetadesc($updated_page['metadesc']);
            $page->setMetarobots($updated_page['metarobots']);

            $page->setContent(preg_replace('/\\[\\[([A-Z0-9_-]+)\\]\\]/', '{\\1}', $updated_page['content']));
            $page->setModule($updated_page['application']);
            $page->setCmd($updated_page['area']);
            $page->setTarget($updated_page['target']);

            $page->setCaching((bool) $updated_page['caching']);

            $skin = $updated_page['skin'];
            if(!$skin)
                $skin = null;
            $page->setSkin($skin);
            $page->setCustomContent($updated_page['customContent']);
            $page->setCssName($updated_page['cssName']);

            if (strlen($updated_page['slug']) > 0) {
                $page->setSlug($updated_page['slug']);
            }

            $this->em->persist($page);
            $this->em->flush();

            die('');
        }
        elseif (intval($_POST['page']['id'])) {
            $updated_page = array_map('contrexx_input2raw', $_POST['page']);

    		$pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');

            $page = $pageRepo->find($updated_page['id']);
            
            if ($updated_page['type']) {
                $page->setType($updated_page['type']);
                $page->setUpdatedAtToNow();
                $page->setTitle($updated_page['name']);
                $page->setContentTitle($updated_page['title']);
                try {
                    $start = new DateTime($updated_page['start'], $this->tz);
                    $end = new DateTime($updated_page['end'], $this->tz);
                } catch (Exception $e) {
                    $start = new DateTime('0000-00-00 00:00', $this->tz);
                    $end = new DateTime('0000-00-00 00:00', $this->tz);
                }
                $page->setStart($start);
                $page->setEnd($end);
                $page->setMetatitle($updated_page['metatitle']);
                $page->setMetakeys($updated_page['metakeys']);
                $page->setMetadesc($updated_page['metadesc']);
                $page->setMetarobots($updated_page['metarobots']);
                $page->setContent(preg_replace('/\\[\\[([A-Z0-9_-]+)\\]\\]/', '{\\1}', $updated_page['content']));
                $page->setModule($updated_page['appliaction']);
                if ($updated_page['area'] == '') { 
                    $updated_page['area'] = null;
                }
                $page->setCmd($updated_page['area']);
                $page->setTarget($updated_page['target']);
                $page->setSlug($updated_page['slug']);
                $page->setCaching((bool) $updated_page['caching']);

                $skin = $updated_page['skin'];
                if(!$skin)
                    $skin = null;
                $page->setSkin($skin);
                $page->setCustomContent($updated_page['customContent']);
                $page->setCssName($updated_page['cssName']);
            }
            elseif ($updated_page['status']) {
                $page->setStatus($updated_page['status']);
            }

            $this->em->persist($page);
            $this->em->flush();

            DoctrineDebug::dump($page);
            die();
        }
	}
}

?>
