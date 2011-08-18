<?php
namespace Cx\Core\Routing;

class ResolverException extends \Exception {};

/**
 * Takes an URL and tries to find the Page.
 */
class Resolver {
    protected $em = null;
    protected $url = null;
    /**
     * language id.
     * @var integer
     */
    protected $lang = null;

    /**
     * the page we found.
     * @var Cx\Model\ContentManager\Page
     */
    protected $page = null;

    /**
     * @param URL $url the url to resolve
     * @param integer $lang the language Id
     * @param $entityManager
     */
    public function __construct($url, $lang, $entityManager) {
        $this->url = $url;
        $this->em = $entityManager;
        $this->lang = $lang;

        $this->resolve();
    }

    /**
     * Does the resolving work, extends $this->url with targetPath and params.
     */
    protected function resolve() {
        $path = $this->url->getPath();

        //(I) see what the model has for us.
        $path = substr($path, 1); //cut leading '/'
        $pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');
        $result = $pageRepo->getPagesAtPath($path, null, $this->lang);

        //(II) sort out errors
        if(!$result)
            throw new ResolverException('Unable to locate page.');

        if(!$result['page'])
            throw new ResolverException('Unable to locate page for this language.');

        //(III) extend our url object with matched path / params
        $this->url->setTargetPath($result['matchedPath']);
        $this->url->setParams($result['unmatchedPath']);

        $this->page = $result['page'];
    }

    public function getPage() {
        return $this->page;
    }
}
