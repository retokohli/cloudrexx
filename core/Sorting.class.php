<?php

/**
 * Provides methods to create sorted tables
 *
 * Lets you create clickable table headers which indicate the sorting
 * field and direction.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.9.0
 * @package     contrexx
 * @subpackage  core
 */

define('SORTING_ORDER_PARAMETER_NAME', 'x_order');

/**
 * Provides methods to create sorted tables
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.9.0
 * @package     contrexx
 * @subpackage  core
 */
class Sorting
{
    /**
     * The bae page URI to use.
     *
     * The sorting parameters will be appended to this string and used
     * to build the header array.
     * @var string
     */
    var $baseUri;

    /**
     * The array of database field names corresponding to the header
     * field names.
     *
     * Note that the first element will be the default.
     * @var array
     */
    var $arrFieldName;

    /**
     * The array of header field names.
     *
     * Note that the first element will be the default.
     * @var array
     */
    var $arrHeaderName;

    /**
     * Flag indicating the default order
     *
     * if true, the default order is ascending, or descending otherwise.
     * @var boolean
     */
    var $flagDefaultAsc;

    /**
     * The order field name.  See {@link setOrder()}.
     * @var string
     */
    var $orderField;

    /**
     * The order direction.  See {@link setOrder()}.
     * @var string
     */
    var $orderDirection;


