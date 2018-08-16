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
 * @copyright   Cloudrexx AG
 * @author      Sam Hawkes <sam.hawkes@comvation.com>
 * @package     cloudrexx
 */

namespace Cx\Core\MediaSource\Model\Entity;

class IndexerException extends \Exception {}

abstract class Indexer extends \Cx\Model\Base\EntityBase
{
    /**
     * @var $type string extension type
     */
    protected $type;

    /**
     * Extension array
     */
    protected $extensions;

    /**
     * Set extensions of indexer
     *
     * @param $extensions array all extensions of indexer
     *
     * @return void
     */
    public function setExtensions($extensions)
    {
        $this->extensions = $extensions;
    }

    /**
     * Get extensions of indexer
     *
     * @return string
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Get type of indexer
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /** Index all files matching the indexer type
     *
     * @param $mediaSource \Cx\Core\MediaSource\Model\Entity\MediaSource
     * @param $path        string path to index
     *
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @return void
     */
    public function index($mediaSource, $path)
    {
        $em = $this->cx->getDb()->getEntityManager();
        $files = array();

        if (filetype($path) == 'dir') {
            $files = $mediaSource->getFileSystem()->getFileList($path);
        } else {
            $file = $mediaSource->getFileSystem()->getFileFromPath($path);
            $files->push($file);
        }
        foreach ($files as $file) {
            $indexerEntry = $em->getRepository(
                $this->getNamespace() . '\Model\Entity\IndexerEntry'
            )->findOneBy(array('path' => $path));
            if (empty($indexerEntry)) {
                $indexerEntry = \Cx\Core\MediaSource\Model\Entity\IndexerEntry();
            }
            $indexerEntry->setPath($path);
            $indexerEntry->setIndexer();
            $indexerEntry->setContent(
                $this->getText($mediaSource, $file->getPath())
            );
            $indexerEntry->setTimestamp(\DateTime('now'));
            $em->persist($indexerEntry);
        }

        $em->flush();
    }

    /**
     * Delete entries to clear the index
     *
     * @param $path string path to string bla
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @return void
     */
    protected function clearIndex($path = '')
    {
        $em = $this->cx->getDb()->getEntityManager();
        $indexerEntryRepo = $em->getRepository(
            $this->getNamespace() . '\Model\Entity\IndexerEntry'
        );
        if (!empty($path)) {
            $indexerEntries = $indexerEntryRepo->findBy(
                array('path' => $path, 'indexer' => $this->getName())
            );
        } else {
            $indexerEntries = $indexerEntryRepo->findBy(
                array('indexer' => $this->getName())
            );
        }
        foreach ($indexerEntries as $indexerEntry) {
            $em->delete($indexerEntry);
        }
        $em->flush();
    }

    /**
     * Return all index entries that match
     *
     * @param $searchterm string term to search
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @return \Cx\Core\MediaSource\Model\Entity\IndexerEntry
     */
    public function getMatch($searchterm, $path)
    {
        $em = $this->cx->getDb()->getEntityManager();

        $qb = $em->createQueryBuilder();
        $query = $qb->select(array('ie'))->from(
            'Cx\Core\MediaSource\Model\Entity\IndexerEntry', 'ie'
        )->where(
            $qb->expr()->like(
                'ie.content', $qb->expr()->literal('%'.$searchterm.'%')
            )
        )->andWhere('ie.path = :path')->setParameter('path', $path)->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * Get text from an indexed file
     *
     * @param $mediaSource \Cx\Core\MediaSource\Model\Entity\MediaSource
     * @param $filepath    string path to file
     *
     * @return string
     */
    abstract protected function getText($mediaSource, $filepath);

}