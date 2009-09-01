<?php

/**
 * Dietiker module sorting functions (custom version for dietiker.ch)
 *
 * @version     $Id: 1.0.0$
 * @package     contrexx
 * @subpackage  module_dietiker
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 */


/**
 * Sorting function -- for sorting an array by its 'ord' field
 * @param   integer     $a        First argument
 * @param   integer     $b        Second argument
 * @return  integer               -1 if the first argument is less than
 *                                the second,
 *                                1 if the first argument is greater than
 *                                the second,
 *                                0 otherwise.
 */
function cmp_ord($a, $b)
{
    if ($a['ord'] < $b['ord']) return -1;
    if ($a['ord'] > $b['ord']) return +1;
    return 0;
}

/**
 * Sorting function -- for sorting an array by its 'active' field
 * @param   integer     $a        First argument
 * @param   integer     $b        Second argument
 * @return  integer               -1 if the first argument is less than
 *                                the second,
 *                                1 if the first argument is greater than
 *                                the second,
 *                                0 otherwise.
 */
function cmp_active($a, $b)
{
    if ($a['active'] < $b['active']) return -1;
    if ($a['active'] > $b['active']) return +1;
    return 0;
}

/**
 * Sorting function -- for sorting an array by its 'product_id' field
 * @param   integer     $a        First argument
 * @param   integer     $b        Second argument
 * @return  integer               -1 if the first argument is less than
 *                                the second,
 *                                1 if the first argument is greater than
 *                                the second,
 *                                0 otherwise.
 */
function cmp_product_id($a, $b)
{
    if ($a['product_id'] < $b['product_id']) return -1;
    if ($a['product_id'] > $b['product_id']) return +1;
    return 0;
}

/**
 * Sorting function -- for sorting an array by its 'designer_id' field
 * @param   integer     $a        First argument
 * @param   integer     $b        Second argument
 * @return  integer               -1 if the first argument is less than
 *                                the second,
 *                                1 if the first argument is greater than
 *                                the second,
 *                                0 otherwise.
 */
function cmp_designer_id($a, $b)
{
    if ($a['designer_id'] < $b['designer_id']) return -1;
    if ($a['designer_id'] > $b['designer_id']) return +1;
    return 0;
}

/**
 * Sorting function -- for sorting an array by its 'reference_id' field
 * @param   integer     $a        First argument
 * @param   integer     $b        Second argument
 * @return  integer               -1 if the first argument is less than
 *                                the second,
 *                                1 if the first argument is greater than
 *                                the second,
 *                                0 otherwise.
 */
function cmp_reference_id($a, $b)
{
    if ($a['reference_id'] < $b['reference_id']) return -1;
    if ($a['reference_id'] > $b['reference_id']) return +1;
    return 0;
}

/**
 * Sorting function -- for sorting an array by its 'category_id' field
 * @param   integer     $a        First argument
 * @param   integer     $b        Second argument
 * @return  integer               -1 if the first argument is less than
 *                                the second,
 *                                1 if the first argument is greater than
 *                                the second,
 *                                0 otherwise.
 */
function cmp_category_id($a, $b)
{
    if ($a['category_id'] < $b['category_id']) return -1;
    if ($a['category_id'] > $b['category_id']) return +1;
    return 0;
}

/**
 * Sorting function -- for sorting an array by its 'download_id' field
 * @param   integer     $a        First argument
 * @param   integer     $b        Second argument
 * @return  integer               -1 if the first argument is less than
 *                                the second,
 *                                1 if the first argument is greater than
 *                                the second,
 *                                0 otherwise.
 */
function cmp_download_id($a, $b)
{
    if ($a['download_id'] < $b['download_id']) return -1;
    if ($a['download_id'] > $b['download_id']) return +1;
    return 0;
}

/**
 * Sorting function -- for sorting an array by its 'property_id' field
 * @param   integer     $a        First argument
 * @param   integer     $b        Second argument
 * @return  integer               -1 if the first argument is less than
 *                                the second,
 *                                1 if the first argument is greater than
 *                                the second,
 *                                0 otherwise.
 */
function cmp_property_id($a, $b)
{
    if ($a['property_id'] < $b['property_id']) return -1;
    if ($a['property_id'] > $b['property_id']) return +1;
    return 0;
}

/**
 * Sorting function -- for sorting an array by its 'name' field
 * @param   integer     $a        First argument
 * @param   integer     $b        Second argument
 * @return  integer               -1 if the first argument is less than
 *                                the second,
 *                                1 if the first argument is greater than
 *                                the second,
 *                                0 otherwise.
 */
function cmp_name($a, $b)
{
    if ($a['name'] < $b['name']) return -1;
    if ($a['name'] > $b['name']) return +1;
    return 0;
}

/**
 * Sorting function -- for sorting an array by its 'type' field
 * @param   integer     $a        First argument
 * @param   integer     $b        Second argument
 * @return  integer               -1 if the first argument is less than
 *                                the second,
 *                                1 if the first argument is greater than
 *                                the second,
 *                                0 otherwise.
 */
