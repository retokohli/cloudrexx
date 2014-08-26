<?php

/**
 * JSON Adapter for Cx\Core_Modules\LinkManager\Model\Entity\Link
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_json
 */

namespace Cx\Core_Modules\LinkManager\Controller;
use \Cx\Core\Json\JsonAdapter;

/**
 * JSON Adapter for Cx\Core_Modules\LinkManager\Model\Entity\Link
 * the class JsonLink handles, the link status whether the link is resolved or not.
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_json
 */
class JsonLink implements JsonAdapter {
    /**
     * Reference to the Doctine EntityManager
     * @var \Doctrine\ORM\EntityManager 
     */
    private $em = null;
    
    /**
     * Reference to the Doctrine NodeRepo
     * @var \Cx\Core_Modules\LinkManager\Model\Repository\LinkRepository
     */
    private $linkRepo = null;
    
    /**
     * List of messages
     * @var Array 
     */
    private $messages = array();
    
    
    /**
     * Constructor
     * the class JsonLink handles, the link status whether the link is resolved or not.
     */
    public function __construct() {
        $this->em = \Env::em();
        if ($this->em) {
            $this->linkRepo = $this->em->getRepository('\Cx\Core_Modules\LinkManager\Model\Entity\Link');
        }
    }
    
    /**
     * Returns the internal name used as identifier for this adapter
     * @return String Name of this adapter
     */
    public function getName() {
        return 'link';
    }
    
    /**
     * Returns an array of method names accessable from a JSON request
     * @return array List of method names
     */
    public function getAccessableMethods() {
        return array('modifyLinkStatus');
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString() {
        return implode('<br />', $this->messages);
    }
    
    /**
     * Edit the link status(link resolved or not)
     * 
     * @return array
     */
    public function modifyLinkStatus() 
    {
        //get post values
        $id             = isset($_GET['id']) ? contrexx_input2raw($_GET['id']) : 0;
        $solvedLinkStat = isset($_GET['status']) ? $_GET['status'] : 0;
        
        $result  = array();
        $objUser = new \Cx\Core_Modules\LinkManager\Controller\User();
        if ($objUser) {
            $user = $objUser->getUpdatedUserName(0, 1);
        }
        
        if (!empty($id)) {
            $linkStatus = ($solvedLinkStat == 0) ? 1 : 0;
            $userId     = $linkStatus ? $user['id'] : 0;
            $brokenLink = $this->linkRepo->findOneBy(array('id' => $id));
            
            $brokenLink->setLinkStatus($linkStatus);
            $brokenLink->setUpdatedBy($userId);
            
            $this->em->persist($brokenLink);
            $this->em->flush();
            
            $result['linkStatus'] = $linkStatus;
            $result['userName']   = $user['name'];
        }
        return $result;
    }
    
}
