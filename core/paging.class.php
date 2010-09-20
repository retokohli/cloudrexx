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
if (preg_match("#".$_SERVER['PHP_SELF']."#", __FILE__)) {
    Header("Location: ../index.php");
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
 * @param     int         $numof_rows     The number of rows being displayed
 * @param     int         $pos            The offset from the first row
 * @param     string      $uri_parameter
 * @param     string      $paging_text
 * @param     boolean     $showeverytime
 * @param     int         $results_per_page
 * @return    string      Result
 * @todo      Change the system to use the new, static class method,
 *            then remove this one.
 */
function getPaging(
    $numof_rows, $pos=null, $uri_parameter, $paging_text,
    $showeverytime=false, $results_per_page=null
) {
    return Paging::getPaging(
        $numof_rows, $pos, $uri_parameter, $paging_text,
        $showeverytime, $results_per_page
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
     * @param     integer     $numof_rows     The number of rows available
     * @param     integer     $position       The optional starting position
     *                                        offset.  Defaults to null
     * @param     string      $uri_parameter  Optional additional URI parameters,
     *                                        *MUST* start with an URI encoded
     *                                        ampersand (&amp;)
     * @param     string      $paging_text    The text to be put in front of the paging
     * @param     boolean     $showeverytime  If true, the paging is shown even if
     *                                        $numof_rows is less than the number of rows
     *                                        on a single page
     * @param     integer     $results_per_page   The optional maximum number of
     *                                        rows to be shown on a single page.
     *                                        Defaults to the corePagingLimit
     *                                        setting.
     * @return    string                      HTML code for the paging
     */
    static function getPaging(
        $numof_rows, $position=null, &$uri_parameter, $paging_text,
        $showeverytime=false, $results_per_page=0
    ) {
        global $_CONFIG, $_CORELANG;

        if (empty($results_per_page))
            $results_per_page = intval($_CONFIG['corePagingLimit']);
        if ($numof_rows <= $results_per_page && !$showeverytime) return '';

        // Remove the old position parameter from the URI
        Html::stripUriParam($uri_parameter, 'pos');
        // Strip script path and name from the URI
        $uri_parameter = preg_replace('/^.*?index.php/', '', $uri_parameter);
        // Remove leading '?', '&', or '&amp;'
        $uri_parameter = preg_replace(
            '/^(?:\?|\&(?:amp;)?)?/', '', $uri_parameter);
        // Prepend an encoded ampersand only if the query is not empty
        if ($uri_parameter) $uri_parameter = '&amp;'.$uri_parameter;
// I don't think it's a good idea to decode the URI without re-encoding
// it later (see getPagingArray())!
//        $uri_parameter = urldecode($uri_parameter);
        if (!isset($position)) $position = self::getPosition();
        // Fix illegal values:
        // The position must be in the range [0 .. numof_rows - 1].
        // If it's outside this range, reset it
        if ($position < 0 || $position >= $numof_rows) $position = 0;

        // Total number of pages: [1 .. n]
//        $numof_pages = intval(0.999 + $numof_rows / $results_per_page);
        $numof_pages = ceil($numof_rows / $results_per_page);
        // Current page number: [1 .. numof_pages]
        $page_number = 1 + intval($position / $results_per_page);

        $corr_value = $results_per_page;
        if ($numof_rows % $results_per_page) {
            $corr_value = $numof_rows % $results_per_page;
        }

        // Set up the base navigation entries
        $array_paging = array(
            'first' => '<a class="pagingFirst" href="index.php?pos=0'.
                          $uri_parameter.'">',
            'last'  => '<a class="pagingLast" href="index.php?pos='.
                          ($numof_rows - $corr_value).$uri_parameter.'">',
            'total' => $numof_rows,
            'lower' => ($numof_rows ? $position + 1 : 0),
            'upper' => $numof_rows,
        );
        if ($position + $results_per_page < $numof_rows) {
            $array_paging['upper'] = $position + $results_per_page;
        }
        // Note:  previous/next link are currently unused.
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
                $array_paging[$i] =
                    '<b class="pagingPage'.$i.'">'.$i.'</b>';
            } else {
                $array_paging[$i] =
                    '<a class="pagingPage'.$i.'" href="index.php?pos='.
                    (($i-1) * $results_per_page).
                    $uri_parameter.'">'.$i.'</a>';
            }
        }
        $paging =
            $paging_text.
            '&nbsp;<span class="pagingLower">'.$array_paging['lower'].
            '</span>&nbsp;'.$_CORELANG['TXT_TO'].
            '&nbsp;<span class="pagingUpper">'.$array_paging['upper'].
            '</span>&nbsp;'.$_CORELANG['TXT_FROM'].
            '&nbsp;<span class="pagingTotal">'.$array_paging['total'].
            '</span>';
        if ($numof_pages) $paging .=
            '&nbsp;&nbsp;[&nbsp;'.$array_paging['first'].
            '&lt;&lt;</a>&nbsp;&nbsp;'.
            '<span class="pagingPages">';
        if ($page_number > 3) $paging .= $array_paging[$page_number-3].'&nbsp;';
        if ($page_number > 2) $paging .= $array_paging[$page_number-2].'&nbsp;';
        if ($page_number > 1) $paging .= $array_paging[$page_number-1].'&nbsp;';
        if ($numof_pages) $paging .= $array_paging[$page_number].'&nbsp;';
        if ($page_number < $numof_pages-0) $paging .= $array_paging[$page_number+1].'&nbsp;';
        if ($page_number < $numof_pages-1) $paging .= $array_paging[$page_number+2].'&nbsp;';
        if ($page_number < $numof_pages-2) $paging .= $array_paging[$page_number+3].'&nbsp;';
        if ($numof_pages) $paging .=
            '</span>&nbsp;'.$array_paging['last'].'&gt;&gt;</a>&nbsp;]';
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
