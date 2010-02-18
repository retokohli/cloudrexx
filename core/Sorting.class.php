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
     * Default URL parameter name for the sorting order
     *
     * You *MUST* specify this yourself using {@see setOrderParameterName()}
     * when using more than one Sorting at a time!
     */
    const ORDER_PARAMETER_NAME = 'x_order';

    /**
     * The base page URI to use.
     *
     * The sorting parameters will be appended to this string and used
     * to build the header array.
     * @var string
     */
    private $baseUri;

    /**
     * The array of database field names corresponding to the header
     * field names.
     *
     * Note that the first element will be the default.
     * @var array
     */
    private $arrFieldName;

    /**
     * The array of header field names.
     *
     * Note that the first element will be the default.
     * @var array
     */
    private $arrHeaderName;

    /**
     * Flag indicating the default order
     *
     * if true, the default order is ascending, or descending otherwise.
     * @var boolean
     */
    private $flagDefaultAsc;

    /**
     * The order field name.  See {@link setOrder()}.
     * @var string
     */
    private $orderField;

    /**
     * The order direction.  See {@link setOrder()}.
     * @var string
     */
    private $orderDirection;

    /**
     * The order parameter name for this Sorting
     */
    private $orderUriParameter;


    /**
     * Constructor
     *
     * Note that the base page URI is handed over by reference and that
     * the order parameter name is removed from that, if present.
     * @param   string  $baseURI        The base page URI, by reference
     * @param   array   $arrFieldName   The acceptable field names.
     * @param   array   $arrHeaderName  The header names for displaying.
     * @param   boolean $flagDefaultAsc The flag indicating the default order
     *                                  direction. Defaults to true (ascending).
     * @return  Sorting
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function __construct(
        &$baseUri, $arrFieldName, $arrHeaderName, $flagDefaultAsc=true,
        $orderUriParameter=self::ORDER_PARAMETER_NAME
    ) {
        // Remove the order parameter name argument from the base URI
        $baseUri = preg_replace(
            '/'.preg_quote($this->orderUriParameter, '\=[^&]*\&?/').'/',
            '', $baseUri);
        $this->baseUri        = $baseUri;
        $this->arrFieldName   = $arrFieldName;
        $this->arrHeaderName  = $arrHeaderName;
//echo("Sorting::__construct(baseUri=$baseUri, arrFieldName=$arrFieldName, arrHeaderName=$arrHeaderName, flagDefaultAsc=$flagDefaultAsc, orderUriParameter=$orderUriParameter):<br />"."Field names: ".var_export($this->arrFieldName, true)."<br />"."Header names: ".var_export($this->arrHeaderName, true)."<hr />");

        $this->flagDefaultAsc = $flagDefaultAsc;
        // Default order parameter name.  Change if needed.
        $this->orderUriParameter = $orderUriParameter;
        // By default, the table will be sorted by the first field,
        // according to the direction flag.
        // The default is overridden by the order stored in the session, if any.
        // If the order parameter is present in the $_REQUEST array,
        // however, it is used instead.
        $this->setOrder(
            (empty($_REQUEST[$this->orderUriParameter])
                ? (empty($_SESSION['sorting'][$this->orderUriParameter])
                    ? $this->arrFieldName[0].' '.
                      ($this->flagDefaultAsc ? 'ASC' : 'DESC')
                    : $_SESSION['sorting'][$this->orderUriParameter])
                : $_REQUEST[$this->orderUriParameter]
            )
        );
//echo("Sorting::__construct(baseUri=$baseUri, arrFieldName=$arrFieldName, arrHeaderName=$arrHeaderName, flagDefaultAsc=$flagDefaultAsc):<br />made order: ".$this->getOrder()."<br />");
    }


    /**
     * Set the order parameter name to be used for this Sorting
     * @param   string    $parameter_name   The parameter name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setOrderParameterName($parameter_name)
    {
        $this->orderUriParameter = $parameter_name;
    }


    /**
     * Returns the order parameter name used for this Sorting
     * @return  string                      The parameter name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getOrderParameterName()
    {
        return $this->orderUriParameter;
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
        $arrHeader = array();
        foreach ($this->arrFieldName as $fieldName) {
            $arrHeader[] = $this->getHeaderForField($fieldName);
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
//echo("Sorting::getHeaderForField(fieldName=$fieldName): ERROR: unknown field name<br />");
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
        $header = $this->arrHeaderName[$field];
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
     *
     * Note that this adds backticks around the order field name.
     * So, if you use qualified names, omit the first and last backticks
     * when initializing the Sorting object, like "table`.`field".
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
        list ($orderField, $orderDirection) = split(' ', $order);
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
                ($this->flagDefaultAsc ? 'ASC' : 'DESC');
        }
        $this->orderField     = $orderField;
        $this->orderDirection = $orderDirection;
        $_SESSION['sorting'][$this->orderUriParameter] = $order;
    }


    /**
     * Returns the sorting order string in URI encoded format
     *
     * The returned string contains both the parameter name, 'order',
     * and the current order string value.
     * @param   string  $field    The optional order field
     * @return  string            URI encoded order string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getOrderUriEncoded($field='')
    {
        if (!$field) {
            $field = $this->orderField;
        }
        return
// TODO: I guess that it's better to leave it to another piece of code
// whether to add '?' or '&'...?
            //'&amp;'.
            $this->orderUriParameter.
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
            '&amp;'.$this->orderUriParameter.
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


    /**
     * Returns the current order field name
     * @return  string      The field name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getOrderField() {
        return $this->orderField;
    }

}

?>
