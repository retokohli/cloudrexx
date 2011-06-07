<?php
/**
 * Contains the search object
 *
 * @author Stefan Heinemann <sh@comvation.com>
 * @copyright Comvation AG <info@comvation.com>
 */

require_once "searchInterface.php";

/**
 * Search object
 *
 * @author Stefan Heinemann <sh@comvation.com>
 * @copyright Comvation AG <info@comvation.com>
 */
class Search {
    /**
     * An array of objects, containing all interfaces
     *
     * @var array
     */
    private $interfaces = array();

    /**
     * The path where the interfaces lie
     *
     * @var string
     */
    private $interfacesPath;

    /**
     * The maximum amount of search results to return
     *
     * Return only the given amount of search results. This is
     * for the ajax on-the-fly search.
     * @var int
     */
    private $maxSearchResults = 6;

    /**
     * Path to the template file
     *
     * @var string
     */
    private $templateFile;

    /**
     * Template object
     *
     * @var object
     */
    private $tpl;

    /**
     * JSON object
     *
     * @var object
     */
    private $json;

    /**
     * The response object
     *
     * @var object
     */
    private $response;

    /**
     * Initialise the whole stuff
     *
     */
    public function __construct()
    {
        // should change when this class is used globally
        $this->interfacesPath = ASCMS_MODULE_PATH."/knowledge/lib/searchInterfaces";
        $this->templateFile = ASCMS_MODULE_PATH."/knowledge/lib/searchTemplate.html";

        require_once(ASCMS_LIBRARY_PATH."/PEAR/Services/JSON.php");
        $this->json = new Services_JSON();

        // the template system
        $this->tpl = new HTML_Template_Sigma('');
        CSRF::add_placeholder($this->tpl);
		$this->tpl->setErrorHandling(PEAR_ERROR_DIE);
		$this->tpl->loadTemplateFile($this->templateFile);

		// make a response object
		$this->response = new searchResponse();

		// get all available interfaces
		$dir = opendir($this->interfacesPath);

        // is this a security issue?
        while (false !== ($file = readdir($dir))) {
            if (preg_match("/^[a-z]+\.php$/i", $file)) {
                include($this->interfacesPath."/".$file);
                list($name) = split("\.", $file);
                $this->interfaces[] = new $name;
            }
        }

        closedir($dir);
    }

    /**
     * Get the result and return them so they can be displayed
     * at the frontend
     */
    public function performSearch()
    {
        $status = 1;


        if (empty($_GET['searchterm'])) {
            // no search term given
            $status = 2;
        } else{
            $searchterm = $_GET['searchterm'];
    		$results = $this->getResults($searchterm);

    		if (count($results) == 0) {
    		    // nothing found
    		    $status = 0;
    		} else {
        		foreach ($results as $result) {
        		    $this->tpl->setVariable(array(
                        "URI"       => $this->makeURI($result['uri']),
                        "TITLE"     => $this->formatTitle($result['title'])
        		    ));
        		    $this->tpl->parse("result");
        		}
                $this->response->content = $this->tpl->get();
    		}
        }

		$this->response->status = $status;
        $response = $this->json->encode($this->response);

		die($response);
    }

    /**
     * Get the results from the interfaces
     *
     * Get the results from every interface. Only take as many
     * as given by the $maxSearchResults variable.
     * @param string $searchterm
     * @return array
     */
    private function getResults($searchterm)
    {
        $results = array();
        $amount = 0;
        $endResult = array();

        $searchterm = $this->formatSearchString($searchterm);
        foreach ($this->interfaces as $interface) {
            $trove = $interface->search($searchterm);
            $amount = $amount + count($trove);
            $results[] = array_reverse($trove);
        }

        $j = 0;
        for ($i = 0; $i < $this->maxSearchResults; $i++) {
            if (!empty($results[$j])) {
                $endResult[] = array_pop($results[$j]);
                $j = (count($results == $j)) ? 0 : $j + 1;
            }
        }

        return $endResult;
    }

    /**
     * Format the URI if needed
     *
     * Not implemented yet
     * @param string $uri
     * @return string
     */
    private function makeURI($uri)
    {
        return $uri;
    }

    /**
     * Format the search string
     *
     * Not implemented yet.
     * Format the search string, e.g. remove unecessary
     * characters.
     * @param string $string
     * @return string
     */
    private function formatSearchString($string)
    {
        return $string;
    }

    private function formatTitle($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, CONTREXX_CHARSET);
    }
}

/**
 * Search response class
 *
 * Helper class for the search response. Is going to be
 * turned into a JSON object for communcation through ajax.
 * @author Stefan Heinemann <sh@comvation.com>
 * @copyright Comvation AG <info@comvation.com>
 */
class searchResponse
{
    /**
     * Status code
     *
     * @var int
     */
    public $status = 1;

    /**
     * Response
     *
     * @var string
     */
    public $content = "";
}


?>
