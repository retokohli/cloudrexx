<?php
/**
 * User class
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_linkmanager
 */

namespace Cx\Modules\LinkManager\Controller;

/**
 * The class User for getting the user name
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_linkmanager
 */

class User extends \User {
    
    /**
     * Get the user name
     * 
     * @param integer $userId
     * 
     * @return string
     */
    public function getUpdatedUserName($userId, $currentUser)
    {
        $objFwUser = \FWUser::getFWUserObject();
        
        if (!empty($userId)) {
            $objUser = $objFwUser->objUser->getUser($userId);
            if ($objUser) {
                return $objUser->getUsername();
            }
        } else if (empty ($userId) && $currentUser) {
            return array(
                'id'    => $objFwUser->objUser->getId(),
                'name'  => $objFwUser->objUser->getUsername()
            );
        }
        return false;
    }        
    
}