<?php
/**
 * Listing handler
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_listing
 */

namespace Cx\Core_Modules\Listing\Controller;

/**
 * This class defines a handler for Listings
 * (for example the PagingController)
 * @author ritt0r
 * @package     contrexx
 * @subpackage  coremodule_listing
 */
abstract class ListingHandler {
    
    /**
     * Constructor for the Handler. Do not
     * do anything Listing specific (e.g.
     * don't initialize page number here)
     */
    public abstract function __construct();
    
    /**
     * Handles the listing by changing parameters like limit and offset
     *
     * Both the params argument and the return value look like this:
     * array(
     *     'offset'     => {integer},   // start offset to use
     *     'count'      => {integer},   // number of entries to show
     *     'criteria'   => {array},     // criteria (similiar to SQLs WHERE)
     *     'order'      => {array},     // order to sort by
     * )
     * @param array $params Parameters
     * @param array $config Configuration
     * @return array The handled parameters
     */
    public abstract function handle($params, $config);
}
