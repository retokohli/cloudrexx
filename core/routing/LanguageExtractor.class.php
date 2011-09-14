<?php
namespace Cx\Core\Routing;

class LanguageExtractorException extends \Exception {};

/**
 * Takes Cx\Core\Routing\URL objects, removes all references to the language from them
 * and finds the language id.
 *
 * URL (example.com/de/a/path) => URL (example.com/a/path), langId
 *
 * This was done to keep language informations inside the URL out of the Routing system, which
 * resolves requests with the target path ('/a/path') and langId only.
 * Doing so also leaves the option to later un-mandatorize the language directories.
 */
class LanguageExtractor {
    /**
     * Maps short language name to id. (e.g. 'de' to 1)
     * @var array ( contrexx_languages.lang => contrexx.languages.id )
     */
    protected $languageIds = array();

    /**
     * Reverse of $languageIds.
     * @var array ( contrexx.languages.id => contrexx_languages.lang )
     */
    protected $languageShortNames = array();


    /**
     * @param $db the $objDatabase.
     * @param $dbPrefix the DBPREFIX
     */
    public function __construct($db, $dbPrefix) {
        //initialize $this->languageIds
        $query = "SELECT id, lang FROM ${dbPrefix}languages";
        $res = $db->Execute($query);

        while(!$res->EOF) {
            $this->languageIds[$res->fields['lang']] = $res->fields['id'];
            $this->languageShortNames[$res->fields['id']] = $res->fields['lang'];
            $res->MoveNext();
        }
    }

    /**
     * @param \Cx\Core\Routing\URL $url where information gets extracted from (URL will be changed to a language-less version of itself
     * @return integer the language id
     */
    public function extractLanguage(&$url) {
        if($url->getTargetPath())
            throw new LanguageExtractorException('Please do not pass an URL that has already been handled by the routing system to extractLanguage().');
        
        //extract the language
        $path = $url->getPath();
        $matches = array();
        preg_match('#^(.*?)/#', $path, $matches);

        if(!isset($matches[1]))
            throw new LanguageExtractorException('No language information found for "' . $url->getDomain() . $url->getPath() . '"');

        $lang = $matches[1];
        if(!isset($this->languageIds[$lang]))
            throw new LanguageExtractorException('Could not find language "' . $lang . '" in URL "'. $url->getDomain() . $url->getPath() . '". is this really a language?');

        $pathWithoutLang = substr($path, strlen($lang.'/'));
        $url->setPath($pathWithoutLang);
        //re-generate suggestions
        $url->suggest();
        return $this->languageIds[$lang];
    }

    /**
     * Add virtual language dir for $langId to $url
     * @param \Cx\Core\Routing\URL $url
     * @param integer $langId
     */
    public function addLanguageDir(&$url, $langId) {
        $path = $url->getPath();

        if(!isset($this->languageShortNames[$langId]))
            throw new LanguageExtractorException('Could not find language with id ' . $langId . ' while trying to add virtual language directory to URL "'. $url->getDomain() . $url->getPath() . '". is this really a language id?');
        
        $lang = $this->languageShortNames[$langId];
        $url->setPath($lang.'/'.$path);
        //re-generate suggestions
        $url->suggest();
    }

    public function getShortNameOfLanguage($id) {
        return $this->languageShortNames[$id];
    }
}