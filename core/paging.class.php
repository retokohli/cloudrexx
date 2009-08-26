<?php
/**
 * Paging
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @version 1.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

//Security-Check
if (eregi("paging.class.php",$_SERVER['PHP_SELF'])) {
    Header("Location: index.php");
    die();
}


/**
 * Function getPaging
 *
 * helping function for the Paging class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @access public
 * @version 1.0.0
 * @global  array       Configuration
 * @global  array       Core language
 * @param   int         $count
 * @param   int         $pos
 * @param   string      $extargv
 * @param   string      $paging_text
 * @param   boolean     $showeverytime
 * @param   int         $limit
 * @return  string      Result
 */
function getPaging($count, $pos, $extargv, $paging_text, $showeverytime = false, $limit = null)
{
    global $_CONFIG, $_CORELANG;

    if ($count > 0) {
        $int_num_result = (empty($limit)) ? intval($_CONFIG['corePagingLimit']) : $limit;
        if (($count > $int_num_result) OR ($showeverytime == true)) {
            $p = new Paging( $count, $pos, $int_num_result, $extargv );
            $array_paging = $p->getPagingArray();
            $array_row_paging = $p->getPagingRowArray();
            $paging = $paging_text.' <span id="pagingLower">'.$array_paging['lower'].'</span> '.$_CORELANG['TXT_TO']
                                  .' <span id="pagingUpper">'.$array_paging['upper'].'</span> '.$_CORELANG['TXT_FROM']
                                  .' <span id="pagingTotal">'.$array_paging['total'].'</span>';

            //$paging .= '&nbsp;&nbsp;[&nbsp;'. $array_paging['previous_link'] .''.$_CORELANG['txtBack'].'</a>&nbsp;&nbsp;' ;
            $paging .= '&nbsp;&nbsp;[&nbsp;'.$array_paging['first'].'&lt;&lt;</a>&nbsp;&nbsp;' ;

            $currpage=$p->getCurrentPage();
            $totalpages=sizeof($array_row_paging);
            //if ($currpage>2 && $totalpages>3) $paging .='..';

            $paging .= '<span id="pagingPages">';

            if ($currpage > 2) $paging .= $array_row_paging[$currpage-3].'&nbsp;';
            if ($currpage > 1) $paging .= $array_row_paging[$currpage-2].'&nbsp;';
            if ($currpage > 0) $paging .= $array_row_paging[$currpage-1].'&nbsp;';

            $paging .= $array_row_paging[$currpage] .'&nbsp;';

            if ($currpage < $totalpages-1) $paging .= $array_row_paging[$currpage+1].'&nbsp;';
            if ($currpage < $totalpages-2) $paging .= $array_row_paging[$currpage+2].'&nbsp;';
            if ($currpage < $totalpages-3) $paging .= $array_row_paging[$currpage+3].'&nbsp;';

            $paging .= '</span>';

            $paging .= '&nbsp;'.$array_paging['last'] .'&gt;&gt;</a>&nbsp;]';
            return $paging;
        } else
            return '';
    } else
        return '';
}


/**
 * Class Paging
 *
 * Creates the link for paging
 * @package contrexx
 * @subpackage core
 * @version 1.0.0
 */
class Paging
{
    var $result_per_page;
    var $row;
    var $cur_position;
    var $ext_argv;

    /**
     * Constructor
     * @param     integer  $row
     * @param     integer  $cur_position
     * @param     integer  $result_per_page
     * @param     string   $ext_argv
     */
    function __construct($row, $cur_position, $result_per_page=30, $ext_argv='')
    {
        $this->row = $row;
        $this->result_per_page = $result_per_page;
        $this->cur_position = $cur_position;
        $this->ext_argv = urldecode( $ext_argv );
    }


    /**
     * Get the number of pages to be displayed
     *
     * This function returns the total number of pages to be displayed.
     * @return    integer  Number of pages
     */
    function getNumberOfPage()
    {
        $int_nbr_page = $this->row / $this->result_per_page;
        return $int_nbr_page;
    }


    /**
     * Get the page number
     *
     * This function returns the current page number.
     * @return    integer  Page number
     */
    function getCurrentPage()
    {
        $int_cur_page = ( $this->cur_position * $this->getNumberOfPage() ) / $this->row;
        return number_format( $int_cur_page, 0 );
    }


    /**
    * Get the paging array
    *
    * This function returns the paging.
    * @return    array    Paging array
    * @todo There has to be a better description than the current one.
    *       What is a 'paging', after all?
    */
    function getPagingArray()
    {
        $array_paging['lower'] = ( $this->cur_position + 1 );

        if ($this->cur_position + $this->result_per_page >= $this->row) {
          $array_paging['upper'] = $this->row;
        } else {
          $array_paging['upper'] = ( $this->cur_position + $this->result_per_page );
        }

        $array_paging['total'] = $this->row;
        $array_paging['first'] = '<a id="pagingFirst" href="index.php?pos=0'.$this->ext_argv.'">';

        if ($this->row % $this->result_per_page ==0) {
           $corr_value=$this->result_per_page;
        } else {
           $corr_value=$this->row % $this->result_per_page;
        }
        $array_paging['last'] = '<a id="pagingLast" href="index.php?pos='. ($this->row - $corr_value).$this->ext_argv .'">';

        if ($this->cur_position != 0) {
          $array_paging['previous_link'] = "<a href=\"index.php?pos=". ( $this->cur_position - $this->result_per_page ).$this->ext_argv ."\">";
        }

        if (($this->row - $this->cur_position ) > $this->result_per_page)
        {
            $int_new_position = $this->cur_position + $this->result_per_page;
            $array_paging['next_link'] = "<a href=\"index.php?pos=$int_new_position". $this->ext_argv ."\">";
        }
        return $array_paging;
    }

    /**
     * Get the paging row array
     *
     * This function returns an array of string (href link with the page number)
     * @return    array    Array of links to all pages
     */
    function getPagingRowArray()
    {
        for ($i=0; $i<$this->getNumberOfPage(); $i++) {
            if ($i == $this->getCurrentPage()) {
                $array_all_page[$i] = '<b id="pagingPage'.$i.'">'. ($i+1) ."</b>";
            } else {
                $int_new_position   = $i * $this->result_per_page;
                $array_all_page[$i] = '<a id="pagingPage'.$i.'" href="index.php?pos='.$int_new_position.$this->ext_argv.'">'. ($i+1) ."</a>";
            }
        }
        return $array_all_page;
    }
}
?>
