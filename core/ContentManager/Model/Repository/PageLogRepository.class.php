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
 * Page log repository
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_contentmanager
 */

namespace Cx\Core\ContentManager\Model\Repository;

use Doctrine\Common\Util\Debug as DoctrineDebug;
use Doctrine\ORM\EntityRepository,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\Query\Expr;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;

/**
 * Log entry exception
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_contentmanager
 */

class LogEntryRepositoryException extends \Exception {};

/**
 * Page log repository
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_contentmanager
 */

class PageLogRepository extends LogEntryRepository
{
    // Doctrine entity manager
    protected $em = null;
    // Page repository
    protected $pageRepo = null;

    /**
     * Constructor
     *
     * @param  EntityManager  $em
     * @param  ClassMetadata  $class
     */
    public function __construct(EntityManager $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->em = $em;
        $this->pageRepo = $this->em->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
    }

    /**
     * Loads all log entries for the
     * given $entity
     *
     * @param object $entity
     * @return array
     */
    public function getLogEntries($entity, $useCache = true)
    {
        $q = $this->getLogEntriesQuery($entity);
        $q->useResultCache($useCache);
        return $q->getResult();
    }

    /**
     * Returns an array with the log entries of the given action with a limiter for the paging. It is used for the content workflow overview.
     * The log entries are filtered by the page object.
     *
     * @todo Known bug: Paging for action = 'deleted' is wrong
     * @todo Action = 'unvalidated' does not work yet
     *
     * @param   string  $action
     * @param   int     $offset
     * @param   int     $limit
     *
     * @return  array   $result
     */
    public function getLogs($action = '', $offset, $limit, &$count = 0)
    {
        $result = array();
        $query = '
            SELECT SQL_CALC_FOUND_ROWS
                c0_.object_id AS objectId,
                c0_.action AS action,
                c0_.logged_at AS loggedAt,
                c0_.version AS version,
                c0_.username AS username
            FROM
                contrexx_log_entry c0_
            INNER JOIN (
                SELECT
                    MAX(c1_.version) AS version,
                    c1_.object_id AS object_id
                FROM
                    contrexx_log_entry c1_
                WHERE
                    (c1_.object_class = :objectClass)
                GROUP BY
                    c1_.object_id
            ) c2_
            ON
                c0_.object_id = c2_.object_id AND
                c0_.version = c2_.version
            LEFT JOIN
                contrexx_content_page AS c3_
            ON
                c3_.id = c0_.object_id AND
                c3_.editingStatus = :editingStatus
            WHERE
                (c0_.action = :action) AND
                (c0_.object_class = :objectClass)
            ORDER BY
                c0_.logged_at DESC
            LIMIT
                ' . $limit . '
            OFFSET
                ' . $offset . '
        ';
        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($query);
        $stmt->bindValue(
            'objectClass',
            'Cx\\Core\\ContentManager\\Model\\Entity\\Page'
        );

        switch ($action) {
            case 'deleted':
                $stmt->bindValue('editingStatus', '');
                $stmt->bindValue('action', 'remove');
                break;
            case 'unvalidated':
                $stmt->bindValue('editingStatus', 'hasDraftWaiting');
                $qb->orWhere('l.action = :orAction')
                   ->setParameter('action', 'create')
                   ->setParameter('orAction', 'update');
                break;
            case 'updated':
                $stmt->bindValue('editingStatus', '');
                $stmt->bindValue('action', 'update');
                break;
            default: // create
                $stmt->bindValue('editingStatus', '');
                $stmt->bindValue('action', 'create');
        }

        switch ($action) {
            case 'deleted':
                $stmt->execute();
                $logs = $stmt->fetchAll();

                // Structure the logs by node id and language
                foreach ($logs as $log) {
                    $page = new \Cx\Core\ContentManager\Model\Entity\Page();
                    $page->setId($log['objectId']);
                    $this->revert($page, $log['version'] - 1);

                    $result[$page->getNodeIdShadowed()][$page->getLang()] = $log;
                }
                break;
            default: // create, update and unvalidated
                $stmt->execute();
                $result = $stmt->fetchAll();
                $conn = $this->em->getConnection();
                $stmt = $conn->prepare('SELECT FOUND_ROWS()');
                $stmt->execute();
                $count = current(current($stmt->fetchAll()));
        }

        return $result;
    }

    /**
     * Returns an array with the log entries of the given action.
     * The log entries are filtered by the page object.
     *
     * @param   string  $action
     * @return  array   $result
     */
    public function getLogsByAction($action = '')
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('l')
           ->from('Cx\Core\ContentManager\Model\Entity\LogEntry', 'l')
           ->where('l.action = :action')
           ->andWhere('l.objectClass = :objectClass')
           ->setParameter('action', $action)
           ->setParameter('objectClass', 'Cx\Core\ContentManager\Model\Entity\Page');
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * Returns the latest logs of all pages.
     * The log entries are filtered by the page object.
     *
     * @return  array  $result
     */
    public function getLatestLog(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        $result = array();

        $qb = $this->em->createQueryBuilder();

        $objectId = $page->getId();

        $qb->select('l')
                ->setMaxResults(1)
                ->from('Cx\Core\ContentManager\Model\Entity\LogEntry', 'l')
                ->where('l.objectClass = :objectClass')
                ->andWhere('l.objectId LIKE :objectId')
                ->orderBy('l.version', 'DESC')
                ->setParameter('objectClass', 'Cx\Core\ContentManager\Model\Entity\Page')
                ->setParameter('objectId', $objectId);

        $logs = $qb->getQuery()->getResult();

        if (is_array($logs)) {
            foreach ($logs as $log) {
                if (!is_array($log)) {
                    $result[$log->getObjectId()] = $log;
                } else {
                    $result[$log['objectId']] = $log['username'];
                }
            }
        }

        return current($result);
    }

    /**
     * Returns the user name from the given log.
     *
     * @param   Cx\Core\ContentManager\Model\Entity\LogEntry
     * @return  string  $username
     */
    public function getUsernameByLog($log)
    {
        if (!is_object($log)) {
            $loggedUser = $log;
        } else {
            $loggedUser = $log->getUsername();
        }
        $user = json_decode($loggedUser);
        $username = $user->{'name'};

        return $username;
    }

}
