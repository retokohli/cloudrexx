<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
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
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Host
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_sync
 */

namespace Cx\Core_Modules\Sync\Model\Entity;

/**
 * Host
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_sync
 */
class Host extends \Cx\Model\Base\EntityBase
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $host
     */
    protected $host;

    /**
     * @var boolean $active
     */
    protected $active;

    /**
     * @var string $apiKey
     */
    protected $apiKey;

    /**
     * @var integer $apiVersion
     */
    protected $apiVersion;

    /**
     * @var string $urlTemplate
     */
    protected $urlTemplate;

    /**
     * @var Cx\Core_Modules\Sync\Model\Entity\HostEntity
     */
    protected $hostEntities;
    
    /**
     * @var string Default template for URI
     */
    protected $defaultUrlTemplate = '[[HOST]]/api/sync/v[[API_VERSION]]/json/[[DATA_SOURCE]]/[[INDEX_DATA]]?apikey=[[API_KEY]]';

    public function __construct()
    {
        $this->hostEntities = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set host
     *
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * Get host
     *
     * @return string $host
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set active
     *
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * Get active
     *
     * @return boolean $active
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set apiKey
     *
     * @param string $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Get apiKey
     *
     * @return string $apiKey
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set apiVersion
     *
     * @param integer $apiVersion
     */
    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
    }

    /**
     * Get apiVersion
     *
     * @return integer $apiVersion
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * Set urlTemplate
     *
     * @param string $urlTemplate
     */
    public function setUrlTemplate($urlTemplate)
    {
        if ($urlTemplate == $this->defaultUrlTemplate) {
            $urlTemplate = null;
        }
        $this->urlTemplate = $urlTemplate;
    }

    /**
     * Get urlTemplate
     *
     * @return string $urlTemplate
     */
    public function getUrlTemplate()
    {
        if (empty($this->urlTemplate)) {
            return $this->defaultUrlTemplate;
        }
        return $this->urlTemplate;
    }

    /**
     * Add hostEntity
     *
     * @param Cx\Core_Modules\Sync\Model\Entity\HostEntity $hostEntity
     */
    public function addHostEntity(\Cx\Core_Modules\Sync\Model\Entity\HostEntity $hostEntity)
    {
        $this->hostEntities[] = $hostEntity;
    }

    /**
     * Get hostEntities
     *
     * @return Doctrine\Common\Collections\Collection $hostEntities
     */
    public function getHostEntities()
    {
        return $this->hostEntities;
    }

    /**
     * Set hostEntities
     *
     * @param array $hostEntities
     */
    public function setHostEntities($hostEntities)
    {
        $this->hostEntities = $hostEntities;
    }
    
    /**
     * Returns the URL for pushing data to this host
     * @param string $dataSourceName DataSource name to get the URL for
     * @param array $entityIndexData (optional) Field=>Value-type array with entity index data
     * @return string URL to push to
     */
    public function getToUri($dataSourceName, $entityIndexData = array()) {
        $indexData = implode('/', $entityIndexData);
        
        //<domain>(/<offset>)/api/sync/<apiVersion>/<outputModule>/<dataSource>/<parameters>[(?apikey=<apikey>(&<options>))|?<options>]
        //<domain>(/<offset>)/api/sync/<apiVersion>/<outputModule>/[[DATA_SOURCE]]/[[INDEX_DATA]][(?apikey=[[API_KEY]](&<options>))|?<options>]
        $uri = $this->getUrlTemplate();
        $uri = str_replace('[[HOST]]', $this->getHost(), $uri);
        $uri = str_replace('[[API_VERSION]]', $this->getApiVersion(), $uri);
        $uri = str_replace('[[DATA_SOURCE]]', $dataSourceName, $uri);
        $uri = str_replace('[[API_KEY]]', $this->getApiKey(), $uri);
        $uri = str_replace('[[INDEX_DATA]]', $indexData, $uri);
        return $uri;
    }
    
    /**
     * Calculates the necessary changeset for a model event on this host and adds it to spooler
     * @todo: If "active" value of this entity changes we should delete the remote entity
     */
    public function handleModelChange($entityIndexData, $entityClassName, $entity, $eventType, $spooler, $sync) {
        // do I sync this entity?
        if (!$this->getActive()) {
            return;
        }
        
        $handle = false;
        foreach ($sync->getHostEntitiesIncludingLegacy() as $hostEntity) {
            if ($hostEntity['host'] != $this) {
                continue;
            }
            if (
                $hostEntity['entityId'] != '*' &&
                $hostEntity['entityId'] != current($entityIndexData)
            ) {
                continue;
            }
            $handle = true;
            break;
        }
        
        // nope: did I before?
        if (!$handle) {
            // nope: return;
            if (!in_array($this, $sync->getRemovedHosts($entityIndexData))) {
                //echo 'Not handling event<br />';
                return;
            }
            // yes: change event type to "delete"
            //echo 'Treating as delete<br />';
            $eventType = 'delete';
        }
        
        // calculate changeset
        $changeset = new \Cx\Core_Modules\Sync\Model\Entity\Changeset($entityIndexData, $entityClassName, $entity, $eventType, $sync);
        
        // spool!
        $spooler->addChangeset($this, $changeset);
    }
    
    public function push($changeset) {
        foreach ($changeset->getChanges() as $change) {
            $content = array();
            if ($change['eventType'] != 'delete') {
                $content = $this->calculateContent($change['entity']);
            }
            $this->sendRequest($content, $change['entityIndexData'], $change['eventType'], $change['sync']);
        }
    }
    
    protected function calculateContent($entity) {
        // customizing for current calendar:
        if (
            get_class($entity) == 'Cx\\Modules\\Calendar\\Model\\Entity\\Event'
        ) {
            // set registration to "extern" and registration uri to our host:
            $entity->setRegistration(
                \Cx\Modules\Calendar\Controller\CalendarEvent::EVENT_REGISTRATION_EXTERNAL
            );
            
            $event = new \Cx\Modules\Calendar\Controller\CalendarEvent($entity->getId());
            /*$url = \Cx\Core\Routing\Url::fromModuleAndCmd('Calendar', 'register', '', array(
                'id' => $entity->getId(),
                'date' => $event->startDate->getTimestamp(),
            ));
            $entity->setRegistrationExternalLink($url->toString());*/
            $url = 'http://bpw.ch/?section=Calendar&cmd=register&id=' . $entity->getId() . '&date=' . $event->startDate->getTimestamp();
            $entity->setRegistrationExternalLink($url);
            
            // from CalendarEventManager:
            if ($event->registration == \Cx\Modules\Calendar\Controller\CalendarEvent::EVENT_REGISTRATION_INTERNAL) {
                $fullyBooked = true;
                if (
                    (
                        $event->registration == \Cx\Modules\Calendar\Controller\CalendarEvent::EVENT_REGISTRATION_EXTERNAL &&
                        !$event->registrationExternalFullyBooked
                    ) ||
                    (
                        $event->registration == \Cx\Modules\Calendar\Controller\CalendarEvent::EVENT_REGISTRATION_INTERNAL &&
                        (
                            empty($event->numSubscriber) ||
                            !\FWValidator::isEmpty($event->getFreePlaces())
                        )
                    )
                ) {
                    $fullyBooked = false;
                }
                $entity->setRegistrationExternalFullyBooked($fullyBooked);
            }
            
            $entity->setPlaceMap($this->makeLinkAbsolute($entity->getPlaceMap(), true));
            $entity->setPic($this->makeLinkAbsolute($entity->getPic(), true));
            $entity->setAttach($this->makeLinkAbsolute($entity->getAttach(), true));
        } else if (
            get_class($entity) == 'Cx\\Modules\\Calendar\\Model\\Entity\\EventField'
        ) {
            $entity->setDescription($this->makeLinkAbsolute($entity->getDescription()));
            $entity->setRedirect($this->makeLinkAbsolute($entity->getRedirect(), true));
        }
        // end customizing
        
        $content = array();
        $metaData = $this->cx->getDb()->getEntityManager()->getClassMetadata(get_class($entity));
        $primaryKeyNames = $metaData->getIdentifierFieldNames(); // get the name of primary key in database table
        foreach ($metaData->getColumnNames() as $column) {
            $field = $metaData->getFieldName($column);
            if (in_array($field, $primaryKeyNames)) {
                //continue;
            }
            $content[$field] = $metaData->getFieldValue($entity, $field);
            if (is_object($content[$field]) && get_class($content[$field]) == 'DateTime') {
                $content[$field] = $content[$field]->format(DATE_ATOM);
            }
        }
        $associationMappings = $metaData->getAssociationMappings();
        $classMethods = get_class_methods($entity);
        foreach ($associationMappings as $field => $associationMapping) {
            if (   $metaData->isSingleValuedAssociation($field)
                && in_array('set'.ucfirst($field), $classMethods)
            ) {
                if ($metaData->getFieldValue($entity, $field)) {
                    $foreignEntity = $metaData->getFieldValue($entity, $field);
                    $indexData = $this->getComponentController()->getEntityIndexData($foreignEntity);
                    $content[$field] = implode('/', $indexData);
                    continue;
                }
                $content[$field]= new $associationMapping['targetEntity']();
            } elseif ($metaData->isCollectionValuedAssociation($field)) {
                $content[$field]= new $associationMapping['targetEntity']();
            }
        }
        return $content;
    }
    
    protected function makeLinkAbsolute($input, $simpleField = false) {
        $input = preg_replace('/\\[\\[([A-Z0-9_-]+)\\]\\]/', '{\\1}', $input);
        $domainRepo = new \Cx\Core\Net\Model\Repository\DomainRepository();
        \LinkGenerator::parseTemplate($input, true, $domainRepo->getMainDomain());
        $domain = 'http://bpw.ch';
        if ($simpleField && !empty($input) && substr($input, 0, strlen($domain)) != $domain) {
            $input = $domain . $input;
        }
        if (!$simpleField) {
            // this replaces image paths...
            $allImg = array();
            preg_match_all('/src="([^"]*)"/', $input, $allImg, PREG_PATTERN_ORDER);
            $size = sizeof($allImg[1]);
            
            $i = 0;
            while ($i < $size) {
                $URLforReplace = $allImg[1][$i];
                
                $replaceUrl = new \Cx\Core\Routing\Url($URLforReplace, true);
                if ($replaceUrl->isInternal()) {
                    $ReplaceWith = $replaceUrl->toString();
                } else {
                    $ReplaceWith = $URLforReplace;
                }
                
                $input = str_replace('"'.$URLforReplace.'"', '"'.$ReplaceWith.'"', $input);
                $i++;
            }
        }
        return $input;
    }
    
    protected function sendRequest($content, $entityIndexData, $eventType, $sync) {
        ob_start();
        
        $method = strtoupper($eventType);
        $url = $this->getToUri(
            $sync->getDataAccess()->getName(),
            $entityIndexData
        );
        
        $config = array(
        );
        $request = new \HTTP_Request2($url, $method, $config);
        $refUrl = \Cx\Core\Routing\Url::fromDocumentRoot();
        $refUrl->setMode('backend');
        $request->setHeader('Referrer', $refUrl->toString());
        $request->setBody(http_build_query($content, null, '&'));
        
        $response = $request->send();
        var_dump($response->getStatus());
        echo '<pre>' . $response->getBody() . '</pre>';
        echo 'Pushed to ' . $url . ' with method ' . $method . ', body was: ' . http_build_query($content);
        $logContents = ob_get_contents();
        ob_end_clean();
        
        $severity = 'INFO';
        if ($response->getStatus() != 200) {
            $severity = 'FATAL';
        }
        $this->cx->getEvents()->triggerEvent('SysLog/Add', array(
           'severity' => $severity,
           'message' => 'Sent ' . strtoupper($method) . ' to ' . $url,
           'data' => $logContents,
        ));
        return $logContents;
    }
}

