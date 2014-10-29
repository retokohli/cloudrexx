<?php

/**
 * Log entry
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  model_contentmanager
 */

namespace Cx\Core\ContentManager\Model\Entity;

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Entity;
use Gedmo\Loggable\Entity\AbstractLogEntry;

/**
 * Cx\Core\ContentManager\Model\Entity\LogEntry
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  model_contentmanager
 * @Table(
 *     name="log_entry",
 *  indexes={
 *      @index(name="log_class_lookup_idx", columns={"object_class"}),
 *      @index(name="log_date_lookup_idx", columns={"logged_at"}),
 *      @index(name="log_user_lookup_idx", columns={"username"})
 *  }
 * )
 * @Entity(repositoryClass="Cx\Core\ContentManager\Model\Repository\PageLogRepository")
 */
class LogEntry extends AbstractLogEntry
{
    /**
     * All required columns are mapped through inherited superclass
     */

    /**
     * @var integer $id
     *
     * @Column(type="integer")
     * @Id
     * @GeneratedValue
     */
    private $id;
    
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
     * Set id
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

}