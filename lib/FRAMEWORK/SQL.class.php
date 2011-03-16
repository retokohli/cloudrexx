<?php
/**
 * Provides SQL building functions.
 */
class SQL
{
    /**
     * Generates insert SQL
     * @param string $table the table name
     * @param array $columns array(
     *     <column_name> => <data> 
     *                 | array(
     *             'val' => string, #the value
     *             [ 'omitEmpty' => boolean ] #skip fields with empty value (null or empty string)? defaults to false
     *         )
     *     )
     *     [ , ... ]
     * @param array $options global options. optional.
     *              array(
     *                  'escape' => boolean #whether strings are escaped automatically
     *              )
     */
    public static function insert($table, $columns, $options=array()) 
    {
        $escape = false;
        if(isset($options['escape']))
           $escape = $options['escape'];
        
        $sql  = 'INSERT INTO `'.DBPREFIX."$table` ";
        $sql .= self::columnPart($columns, $escape);
        return $sql;
    }

    /**
     * Generates update SQL
     * @param string $table the table name
     * @param array $columns array(
     *     <column_name> => <data> 
     *                 | array(
     *             'val' => string, #the value
     *             [ 'omitEmpty' => boolean ] #skip fields with empty value (null or empty string)? defaults to false
     *         )
     *     )
     *     [ , ... ]
     * @param array $options global options. optional.
     *              array(
     *                  'escape' => boolean #whether strings are escaped automatically
     *              )
     */
    public static function update($table, $columns, $options=array())
    {
        $escape = false;
        if(isset($options['escape']))
            $escape = $options['escape'];

        $sql  = 'UPDATE `'.DBPREFIX."$table` ";
        $sql .= self::columnPart($columns, $escape);
        return $sql;        
    }

    protected static function columnPart($columns, $escape) 
    {
        $result = "SET \n";

        $firstCol = true;
        foreach($columns as $column => $data) {
            $value = '';
            if(!is_array($data)) { //raw data provided
                $value = self::apostrophizeIfString($data, $escape);
            }
            else { //hooray, array.
                $value = $data['val'];
                if($data['omitEmpty'] === true) { //skip null and empty strings
                    if(null === $value || '' === $value) {
                        continue;
                    }
                }
                $value = self::apostrophizeIfString($value, $escape);
            }
            $result .= '    '.($firstCol ? '' : ',') ."`$column` = $value\n";
            $firstCol = false;
        }
        return $result;
    }

    protected static function apostrophizeIfString($value, $escape) 
    {
        if(is_string($value)) { //escape strings
            if($escape)
                $value = contrexx_raw2db($value);
            return "'$value'";
        }
        return $value;
    }
}
