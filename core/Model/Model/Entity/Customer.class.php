<?php

namespace Cx\Core\Model\Model\Entity;
/*
 * Customer class
  * */
class Customer{
    
    /*
     * Array of the customer information
     * */
    protected $customerInfo = array();
    
    /*
    * Id of the already created customer
    * */
    protected $customerId;
    
    /* setCustomerInfo Sets all the customer info array like name, company name etc.
     * @param $infoArray array of customer info companyName,personName,username,
     * password,status,phone,fax,email,address, city,country
     * */
    public function setCustomerInfo($infoArray){
        $this->customerInfo = $infoArray;
    }
    
    /* getCustomerInfo get all customer info
     * @return customerInfo
     * */
    public function getCustomerInfo(){
        return $this->customerInfo;    
    }
    
    /* setCustomerId Sets the ID of customer.
     * @param $id id of the customer to be set
     */
    public function setCustomerId($id){
        $this->customerId = $id;
    }
    
    /* getCustomerInfo get id of customer
     * @return $customerId id of the created customer
     * */
    public function getCustomerId(){
        return $this->customerId;
    }
    
}
    
