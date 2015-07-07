<?php

/**
 * Class RewriteRule
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_routing
 */

namespace Cx\Core\Routing\Model\Entity;

/**
 * Class RewriteRule
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_routing
 */
class RewriteRule extends \Cx\Model\Base\EntityBase
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * Regex
     * 
     * @var \Cx\Lib\Helpers\RegularExpression $regularExpression
     */
    protected $regularExpression;
    
    
    /**
     * Rewrite Status Code
     * 
     * @var integer
     */
    protected $rewriteStatusCode;

    /**     
     * @var boolean $continueOnMatch
     */
    protected $continueOnMatch;
    
    /**
     * Get id
     * 
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get Regular expression
     * 
     * @return \Cx\Lib\Helpers\RegularExpression 
     */
    public function getRegularExpression()
    {
        if (!($this->regularExpression instanceof \Cx\Lib\Helpers\RegularExpression)) {
            $this->regularExpression = new \Cx\Lib\Helpers\RegularExpression($this->regularExpression);
        }
        
        return $this->regularExpression;
    }

    /**
     * Get Rewrite Status Code
     * 
     * @return integer
     */
    function getRewriteStatusCode()
    {
        return $this->rewriteStatusCode;
    }
    
    /**
     * @return boolean
     */
    public function getContinueOnMatch()
    {
        return $this->continueOnMatch;
    }

    /**
     * Set regular expression
     * 
     * @param mixed $regularExpression \Cx\Lib\Helpers\RegularExpression or string
     */
    public function setRegularExpression($regularExpression)
    {
        if (!($regularExpression instanceof \Cx\Lib\Helpers\RegularExpression)) {
            $regularExpression = new \Cx\Lib\Helpers\RegularExpression($regularExpression);
        }
        
        $this->regularExpression = $regularExpression;
    }

    /**
     * Set the rewrite status code
     * 
     * @param integer $rewriteStatusCode
     */
    function setRewriteStatusCode($rewriteStatusCode)
    {
        $this->rewriteStatusCode = $rewriteStatusCode;
    }
    
    /**
     * Set continue on match
     * 
     * @param boolean $continueOnMatch
     */
    public function setContinueOnMatch($continueOnMatch)
    {
        $this->continueOnMatch = $continueOnMatch;
    }

    public function matches(\Cx\Core\Routing\Url $url)
    {
        return $this->getRegularExpression()->match($url->toString());
    }
    
    /**
     * Resolve
     */
    public function resolve(\Cx\Core\Routing\Url $url, &$continue)
    {
        if (!$this->matches($url)) {
            return $url;
        }

        $continue = $this->getContinueOnMatch();
        $newUrl = \Cx\Core\Routing\Url::fromMagic(
            $this->getRegularExpression()->replace($url->toString())
        );
        \DBG::log('Redirecting to ' . $newUrl->toString());
        return $newUrl;
    }
}
