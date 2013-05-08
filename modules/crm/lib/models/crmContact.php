<?php

class crmContact {
    
    protected $moduleName = "crm";
    
    function load($id = null) {
        global $objDatabase;

        $this->id = $id;
        if (!empty($this->id)) {
            $query = "SELECT c.id, c.customer_id, c.customer_type,
                             c.customer_name, c.customer_addedby,
                             c.customer_currency, c.contact_familyname,
                             c.contact_role, c.contact_customer, c.contact_language,
                             c.notes, c.contact_type,c.user_account,c.added_date,c.industry_type,                             
                             u.username AS resource,e.email,p.phone, c.datasource,
                             c.gender,c.profile_picture
                         FROM `".DBPREFIX."module_{$this->moduleName}_contacts` AS c
                         LEFT JOIN ".DBPREFIX."access_users u
                             ON c.`customer_addedby` = u.`id`
                         LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_customer_contact_emails` as e
                             ON (c.`id` = e.`contact_id` AND e.`is_primary` = '1')
                         LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_customer_contact_phone` as p
                             ON (c.`id` = p.`contact_id` AND p.`is_primary` = '1')
                         WHERE c.`id` = {$this->id}";
            $objResult = $objDatabase->Execute($query);
            if (FALSE != $objResult) {
                $this->contactType      = $objResult->fields['contact_type'];                
                $this->customerId       = $objResult->fields['customer_id'];
                $this->customerType     = $objResult->fields['customer_type'];
                $this->customerName     = $objResult->fields['customer_name'];
                $this->family_name      = $objResult->fields['contact_familyname'];
                $this->contact_role     = $objResult->fields['contact_role'];
                $this->contact_language = $objResult->fields['contact_language'];
                $this->contact_customer = $objResult->fields['contact_customer'];
                $this->addedUser        = $objResult->fields['customer_addedby'];
                $this->currency         = $objResult->fields['customer_currency'];                
                $this->notes            = $objResult->fields['notes'];
                $this->industryType     = $objResult->fields['industry_type'];
                $this->account_id       = $objResult->fields['user_account'];                
                $this->datasource       = $objResult->fields['datasource'];
                $this->contact_gender   = $objResult->fields['gender'];
                $this->profile_picture  = $objResult->fields['profile_picture'];

                $this->email            = $objResult->fields['email'];
                $this->phone            = $objResult->fields['phone'];
                $this->added_date       = $objResult->fields['added_date'];                
            }
            return TRUE;
        }
        return FALSE;
    }

    function getCustomerDetails() {
        global $objDatabase, $_LANGID;
        
        $query = "SELECT   c.id,
                           c.customer_id,
                           c.customer_type,
                           c.customer_name,
                           c.contact_familyname,
                           c.contact_type,
                           c.contact_customer AS contactCustomerId,
                           c.status,
                           c.added_date,
                           c.contact_role,
                           c.notes,
                           c.customer_addedby,
                           c.industry_type,
                           idn.value AS industry_name,
                           c.user_account,
                           c.datasource,
                           c.gender,
                           con.customer_name AS contactCustomer,                           
                           t.label AS cType,
                           u.username AS added_user,
                           lang.name AS language,
                           curr.name AS currency,
                           c.profile_picture
                       FROM `".DBPREFIX."module_{$this->moduleName}_contacts` AS c
                       LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_contacts` AS con
                         ON c.contact_customer =con.id
                       LEFT JOIN ".DBPREFIX."module_{$this->moduleName}_customer_types AS t
                         ON c.customer_type = t.id
                       LEFT JOIN ".DBPREFIX."module_{$this->moduleName}_industry_types AS i
                         ON c.industry_type = i.id
                       LEFT JOIN ".DBPREFIX."module_{$this->moduleName}_industry_type_local AS idn
                         ON idn.entry_id = i.id AND lang_id = {$_LANGID}
                       LEFT JOIN ".DBPREFIX."module_{$this->moduleName}_currency AS curr
                         ON c.customer_currency = curr.id
                       LEFT JOIN ".DBPREFIX."access_users AS u
                         ON c.customer_addedby = u.id
                       LEFT JOIN ".DBPREFIX."languages AS lang
                         ON c.contact_language = lang.id
                       WHERE c.id = {$this->id}";
        $objResult = $objDatabase->SelectLimit($query, 1);
        
        if ($objResult) {
            foreach ($objResult->fields as $key => $value) {
                $customerDetail[$key] = $value;
            }            
        }

        return $customerDetail;
    }

    function save() {
        global $objDatabase;

        $fields = array(
            'customer_id'       => isset ($this->customerId) ? $this->customerId : '',
            'customer_type'     => isset ($this->customerType) ? (int) $this->customerType : 0,
            'customer_name'     => isset ($this->customerName) ? $this->customerName : '',
            'customer_addedby'  => isset ($this->addedUser) ? (int) $this->addedUser : 1,
            'customer_currency' => isset ($this->currency) ? (int) $this->currency : 0,
            'contact_familyname'=> isset ($this->family_name) ? $this->family_name : '',
            'contact_role'      => isset ($this->contact_role) ? $this->contact_role : '',
            'contact_customer'  => isset ($this->contact_customer) ? (int) $this->contact_customer : '',
            'contact_language'  => isset ($this->contact_language) ? (int) $this->contact_language : '',
            'notes'             => isset ($this->notes) ? $this->notes : '',
            'industry_type'     => isset ($this->industryType) ? $this->industryType : '',
            'contact_type'      => isset ($this->contactType) ? (int) $this->contactType : '',
            'user_account'      => isset ($this->account_id) ? (int) $this->account_id : '',
            'gender'            => isset ($this->contact_gender) ? (int) $this->contact_gender : '',
            'profile_picture'   => array ( 'val' => isset ($this->profile_picture) && !empty($this->profile_picture) ? $this->profile_picture : NULL, 'omitEmpty' => true)
        );

        if (!isset($this->id) || empty ($this->id)) {
            $fields['datasource'] = isset ($this->datasource) ? $this->datasource : '';
            $fields['added_date'] = date('Y-m-d H:i:s');
            $query = SQL::insert("module_{$this->moduleName}_contacts", $fields, array('escape' => true));
        } else {
            $query = SQL::update("module_{$this->moduleName}_contacts", $fields, array('escape' => true))." WHERE `id` = {$this->id}";
        }
        //echo $query; exit();
        if ($objDatabase->execute($query)) {
            if (!isset($this->id) || empty ($this->id))
                    $this->id = $objDatabase->INSERT_ID();
            
            return true;
        }
        
        return false;
    }
    
    function  __set($name,  $value) {
        $this->{$name} = $value;
    }

    function  __get($name) {        
        return $this->{$name};
    }

    function clean() {
        $this->id               = 0;
        $this->contactType      = 0;
        $this->customerId       = '';
        $this->customerType     = 0;
        $this->customerName     = '';
        $this->family_name      = '';
        $this->contact_role     = '';
        $this->contact_language = 0;
        $this->contact_customer = 0;
        $this->addedUser        = 0;
        $this->currency         = 0;
        $this->notes            = '';
        $this->industryType     = 0;
        $this->account_id       = 0;
        $this->datasource       = 0;
        $this->contact_gender   = 0;
        $this->profile_picture  = '';

        $this->email            = '';
        $this->phone            = '';
        $this->added_date       = '';
    }
}
