<?php

namespace Cx\Core\Routing\Model\Entity;

/**
// resolving (incl. aliases), virtual language dirs and can be generated from pages and so
 */
class FrontendUrl extends \Cx\Core\Routing\Model\Entity\Url {
    protected $page = null;
    
    /**
     * Returns an Url object for module, cmd and lang
     * @todo There could be more than one page using the same module and cmd per lang
     * @param string $module Module name
     * @param string $cmd (optional) Module command, default is empty string
     * @param int $lang (optional) Language to use, default is FRONTENT_LANG_ID
     * @param array $parameters (optional) HTTP GET parameters to append
     * @param string $scheme (optional) The scheme to use
     * @param boolean $returnErrorPageOnError (optional) If set to TRUE, this method will return an URL object that point to the error page of Cloudrexx. Defaults to TRUE.
     * @return \Cx\Core\Routing\Model\Entity\Url Url object for the supplied module, cmd and lang
     */
    public static function fromModuleAndCmd($module, $cmd = '', $lang = '', $parameters = array(), $scheme = '', $returnErrorPageOnError = true) {
        if ($lang == '') {
            $lang = FRONTEND_LANG_ID;
        }
        $pageRepo = \Env::get('em')->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
        $page = $pageRepo->findOneByModuleCmdLang($module, $cmd, $lang);

        // In case we were unable to locate the requested page, we shall
        // return the URL to the error page
        if (!$page && $returnErrorPageOnError && $module != 'Error') {
            $page = $pageRepo->findOneByModuleCmdLang('Error', '', $lang);
        }

        // In case we were unable to locate the requested page
        // and were also unable to locate the error page, we shall
        // return the URL to the Homepage
        if (!$page && $returnErrorPageOnError) {
            return static::fromDocumentRoot(null, $lang, $scheme);
        }

        // Throw an exception if we still were unable to locate
        // any usfull page till now
        if (!$page) {
        \DBG::stack();
            throw new UrlException("Unable to find a page with MODULE:$module and CMD:$cmd in language:$lang!");
        }

        return static::fromPage($page, $parameters, $scheme, true);
    }
    
    /**
     * Returns an Url object pointing to the documentRoot of the website
     * @param int $lang (optional) Language to use, default is FRONTEND_LANG_ID
     * @param string $scheme (optional) The scheme to use
     * @return \Cx\Core\Routing\Model\Entity\Url Url object for the documentRoot of the website
     */
    public static function fromDocumentRoot($arrParameters = array(), $lang = '', $scheme = '') {
        if (php_sapi_name() == 'cli') {
            return \Cx\Core\Routing\Model\Entity\Url::fromString(
                'file://todo' . $this->cx->getWebsiteOffsetPath()
            );
        }
        if (empty($scheme)) {
            $scheme = empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' ? 'http' : 'https';
        }
        $domainRepository = new \Cx\Core\Net\Model\Repository\DomainRepository();
        $host = $domainRepository->getMainDomain()->getName();
        $cx = \Cx\Core\Core\Controller\Cx::instanciate();
        $request = $cx->getWebsiteOffsetPath();
        $params = '?';
        foreach ($arrParameters as $key=>$value) {
            $params .= $key . '=' . $value . '&';
        }
        $params = substr($params, 0, -1);
        $url = \Cx\Core\Routing\Model\Entity\Url::fromString(
            $scheme . '://' . $host . '/' . $request . $params
        );
        return $url;
    }
    
    /**
     * Returns the URL object for a page
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page Page to get the URL to
     * @param array $parameters (optional) HTTP GET parameters to append
     * @param string $scheme (optional) The scheme to use
     * @return \Cx\Core\Routing\Model\Entity\Url Url object for the supplied page
     */
    public static function fromPage($page, $parameters = array(), $scheme = '') {
        $url = static::fromDocumentRoot($parameters, $page->getLang(), $scheme);
        $url->setPath($url->getPath() . $page->getPath());
        return $url;
    }
    
    public function getPage() {
        if ($this->page) {
            return $this->page;
        }
        
        $em = $this->cx->getDb()->getEntityManager();
        $pageRepo = $em->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
        $page = null;
        
        // is alias
        if ($this->getLanguageCode(false) == null) {
            $pages = $pageRepo->getPagesAtPath($this->getPathWithoutOffset(), null, null, false, \Cx\Core\ContentManager\Model\Repository\PageRepository::SEARCH_MODE_ALIAS_ONLY);
            if (isset($pages['page'])) {
                $page = current($pages['page']);
                $this->page = $page;
                return $page;
            }
        }
        
        // resolve page
        $lang = $this->getLanguageCode();
        $langId = \FWLanguage::getLanguageIdByCode($lang);
        
        if (in_array($this->getPathWithoutOffsetAndLangDir(), array('/', '/index.php'))) {
            // home page
            $page = $pageRepo->findOneByModuleCmdLang('home', '', $langId);
        } else {
            $pages = $pageRepo->getPagesAtPath($this->getPathWithoutOffset(), null, $langId);
            if (isset($pages['page'])) {
                $page = $pages['page'];
            }
        }
        /*if ($page) {
            var_dump($this->getPagePathWithLanguageDirectory($page));
        } else {
            var_dump($page);
        }*/
        $this->page = $page;
        return $page;
    }
    
    public function getPathWithoutOffsetAndLangDir() {
        $path = $this->getPathWithoutOffset();
        $languageCode = $this->getLanguageCode(false);
        if ($languageCode) {
            $path = str_replace('/' . $languageCode, '', $path);
        }
        return $path;
    }
    
    /**
     * Returns the language code this URL points to
     * @param boolean $fallback (optional) If there's no language in the URL, this will return the default language unless this param is set to false
     * @return int|null
     */
    public function getLanguageCode($fallback = true) {
        $resolvePathParts = explode($this->getPathDelimiter(), $this->getPathWithoutOffset());
        if (count($resolvePathParts) < 2) {
            if ($fallback) {
                return \FWLanguage::getLanguageCodeById(\FWLanguage::getDefaultLangId());
            }
            return null;
        }
        if (strlen($resolvePathParts[1]) != 2) {
            if ($fallback) {
                return \FWLanguage::getLanguageCodeById(\FWLanguage::getDefaultLangId());
            }
            return null;
        }
        $activeLanguages = \FWLanguage::getActiveFrontendLanguages();
        if (!isset($activeLanguages[\FWLanguage::getLanguageIdByCode($resolvePathParts[1])])) {
            if ($fallback) {
                return \FWLanguage::getLanguageCodeById(\FWLanguage::getDefaultLangId());
            }
            return null;
        }
        return $resolvePathParts[1];
    }
    
    public function setLanguageCode($langCode) {
        // check if correct lang code
        // get path parts
        // get offset parts
        // check if first non-offset part is lang dir
            // if so: replace
            // otherwise: add between
    }
    
    protected function getPagePathWithLanguageDirectory($page) {
        $langId = $page->getLang();
        $lang = \FWLanguage::getLanguageCodeById($langId);
        return '/' . $lang . $page->getPath();
    }
}

