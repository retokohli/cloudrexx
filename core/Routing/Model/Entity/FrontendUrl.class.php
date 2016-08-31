<?php

namespace Cx\Core\Routing\Model\Entity;

/**
// resolving (incl. aliases), virtual language dirs and can be generated from pages and so
 */
class FrontendUrl extends \Cx\Core\Routing\Model\Entity\Url {
    protected $page = null;
    
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

