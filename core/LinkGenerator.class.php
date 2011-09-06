<?php
class LinkGeneratorException {}
/**
 * Handles the [[NODE_<ID>_<LANGID>]] placeholders.
 */
class LinkGenerator {
    /**
     * array ( placeholder_name => placeholder_link
     *
     * @var array stores the placeholders found by scan()
     */
    protected $placeholders = null;
    /**
     * @var boolean whether fetch() ran.
     */
    protected $fetchingDone = false;
    /**
     * @var string link prefix (domain/offset)
     */
    protected $prefix = '';

    public function __construct($linkPrefix) {
        $this->prefix = $linkPrefix;
    }
    /**
     * Scans the given string for placeholders and remembers them
     * @param string $content
     */
    public function scan(&$content) {
        $this->placeholders = array();
        $this->fetchingDone = false;
        
        $regex = '{{(NODE_(\d+)_(\d+))}}';

        $matches = array();
        preg_match_all($regex, $content, $matches);

        if(count($matches) == 0)
            return;

        for($i = 0; $i < count($matches[0]); $i++) {           
            $this->placeholders[$matches[1][$i]] = array('nodeid' => $matches[2][$i], 'lang' => $matches[3][$i]);
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
            throw new LinkGeneratorException('Seems like scan() was not called before calling fetch().');

        $qb = $em->createQueryBuilder();
        $qb->add('select', new Doctrine\ORM\Query\Expr\Select(array('p')));
        $qb->add('from', new Doctrine\ORM\Query\Expr\From('Cx\Model\ContentManager\Page', 'p'));
       
        //build a big or with all the node ids and pages 
        $expr = null;
        foreach($this->placeholders as $placeholder => $data) {
            if($expr) {
                $expr = $qb->expr()->orx(
                    $expr,
                    $qb->expr()->andx(
                        $qb->expr()->eq('p.node', $data['nodeid']),
                        $qb->expr()->eq('p.lang', $data['lang'])
                    )
                );
            }
            else {
                $expr = $qb->expr()->andx(
                    $qb->expr()->eq('p.node', $data['nodeid']),
                    $qb->expr()->eq('p.lang', $data['lang'])
                );
            }
        }

        //fetch the nodes if there are any in the query
        if($expr) {
            $pageRepo = $em->getRepository('Cx\Model\ContentManager\Page');

            $qb->add('where', $expr);
            $pages = $qb->getQuery()->getResult();
            foreach($pages as $page) {
                $placeholder = 'NODE_'.$page->getNode()->getId().'_'.$page->getLang();
                //build placeholder's value
                $this->placeholders[$placeholder] = $this->prefix . $pageRepo->getPath($page, true);
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
     * Sets all variables on the given template.
     * @param SigmaTemplate $template
     */
    public function replace($template) {
        if($this->placeholders === null)
            throw new LinkGeneratorException('Usage: scan(), then fetch(), then replace().');
        if($this->fetchingDone === false)
            throw new LinkGeneratorException('Seems like fetch() was not called before calling replace().');

        $template->setVariable($this->placeholders);   
    }
}