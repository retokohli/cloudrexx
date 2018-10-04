<?php

namespace Cx\Modules\CHDIRTravelLog\Model\Entity;

/**
 * Journey
 */
class Journey
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
     * Set att
     *
     * @param integer $att
     * @return Journey
     */
    public function setAtt($att)
    {
        $this->att = $att;

        return $this;
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

        return $this;
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

        return $this;
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

        return $this;
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

        return $this;
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

        return $this;
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

        return $this;
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

        return $this;
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
