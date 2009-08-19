<?php

/**
 * HTML Tag helpers
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.2.0
 * @package     contrexx
 * @subpackage  module_hotelcard
 */

/**
 * HTML Tag Class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.2.0
 * @package     contrexx
 * @subpackage  module_hotelcard
 */
class HtmlTag
{
    /**
     * The tag name, like 'div' or 'img'
     * @var   string
     */
    private $name = false;
    /**
     * The next sibling of the tag
     *
     * May be empty, a HtmlTag object or a string
     * @var   mixed
     */
    private $next_sibling = false;
    /**
     * The first child of the tag
     *
     * May be empty, a HtmlTag object or a string
     * @var   mixed
     */
    private $first_child = false;
    /**
     * The attributes of the tag
     * @var   array
     */
    private $attributes = array();


    /**
     * Construct a Tag object
     *
     * The attributes array, if specified, must be of the form
     *  array(
     *    attribute name => attribute value,
     *    ... more ...
     *  )
     * @param   string    $name         The tag name
     * @param   array     $attributes   The optional list of attributes
     */
    function __construct($name, $attributes=array())
    {
        $this->name = $name;
        $this->attributes = $attributes;
    }


    /**
     * Returns the name of the tag object
     * @return  string          The tag name
     */
    function getName()
    {
        return $this->name;
    }


    /**
     * Returns the attributes array
     * @return  array           The attribute array
     */
    function getAttributes()
    {
        return $this->attributes;
    }


    /**
     * Returns the value for the given attribute name
     *
     * If the attribute with the given name is not present,
     * the empty string is returned.
     * @param   string      $name     The attribute name
     * @return  string                The attribute value, or the empty string
     */
    function getAttributeValue($name)
    {
        return
            (isset($this->attributes[$name])
                ? $this->attributes[$name] : '');
    }


    /**
     * Sets the value of the given attribute
     *
     * If the value is empty, the attribute is removed.
     * @param   string    $name       The attribute name
     * @param   string    $value      The attribute value
     * @return  void
     */
    function setAttribute($name, $value)
    {
        if (empty($value))
            unset($this->attributes[$name]);
        else
            $this->attributes[$name] = $value;
    }


    /**
     * Returns the string representation of the tag
     * @return  string                The string representation of the tag
     */
    function toString()
    {
        return
            '<'.$this->name.
            $this->getAttributeString().
            ($this->first_child
              ? '>'.
                (is_a('HtmlTag', $this->first_child)
                  ? $this->first_child->toString
                  : $this->first_child
                )
              : ''
            );
    }


    /**
     * Returns the string representation of the tag's attributes
     *
     * If there are no attributes, the empty string is returned.
     * Otherwise, a leading space is prepended to the string.
     * @return  string                The string representation of the tags'
     *                                attributes
     */
    function getAttributeString()
    {
        if (empty($this->attributes)) return '';
        $attributes = '';
        foreach ($this->attributes as $name => $value) {
            $attributes .= ' '.$name.'="'.$value.'"';
        }
        return $attributes;
    }

}

?>
