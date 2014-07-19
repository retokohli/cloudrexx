<?php
/**
 * Class User
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Manish Thakur <manishthakur@cdnsol.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
 
namespace Cx\Core_Modules\MultiSite\Model\Entity;

/**
 * Class User
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Manish Thakur <manishthakur@cdnsol.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class User extends \User {
    public function setId($id){
        $this->id = $id;
    }    
}
