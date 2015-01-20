<?php

/**
 * Product
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_pim
 */

namespace Cx\Modules\Pim\Model\Entity;

class ProductException extends \Exception {};

/**
 * Product
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_pim
 */
class Product extends \Cx\Model\Base\EntityBase {
    protected $id;
    protected $name;
    protected $renewalOptions = array();
    protected $defaultRenewalOption = array();
    protected $entityClass = null;
    protected $entityAttributes = array();
    protected $renewable = false;
    protected $expirable = false;
    protected $upgradable = false;
    protected $expirationUnit = null;
    protected $expirationQuantifier = null;
    protected $upgradableProducts = array();
	protected $prices;
    protected $subscriptions;
    protected $noteEntity = null;
    protected $noteRenewal = null;
    protected $noteUpgrade = null;
    protected $noteExpiration = null;
    protected $notePrice = null;
    
    /**
     * @var integer
     */
    protected $cancellationQuantifier = null;
    
    /**
     * @var string
     */
    protected $cancellationUnit = null;

    /**
     * @var array $upgrades
     */
    protected $upgrades;

    const UNIT_DAY = 'day';
    const UNIT_MONTH = 'month';
    const UNIT_WEEK = 'week';
    const UNIT_YEAR = 'year';

    public function __construct() {
        $this->initRenewalConfig();
    	$this->upgrades = new \Doctrine\Common\Collections\ArrayCollection();
    	$this->prices = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getRenewalOptions() {
        return $this->renewalOptions;
    }

    public function setRenewalOptions($renewalOptions) {
        $this->renewalOptions = $renewalOptions;
    }

    public function getDefaultRenewalOption() {
        return $this->defaultRenewalOption;
    }

    public function setDefaultRenewalOption($defaultRenewalOption) {
        return $this->defaultRenewalOption = $defaultRenewalOption;
    }

    public function getEntityClass() {
        return $this->entityClass;
    }

    public function setEntityClass($entityClass) {
        $this->entityClass = $entityClass;
    }

    public function getEntityAttributes() {
        return $this->entityAttributes;
    }

    public function setEntityAttributes($entityAttributes) {
        $this->entityAttributes = $entityAttributes;
    }

    public function getRenewable() {
        return $this->renewable;
    }

    public function isRenewable() {
        return $this->getRenewable();
    }

    public function setRenewable($renewable) {
        $this->renewable = $renewable;
    }

    public function getExpirable() {
        return $this->expirable;
    }

    public function isExpirable() {
        return $this->getExpirable();
    }

    public function setExpirable($expirable) {
        $this->expirable = $expirable;
    }

    public function getUpgradable() {
        return $this->upgradable;
    }

    public function isUpgradable() {
        return $this->getUpgradable();
    }

    public function setUpgradable($upgradable) {
        $this->upgradable = $upgradable;
    }

    public function getExpirationUnit() {
        return $this->expirationUnit;
    }

    public function setExpirationUnit($expirationUnit) {
        $this->expirationUnit = $expirationUnit;
    }

    public function getExpirationQuantifier() {
        return $this->expirationQuantifier;
    }

    public function setExpirationQuantifier($expirationQuantifier) {
        $this->expirationQuantifier = $expirationQuantifier;
    }
    
    /**
     * Getter for $this->cancellationQuantifier
     * 
     * @return integer
     */
    public function getCancellationQuantifier()
    {
        return $this->cancellationQuantifier;
    }
    
    /**
     * Setter for $this->cancellationQuantifier
     * 
     * @param integer $cancellationQuantifier
     */
    public function setCancellationQuantifier($cancellationQuantifier)
    {
        $this->cancellationQuantifier = $cancellationQuantifier;
    }
    
    /**
     * Getter for $this->cancellationUnit
     * 
     * @return string
     */
    public function getCancellationUnit()
    {
        return $this->cancellationUnit;
    }
    
    /**
     * Setter for $this->cancellationUnit
     * 
     * @param string $cancellationUnit
     */
    public function setCancellationUnit($cancellationUnit)
    {
        $this->cancellationUnit = $cancellationUnit;
    }

    public function getUpgradableProducts() {
        return $this->upgradableProducts;
    }

    public function setUpgradableProducts($upgradableProducts) {
        $this->upgradableProducts = $upgradableProducts;
    }

    public function getPrice() {
        return $this->price;
    }

    public function setPrice($price) {
        $this->price = $price;
    }

    public function getSubscriptions() {
        return $this->subscriptions;
    }

    public function setSubscriptions($subscriptions) {
        $this->subscriptions = $subscriptions;
    }
    
    public function getNoteEntity() {
        return $this->noteEntity;
    }

    public function setNoteEntity($noteEntity) {
        $this->noteEntity = $noteEntity;
    }
    
    public function getNoteRenewal() {
        return $this->noteRenewal;
    }

    public function setNoteRenewal($noteRenewal) {
        $this->noteRenewal = $noteRenewal;
    }
    
    public function getNoteUpgrade() {
        return $this->noteUpgrade;
    }

    public function setNoteUpgrade($noteUpgrade) {
        $this->noteUpgrade = $noteUpgrade;
    }
    
    public function getNoteExpiration() {
        return $this->noteExpiration;
    }

    public function setNoteExpiration($noteExpiration) {
        $this->noteExpiration = $noteExpiration;
    }
    
    public function getNotePrice() {
        return $this->notePrice;
    }

    public function setNotePrice($notePrice) {
        $this->notePrice = $notePrice;
    }
    
    public function getNewEntityForSale($saleOptions) {
        return \Env::get('em')->getRepository($this->entityClass)->findOneForSale($this->entityAttributes, $saleOptions);
    }

    public function getEntityById($entityId) {
        $entityIdKey = \Env::get('em')->getClassMetadata($this->entityClass)->getSingleIdentifierFieldName(); 
        return \Env::get('em')->getRepository($this->entityClass)->findOneBy(array($entityIdKey => $entityId));
    }

    public function getExpirationDate($expirationUnit = '', $expirationQuantifier = 0) {
        if (!$this->expirable) {
            throw new ProductException('Product is not expirable.');
        }
        $defaultExpiration = new \DateTime("+$this->expirationQuantifier $this->expirationUnit");
        $currentExpiration = new \DateTime("+$expirationQuantifier  $expirationUnit");

        if ($defaultExpiration > $currentExpiration) {
            return $defaultExpiration;
        }

        return $currentExpiration;
    }

    public function getRenewalDate($unit, $quantifier) {
        if (!$this->renewable) {
            throw new ProductException('Product is not renewable.');
        }
        if (!$this->isValidRenewalDefinition($unit, $quantifier)) {
            throw new ProductException("Invalid renewal definition supplied: $quantifier $unit");
        }

        $renewalDate = new \DateTime();
        $renewalDate->modify("+$quantifier $unit");
        return $renewalDate;
    }

    protected function isValidRenewalDefinition($unit, $quantifier) {
        return    isset($this->renewalOptions[$unit])
               && in_array($quantifier, $this->renewalOptions[$unit]);
    }

    public function getRenewalDefinition($unit, $quantifier) {
        if (empty($this->renewalOptions)) {
            $this->initRenewalConfig();
        }
        
        if ($this->isValidRenewalDefinition($unit, $quantifier)) {
            return array($unit, $quantifier);
        }

        return $this->defaultRenewalOption;
    }

    protected function initRenewalConfig() {
        $this->renewalOptions = array(
            self::UNIT_MONTH  => array(1   ),
            self::UNIT_YEAR    => array(1, 2),
        );
        $this->defaultRenewalOption = array(self::UNIT_YEAR, 1);
    }
    
    /**
     * Get the available upgrades
     * 
     * @return array Return the available upgrades
     */
    public function getUpgrades()
    {
        return $this->upgrades;
    }
    
    /**
     * Add upgrade product to the existing upgrades
     * 
     * @param type $upgrade
     */
    public function addUpgrade(Product $upgrade)
    {
        $this->upgrades[] = $upgrade;
    }

    /**
     * Get the payment amount based on the unit and quantifier
     *
     * @param string $unit
     * @param integer $quantifier
     * @param \Cx\Modules\Crm\Model\Entity\Currency $currency
     *
     * @return \Cx\Core\Core\Controller\Cx|float|int
     */
    public function getPaymentAmount($unit = self::UNIT_MONTH, $quantifier = 1, $currency = null) {
        $paymentAmount = 0;
        $prices = $this->getPrices();
        $amount = 0;
        foreach ($prices as $price) {
            if ($price->getCurrency() != $currency) {
                continue;
            }
            $amount = $price->getAmount();
        }
        switch ($unit) {
            case self::UNIT_DAY:
                $paymentAmount = $amount * ($quantifier / 30);
                break;
            case self::UNIT_WEEK:
                $paymentAmount = $amount * ($quantifier / 4);
                break;
            case self::UNIT_MONTH:
                $paymentAmount = $amount * $quantifier;
                break;
            case self::UNIT_YEAR:
                $paymentAmount = $amount * $quantifier * 12;
                if ($quantifier > 1) {
                    $paymentAmount *= 0.9;
                }
                break;
        }
        return $paymentAmount;
    }

    /**
     * Add prices
     *
     * @param \Cx\Modules\Pim\Model\Entity\Price $prices
     */
    public function addPrices(\Cx\Modules\Pim\Model\Entity\Price $prices)
    {
        $this->prices[] = $prices;
    }

    /**
     * Get prices
     *
     * @return \Doctrine\Common\Collections\Collection $prices
     */
    public function getPrices()
    {
        return $this->prices;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
