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
     * Doctrine PageRepository
     */
    protected $pageRepo = null;

    /**
     * @param URL $url the url to resolve
     * @param integer $lang the language Id
     * @param $entityManager
     */
    public function __construct($url, $lang, $entityManager) {
        $this->url = $url;
        $this->em = $entityManager;
        $this->lang = $lang;
        $this->pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');

        $this->resolve();
    }

    /**
     * Does the resolving work, extends $this->url with targetPath and params.
     */
    protected function resolve() {
        $path = $this->url->getSuggestedTargetPath();

        //(I) see what the model has for us.
        $result = $this->pageRepo->getPagesAtPath($path, null, $this->lang);

        //(II) sort out errors
        if(!$result)
            throw new ResolverException('Unable to locate page (tried path ' . $path .').');

        if(!$result['page'])
            throw new ResolverException('Unable to locate page for this language. (tried path ' . $path .').');

        //(III) extend our url object with matched path / params
        $this->url->setTargetPath($result['matchedPath']);
        $this->url->setParams($result['unmatchedPath'] . $this->url->getSuggestedParams());

        $this->page = $result['page'];

        /*
          the page we found could be a redirection.
          in this case, the URL object is overwritten with the target details and
          resolving starts over again.
         */
        $target = $this->page->getTarget();
        if($target) {
            if($this->page->isTargetInternal()) {
//TODO: add check for endless/circular redirection (a -> b -> a -> b ... and more complex)
                $id = $this->page->getTargetPageId();
                $qs = $this->page->getTargetQueryString();

                $targetPage = $this->pageRepo->find($id);
                if($targetPage === null)
                    throw new ResolverException('Found invalid redirection target on page "'.$this->page->getTitle().'" with id "'.$this->page->getId().'": tried to redirect to page with id "'. $id .'", which does not exist.');

                $targetPath = $this->pageRepo->getPath($targetPage);

                $this->url->setPath($targetPath.$qs);
                $this->resolve();
            }
            else { //external target - redirect via HTTP 302
                header('Location: '.$target);
                die();
            }
        }
    }

    public function getPage() {
        return $this->page;
    }
}
