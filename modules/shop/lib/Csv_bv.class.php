<?php
/**
 * The Shop
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */

/**
 * The Shop
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */
class Csv_bv
{
    /**
      * Seperator character
      * @var char
      * @access private
      */
    var $mFldSeperator;

    /**
      * Enclose character
      * @var char
      * @access private
      */
    var $mFldEnclosure;

    /**
      * Escape character
      * @var char
      * @access private
      */
    var $mFldEscapor;

    /**
      * Length of the largest row in bytes.Default is 4096
      * @var int
      * @access private
      */
    var $mRowSize;

    /**
      * Holds the file pointer
      * @var resource
      * @access private
      */
    var $mHandle;

    /**
      * Counts the number of rows that have been returned
      * @var int
      * @access private
      */
    var $mRowCount;

    /**
      * Counts the number of empty rows that have been skipped
      * @var int
      * @access private
      */
    var $mSkippedRowCount;

    /**
      * Determines whether empty rows should be skipped or not.
      * By default empty rows are returned.
      * @var boolean
      * @access private
      */
    var $mSkipEmptyRows;

    /**
      * Specifies whether the fields leading and trailing \s and \t should be removed
      * By default it is TRUE.
      * @var boolean
      * @access private
      */
    var $mTrimFields;


    /**
      * Constructor
      *
      * Only initialises class settings variables.
      * @param str $file - file path
      * @param str $seperator - Only one character is allowed (optional)
      * @param str $enclose - Only one character is allowed (optional)
      * @param str $escape - Only one character is allowed (optional)
      * @access public
      */
    function __construct($file, $seperator=';', $enclose='"', $escape='')
    {
        $this->mFldSeperator = $seperator;
        $this->mFldEnclosure = $enclose;
        $this->mFldEscapor = $escape;

        $this->mSkipEmptyRows = TRUE;
        $this->mTrimFields =  TRUE;

        $this->mRowCount = 0;
        $this->mSkippedRowCount = 0;

        $this->mRowSize = 4096;

        // Open file
        $this->mHandle = @fopen($file, "r") or trigger_error('Unable to open csv file', E_USER_ERROR);
    }


    /**
      * csv::NextLine() returns an array of fields from the next csv line.
      *
      * The position of the file pointer is stored in PHP internals.
      *
      * Empty rows can be skipped
      * Leading and trailing \s and \t can be removed from each field
      *
      * @access public
      * @return array of fields
      */
    function NextLine()
    {

        $arr_row = fgetcsv ($this->mHandle, $this->mRowSize, $this->mFldSeperator, $this->mFldEnclosure);

        if (feof($this->mHandle)){
            return False;
        }

        $this->mRowCount++;

        //-------------------------
        // Skip empty rows if asked to
        if ($this->mSkipEmptyRows){


            if ($arr_row[0] === ''  && count($arr_row) === 1){

                $this->mRowCount--;
                $this->mSkippedRowCount++;

                $arr_row = $this->NextLine();

                // This is to avoid a warning when empty lines are found at the bvery end of a file.
                if (!is_array($arr_row)){ // This will only happen if we are at the end of a file.
                    return FALSE;
                }
            }
        }

        //-------------------------
        // Remove leading and trailing spaces \s and \t
        if ($this->mTrimFields){
            array_walk($arr_row, array($this, 'ArrayTrim'));
        }

        //-------------------------
        // Remove escape character if it is not empty and different from the enclose character
        // otherwise fgetcsv removes it automatically and we don't have to worry about it.
        if ($this->mFldEscapor !== '' && $this->mFldEscapor !== $this->mFldEnclosure){
            array_walk($arr_row, array($this, 'ArrayRemoveEscapor'));
        }
        return $arr_row;
    }


    /**
      * csv::Csv2Array will return the whole csv file as 2D array
      *
      * @access public
      */
    function Csv2Array()
    {

        $arr_csv = array();

        while ($arr_row = $this->NextLine()){
            $arr_csv[] = $arr_row;
        }

        return $arr_csv;
    }


    /**
      * csv::ArrayTrim will remove \s and \t from an array
      *
      * It is called from array_walk.
      * @access private
      */
    function ArrayTrim(&$item, $key)
    {
        $item = trim($item, " \t"); // space and tab
    }


    /**
      * csv::ArrayRemoveEscapor will escape the enclose character
      *
      * It is called from array_walk.
      * @access private
      */
    function ArrayRemoveEscapor(&$item, $key)
    {
        $item = str_replace($this->mFldEscapor.$this->mFldEnclosure, $this->mFldEnclosure, $item);
    }


    /**
      * csv::RowCount return the current row count
      *
      * @access public
      * @return int
      */
    function RowCount()
    {
        return $this->mRowCount;
    }


    /**
      * csv::RowCount return the current skipped row count
      *
      * @access public
      * @return int
      */
    function SkippedRowCount()
    {
        return $this->mSkippedRowCount;
    }


    /**
      * csv::SkipEmptyRows, sets whether empty rows should be skipped or not
      *
      * @access public
      * @param bool $bool
      * @return void
      */
    function SkipEmptyRows($bool = TRUE)
    {
        $this->mSkipEmptyRows = $bool;
    }


    /**
      * csv::TrimFields, sets whether fields should have their \s and \t removed.
      *
      * @access public
      * @param bool $bool
      * @return void
      */
    function TrimFields($bool = TRUE)
    {
        $this->mTrimFields = $bool;
    }
}

?>
