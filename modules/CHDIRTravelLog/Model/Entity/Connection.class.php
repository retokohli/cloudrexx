<?php

namespace Cx\Modules\CHDIRTravelLog\Model\Entity;

/**
 * Connection
 */
class Connection
{
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
