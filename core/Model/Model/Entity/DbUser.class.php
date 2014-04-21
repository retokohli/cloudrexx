<?php

namespace \Cx\Core\Model\Model\Entity\DbUser;
/*
 * DbUser class
 * */
class DbUser{
    
    protected $id;
    protected $name;
    
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
}
