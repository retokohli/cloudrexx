<?php
/**
 * Contrexx
 *
 * @link      http://www.contrexx.com
 * @copyright Comvation AG 2007-2014
 * @version   Contrexx 4.0
 * 
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Contrexx" is a registered trademark of Comvation AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

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
