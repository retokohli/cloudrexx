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
     * This method is called once for each Listing
     * @param int $offset The limit offset for the listing
     * @param int $count The limit count for the listing
     * @param array $criteria Criteria to be matched by the listing
     * @param array $order Order listing by this fields
     * @param array $args Arguments supplied for this listing
     */
    public abstract function handle(&$offset, &$count, &$criteria, &$order, &$args);
}
