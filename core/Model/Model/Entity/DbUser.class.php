<?php

namespace Cx\Core\Model\Model\Entity;
/*
 * DbUser class
 * */
class DbUser{
    
    protected $id;
    protected $name;
    protected $dbPassword;
    
    /**
     * Set db user id 
     * @param string $id id of the dbUser
     */
    public function setId($id=''){
        $this->id = $id;     
    } 
    
    /**
     * get db user id 
     */
    public function getId(){
        return $this->id;
    }
    
    /**
    * set db username 
    * @param string $name name of the dbUser
    */
    public function setName($name=''){
        $this->name = $name;     
    } 
    
    /**
    * get db username 
    */
    public function getName(){
        return $this->name;
    }
    
    /**
    * set db password 
    * @param string $dbPassword password for the dbUser to be created
    */
    public function setDbPassword($dbPassword=''){
        $this->dbPassword = $dbPassword;     
    } 
    
    /**
    * get db password 
    */
    public function getDbPassword(){
        return $this->dbPassword;
    }
}
