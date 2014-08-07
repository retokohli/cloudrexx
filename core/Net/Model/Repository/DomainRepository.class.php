<?php
/**
 * Domain Repository
 *
 * Repository to manage the domain entities.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @package     contrexx
 * @subpackage  core_model
 */

namespace Cx\Core\Net\Model\Repository;

/**
 * Domain Repository
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @package     contrexx
 * @subpackage  core_model
 */
class DomainRepositoryException extends \Exception {};

/**
 * Domain Repository
 *
 * Repository to manage the domain entities.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @package     contrexx
 * @subpackage  core_model
 */
class DomainRepository extends \Cx\Core\Model\Controller\YamlRepository {
    /**
     * Constructor to initialize the YamlRepository with source
     * file config/DomainRepository.yml.
     */
    public function __construct() {
        parent::__construct(\Env::get('cx')->getWebsiteConfigPath() . '/DomainRepository.yml');
        
        //Initialize the Hostname Domain
        $hostName = new \Cx\Core\Net\Model\Entity\Domain($_SERVER['SERVER_NAME']);
        
        $hostName->setVirtual(true);
        //attach the hostname domain entity to repository
        $this->add($hostName);
    }
    
    public function getMainDomain() {
        $config = \Env::get('config');
        
        if (!empty($config['mainDomainId']) && isset($this->entities[$config['mainDomainId']])) {
            return $this->entities[$config['mainDomainId']];
        }

        $objDomain = $this->findBy(array('name' => $_SERVER['SERVER_NAME']));
        return $objDomain[0];
    }
}

