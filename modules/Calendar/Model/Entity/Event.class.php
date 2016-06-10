<?php

namespace Cx\Modules\Calendar\Model\Entity;

/**
 * Cx\Modules\Calendar\Model\Entity\Event
 */
class Event
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $type
     */
    private $type;

    /**
     * @var datetime $startDate
     */
    private $startDate;

    /**
     * @var datetime $endDate
     */
    private $endDate;

    /**
     * @var boolean $useCustomDateDisplay
     */
    private $useCustomDateDisplay;

    /**
     * @var integer $showStartDateList
     */
    private $showStartDateList;

    /**
     * @var integer $showEndDateList
     */
    private $showEndDateList;

    /**
     * @var integer $showStartTimeList
     */
    private $showStartTimeList;

    /**
     * @var integer $showEndTimeList
     */
    private $showEndTimeList;

    /**
     * @var integer $showTimeTypeList
     */
    private $showTimeTypeList;

    /**
     * @var integer $showStartDateDetail
     */
    private $showStartDateDetail;

    /**
     * @var integer $showEndDateDetail
     */
    private $showEndDateDetail;

    /**
     * @var integer $showStartTimeDetail
     */
    private $showStartTimeDetail;

    /**
     * @var integer $showEndTimeDetail
     */
    private $showEndTimeDetail;

    /**
     * @var integer $showTimeTypeDetail
     */
    private $showTimeTypeDetail;

    /**
     * @var integer $google
     */
    private $google;

    /**
     * @var integer $access
     */
    private $access;

    /**
     * @var integer $priority
     */
    private $priority;

    /**
     * @var integer $price
     */
    private $price;

    /**
     * @var string $link
     */
    private $link;

    /**
     * @var string $pic
     */
    private $pic;

    /**
     * @var string $attach
     */
    private $attach;

    /**
     * @var integer $placeMediadirId
     */
    private $placeMediadirId;

    /**
     * @var string $showIn
     */
    private $showIn;

    /**
     * @var string $invitedGroups
     */
    private $invitedGroups;

    /**
     * @var text $invitedMails
     */
    private $invitedMails;

    /**
     * @var integer $invitationSent
     */
    private $invitationSent;

    /**
     * @var string $invitationEmailTemplate
     */
    private $invitationEmailTemplate;

    /**
     * @var integer $registration
     */
    private $registration;

    /**
     * @var string $registrationNum
     */
    private $registrationNum;

    /**
     * @var string $registrationNotification
     */
    private $registrationNotification;

    /**
     * @var string $emailTemplate
     */
    private $emailTemplate;

    /**
     * @var boolean $ticketSales
     */
    private $ticketSales;

    /**
     * @var text $numSeating
     */
    private $numSeating;

    /**
     * @var smallint $seriesStatus
     */
    private $seriesStatus;

    /**
     * @var integer $seriesType
     */
    private $seriesType;

    /**
     * @var integer $seriesPatternCount
     */
    private $seriesPatternCount;

    /**
     * @var string $seriesPatternWeekday
     */
    private $seriesPatternWeekday;

    /**
     * @var integer $seriesPatternDay
     */
    private $seriesPatternDay;

    /**
     * @var integer $seriesPatternWeek
     */
    private $seriesPatternWeek;

    /**
     * @var integer $seriesPatternMonth
     */
    private $seriesPatternMonth;

    /**
     * @var integer $seriesPatternType
     */
    private $seriesPatternType;

    /**
     * @var integer $seriesPatternDouranceType
     */
    private $seriesPatternDouranceType;

    /**
     * @var integer $seriesPatternEnd
     */
    private $seriesPatternEnd;

    /**
     * @var datetime $seriesPatternEndDate
     */
    private $seriesPatternEndDate;

    /**
     * @var integer $seriesPatternBegin
     */
    private $seriesPatternBegin;

    /**
     * @var text $seriesPatternExceptions
     */
    private $seriesPatternExceptions;

    /**
     * @var boolean $status
     */
    private $status;

    /**
     * @var boolean $confirmed
     */
    private $confirmed;

    /**
     * @var boolean $showDetailView
     */
    private $showDetailView;

    /**
     * @var string $author
     */
    private $author;

    /**
     * @var boolean $allDay
     */
    private $allDay;

    /**
     * @var boolean $locationType
     */
    private $locationType;

    /**
     * @var string $place
     */
    private $place;

    /**
     * @var integer $placeId
     */
    private $placeId;

    /**
     * @var string $placeStreet
     */
    private $placeStreet;

    /**
     * @var string $placeZip
     */
    private $placeZip;

    /**
     * @var string $placeCity
     */
    private $placeCity;

    /**
     * @var string $placeCountry
     */
    private $placeCountry;

    /**
     * @var string $placeLink
     */
    private $placeLink;

    /**
     * @var string $placeMap
     */
    private $placeMap;

    /**
     * @var boolean $hostType
     */
    private $hostType;

    /**
     * @var string $orgName
     */
    private $orgName;

    /**
     * @var string $orgStreet
     */
    private $orgStreet;

    /**
     * @var string $orgZip
     */
    private $orgZip;

    /**
     * @var string $orgCity
     */
    private $orgCity;

    /**
     * @var string $orgCountry
     */
    private $orgCountry;

    /**
     * @var string $orgLink
     */
    private $orgLink;

    /**
     * @var string $orgEmail
     */
    private $orgEmail;

    /**
     * @var integer $hostMediadirId
     */
    private $hostMediadirId;

    /**
     * @var Cx\Modules\Calendar\Model\Entity\EventField
     */
    private $eventFields;

    /**
     * @var Cx\Modules\Calendar\Model\Entity\Registration
     */
    private $registrations;

    /**
     * @var Cx\Modules\Calendar\Model\Entity\Category
     */
    private $category;

    /**
     * @var Cx\Modules\Calendar\Model\Entity\RegistrationForm
     */
    private $registrationForm;

    public function __construct()
    {
        $this->eventFields = new \Doctrine\Common\Collections\ArrayCollection();
        $this->registrations = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set type
     *
     * @param integer $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return integer $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set startDate
     *
     * @param datetime $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * Get startDate
     *
     * @return datetime $startDate
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param datetime $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * Get endDate
     *
     * @return datetime $endDate
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set useCustomDateDisplay
     *
     * @param boolean $useCustomDateDisplay
     */
    public function setUseCustomDateDisplay($useCustomDateDisplay)
    {
        $this->useCustomDateDisplay = $useCustomDateDisplay;
    }

    /**
     * Get useCustomDateDisplay
     *
     * @return boolean $useCustomDateDisplay
     */
    public function getUseCustomDateDisplay()
    {
        return $this->useCustomDateDisplay;
    }

    /**
     * Set showStartDateList
     *
     * @param integer $showStartDateList
     */
    public function setShowStartDateList($showStartDateList)
    {
        $this->showStartDateList = $showStartDateList;
    }

    /**
     * Get showStartDateList
     *
     * @return integer $showStartDateList
     */
    public function getShowStartDateList()
    {
        return $this->showStartDateList;
    }

    /**
     * Set showEndDateList
     *
     * @param integer $showEndDateList
     */
    public function setShowEndDateList($showEndDateList)
    {
        $this->showEndDateList = $showEndDateList;
    }

    /**
     * Get showEndDateList
     *
     * @return integer $showEndDateList
     */
    public function getShowEndDateList()
    {
        return $this->showEndDateList;
    }

    /**
     * Set showStartTimeList
     *
     * @param integer $showStartTimeList
     */
    public function setShowStartTimeList($showStartTimeList)
    {
        $this->showStartTimeList = $showStartTimeList;
    }

    /**
     * Get showStartTimeList
     *
     * @return integer $showStartTimeList
     */
    public function getShowStartTimeList()
    {
        return $this->showStartTimeList;
    }

    /**
     * Set showEndTimeList
     *
     * @param integer $showEndTimeList
     */
    public function setShowEndTimeList($showEndTimeList)
    {
        $this->showEndTimeList = $showEndTimeList;
    }

    /**
     * Get showEndTimeList
     *
     * @return integer $showEndTimeList
     */
    public function getShowEndTimeList()
    {
        return $this->showEndTimeList;
    }

    /**
     * Set showTimeTypeList
     *
     * @param integer $showTimeTypeList
     */
    public function setShowTimeTypeList($showTimeTypeList)
    {
        $this->showTimeTypeList = $showTimeTypeList;
    }

    /**
     * Get showTimeTypeList
     *
     * @return integer $showTimeTypeList
     */
    public function getShowTimeTypeList()
    {
        return $this->showTimeTypeList;
    }

    /**
     * Set showStartDateDetail
     *
     * @param integer $showStartDateDetail
     */
    public function setShowStartDateDetail($showStartDateDetail)
    {
        $this->showStartDateDetail = $showStartDateDetail;
    }

    /**
     * Get showStartDateDetail
     *
     * @return integer $showStartDateDetail
     */
    public function getShowStartDateDetail()
    {
        return $this->showStartDateDetail;
    }

    /**
     * Set showEndDateDetail
     *
     * @param integer $showEndDateDetail
     */
    public function setShowEndDateDetail($showEndDateDetail)
    {
        $this->showEndDateDetail = $showEndDateDetail;
    }

    /**
     * Get showEndDateDetail
     *
     * @return integer $showEndDateDetail
     */
    public function getShowEndDateDetail()
    {
        return $this->showEndDateDetail;
    }

    /**
     * Set showStartTimeDetail
     *
     * @param integer $showStartTimeDetail
     */
    public function setShowStartTimeDetail($showStartTimeDetail)
    {
        $this->showStartTimeDetail = $showStartTimeDetail;
    }

    /**
     * Get showStartTimeDetail
     *
     * @return integer $showStartTimeDetail
     */
    public function getShowStartTimeDetail()
    {
        return $this->showStartTimeDetail;
    }

    /**
     * Set showEndTimeDetail
     *
     * @param integer $showEndTimeDetail
     */
    public function setShowEndTimeDetail($showEndTimeDetail)
    {
        $this->showEndTimeDetail = $showEndTimeDetail;
    }

    /**
     * Get showEndTimeDetail
     *
     * @return integer $showEndTimeDetail
     */
    public function getShowEndTimeDetail()
    {
        return $this->showEndTimeDetail;
    }

    /**
     * Set showTimeTypeDetail
     *
     * @param integer $showTimeTypeDetail
     */
    public function setShowTimeTypeDetail($showTimeTypeDetail)
    {
        $this->showTimeTypeDetail = $showTimeTypeDetail;
    }

    /**
     * Get showTimeTypeDetail
     *
     * @return integer $showTimeTypeDetail
     */
    public function getShowTimeTypeDetail()
    {
        return $this->showTimeTypeDetail;
    }

    /**
     * Set google
     *
     * @param integer $google
     */
    public function setGoogle($google)
    {
        $this->google = $google;
    }

    /**
     * Get google
     *
     * @return integer $google
     */
    public function getGoogle()
    {
        return $this->google;
    }

    /**
     * Set access
     *
     * @param integer $access
     */
    public function setAccess($access)
    {
        $this->access = $access;
    }

    /**
     * Get access
     *
     * @return integer $access
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * Set priority
     *
     * @param integer $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * Get priority
     *
     * @return integer $priority
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set price
     *
     * @param integer $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * Get price
     *
     * @return integer $price
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set link
     *
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * Get link
     *
     * @return string $link
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set pic
     *
     * @param string $pic
     */
    public function setPic($pic)
    {
        $this->pic = $pic;
    }

    /**
     * Get pic
     *
     * @return string $pic
     */
    public function getPic()
    {
        return $this->pic;
    }

    /**
     * Set attach
     *
     * @param string $attach
     */
    public function setAttach($attach)
    {
        $this->attach = $attach;
    }

    /**
     * Get attach
     *
     * @return string $attach
     */
    public function getAttach()
    {
        return $this->attach;
    }

    /**
     * Set placeMediadirId
     *
     * @param integer $placeMediadirId
     */
    public function setPlaceMediadirId($placeMediadirId)
    {
        $this->placeMediadirId = $placeMediadirId;
    }

    /**
     * Get placeMediadirId
     *
     * @return integer $placeMediadirId
     */
    public function getPlaceMediadirId()
    {
        return $this->placeMediadirId;
    }

    /**
     * Set showIn
     *
     * @param string $showIn
     */
    public function setShowIn($showIn)
    {
        $this->showIn = $showIn;
    }

    /**
     * Get showIn
     *
     * @return string $showIn
     */
    public function getShowIn()
    {
        return $this->showIn;
    }

    /**
     * Set invitedGroups
     *
     * @param string $invitedGroups
     */
    public function setInvitedGroups($invitedGroups)
    {
        $this->invitedGroups = $invitedGroups;
    }

    /**
     * Get invitedGroups
     *
     * @return string $invitedGroups
     */
    public function getInvitedGroups()
    {
        return $this->invitedGroups;
    }

    /**
     * Set invitedMails
     *
     * @param text $invitedMails
     */
    public function setInvitedMails($invitedMails)
    {
        $this->invitedMails = $invitedMails;
    }

    /**
     * Get invitedMails
     *
     * @return text $invitedMails
     */
    public function getInvitedMails()
    {
        return $this->invitedMails;
    }

    /**
     * Set invitationSent
     *
     * @param integer $invitationSent
     */
    public function setInvitationSent($invitationSent)
    {
        $this->invitationSent = $invitationSent;
    }

    /**
     * Get invitationSent
     *
     * @return integer $invitationSent
     */
    public function getInvitationSent()
    {
        return $this->invitationSent;
    }

    /**
     * Set invitationEmailTemplate
     *
     * @param string $invitationEmailTemplate
     */
    public function setInvitationEmailTemplate($invitationEmailTemplate)
    {
        $this->invitationEmailTemplate = $invitationEmailTemplate;
    }

    /**
     * Get invitationEmailTemplate
     *
     * @return string $invitationEmailTemplate
     */
    public function getInvitationEmailTemplate()
    {
        return $this->invitationEmailTemplate;
    }

    /**
     * Set registration
     *
     * @param integer $registration
     */
    public function setRegistration($registration)
    {
        $this->registration = $registration;
    }

    /**
     * Get registration
     *
     * @return integer $registration
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * Set registrationNum
     *
     * @param string $registrationNum
     */
    public function setRegistrationNum($registrationNum)
    {
        $this->registrationNum = $registrationNum;
    }

    /**
     * Get registrationNum
     *
     * @return string $registrationNum
     */
    public function getRegistrationNum()
    {
        return $this->registrationNum;
    }

    /**
     * Set registrationNotification
     *
     * @param string $registrationNotification
     */
    public function setRegistrationNotification($registrationNotification)
    {
        $this->registrationNotification = $registrationNotification;
    }

    /**
     * Get registrationNotification
     *
     * @return string $registrationNotification
     */
    public function getRegistrationNotification()
    {
        return $this->registrationNotification;
    }

    /**
     * Set emailTemplate
     *
     * @param string $emailTemplate
     */
    public function setEmailTemplate($emailTemplate)
    {
        $this->emailTemplate = $emailTemplate;
    }

    /**
     * Get emailTemplate
     *
     * @return string $emailTemplate
     */
    public function getEmailTemplate()
    {
        return $this->emailTemplate;
    }

    /**
     * Set ticketSales
     *
     * @param boolean $ticketSales
     */
    public function setTicketSales($ticketSales)
    {
        $this->ticketSales = $ticketSales;
    }

    /**
     * Get ticketSales
     *
     * @return boolean $ticketSales
     */
    public function getTicketSales()
    {
        return $this->ticketSales;
    }

    /**
     * Set numSeating
     *
     * @param text $numSeating
     */
    public function setNumSeating($numSeating)
    {
        $this->numSeating = $numSeating;
    }

    /**
     * Get numSeating
     *
     * @return text $numSeating
     */
    public function getNumSeating()
    {
        return $this->numSeating;
    }

    /**
     * Set seriesStatus
     *
     * @param smallint $seriesStatus
     */
    public function setSeriesStatus($seriesStatus)
    {
        $this->seriesStatus = $seriesStatus;
    }

    /**
     * Get seriesStatus
     *
     * @return smallint $seriesStatus
     */
    public function getSeriesStatus()
    {
        return $this->seriesStatus;
    }

    /**
     * Set seriesType
     *
     * @param integer $seriesType
     */
    public function setSeriesType($seriesType)
    {
        $this->seriesType = $seriesType;
    }

    /**
     * Get seriesType
     *
     * @return integer $seriesType
     */
    public function getSeriesType()
    {
        return $this->seriesType;
    }

    /**
     * Set seriesPatternCount
     *
     * @param integer $seriesPatternCount
     */
    public function setSeriesPatternCount($seriesPatternCount)
    {
        $this->seriesPatternCount = $seriesPatternCount;
    }

    /**
     * Get seriesPatternCount
     *
     * @return integer $seriesPatternCount
     */
    public function getSeriesPatternCount()
    {
        return $this->seriesPatternCount;
    }

    /**
     * Set seriesPatternWeekday
     *
     * @param string $seriesPatternWeekday
     */
    public function setSeriesPatternWeekday($seriesPatternWeekday)
    {
        $this->seriesPatternWeekday = $seriesPatternWeekday;
    }

    /**
     * Get seriesPatternWeekday
     *
     * @return string $seriesPatternWeekday
     */
    public function getSeriesPatternWeekday()
    {
        return $this->seriesPatternWeekday;
    }

    /**
     * Set seriesPatternDay
     *
     * @param integer $seriesPatternDay
     */
    public function setSeriesPatternDay($seriesPatternDay)
    {
        $this->seriesPatternDay = $seriesPatternDay;
    }

    /**
     * Get seriesPatternDay
     *
     * @return integer $seriesPatternDay
     */
    public function getSeriesPatternDay()
    {
        return $this->seriesPatternDay;
    }

    /**
     * Set seriesPatternWeek
     *
     * @param integer $seriesPatternWeek
     */
    public function setSeriesPatternWeek($seriesPatternWeek)
    {
        $this->seriesPatternWeek = $seriesPatternWeek;
    }

    /**
     * Get seriesPatternWeek
     *
     * @return integer $seriesPatternWeek
     */
    public function getSeriesPatternWeek()
    {
        return $this->seriesPatternWeek;
    }

    /**
     * Set seriesPatternMonth
     *
     * @param integer $seriesPatternMonth
     */
    public function setSeriesPatternMonth($seriesPatternMonth)
    {
        $this->seriesPatternMonth = $seriesPatternMonth;
    }

    /**
     * Get seriesPatternMonth
     *
     * @return integer $seriesPatternMonth
     */
    public function getSeriesPatternMonth()
    {
        return $this->seriesPatternMonth;
    }

    /**
     * Set seriesPatternType
     *
     * @param integer $seriesPatternType
     */
    public function setSeriesPatternType($seriesPatternType)
    {
        $this->seriesPatternType = $seriesPatternType;
    }

    /**
     * Get seriesPatternType
     *
     * @return integer $seriesPatternType
     */
    public function getSeriesPatternType()
    {
        return $this->seriesPatternType;
    }

    /**
     * Set seriesPatternDouranceType
     *
     * @param integer $seriesPatternDouranceType
     */
    public function setSeriesPatternDouranceType($seriesPatternDouranceType)
    {
        $this->seriesPatternDouranceType = $seriesPatternDouranceType;
    }

    /**
     * Get seriesPatternDouranceType
     *
     * @return integer $seriesPatternDouranceType
     */
    public function getSeriesPatternDouranceType()
    {
        return $this->seriesPatternDouranceType;
    }

    /**
     * Set seriesPatternEnd
     *
     * @param integer $seriesPatternEnd
     */
    public function setSeriesPatternEnd($seriesPatternEnd)
    {
        $this->seriesPatternEnd = $seriesPatternEnd;
    }

    /**
     * Get seriesPatternEnd
     *
     * @return integer $seriesPatternEnd
     */
    public function getSeriesPatternEnd()
    {
        return $this->seriesPatternEnd;
    }

    /**
     * Set seriesPatternEndDate
     *
     * @param datetime $seriesPatternEndDate
     */
    public function setSeriesPatternEndDate($seriesPatternEndDate)
    {
        $this->seriesPatternEndDate = $seriesPatternEndDate;
    }

    /**
     * Get seriesPatternEndDate
     *
     * @return datetime $seriesPatternEndDate
     */
    public function getSeriesPatternEndDate()
    {
        return $this->seriesPatternEndDate;
    }

    /**
     * Set seriesPatternBegin
     *
     * @param integer $seriesPatternBegin
     */
    public function setSeriesPatternBegin($seriesPatternBegin)
    {
        $this->seriesPatternBegin = $seriesPatternBegin;
    }

    /**
     * Get seriesPatternBegin
     *
     * @return integer $seriesPatternBegin
     */
    public function getSeriesPatternBegin()
    {
        return $this->seriesPatternBegin;
    }

    /**
     * Set seriesPatternExceptions
     *
     * @param text $seriesPatternExceptions
     */
    public function setSeriesPatternExceptions($seriesPatternExceptions)
    {
        $this->seriesPatternExceptions = $seriesPatternExceptions;
    }

    /**
     * Get seriesPatternExceptions
     *
     * @return text $seriesPatternExceptions
     */
    public function getSeriesPatternExceptions()
    {
        return $this->seriesPatternExceptions;
    }

    /**
     * Set status
     *
     * @param boolean $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return boolean $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set confirmed
     *
     * @param boolean $confirmed
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;
    }

    /**
     * Get confirmed
     *
     * @return boolean $confirmed
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * Set showDetailView
     *
     * @param boolean $showDetailView
     */
    public function setShowDetailView($showDetailView)
    {
        $this->showDetailView = $showDetailView;
    }

    /**
     * Get showDetailView
     *
     * @return boolean $showDetailView
     */
    public function getShowDetailView()
    {
        return $this->showDetailView;
    }

    /**
     * Set author
     *
     * @param string $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * Get author
     *
     * @return string $author
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set allDay
     *
     * @param boolean $allDay
     */
    public function setAllDay($allDay)
    {
        $this->allDay = $allDay;
    }

    /**
     * Get allDay
     *
     * @return boolean $allDay
     */
    public function getAllDay()
    {
        return $this->allDay;
    }

    /**
     * Set locationType
     *
     * @param boolean $locationType
     */
    public function setLocationType($locationType)
    {
        $this->locationType = $locationType;
    }

    /**
     * Get locationType
     *
     * @return boolean $locationType
     */
    public function getLocationType()
    {
        return $this->locationType;
    }

    /**
     * Set place
     *
     * @param string $place
     */
    public function setPlace($place)
    {
        $this->place = $place;
    }

    /**
     * Get place
     *
     * @return string $place
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set placeId
     *
     * @param integer $placeId
     */
    public function setPlaceId($placeId)
    {
        $this->placeId = $placeId;
    }

    /**
     * Get placeId
     *
     * @return integer $placeId
     */
    public function getPlaceId()
    {
        return $this->placeId;
    }

    /**
     * Set placeStreet
     *
     * @param string $placeStreet
     */
    public function setPlaceStreet($placeStreet)
    {
        $this->placeStreet = $placeStreet;
    }

    /**
     * Get placeStreet
     *
     * @return string $placeStreet
     */
    public function getPlaceStreet()
    {
        return $this->placeStreet;
    }

    /**
     * Set placeZip
     *
     * @param string $placeZip
     */
    public function setPlaceZip($placeZip)
    {
        $this->placeZip = $placeZip;
    }

    /**
     * Get placeZip
     *
     * @return string $placeZip
     */
    public function getPlaceZip()
    {
        return $this->placeZip;
    }

    /**
     * Set placeCity
     *
     * @param string $placeCity
     */
    public function setPlaceCity($placeCity)
    {
        $this->placeCity = $placeCity;
    }

    /**
     * Get placeCity
     *
     * @return string $placeCity
     */
    public function getPlaceCity()
    {
        return $this->placeCity;
    }

    /**
     * Set placeCountry
     *
     * @param string $placeCountry
     */
    public function setPlaceCountry($placeCountry)
    {
        $this->placeCountry = $placeCountry;
    }

    /**
     * Get placeCountry
     *
     * @return string $placeCountry
     */
    public function getPlaceCountry()
    {
        return $this->placeCountry;
    }

    /**
     * Set placeLink
     *
     * @param string $placeLink
     */
    public function setPlaceLink($placeLink)
    {
        $this->placeLink = $placeLink;
    }

    /**
     * Get placeLink
     *
     * @return string $placeLink
     */
    public function getPlaceLink()
    {
        return $this->placeLink;
    }

    /**
     * Set placeMap
     *
     * @param string $placeMap
     */
    public function setPlaceMap($placeMap)
    {
        $this->placeMap = $placeMap;
    }

    /**
     * Get placeMap
     *
     * @return string $placeMap
     */
    public function getPlaceMap()
    {
        return $this->placeMap;
    }

    /**
     * Set hostType
     *
     * @param boolean $hostType
     */
    public function setHostType($hostType)
    {
        $this->hostType = $hostType;
    }

    /**
     * Get hostType
     *
     * @return boolean $hostType
     */
    public function getHostType()
    {
        return $this->hostType;
    }

    /**
     * Set orgName
     *
     * @param string $orgName
     */
    public function setOrgName($orgName)
    {
        $this->orgName = $orgName;
    }

    /**
     * Get orgName
     *
     * @return string $orgName
     */
    public function getOrgName()
    {
        return $this->orgName;
    }

    /**
     * Set orgStreet
     *
     * @param string $orgStreet
     */
    public function setOrgStreet($orgStreet)
    {
        $this->orgStreet = $orgStreet;
    }

    /**
     * Get orgStreet
     *
     * @return string $orgStreet
     */
    public function getOrgStreet()
    {
        return $this->orgStreet;
    }

    /**
     * Set orgZip
     *
     * @param string $orgZip
     */
    public function setOrgZip($orgZip)
    {
        $this->orgZip = $orgZip;
    }

    /**
     * Get orgZip
     *
     * @return string $orgZip
     */
    public function getOrgZip()
    {
        return $this->orgZip;
    }

    /**
     * Set orgCity
     *
     * @param string $orgCity
     */
    public function setOrgCity($orgCity)
    {
        $this->orgCity = $orgCity;
    }

    /**
     * Get orgCity
     *
     * @return string $orgCity
     */
    public function getOrgCity()
    {
        return $this->orgCity;
    }

    /**
     * Set orgCountry
     *
     * @param string $orgCountry
     */
    public function setOrgCountry($orgCountry)
    {
        $this->orgCountry = $orgCountry;
    }

    /**
     * Get orgCountry
     *
     * @return string $orgCountry
     */
    public function getOrgCountry()
    {
        return $this->orgCountry;
    }

    /**
     * Set orgLink
     *
     * @param string $orgLink
     */
    public function setOrgLink($orgLink)
    {
        $this->orgLink = $orgLink;
    }

    /**
     * Get orgLink
     *
     * @return string $orgLink
     */
    public function getOrgLink()
    {
        return $this->orgLink;
    }

    /**
     * Set orgEmail
     *
     * @param string $orgEmail
     */
    public function setOrgEmail($orgEmail)
    {
        $this->orgEmail = $orgEmail;
    }

    /**
     * Get orgEmail
     *
     * @return string $orgEmail
     */
    public function getOrgEmail()
    {
        return $this->orgEmail;
    }

    /**
     * Set hostMediadirId
     *
     * @param integer $hostMediadirId
     */
    public function setHostMediadirId($hostMediadirId)
    {
        $this->hostMediadirId = $hostMediadirId;
    }

    /**
     * Get hostMediadirId
     *
     * @return integer $hostMediadirId
     */
    public function getHostMediadirId()
    {
        return $this->hostMediadirId;
    }

    /**
     * Add eventFields
     *
     * @param Cx\Modules\Calendar\Model\Entity\EventField $eventFields
     */
    public function addEventFields(\Cx\Modules\Calendar\Model\Entity\EventField $eventFields)
    {
        $this->eventFields[] = $eventFields;
    }

    /**
     * Get eventFields
     *
     * @return Doctrine\Common\Collections\Collection $eventFields
     */
    public function getEventFields()
    {
        return $this->eventFields;
    }

    /**
     * Add registrations
     *
     * @param Cx\Modules\Calendar\Model\Entity\Registration $registrations
     */
    public function addRegistrations(\Cx\Modules\Calendar\Model\Entity\Registration $registrations)
    {
        $this->registrations[] = $registrations;
    }

    /**
     * Get registrations
     *
     * @return Doctrine\Common\Collections\Collection $registrations
     */
    public function getRegistrations()
    {
        return $this->registrations;
    }

    /**
     * Set category
     *
     * @param Cx\Modules\Calendar\Model\Entity\Category $category
     */
    public function setCategory(\Cx\Modules\Calendar\Model\Entity\Category $category)
    {
        $this->category = $category;
    }

    /**
     * Get category
     *
     * @return Cx\Modules\Calendar\Model\Entity\Category $category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set registrationForm
     *
     * @param Cx\Modules\Calendar\Model\Entity\RegistrationForm $registrationForm
     */
    public function setRegistrationForm(\Cx\Modules\Calendar\Model\Entity\RegistrationForm $registrationForm)
    {
        $this->registrationForm = $registrationForm;
    }

    /**
     * Get registrationForm
     *
     * @return Cx\Modules\Calendar\Model\Entity\RegistrationForm $registrationForm
     */
    public function getRegistrationForm()
    {
        return $this->registrationForm;
    }
}