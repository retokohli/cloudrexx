<?php

/**
 * CrawlerRepository
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_linkmanager
 */

namespace Cx\Modules\LinkManager\Model\Repository;

/**
 * The class CrawlerRepository for getting the last run details and get all the crawler run details from db
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_linkmanager
 */
class CrawlerRepository extends \Doctrine\ORM\EntityRepository {     
    
    /**
     * get the last run detail by the language
     * 
     * @param integer $lang language id
     * 
     * @return object
     */
    public function getLastRunByLang($lang)
    {   
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('crawler')
            ->from('Cx\Modules\LinkManager\Model\Entity\Crawler', 'crawler')
            ->where("crawler.lang = :lang")
            ->orderBy("crawler.id", "DESC")
            ->getDql();
        $qb->setParameter("lang", $lang)->setMaxResults(1);
        $objResult = $qb->getQuery()->getResult();
        
        return $objResult[0];
    }
    
    /**
     * get the last run details
     * 
     * @return object
     */
    public function getLatestRunDetails()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('crawler')
           ->from('Cx\Modules\LinkManager\Model\Entity\Crawler', 'crawler')
           ->where('crawler.runStatus != :runStatus')
           ->orderBy("crawler.id", "DESC")
           ->getDql();
        $qb->setParameter('runStatus', 'running')->setMaxResults(1);
        
        return $qb->getQuery()->getResult();
    }
    
    /**
     * get the crawler entry counts
     * 
     * @return integer
     */
    public function crawlerEntryCount()
    {
        $objResult = new \Doctrine\Common\Collections\ArrayCollection($this->findAll());
        
        return $objResult->count();
    }
    
    /**
     * get the crawler run entries
     * 
     * @param integer $pos       position
     * @param integer $pageLimit page limit 
     * 
     * @return array
     */
    public function getCrawlerRunEntries($pos, $pageLimit)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('crawler')
            ->from('Cx\Modules\LinkManager\Model\Entity\Crawler', 'crawler')
            ->orderBy("crawler.id", "DESC")
            ->getQuery();
        $qb->setFirstResult($pos)->setMaxResults($pageLimit);
        
        return new \Doctrine\Common\Collections\ArrayCollection($qb->getQuery()->getResult());
    }
}
