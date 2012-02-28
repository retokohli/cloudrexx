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
		try {
		    // browsers will pass rendering of application/* MIMEs to other applications, usually.
		    // Skip the following line for debugging, if so desired
		    header('Content-Type: application/json');

		    // CSRF protection adds CSRF info to anything it's able to find. Disable it whenever
		    // outputting json
		    $csrf_tags = ini_get('url_rewriter.tags');
		    ini_set('url_rewriter.tags', '');

		    $output = call_user_func(
					     array($this->adapters[$_GET['object']], $_GET['act']),
					     array('get' => $_GET, 'post' => $_POST)
					     );

		    return json_encode(array(
					     'status' => 'success',
					     'data'   => $output
					     ));
		}
		catch (Exception $e) {
		    return json_encode(array(
					     'status' => 'error',
					     'message'   => $e->getMessage()
					     ));
		}

		// Just a reminder to switch csrf prot back on after being done outputting json. This
		// will never get called
		ini_set('url_rewriter.tags', $csrf_tags);
	    }
	}
}

?>
