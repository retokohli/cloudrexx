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
     * @var string $regularExpression
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
     * @return string 
     */
    public function getRegularExpression()
    {
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
     * @param \Helpers\RegularExpression $regularExpression
     */
    public function setRegularExpression(\Helpers\RegularExpression $regularExpression)
    {
        $this->regularExpression = $regularExpression->getRegex();
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
    
    /**
     * Resolve
     */
    public function resolve(\Cx\Core\Routing\Url $url, &$continue)
    {
<<<<<<< Updated upstream
        
=======
        if (!$this->matches($url)) {
            return $url;
        }

        $continue = $this->getContinueOnMatch();
        $newUrl = \Cx\Core\Routing\Url::fromMagic(
            $this->getRegularExpression()->replace($url->toString())
        );
        \DBG::log('Redirecting to ' . $newUrl->toString());
        return $newUrl;
>>>>>>> Stashed changes
    }
}