function cmp_type($a, $b)
{
    if ($a['type'] < $b['type']) return -1;
    if ($a['type'] > $b['type']) return +1;
    return 0;
}

/**
 * Sorting function -- for sorting an array by its 'path' field
 * @param   integer     $a        First argument
 * @param   integer     $b        Second argument
 * @return  integer               -1 if the first argument is less than
 *                                the second,
 *                                1 if the first argument is greater than
 *                                the second,
 *                                0 otherwise.
 */
function cmp_path($a, $b)
{
    if ($a['path'] < $b['path']) return -1;
    if ($a['path'] > $b['path']) return +1;
    return 0;
}

/**
 * Sorting function -- for sorting an array by its 'product_name' field
 * @param   integer     $a        First argument
 * @param   integer     $b        Second argument
 * @return  integer               -1 if the first argument is less than
 *                                the second,
 *                                1 if the first argument is greater than
 *                                the second,
 *                                0 otherwise.
 */
function cmp_product_name($a, $b)
{
    if ($a['product_name'] < $b['product_name']) return -1;
    if ($a['product_name'] > $b['product_name']) return +1;
    return 0;
}


// Added 20081014

/**
 * Sorting function -- for sorting an array by its 'line_id' field
 * @param   integer     $a        First argument
 * @param   integer     $b        Second argument
 * @return  integer               -1 if the first argument is less than
 *                                the second,
 *                                1 if the first argument is greater than
 *                                the second,
 *                                0 otherwise.
 */
function cmp_line_id($a, $b)
{
    if ($a['line_id'] < $b['line_id']) return -1;
    if ($a['line_id'] > $b['line_id']) return +1;
    return 0;
}


/**
 * Sorting function -- for sorting an array by its 'line_name' field
 * @param   integer     $a        First argument
 * @param   integer     $b        Second argument
 * @return  integer               -1 if the first argument is less than
 *                                the second,
 *                                1 if the first argument is greater than
 *                                the second,
 *                                0 otherwise.
 */
function cmp_line_name($a, $b)
{
    if ($a['line_name'] < $b['line_name']) return -1;
    if ($a['line_name'] > $b['line_name']) return +1;
    return 0;
}


/**
 * Sorting function -- for sorting an array by its 'price_group_id' field
 * @param   integer     $a        First argument
 * @param   integer     $b        Second argument
 * @return  integer               -1 if the first argument is less than
 *                                the second,
 *                                1 if the first argument is greater than
 *                                the second,
 *                                0 otherwise.
 */
function cmp_price_group_id($a, $b)
{
    if ($a['price_group_id'] < $b['price_group_id']) return -1;
    if ($a['price_group_id'] > $b['price_group_id']) return +1;
    return 0;
}


/**
 * Sorting function -- for sorting an array by its 'material_id' field
 * @param   integer     $a        First argument
 * @param   integer     $b        Second argument
 * @return  integer               -1 if the first argument is less than
 *                                the second,
 *                                1 if the first argument is greater than
 *                                the second,
 *                                0 otherwise.
 */
function cmp_material_id($a, $b)
{
    if ($a['material_id'] < $b['material_id']) return -1;
    if ($a['material_id'] > $b['material_id']) return +1;
    return 0;
}


/**
 * Sorting function -- for sorting an array by its 'material_name' field
 * @param   integer     $a        First argument
 * @param   integer     $b        Second argument
 * @return  integer               -1 if the first argument is less than
 *                                the second,
 *                                1 if the first argument is greater than
 *                                the second,
 *                                0 otherwise.
 */
function cmp_material_name($a, $b)
{
    if ($a['material_name'] < $b['material_name']) return -1;
    if ($a['material_name'] > $b['material_name']) return +1;
    return 0;
}


/**
 * Sorting function -- for sorting an array by its 'manufacturer_id' field
 * @param   integer     $a        First argument
 * @param   integer     $b        Second argument
 * @return  integer               -1 if the first argument is less than
 *                                the second,
 *                                1 if the first argument is greater than
 *                                the second,
 *                                0 otherwise.
 */
function cmp_manufacturer_id($a, $b)
{
    if ($a['manufacturer_id'] < $b['manufacturer_id']) return -1;
    if ($a['manufacturer_id'] > $b['manufacturer_id']) return +1;
    return 0;
}


/**
 * Sorting function -- for sorting an array by its 'manufacturer_name' field
 * @param   integer     $a        First argument
 * @param   integer     $b        Second argument
 * @return  integer               -1 if the first argument is less than
 *                                the second,
 *                                1 if the first argument is greater than
 *                                the second,
 *                                0 otherwise.
 */
function cmp_manufacturer_name($a, $b)
{
    if ($a['manufacturer_name'] < $b['manufacturer_name']) return -1;
    if ($a['manufacturer_name'] > $b['manufacturer_name']) return +1;
    return 0;
}



?>
