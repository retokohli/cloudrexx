<?php
class LinkGeneratorException {}
/**
 * Handles the [[NODE_<ID>]], [[NODE_<ID>_<LANGID>]] placeholders.
 */
class LinkGenerator {
    /**
     * array ( placeholder_name => placeholder_link
     *
     * @var array stores the placeholders found by scan()
     */
    protected $placeholders = array();
    /**
     * @var boolean whether fetch() ran.
     */
    protected $fetchingDone = false;

    public static function parseTemplate(&$content)
    {
        $lg = new LinkGenerator();

        if (!is_array($content)) {
            $arrTemplates = array(&$content);
        } else {
            $arrTemplates = &$content;
        }

        foreach ($arrTemplates as &$template) {
            $lg->scan($template);
        }

        $lg->fetch(Env::get('em'));        

        foreach ($arrTemplates as &$template) {
            $lg->replaceIn($template);
        }
    }

    /**
     * Scans the given string for placeholders and remembers them
     * @param string $content
     */
    public function scan(&$content) {
        $this->fetchingDone = false;
        
        $regex = '/\{(NODE_(\d+)(?:_(\d+))?)\}/';

        $matches = array();
        preg_match_all($regex, $content, $matches);

        if(count($matches) == 0)
            return;

        for($i = 0; $i < count($matches[0]); $i++) {           
            $langId = empty($matches[3][$i]) ? FRONTEND_LANG_ID : $matches[3][$i];
            $this->placeholders[$matches[1][$i]] = array('nodeid' => $matches[2][$i], 'lang' => $langId);
        }
    }

    public function getPlaceholders() {
        return $this->placeholders;
    }

    /**
     * Uses the given Entity Manager to retrieve all links for the placeholders
     * @param EntityManager $em
     */
    public function fetch($em) {
        if($this->placeholders === null)
            throw new LinkGeneratorException('Seems like scan() was never called before calling fetch().');

        $qb = $em->createQueryBuilder();
        $qb->add('select', new Doctrine\ORM\Query\Expr\Select(array('p')));
        $qb->add('from', new Doctrine\ORM\Query\Expr\From('Cx\Model\ContentManager\Page', 'p'));
       
        //build a big or with all the node ids and pages 
        $arrExprs = null;
        $fetchedPages = array();
        foreach($this->placeholders as $placeholder => $data) {
            if (isset($fetchedPages[$data['nodeid']][$data['lang']])) {
                continue;
            }

            $arrExprs[] = $qb->expr()->andx(
                $qb->expr()->eq('p.node', $data['nodeid']),
                $qb->expr()->eq('p.lang', $data['lang'])
            );

            $fetchedPages[$data['nodeid']][$data['lang']] = true;
        }

        //fetch the nodes if there are any in the query
        if($arrExprs) {
            $pageRepo = $em->getRepository('Cx\Model\ContentManager\Page');

            foreach ($arrExprs as $expr) {
                $qb->orWhere($expr);
            }

            $pages = $qb->getQuery()->getResult();
            foreach($pages as $page) {
                $prefix = ASCMS_PATH_OFFSET.'/'.FWLanguage::getLanguageCodeById($page->getLang()).'/';

                $placeholder = 'NODE_'.$page->getNode()->getId().'_'.$page->getLang();
                //build placeholder's value
                $this->placeholders[$placeholder] = $prefix . $pageRepo->getPath($page, true);

                if ($page->getLang() == FRONTEND_LANG_ID) {
                    $placeholder = 'NODE_'.$page->getNode()->getId();
                    //build placeholder's value
                    $this->placeholders[$placeholder] = $prefix . $pageRepo->getPath($page, true);
                }
            }
        }

        //remove the placeholders we could not find a link for
        //(maybe we'll build a 404 link later or store the fails somewhere
        // to notify the user of dead links?)
        foreach($this->placeholders as $placeholder => $data) {
            if(!is_string($data))
                unset($this->placeholders[$placeholder]);
        }

        $this->fetchingDone = true;
    }

    /**
     * Replaces all variables in the given string
     * @var string $string
     */
    public function replaceIn(&$string) {
        if($this->placeholders === null)
            throw new LinkGeneratorException('Usage: scan(), then fetch(), then replace().');
        if($this->fetchingDone === false)
            throw new LinkGeneratorException('Seems like fetch() was not called before calling replace().');

        foreach($this->placeholders as $placeholder => $link) {
            $string = str_replace('{'.$placeholder.'}', $link, $string);
        }
    }
}
