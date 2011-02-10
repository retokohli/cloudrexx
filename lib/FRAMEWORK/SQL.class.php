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
     * @param array $options global options. optional. and not implemented yet.
     */
    public static function insert($table, $columns, $options=array()) 
    {
        $sql  = 'INSERT INTO `'.DBPREFIX."$table` ";
        $sql .= self::columnPart($columns);
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
     * @param array $options global options. optional. and not implemented yet.
     */
    public static function update($table, $columns, $options=array())
    {
        $sql  = 'UPDATE `'.DBPREFIX."$table` ";
        $sql .= self::columnPart($columns);
        return $sql;        
    }

    protected static function columnPart($columns) 
    {
        $result = "SET \n";

        $firstCol = true;
        foreach($columns as $column => $data) {
            $value = '';
            if(!is_array($data)) { //raw data provided
                $value = self::apostrophizeIfString($data);
            }
            else { //hooray, array.
                $value = $data['val'];
                if($data['omitEmpty'] === true) { //skip null and empty strings
                    if(null === $value || '' === $value) {
                        continue;
                    }
                }
                $value = self::apostrophizeIfString($value);
            }
            $result .= '    '.($firstCol ? '' : ',') ."`$column` = $value\n";
            $firstCol = false;
        }
        return $result;
    }

    protected static function apostrophizeIfString($value) 
    {
        if(is_string($value)) //escape strings
            return "'$value'";
        return $value;
    }
}