    /**
     * Constructor (PHP4)
     *
     * @param   string  $baseURI        The base page URI.
     * @param   array   $arrFieldName   The acceptable field names.
     * @param   array   $arrHeaderName  The header names for displaying.
     * @param   boolean $flagDefaultAsc The flag indicating the default order
     *                                  direction. Defaults to true (ascending).
     * @return  Sorting
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function Sorting(
        $baseUri, $arrFieldName, $arrHeaderName, $flagDefaultAsc=true
    ) {
        __construct(
            $baseUri, $arrFieldName, $arrHeaderName, $flagDefaultAsc
        );
    }

    /**
     * Constructor (PHP5)
     *
     * @param   string  $baseURI        The base page URI.
     * @param   array   $arrFieldName   The acceptable field names.
     * @param   array   $arrHeaderName  The header names for displaying.
     * @param   boolean $flagDefaultAsc The flag indicating the default order
     *                                  direction. Defaults to true (ascending).
     * @return  Sorting
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function __construct(
        $baseUri, $arrFieldName, $arrHeaderName, $flagDefaultAsc=true
    ) {
        $this->baseUri        = $baseUri;
        $this->arrFieldName   = $arrFieldName;
        $this->arrHeaderName  = $arrHeaderName;
        $this->flagDefaultAsc = $flagDefaultAsc;
        // By default, the table will be sorted by the first field,
        // according to the direction flag.
        // If the order parameter is present in the $_REQUEST array,
        // however, it is used instead.
        $this->setOrder(
            (empty($_REQUEST[SORTING_ORDER_PARAMETER_NAME])
                ?   $this->arrFieldName[0].' '.
                    ($this->flagDefaultAsc ? 'ASC' : 'DESC')
                :   $_REQUEST[SORTING_ORDER_PARAMETER_NAME]
            )
        );
//echo("Sorting::__construct(baseUri=$baseUri, arrFieldName=$arrFieldName, arrHeaderName=$arrHeaderName, flagDefaultAsc=$flagDefaultAsc):<br />made order: ".$this->getOrder()."<br />");
    }


    /**
     * Returns an array of strings to display the table headers.
     *
     * Uses the order currently stored in the object, as set by
     * setOrder().
     * The array is, of course, in the same order as the arrays of
     * field and header names used.
     * @return  array               The array of clickable table headers.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getHeaderArray()
    {
        global $_ARRAYLANG;

        $arrHeader = array();
        for ($count = 0; $count < count($this->arrFieldName); ++$count) {
            $arrHeader[] = $this->getHeaderForField($field);
        }
        return $arrHeader;
    }


    /**
     * Returns a string to display the table header for the given field name.
     *
     * Uses the order currently stored in the object, as set by
     * setOrder().
     * @return  string      The string for a clickable table header field.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getHeaderForField($fieldName)
    {
        global $_ARRAYLANG;

        $fieldIndex = array_search($fieldName, $this->arrFieldName);
        if ($fieldIndex === false) {
echo("Sorting::getHeaderForField(fieldName=$fieldName): ERROR: unknown field name<br />");
            return '';
        }
        $orderDirectionString =
            ($this->orderDirection == 'ASC'
                ? $_ARRAYLANG['TXT_CORE_SORTING_ASCENDING']
                : $_ARRAYLANG['TXT_CORE_SORTING_DESCENDING']
            );
        $orderDirectionImage =
            '<img src="'.ASCMS_ADMIN_WEB_PATH.'/images/icons/'.
                strtolower($this->orderDirection).
            '.png" border=0 alt="'.$orderDirectionString.
            '" title="'.$orderDirectionString.'" />';

        $field  = $this->arrFieldName[$fieldIndex];
        $header = $this->arrHeaderName[$fieldIndex];
        $strHeader =
            "<a href='$this->baseUri".
            $this->getOrderReverseUriEncoded($field).
            "'>$header".
            ($this->orderField == $field ? "&nbsp;$orderDirectionImage" : '').
            '</a>';
//echo("Sorting::getHeaderForField(fieldName=$fieldName): made header: ".htmlentities($strHeader)."<br />");
        return $strHeader;
    }


    /**
     * Returns the current order string (SQL-ish syntax)
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getOrder()
    {
        // Better backquote all field names to avoid SQL errors on
        // reserved words
        return "`$this->orderField` $this->orderDirection";
    }


    /**
     * Sets the order string (SQL-ish syntax)
     * @param   string  $order      The order string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setOrder($order)
    {
        list($orderField, $orderDirection) = split(' ', $order);
        // If the order field isn't in the list of accepted field names,
        // fall back to default
        if (!in_array($orderField, $this->arrFieldName)) {
            $orderField = $this->arrFields[0];
        }
        switch ($orderDirection) {
          case 'ASC':
          case 'DESC':
            break;
          default:
            $orderDirection =
                ($this->flagDefaultAsc
                    ? 'ASC'
                    : 'DESC'
                );
        }
        $this->orderField       = $orderField;
        $this->orderDirection   = $orderDirection;
    }


    /**
     * Returns the sorting order string in URI encoded format
     *
     * The returned string contains both the parameter name, 'order',
     * and the current order string value.
     * It is ready to be used in an URI in a link.
     * @return  string          URI encoded order string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getOrderUriEncoded($field='')
    {
        if (!$field) {
            $field = $this->orderField;
        }
        return
            '&amp;'.SORTING_ORDER_PARAMETER_NAME.
            '='.urlencode("$field $this->orderDirection");
    }


    /**
     * Returns the sorting order string in URI encoded format
     *
     * The returned string contains both the parameter name, 'order',
     * and the current order string value.
     * It is ready to be used in an URI in a link.
     * @return  string          URI encoded order string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getOrderReverseUriEncoded($field='')
    {
        if (!$field) {
            $field = $this->orderField;
        }
        $orderDirectionReverse = $this->getOrderDirectionReverse();
        return
            '&amp;'.SORTING_ORDER_PARAMETER_NAME.
            '='.urlencode("$field $orderDirectionReverse");
    }


    /**
     * Returns the sorting direction string
     *
     * This is either 'ASC' or 'DESC'.
     * @return  string          order direction string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getOrderDirection()
    {
        return $this->orderDirection;
    }


    /**
     * Returns the reverse sorting direction string
     *
     * This is either 'ASC' or 'DESC'.
     * @return  string          reverse order direction string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getOrderDirectionReverse()
    {
        $orderDirectionReverse = '';
        switch ($this->orderDirection) {
          case 'ASC':
            return 'DESC';
          case 'DESC':
            return 'ASC';
        }
        return
            ($this->flagDefaultAsc
                ? 'DESC'
                : 'ASC'
            );
    }

}

?>