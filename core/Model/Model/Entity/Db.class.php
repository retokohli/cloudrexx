<?php
namespace \Cx\Core\Model\Model\Entity;
/*
 * Db class
 * */
class Db{
    
    protected $id;
    protected $name;  

    /**
     * Set db id 
     * @param string $id id of the dbUser
     */
    public function setId($id=''){
        $this->id = $id;     
    } 
    
    /**
     * get db id 
     */
    public function getId(){
        return $this->id;
    }
    
    /**
    * set db name 
    * @param string $name name of the db
    */
    public function setName($name=''){
        $this->name = $name;     
    } 
    
    /**
    * get db name 
    */
    public function getName(){
        return $this->name;
    }
}
