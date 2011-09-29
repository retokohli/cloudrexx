<?php

namespace Cx\Model\ContentManager;

/**
 * Cx\Model\ContentManager\Page
 */
class Page extends \Cx\Model\Base\EntityBase
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $lang
     */
    private $lang;

    /**
     * @var string $title
     */
    private $title;

    /**
     * @var text $content
     */
    private $content;

    /**
     * @var string $customContent
     */
    private $customContent;

    /**
     * @var string $cssName
     */
    private $cssName;

    /**
     * @var string $metatitle
     */
    private $metatitle;

    /**
     * @var string $metadesc
     */
    private $metadesc;

    /**
     * @var string $metakeys
     */
    private $metakeys;

    /**
     * @var string $metarobots
     */
    private $metarobots;

    /**
     * @var date $start
     */
    private $start;

    /**
     * @var date $end
     */
    private $end;

    /**
     * @var boolean $editingStatus
     */
    private $editingStatus;

    /**
     * @var boolean $display
     */
    private $display;

    /**
     * @var boolean $active
     */
    private $active;

    /**
     * @var string $target
     */
    private $target;

    /**
     * @var integer $module
     */
    private $module;

    /**
     * @var string $cmd
     */
    private $cmd;

    /**
     * @var Cx\Model\ContentManager\Node
     */
    private $node;

    /**
     * @var int $slugSuffix
     */
    private $slugSuffx = 0;

    /**
     * @var int $slugBase
     */
    private $slugBase = '';

    /**
     * @var Cx\Model\ContentManager\Skin
     */
    private $skin;

    public function __construct() {
        //default values
        $this->type = 'content';
        $this->content = '';
        $this->editingStatus = '';
        $this->active = false;
        $this->display = true;
        $this->caching = false;
        $this->protection = 0;
        $this->setUpdatedAtToNow();

        $typeValidator = new \Zend_Validate();
        $typeValidator->addValidator(new \Zend_Validate_StringLength(array('max' => 16)));
        $typeValidator->addValidator(new \Zend_Validate_Alnum());
        
        $this->validators = array(
            'lang' => new \Zend_Validate_Int(),
            'type' => $typeValidator,
            //caching is boolean, not checked

            'title' => new \Zend_Validate_StringLength(array('max' => 255)),
            'customContent' => new \Zend_Validate_StringLength(array('max' => 64)),
            'cssName' => new \Zend_Validate_StringLength(array('max' => 255)),
            'metatitle' => new \Zend_Validate_StringLength(array('max' => 255)),
            'metadesc' => new \Zend_Validate_StringLength(array('max' => 255)),
            'metakeys' => new \Zend_Validate_StringLength(array('max' => 255)),
            'metarobots' => new \Zend_Validate_StringLength(array('max' => 255)),
            //'start' => maybe date? format?
            //'end' => maybe date? format?
            'editingStatus'  => new \Zend_Validate_StringLength(array('max' => 16)),
            'username' => new \Zend_Validate_StringLength(array('max' => 64)),
            //display is boolean, not checked
            //active is boolean, not checked
            'target' => new \Zend_Validate_StringLength(array('max' => 255)),
            'module' => new \Zend_Validate_Alnum(),
            'cmd' => new \Zend_Validate_Regex(array('pattern' => '/^[A-Za-z0-9_]+$/')),            
        );
    }

    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set lang
     *
     * @param integer $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    /**
     * Get lang
     *
     * @return integer $lang
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $wasEmpty = $this->getTitle() == '';

        $this->title = $title;
        
        if($wasEmpty)
            $this->refreshSlug();

        if($this->getContentTitle() == '')
            $this->setContentTitle($this->title);
    }

    /**
     * Sets a correct slug based on the current title.
     */
    protected function refreshSlug() {
        $slug = $this->getSlugProposal();
        $this->setSlug($slug);
    }

    /**
     * Proposes a version of the title that can be used as slug.
     * The result may need a suffix if titles of pages on sibling nodes
     * result in the same slug.
     *
     * @return string
     */
    protected function getSlugProposal() {
        $slug = $this->getTitle();
        $slug = preg_replace('/\s/', '-', $slug);
        $slug = preg_replace('/[^a-zA-Z0-9-_]/', '', $slug);
        return $slug;
    }

    public function nextSlug() {
        $this->setSlug($this->slugBase . '-' . ++$this->slugSuffix, true);
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param text $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return text $content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set customContent
     *
     * @param string $customContent
     */
    public function setCustomContent($customContent)
    {
        $this->customContent = $customContent;
    }

    /**
     * Get customContent
     *
     * @return string $customContent
     */
    public function getCustomContent()
    {
        return $this->customContent;
    }

    /**
     * Set cssName
     *
     * @param string $cssName
     */
    public function setCssName($cssName)
    {
        $this->cssName = $cssName;
    }

    /**
     * Get cssName
     *
     * @return string $cssName
     */
    public function getCssName()
    {
        return $this->cssName;
    }

    /**
     * Set metatitle
     *
     * @param string $metatitle
     */
    public function setMetatitle($metatitle)
    {
        $this->metatitle = $metatitle;
    }

    /**
     * Get metatitle
     *
     * @return string $metatitle
     */
    public function getMetatitle()
    {
        return $this->metatitle;
    }

    /**
     * Set metadesc
     *
     * @param string $metadesc
     */
    public function setMetadesc($metadesc)
    {
        $this->metadesc = $metadesc;
    }

    /**
     * Get metadesc
     *
     * @return string $metadesc
     */
    public function getMetadesc()
    {
        return $this->metadesc;
    }

    /**
     * Set metakeys
     *
     * @param string $metakeys
     */
    public function setMetakeys($metakeys)
    {
        $this->metakeys = $metakeys;
    }

    /**
     * Get metakeys
     *
     * @return string $metakeys
     */
    public function getMetakeys()
    {
        return $this->metakeys;
    }

    /**
     * Set metarobots
     *
     * @param string $metarobots
     */
    public function setMetarobots($metarobots)
    {
        $this->metarobots = $metarobots;
    }

    /**
     * Get metarobots
     *
     * @return string $metarobots
     */
    public function getMetarobots()
    {
        return $this->metarobots;
    }

    /**
     * Set start
     *
     * @param date $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * Get start
     *
     * @return date $start
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set end
     *
     * @param date $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * Get end
     *
     * @return date $end
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set editingStatus
     *
     * @param boolean $editingStatus
     */
    public function setEditingStatus($editingStatus)
    {
        $this->editingStatus = $editingStatus;
    }

    /**
     * Get editingStatus
     *
     * @return boolean $editingStatus
     */
    public function getEditingStatus()
    {
        return $this->editingStatus;
    }

    /**
     * Set display
     *
     * @param boolean $display
     */
    public function setDisplay($display)
    {
        $this->display = $display;
    }

    /**
     * Get display
     *
     * @return boolean $display
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * Set active
     *
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * Get active
     *
     * @return boolean $active
     */
    public function getStatus()
    {
        if ($this->display) return "active";
        if ($this->active) return "hidden";
        return "disabled";
    }

    /**
     * Set status
     *
     * @param boolean $status
     */
    public function setStatus($status)
    {
        if ($status == "active") {
            $this->active = true;
            $this->display = true;
        }
        elseif ($status == "hidden") {
            $this->active = true;
            $this->display = false;
        }
        else {
            $this->active = false;
            $this->display = false;
        }
        
    }

    /**
     * Get status
     *
     * @return boolean $status
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set target
     *
     * @param string $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * Get target
     *
     * @return string $target
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Whether target references an internal page
     * @return boolean
     */
    public function isTargetInternal() {
        //internal targets are formed like <page_id>|<querystring>
        return is_numeric(substr($this->target,0,1));
    }

    /**
     * Gets the target nodes' id.
     * @return integer id for internal targets, 0 else.
     */
    public function getTargetNodeId() {
        if(!$this->isTargetInternal())
            return 0;
        
        $c = $this->cutTarget();
        return intval($c['nodeId']);
    }

    protected function cutTarget() {
        $t = $this->getTarget();
        $matches = array();
        
        preg_match('#(\d+)(-(\d+))?\|(.*)#', $t, $matches);
        return array(
            'nodeId' => $matches[1],
            'langId' => $matches[3],
            'queryString' => $matches[4],
        );
    }

    /**
     * Get the target pages' language id.
     * @return integer id for set language, 0 if it is not set or external target
     */
    public function getTargetLangId() {
        if(!$this->isTargetInternal())
            return 0;

        $c = $this->cutTarget();
        return intval($c['langId']);
    }

    /**
     * Gets the target pages' querystring.

     * @return string querystring for internal targets, null else
     */
    public function getTargetQueryString() {
        if(!$this->isTargetInternal())
            return null;

        $c = $this->cutTarget();
        return $c['queryString'];
    }

    /**
     * Set module
     *
     * @param integer $module
     */
    public function setModule($module)
    {
        $this->module = $module;
    }

    /**
     * Get module
     *
     * @return integer $module
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set cmd
     *
     * @param string $cmd
     */
    public function setCmd($cmd)
    {
        $this->cmd = $cmd;
    }

    /**
     * Get cmd
     *
     * @return string $cmd
     */
    public function getCmd()
    {
        return $this->cmd;
    }

    /**
     * Set node
     *
     * @param Cx\Model\ContentManager\Node $node
     */
    public function setNode(\Cx\Model\ContentManager\Node $node)
    {
        $node->addAssociatedPage($this);
        $this->node = $node;
    }

    /**
     * Get node
     *
     * @return Cx\Model\ContentManager\Node $node
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * Set skin
     *
     * @param Cx\Model\ContentManager\Skin $skin
     */
    public function setSkin($skin)
    {
        $this->skin = $skin;
    }

    /**
     * Get skin
     *
     * @return Cx\Model\ContentManager\Skin $skin
     */
    public function getSkin()
    {
        return $this->skin;
    }
    /**
     * @var boolean $caching
     */
    private $caching;

    /**
     * @var integer $user
     */
    private $user;


    /**
     * Set caching
     *
     * @param boolean $caching
     */
    public function setCaching($caching)
    {
        $this->caching = $caching;
    }

    /**
     * Get caching
     *
     * @return boolean $caching
     */
    public function getCaching()
    {
        return $this->caching;
    }

    /**
     * Set user
     *
     * @param integer $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return integer $user
     */
    public function getUser()
    {
        return $this->user;
    }
    /**
     * @var string $type
     */
    private $type;


    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }
    /**
     * @var string $username
     */
    private $username;


    /**
     * Set username
     *
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Get username
     *
     * @return string $username
     */
    public function getUsername()
    {
        return $this->username;
    }
    /**
     * @prePersist
     */
    public function validate()
    {
        //workaround, this method is regenerated each time
        parent::validate(); 
    }

    public function setUpdatedAtToNow()
    {
        $this->updatedAt = new \DateTime("now");
    }
    /**
     * @var datetime $updatedAt
     */
    private $updatedAt;


    /**
     * Set updatedAt
     *
     * @param datetime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get updatedAt
     *
     * @return datetime $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
    
    /**
     * @var integer $protection
     */
    private $protection;

    const FRONTEND_PROTECTION = 1;
    const BACKEND_PROTECTION = 2;    

    /**
     * Whether page access from frontend is protected.
     * @return boolean
     */
    public function isFrontendProtected() {
        return (bool) ($this->protection & self::FRONTEND_PROTECTION);
    }

    /**
     * Whether page access from backend is protected.
     * @return boolean
     */
    public function isBackendProtected() {
        return (bool) ($this->protection & self::BACKEND_PROTECTION);
    }

    /**
     * Set page access from frontend.
     * @param boolean $val
     */
    public function setFrontendProtected($val) {
        if($val)
            $this->protection = $this->protection & self::FRONTEND_PROTECTION;
        else
            $this->protection = $this->protection ^ self::FRONTEND_PROTECTION;
    }

    /**
     * Set page access from backend.
     * @param boolean $val
     */
    public function setBackendProtected($val) {
        if($val)
            $this->protection = $this->protection & self::BACKEND_PROTECTION;
        else
            $this->protection = $this->protection ^ self::BACKEND_PROTECTION;
    }

    /**
     * Set protection
     *
     * @param integer $protection
     */
    public function setProtection($protection)
    {
        $this->protection = $protection;
    }

    /**
     * Get protection
     *
     * @return integer $protection
     */
    public function getProtection()
    {
        return $this->protection;
    }

    /**
     * Alias for getDisplay()
     *
     * @return boolean
     */
    public function isVisible() {
        return $this->display;
    }

    /**
     * Alias for getActive()
     *
     * @return boolean
     */
    public function isActive() {
        return $this->active;
    }

    /**
     * @var string $slug
     */
    private $slug;

    /**
     * Set slug
     *
     * @param string $slug
     * @param boolean $nextSlugCall set by { @see nextSlug() }
     */
    public function setSlug($slug, $nextSlugCall=false)
    {
        $this->slug = $slug;

        if(!$nextSlugCall) {
            $this->slugSuffix = 0;
            $this->slugBase = $this->slug;
        }
    }

    /**
     * Get slug
     *
     * @return string $slug
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Copies data from another Page.
     *
     * @param \Cx\Model\ContentManager\Page $source
     * @param boolean $includeContent whether to copy content. defaults to true.
     * @param boolean $includeModuleAndCmd whether to copy module and cmd. defaults to true.
     */
    public function copyFrom($source, $includeContent=true, $includeModuleAndCmd=true) {
        $this->setTitle($source->getTitle());

        if($includeContent)
            $this->setContent($source->getContent());

        if($includeModuleAndCmd) {
            $this->setModule($source->getModule());
            $this->setCmd($source->getCmd());
        }

        $this->setNode($source->getNode());

        $this->setActive($source->getActive());
        $this->setDisplay($source->getDisplay());

        $this->setLang($source->getLang());
        $this->setUsername($source->getUsername());

        $this->setType($source->getType());
        $this->setCaching($source->getCaching());
        $this->setContentTitle($source->getContentTitle());
        $this->setSlug($source->getSlug());
        $this->setCustomContent($source->getCustomContent());
        $this->setCssName($source->getCssName());
        $this->setSkin($source->getSkin());
        $this->setMetatitle($source->getMetatitle());
        $this->setMetadesc($source->getMetadesc());
        $this->setMetakeys($source->getMetakeys());
        $this->setMetarobots($source->getMetarobots());
        $this->setStart($source->getStart());
        $this->setEnd($source->getEnd());
        $this->setEditingStatus($source->getEditingStatus());
        $this->setProtection($source->getProtection());
        $this->setTarget($source->getTarget());

    }
    /**
     * @var string $contentTitle
     */
    private $contentTitle;


    /**
     * Set contentTitle
     *
     * @param string $contentTitle
     */
    public function setContentTitle($contentTitle)
    {
        $this->contentTitle = $contentTitle;
    }

    /**
     * Get contentTitle
     *
     * @return string $contentTitle
     */
    public function getContentTitle()
    {
        return $this->contentTitle;
    }
    /**
     * @var string $linkTarget
     */
    private $linkTarget;


    /**
     * Set linkTarget
     *
     * @param string $linkTarget
     */
    public function setLinkTarget($linkTarget)
    {
        $this->linkTarget = $linkTarget;
    }

    /**
     * Get linkTarget
     *
     * @return string $linkTarget
     */
    public function getLinkTarget()
    {
        return $this->linkTarget;
    }
}