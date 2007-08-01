<?php

/**
 * Provides methods to create sorted tables
 *
 * Lets you create clickable table headers which indicate the sorting
 * field and direction.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  core
 */

/**
 * Provides methods to create sorted tables
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
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
     * The order field and direction specified by calling setOrder().
     * @var string
     */
    var $order;


    /**
     * Constructor (PHP4)
     *
     * @param   string  $baseURI        The base page URI.
     * @param   array   $arrFieldName   The acceptable field names.
     * @param   array   $arrHeaderName  The header names for displaying.
     * @param   boolean $flagDefaultAsc The flag indicating the default order
     *                                  direction. Defaults to true (ascending).
     * @return  Sorting
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
     */
    function Sorting(
        $baseUri, $arrFieldName, $arrHeaderName, $flagDefaultAsc=true
    ) {
        $this->baseUri        = $baseUri;
        $this->arrFieldName   = $arrFieldName;
        $this->arrHeaderName  = $arrHeaderName;
        $this->flagDefaultAsc = $flagDefaultAsc;
    }


    /**
     * Returns an array of strings to display the table headers.
     *
     * The $order parameter lets you choose the sorting field and order
     * on the fly.  Use a SQL-like string like "field_name ASC".
     * If you omit $order, the method tries to use the order set with
     * setOrder().  If this fails, the default is used.
     * @param   string  $order      The optional ordering, SQL style.
     * @return  array               The array of clickable table headers.
     */
    function getHeaderArray($order='')
    {
        global $_ARRAYLANG;

        if (!$order) {
            $order = $this->order;
        }
        list($orderField, $orderDirection) = split(' ', $order);
        if (!in_array($orderField, $this->arrFieldName)) {
            $orderField = $this->arrFields[0];
        }
        $orderDirectionReverse = '';
        switch ($orderDirection) {
          case 'ASC':
            $orderDirectionReverse = 'DESC';
            break;
          case 'DESC':
            $orderDirectionReverse = 'ASC';
            break;
          default:
            if ($this->flagDefaultAsc) {
                $orderDirection        = 'ASC';
                $orderDirectionReverse = 'DESC';
            } else {
                $orderDirection        = 'DESC';
                $orderDirectionReverse = 'ASC';
            }
        }
        $orderDirectionString =
            ($orderDirection == 'ASC'
                ? $_ARRAYLANG['TXT_CORE_ORDER_ASCENDING']
                : $_ARRAYLANG['TXT_CORE_ORDER_DESCENDING']
            );
        $orderDirectionImage =
            '<img src="'.ASCMS_ADMIN_WEB_PATH.'/images/icons/'.
                strtolower($orderDirection).
            '.png" border=0 alt="'.$orderDirectionString.
            '" title="'.$orderDirectionString.'" />';

        $arrHeader = array();
        for ($count = 0; $count < count($this->arrFieldName); ++$count) {
            $field  = $this->arrFieldName[$count];
            $header = $this->arrHeaderName[$count];
            $arrHeader[] =
                "<a href='$this->baseUri&amp;order=$field+$orderDirectionReverse'>
                $header&nbsp;".
                ($orderField == $field ? $orderDirectionImage : '').
                '</a>';
        }
        return $arrHeader;
    }


    function setOrder($order)
    {
        $this->order = $order;
    }


}

?>