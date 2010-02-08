<?php

/**
 * Paging
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @author      Reto Kohli <reto.kohli@comvation.com> (Rewritten statically)
 * @version     2.2.0
 * @package     contrexx
 * @subpackage  core
 */

/**
 * @ignore
 */
require_once ASCMS_CORE_PATH.'/Html.class.php';

//Security-Check
if (eregi("paging.class.php",$_SERVER['PHP_SELF'])) {
    Header("Location: index.php");
    die();
}

/**
 * OBSOLETE
 * Use the {@see Paging::getPaging()} method instead.
 *
 * Returs a string representing the complete paging HTML code for the
 * current page.
 * Note that the old $pos parameter is obsolete as well,
 * see {@see getPosition()}.
 * @copyright CONTREXX CMS - COMVATION AG
 * @author    Comvation Development Team <info@comvation.com>
 * @access    public
 * @version   1.0.0
 * @global    array       $_CONFIG        Configuration
 * @global    array       $_CORELANG      Core language
 * @param     int         $count          The number of rows being displayed
 * @param     int         $pos            The offset from the first row
 * @param     string      $uri_parameter
 * @param     string      $paging_text
 * @param     boolean     $showeverytime
 * @param     int         $limit
 * @return    string      Result
 * @todo      Change the system to use the new, static class method,
 *            then remove this one.
 */
function getPaging($count, $pos=null, $uri_parameter, $paging_text, $showeverytime=false, $limit=null)
{
    return Paging::getPaging(
        $count, $pos, $uri_parameter, $paging_text, $showeverytime, $limit
    );
}


/**
 * Creates the paging
 * @package     contrexx
 * @subpackage  core
 * @version     2.2.0
 * @author      Reto Kohli <reto.kohli@comvation.com> (Rewritten statically)
 */
