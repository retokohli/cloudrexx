<?php
namespace Cx\Core\Model\Model\Entity;
/*
 * class SubscriptionInfo
 * 
 * */
class SubscriptionInfo{
    
    /*
    * Id of the created subscription 
    */
    protected $subscriptionId;
    
    /*
    * Array of Info of the subscription to be created
    */
    protected $subscriptionName;
    
    /*
    * type of the host
    */
    protected $hostType = 'vrt_hst';
    
    /*
    * ipAddress
    */
    protected $ipAddress;
     
    /*
    * subscriptionStatus
    */
    protected $subscriptionStatus = 0;
    
    /*
    * ftpLoginName
    */
    protected $ftpLoginName;
    
    /*
    * ftpPassword
    */
    protected $ftpPassword;
    
    
    /*
    * setSubscriptionId sets the Id of the created subscription
    * @param $subscriptionId
    */
    function setSubscriptionId($subscriptionId){
        $this->subscriptionId = $subscriptionId;
    }
    
    /* 
    * getSubscriptionId id of the created subscription
    * @return $subscriptionId
    */
    function getSubscriptionId(){
        return $this->subscriptionId;
    }
    
    /*
    * setSubscriptionName sets the value of the $subscriptionName
    * @param $subscriptionName string
    */
    function setSubscriptionName($subscriptionName){
        $this->subscriptionName = $subscriptionName;
    }
    
    /*
    * getSubscriptionName of the Subscription
    * @return $SubscriptionName
    */
    function getSubscriptionName(){
        return $this->subscriptionName;
    }
    
    /*
    * setHostType sets the value of the $hostTame
    * @param $hostTame string
    */
    function setHostType($hostTame){
        $this->hostType = $hostTame;
    }
    
    /*
    * getHostType of the Subscription
    * @return $hostType
    */
    function getHostType(){
        return $this->hostType;
    }
    
    /*
    * setIpAddress of the Subscription
    * @param $ipAddress
    */
    function setIpAddress($ipAddress){
        $this->ipAddress = $ipAddress;    
    }
    
    /*
    * getIpAddress of the Subscription
    * @return $ipAddress
    */
    function getIpAddress(){
        return $this->ipAddress;
    }
    
    /*
    * setSubscriptionStatus of the Subscription
    * @param $subscriptionStatus
    */
    function setSubscriptionStatus($subscriptionStatus){
        $this->subscriptionStatus = $subscriptionStatus;   
    }
    
    /*
    * getSubscriptionStatus of the Subscription
    * @return $subscriptionStatus
    */
    function getSubscriptionStatus(){
        return $this->subscriptionStatus;
    }
    
    /*
    * setFtpLoginName of the Subscription
    * @param $subscriptionStatus
    */
    function setFtpLoginName($ftpLoginName){
        $this->ftpLoginName = $ftpLoginName;   
    }
    
    /* 
    * getFtpLoginName of the Subscription
    * @return $ftpLoginName
    */
    function getFtpLoginName(){
        return $this->ftpLoginName;
    }
    
    /* 
    * setFtpPassword of the Subscription
    * @param $ftpPassword
    */
    function setFtpPassword($ftpPassword){
        $this->ftpPassword = $ftpPassword;
    }
    
    /*
    * getFtpPassword of the Subscription
    * @return $ftpPassword
    */
    function getFtpPassword(){
        return $this->ftpPassword;
    }

}
    
