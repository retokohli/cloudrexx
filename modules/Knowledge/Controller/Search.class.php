<?php
/**
 * Contains the search object
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Stefan Heinemann <sh@comvation.com>
 * @package     contrexx
 * @subpackage  module_knowledge
 */

namespace Cx\Modules\Knowledge\Controller;

/**
 * Search object
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Stefan Heinemann <sh@comvation.com>
 * @package     contrexx
 * @subpackage  module_knowledge
 */
class Search {
    /**
     * An array of objects, containing all interfaces
     *
     * @var array
     */
    private $interfaces = array();

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
        $this->templateFile = ASCMS_MODULE_PATH."/Knowledge/Data/searchTemplate.html";

        // the template system
        $this->tpl = new \Cx\Core\Html\Sigma('');
        $this->tpl->setErrorHandling(PEAR_ERROR_DIE);
        $this->tpl->loadTemplateFile($this->templateFile);

        // make a response object
        $this->response = new SearchResponse();
        $this->interfaces[] = new SearchKnowledge();
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
        $response = json_encode($this->response);

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