class Paging
{
    /**
     * Returs a string representing the complete paging HTML code for the
     * current page
     * @author    Reto Kohli <reto.kohli@comvation.com> (Rewritten statically)
     * @access    public
     * @global    array       $_CONFIG        Configuration
     * @global    array       $_CORELANG      Core language
     * @param     integer     $count          The number of rows available
     * @param     integer     $position       The optional starting position
     *                                        offset.  Defaults to null
     * @param     string      $uri_parameter  Optional additional URI parameters,
     *                                        *MUST* start with an URI encoded
     *                                        ampersand (&amp;)
     * @param     string      $paging_text    The text to be put in front of the paging
     * @param     boolean     $showeverytime  If true, the paging is shown even if
     *                                        $count is less than the number of rows
     *                                        on a single page
     * @param     integer     $limit          The optional maximum number of
     *                                        rows to be shown on a single page.
     *                                        Defaults to the corePagingLimit
     *                                        setting.
     * @return    string                      HTML code for the paging
     */
    static function getPaging(
        $count, $position=null, &$uri_parameter, $paging_text, $showeverytime=false, $limit=0)
    {
        global $_CONFIG, $_CORELANG;

        $results_per_page =
            (empty($limit) ? intval($_CONFIG['corePagingLimit']) : $limit);
        if ($count <= $limit && !$showeverytime) return '';
        $numof_rows = $count;

        // Remove the old position parameter from the URI
        Html::stripUriParam($uri_parameter, 'pos');
        // Replace leading '?', '&', or '&amp;' by a leading '&amp;'
        $uri_parameter = preg_replace(
            '/^(?:\?|\&(?:amp;)?)?/', '&amp;', $uri_parameter);

// I don't think it's a good idea to decode the URI without re-encoding
// it later (see getPagingArray())!
//        $uri_parameter = urldecode($uri_parameter);
        $uri_parameter = $uri_parameter;
        // Strip script path, script name, and query mark (?) from the
        // local URI
        $uri_parameter = preg_replace('/.*?index.php\??/', '', $uri_parameter);
        // Prepend an encoded ampersand if the query is not empty
        if ($uri_parameter) $uri_parameter = '&amp;'.$uri_parameter;

        if (empty($position))
            $position = self::getPosition();

        // Fix illegal values:
        // The position must be in the range [0 .. numof_rows - 1].
        // If it's outside this range, reset it to zero
        if ($position < 0 || $position >= $numof_rows)
            $position = $numof_rows - 1;

        // Total number of pages: [1 .. n]
        $numof_pages = intval(0.999 + $numof_rows / $results_per_page);
        // Current page number: [1 .. numof_pages]
        $page_number = 1 + intval($position / $results_per_page);

        $corr_value = $results_per_page;
        if ($numof_rows % $results_per_page) {
            $corr_value = $numof_rows % $results_per_page;
        }

        // Set up the base navigation entries
        $array_paging = array(
            'first' => '<a href="index.php?pos=0'.$uri_parameter.'">',
            'last'  => '<a href="index.php?pos='.($numof_rows - $corr_value).$uri_parameter.'">',
            'total' => $numof_rows,
            'lower' => $position + 1,
            'upper' => $numof_rows,
        );
        if ($position + $results_per_page < $numof_rows) {
            $array_paging['upper'] = $position + $results_per_page;
        }
        if ($position != 0) {
            $array_paging['previous_link'] =
                '<a href="index.php?pos='.
                ($position - $results_per_page).
                $uri_parameter.'">';
        }
        if (($numof_rows - $position) > $results_per_page) {
            $int_new_position = $position + $results_per_page;
            $array_paging['next_link'] =
                '<a href="index.php?pos='.$int_new_position.
                $uri_parameter.'">';
        }

        // Add single pages, indexed by page numbers [1 .. numof_pages]
        for ($i = 1; $i <= $numof_pages; ++$i) {
            if ($i == $page_number) {
                $array_paging[$i] = '<b>'.$i.'</b>';
            } else {
                $array_paging[$i] =
                    '<a href="index.php?pos='.
                    (($i-1) * $results_per_page).
                    $uri_parameter.'">'.$i.'</a>';
            }
        }

        $paging =
            $paging_text.' '.
            $array_paging['lower'].' '.$_CORELANG['TXT_TO'].' '.$array_paging['upper'].' '.
            $_CORELANG['TXT_FROM'].' '.$array_paging['total'].
            '&nbsp;&nbsp;[&nbsp;'.$array_paging['first'].'&lt;&lt;</a>&nbsp;&nbsp;';
        if ($page_number > 3) $paging .= $array_paging[$page_number-3].'&nbsp;';
        if ($page_number > 2) $paging .= $array_paging[$page_number-2].'&nbsp;';
        if ($page_number > 1) $paging .= $array_paging[$page_number-1].'&nbsp;';
        $paging .= $array_paging[$page_number].'&nbsp;';
        if ($page_number < $numof_pages-0) $paging .= $array_paging[$page_number+1].'&nbsp;';
        if ($page_number < $numof_pages-1) $paging .= $array_paging[$page_number+2].'&nbsp;';
        if ($page_number < $numof_pages-2) $paging .= $array_paging[$page_number+3].'&nbsp;';
        $paging .= '&nbsp;'.$array_paging['last'].'&gt;&gt;</a>&nbsp;]';
//echo("Paging::getPaging(count $count, uri_parameter $uri_parameter, paging_text $paging_text, showeverytime $showeverytime, limit $limit):<br />"."results_per_page ".$results_per_page."<br />numof_rows ".$numof_rows."<br />position ".$position."<br />uri_parameter ".$uri_parameter."<br />page_number ".$page_number."<br />numof_pages ".$numof_pages."<br />"."PAGING: ".htmlentities($paging)."<br />".$paging."<hr />");
        return $paging;
    }


    /**
     * Returns the current offset
     *
     * If the parameter 'pos' is present in the request, it overrides
     * the value stored in the session, if any.  Defaults to zero.
     * @return  integer           The position offset
     */
    static function getPosition()
    {
        if (!isset($_SESSION['paging']['pos']))
            $_SESSION['paging']['pos'] = 0;
        if (isset($_REQUEST['pos'])) {
            $position = intval($_REQUEST['pos']);
            unset($_REQUEST['pos']);
            $_SESSION['paging']['pos'] = $position;
        }
        return $_SESSION['paging']['pos'];
    }


    /**
     * Resets the paging offset to zero
     *
     * Call this if your query results in less records than the offset.
     */
    static function reset()
    {
        $_SESSION['paging']['pos'] = 0;
        unset($_REQUEST['pos']);
    }

}

?>
