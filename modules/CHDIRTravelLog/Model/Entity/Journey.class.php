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
 * Journey
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @package     cloudrexx
 * @subpackage  module_chdirtravellog
 */
class Journey extends \Cx\Model\Base\EntityBase
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
    protected $att;

    /**
     * @var \DateTime
     */
    protected $reisedat;

    /**
     * @var string
     */
    protected $verbnr;

    /**
     * @var integer
     */
    protected $rbn;

    /**
     * @var integer
     */
    protected $reisen;

    /**
     * @var string
     */
    protected $d;

    /**
     * @var string
     */
    protected $atStart;

    /**
     * @var string
     */
    protected $atRecs;

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
     * @return Journey
     */
    public function setProject($project)
    {
        $this->project = $project;
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
     * Set att
     *
     * @param integer $att
     * @return Journey
     */
    public function setAtt($att)
    {
        $this->att = $att;
    }

    /**
     * Get att
     *
     * @return integer
     */
    public function getAtt()
    {
        return $this->att;
    }

    /**
     * Set reisedat
     *
     * @param \DateTime $reisedat
     * @return Journey
     */
    public function setReisedat($reisedat)
    {
        $this->reisedat = $reisedat;
    }

    /**
     * Get reisedat
     *
     * @return \DateTime
     */
    public function getReisedat()
    {
        return $this->reisedat;
    }

    /**
     * Set verbnr
     *
     * @param string $verbnr
     * @return Journey
     */
    public function setVerbnr($verbnr)
    {
        $this->verbnr = $verbnr;
    }

    /**
     * Get verbnr
     *
     * @return string
     */
    public function getVerbnr()
    {
        return $this->verbnr;
    }

    /**
     * Set rbn
     *
     * @param integer $rbn
     * @return Journey
     */
    public function setRbn($rbn)
    {
        $this->rbn = $rbn;
    }

    /**
     * Get rbn
     *
     * @return integer
     */
    public function getRbn()
    {
        return $this->rbn;
    }

    /**
     * Set reisen
     *
     * @param integer $reisen
     * @return Journey
     */
    public function setReisen($reisen)
    {
        $this->reisen = $reisen;
    }

    /**
     * Get reisen
     *
     * @return integer
     */
    public function getReisen()
    {
        return $this->reisen;
    }

    /**
     * Set d
     *
     * @param string $d
     * @return Journey
     */
    public function setD($d)
    {
        $this->d = $d;
    }

    /**
     * Get d
     *
     * @return string
     */
    public function getD()
    {
        return $this->d;
    }

    /**
     * Set atStart
     *
     * @param string $atStart
     * @return Journey
     */
    public function setAtStart($atStart)
    {
        $this->atStart = $atStart;
    }

    /**
     * Get atStart
     *
     * @return string
     */
    public function getAtStart()
    {
        return $this->atStart;
    }

    /**
     * Set atRecs
     *
     * @param string $atRecs
     * @return Journey
     */
    public function setAtRecs($atRecs)
    {
        $this->atRecs = $atRecs;
    }

    /**
     * Get atRecs
     *
     * @return string
     */
    public function getAtRecs()
    {
        return $this->atRecs;
    }
}
