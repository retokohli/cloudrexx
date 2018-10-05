<?php declare(strict_types=1);
/**
 * Cloudrexx App by Comvation AG
 *
 * PHP Version 7.1 - 7.2
 *
 * @category  CloudrexxApp
 * @package   CHDIRTravelLog
 * @author    Comvation AG <info@comvation.com>
 * @copyright 2018 ch-direct
 * @link      https://www.comvation.com/
 *
 * Unauthorized copying, changing or deleting
 * of any file from this app is strictly prohibited
 *
 * Authorized copying, changing or deleting
 * can only be allowed by a separate contract
 */

namespace Cx\Modules\CHDIRTravelLog\Model\Entity;

/**
 * Connection
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @package     cloudrexx
 * @subpackage  module_chdirtravellog
 */
class Connection
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $project;

    /**
     * @var integer
     */
    protected $verbindungsnummer;

    /**
     * @var string
     */
    protected $sequenznummer;

    /**
     * @var string
     */
    protected $verbindungsstring;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set project
     *
     * @param string $project
     * @return Connection
     */
    public function setProject($project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return string
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set verbindungsnummer
     *
     * @param integer $verbindungsnummer
     * @return Connection
     */
    public function setVerbindungsnummer($verbindungsnummer)
    {
        $this->verbindungsnummer = $verbindungsnummer;

        return $this;
    }

    /**
     * Get verbindungsnummer
     *
     * @return integer
     */
    public function getVerbindungsnummer()
    {
        return $this->verbindungsnummer;
    }

    /**
     * Set sequenznummer
     *
     * @param string $sequenznummer
     * @return Connection
     */
    public function setSequenznummer($sequenznummer)
    {
        $this->sequenznummer = $sequenznummer;

        return $this;
    }

    /**
     * Get sequenznummer
     *
     * @return string
     */
    public function getSequenznummer()
    {
        return $this->sequenznummer;
    }

    /**
     * Set verbindungsstring
     *
     * @param string $verbindungsstring
     * @return Connection
     */
    public function setVerbindungsstring($verbindungsstring)
    {
        $this->verbindungsstring = $verbindungsstring;

        return $this;
    }

    /**
     * Get verbindungsstring
     *
     * @return string
     */
    public function getVerbindungsstring()
    {
        return $this->verbindungsstring;
    }
}
