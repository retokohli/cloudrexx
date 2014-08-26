<?php

/**
 * LinkRepository
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_linkmanager
 */

namespace Cx\Core_Modules\LinkManager\Model\Repository;

/**
 * LinkRepository
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_linkmanager
 */
class LinkRepository extends \Doctrine\ORM\EntityRepository {
    
    /**
     * Get all the broken links
     * 
     * @param integer $pos
     * @param integer $pageLimit
     * 
     * @return array
     */
    public function getBrokenLinks($pos, $pageLimit)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('link')
           ->from('Cx\Core_Modules\LinkManager\Model\Entity\Link', 'link')     
           ->where('link.flagStatus = :flagStatus')
           ->orderBy('link.id', 'DESC')
           ->getDql();
        $qb->setParameter('flagStatus', 0)->setFirstResult($pos)->setMaxResults($pageLimit);
        
        return new \Doctrine\Common\Collections\ArrayCollection($qb->getQuery()->getResult());
    }

    /**
     * get the broken links count
     * 
     * @return integer
     */
    public function brokenLinkCount()
    {
        $objResult = new \Doctrine\Common\Collections\ArrayCollection($this->findBy(array('flagStatus' => 0)));
        
        return $objResult->count();
    }
    
    /**
     * get the broken links count by language
     * 
     * @return integer
     */
    public function brokenLinkCountByLang($lang)
    {
        $objResult = new \Doctrine\Common\Collections\ArrayCollection($this->findBy(array('lang' => $lang, 'flagStatus' => 0)));
        
        return $objResult->count();
    }
    
    /**
     * get the selected links
     * 
     * @param array $ids
     * 
     * @return array
     */
    public function getSelectedLinks($ids = array())
    {
        try {
            $query = $this->getEntityManager()->createQuery('SELECT l FROM Cx\Core_Modules\LinkManager\Model\Entity\Link l WHERE l.id IN ('.implode(',', $ids).')');
            $objResult = $query->getResult(); 
            if (!$objResult) {
                $objResult = array();
            } 
            return new \Doctrine\Common\Collections\ArrayCollection($objResult);
        } catch (\Exception $error) {
            die('Error:' . $error);
        }
    }

    /**
     * get the link by path
     * 
     * @param string $path
     * 
     * @return object
     */
    public function getLinkByPath($path)
    {
        return $this->findOneBy(array('requestedPath' => $path));
    }

    /**
     * get the non detected links during the crawler run
     * 
     * @param datetime $startTime
     * @param integer  $lang
     * 
     * @return array
     */
    public function getNonDetectedLinks($startTime, $lang)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('link')
           ->from('Cx\Core_Modules\LinkManager\Model\Entity\Link', 'link')     
           ->where('link.detectedTime < :start')
           ->andWhere('link.lang = :lang')
           ->getDql();
        $qb->setParameter('start', $startTime)->setParameter('lang', $lang);
        
        return new \Doctrine\Common\Collections\ArrayCollection($qb->getQuery()->getResult());
    }

    /**
     * get the detected broken links count
     * 
     * @param datetime $startTime
     * @param integer  $lang
     * 
     * @return integer
     */
    public function getDetectedBrokenLinksCount($startTime, $lang)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('link')
           ->from('Cx\Core_Modules\LinkManager\Model\Entity\Link', 'link')     
           ->where('link.detectedTime > :start')
           ->andWhere('link.flagStatus = :flagStatus')
           ->andWhere('link.lang = :lang')
           ->orderBy('link.id', 'DESC')
           ->getDql();
        $qb->setParameter('start', $startTime)->setParameter('flagStatus', 0)->setParameter('lang', $lang);
        $objResult = new \Doctrine\Common\Collections\ArrayCollection($qb->getQuery()->getResult());
        
        return $objResult->count();
    }
    
    /**
     * get the all links count
     * 
     * @param datetime $startTime
     * @param integer  $lang
     * 
     * @return integer
     */
    public function getLinksCountByLang($startTime, $lang)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('link')
           ->from('Cx\Core_Modules\LinkManager\Model\Entity\Link', 'link')     
           ->where('link.detectedTime > :start')
           ->andWhere('link.lang = :lang')
           ->orderBy('link.id', 'DESC')
           ->getDql();
        $qb->setParameter('start', $startTime)->setParameter('lang', $lang);
        $objResult = new \Doctrine\Common\Collections\ArrayCollection($qb->getQuery()->getResult());
        
        return $objResult->count();
    }
}
