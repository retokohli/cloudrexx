<?php
/**
 * Contains the class for the tag cloud
 *
 * @author Stefan Heinemann <sh@comvation.com>
 * @copyright Comvation AG <info@comvation.com>
 */

/**
 * Provide all the necessary database operations for the tag cloud
 * 
 * @author Stefan Heinemann <sh@comvation.com>
 * @copyright Comvation AG <info@comvation.com>
 */
class TagCloud 
{
    /**
     * The font size of the highest (biggest) tag
     *
     * @var int
     */
    private $highestFont;
    
    /**
     * The font size of the lowest (smallest) tag in the cloud
     *
     * @var unknown_type
     */
    private $lowestFont;
    
    /**
     * The tags to display
     *
     * @var array
     * @see setTags
     */
    private $tags;
    
    /**
     * TODO: what's this for?
     *
     * @var int
     */
    private $lowestTagVal;
    
    /**
     * TODO: what's this for?
     *
     * @var int
     */
    private $highestTagVal;
    
    /**
     * URL format
     *
     * @var string
     */
    private $urlFormat;
    
    /**
     * Constructor
     *
     * Construct the cloud object. For the tag array syntax see the 
     * setTags function.
     * @see setTags
     * @param int $highestFont
     * @param int $lowestFont
     * @param array $tags
     * @param int $lowestTagVal
     * @param int $highestTagVal
     */
    public function __construct($highestFont=20, $lowestFont=10, $tags=array(), $lowestTagVal=10, $highestTagVal=20, $urlFormat="%id")
    {
        $this->highestFont = $highestFont;
        $this->lowestFont = $lowestFont;
        $this->tags = $tags;
        $this->lowestTagVal = $lowestTagVal;
        $this->highestTagVal = $highestTagVal;
        $this->urlFormat = $urlFormat;
    }
    
    /**
     * Return the cloud
     *
     * @return string
     */
    public function getCloud()
    {
        $fontDiff = $this->highestFont - $this->lowestFont;
        $tagDiff = $this->highestTagVal - $this->lowestTagVal;
        $step = $tagDiff / $fontDiff;
        if ($step == 0) {
            //prevent from division by zero
            $step = 1;
        }
        
        $cloud = "<div class=\"cloud clearfix\"><ul class=\"cloud\">";
        foreach ($this->tags as $tagId => $tag) {
            $size = $this->lowestFont + round($tag['popularity'] / $step);
            $uri = "<a href=\"".$this->formatUrl($tag['id'])."\">".$tag['name']."</a>";
            
            $cloud .= "<li style=\"font-size: ".$size."px;\">".$uri."</li>";
        }
        $cloud .= "</ul></div>";
        
        return $cloud;
    }
    
    /**
     * Set the font options
     *
     * @param int $highest
     * @param int $lowest
     */
    public function setFont($highest, $lowest)
    {
        $this->highestFont = intval($highest);
        $this->lowestFont = intval($lowest);
    }
    
    /**
     * Set the tags
     *
     * Set the tags with this format:
     * array(
     *  index => array(
     *      "id"    => 0,
     *      "name"  => "Tagname",
     *      "popularity" => 15
     *      )
     * )
     * @param array $tags
     * @throws Exception
     */
    public function setTags($tags)
    {
        if (gettype($tags) != "array") {
            throw new Exception("not an array given in TagCloud::setTags(array);");
        }
        
        $this->tags = $tags;
    }
    
    /**
     * Set the tag values
     *
     * @param int $highest
     * @param int $lowest
     */
    public function setTagVals($highest, $lowest)
    {
        $this->highestTagVal = intval($highest);
        $this->lowestTagVal = intval($lowest);
    }
    
    /**
     * Set the url format
     *
     * @param string $format
     */
    public function setUrlFormat($format)
    {
        $this->urlFormat = $format;
    }
    
    /**
     * Format the url
     *
     * Replace the %id placeholder with the actual id
     * @param int $id
     * @return string
     */
    private function formatUrl($id)
    {
        return preg_replace("/%id/i", $id, $this->urlFormat);
    }
}