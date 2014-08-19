<?php

/**
 * SessionVariable
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     contrexx
 * @subpackage  core_session
 */

namespace Cx\Core\Session\Model\Entity;

/**
 * SessionVariable
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     contrexx
 * @subpackage  core_session
 */
class SessionVariable extends \Cx\Model\Base\EntityBase
{
    /**
     * @var integer $id
     */
    private $id;
    
    /**     
     * @var string $sessionId
     */
    private $sessionId;

    /**
     * @var integer $lft
     */
    private $lft;

    /**
     * @var integer $rgt
     */
    private $rgt;

    /**
     * @var integer $lvl
     */
    private $lvl;
    
    /**
     * @var string $variable
     */
    private $variable;
    
    /**
     * @var string $value
     */
    private $value;

    /**
     * @var Cx\Core\Session\Model\Entity\SessionVariable
     */
    private $children;

    /**
     * @var Cx\Core\Session\Model\Entity\SessionVariable
     */
    private $parent;
    
    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set id
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * Set session id
     * 
     * @param string $sessionId
     */
    public function setSessionId($sessionId) {
        $this->sessionId = $sessionId;
    }
    
    /**
     * Get session id
     * 
     * @return string $sessionId
     */
    public function getSessionId() {
        return $this->sessionId;
    }
    
    /**
     * Set lft
     *
     * @param integer $lft
     */
    public function setLft($lft)
    {
        $this->lft = $lft;
    }

    /**
     * Get lft
     *
     * @return integer $lft
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * Set rgt
     *
     * @param integer $rgt
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;
    }

    /**
     * Get rgt
     *
     * @return integer $rgt
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * Set lvl
     *
     * @param integer $lvl
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;
    }

    /**
     * Get lvl
     *
     * @return integer $lvl
     */
    public function getLvl()
    {
        return $this->lvl;
    }
    
    /**
     * Set the session variable
     * 
     * @param string $variable
     */
    public function setVariable($variable) {
        $this->variable = $variable;
    }
        
    /**
     * get the session variable
     * 
     * @return string $variable
     */
    public function getVariable() {
        return $this->variable;
    }
    
    /**
     * Set the session value
     * 
     * @param string $value
     */
    public function setValue($value) {
        $this->value = $value;
    }
    
    /**
     * Get the session value
     * 
     * @return string $value
     */
    public function getValue() {
        return $this->value;
    }
    
    /**
     * Set parent
     *
     * @param Cx\Core\Session\Model\Entity\SessionVariable $parent
     */
    public function setParent(\Cx\Core\Session\Model\Entity\SessionVariable $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return Cx\Core\Session\Model\Entity\SessionVariable $parent
     */
    public function getParent()
    {
        if (is_int($this->parent)) {
            $repo = \Env::em()->getRepository('Cx\Core\ContentManager\Model\Entity\SessionVariable');
            $this->parent = $repo->find($this->parent);
        }
        return $this->parent;
    }
}
