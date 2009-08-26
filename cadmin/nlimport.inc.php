<?php
/*********************************/
/********  SETTINGS  *************/
/*********************************/
$_CONFIG['newsletterImportCategoryName'] = "Importierte Benutzer"; //name of the newsletter category to import to
$_CONFIG['defaultUserStatus'] = 1; //specifies the status of the newly added newsletter users
set_time_limit(300);

//check if configured newsletter category where we import into is existent, create if necessary
$newsletterListIdToImportTo = checkCreateNewsletterCategory($_CONFIG['newsletterImportCategoryName']);

$arrTables = $objDatabase->MetaTables('TABLES');

importUsersIntoCategory(fetchShopUsers(),   $newsletterListIdToImportTo);
importUsersIntoCategory(fetchSystemUsers(), $newsletterListIdToImportTo);
importUsersIntoCategory(fetchVotingUsers(), $newsletterListIdToImportTo);

/**
 * imports users to the specified category, adds users if they don't exists yet
 *
 * @param array $arrNewsletterUsersToImport
 * @param integer $newsletterListID
 * @return array
 */
function importUsersIntoCategory($arrNewsletterUsersToImport, $newsletterListID){
    global $objDatabase;
    require_once ASCMS_MODULE_PATH.'/newsletter/lib/NewsletterLib.class.php';
    $objNewsletterLib = new NewsletterLib();
    $addedUsersCount = 0;
    $associatedWithListUserCount = 0;
    foreach ($arrNewsletterUsersToImport as $u) {
        $objRS = $objDatabase->Execute('
            SELECT `id`
            FROM `'.DBPREFIX.'module_newsletter_user`
            WHERE `email` = "'.$u['email'].'"'
        );
        if($objRS->RecordCount() == 1){
            if($objNewsletterLib->_addRecipient2List($objRS->fields['id'], $newsletterListID)){
                $associatedWithListUserCount++;
            }
        } else {
            if($objNewsletterLib->_addRecipient($u['email'], $u['uri'], $u['sex'], '', $u['lastname'], $u['firstname'], $u['company'],
                                         $u['street'], $u['zip'], $u['city'], $u['country'], $u['phone'], $u['birthday'],
                                         $u['status'], array($newsletterListID))){
                $addedUsersCount++;
                $associatedWithListUserCount++;
            }
        }
    }
    return array('added' => $addedUsersCount, 'associated' => $associatedWithListUserCount);
}

/**
 * Fetches the system users
 *
 * @return array
 */
function fetchSystemUsers(){
    global $objDatabase, $_CONFIG;
    $arrUsers = array();
    $objRS = $objDatabase->Execute('
        SELECT  `u`.`active`,        `u`.`username`,     `u`.`email`,   `p`.`gender`, `p`.`firstname`, `p`.`lastname`,
                `p`.`company`,       `p`.`address`,      `p`.`city`,    `p`.`zip`,    `p`.`country`,   `p`.`phone_office`,
                `p`.`phone_private`, `p`.`phone_mobile`, `p`.`website`, `p`.`birthday`
        FROM `'.DBPREFIX.'access_users` AS `u`
        LEFT JOIN `'.DBPREFIX.'access_user_profile` AS `p` ON (`u`.`id` = `p`.`user_id`)
        WHERE INSTR(`u`.`email`, "@") > 0'
    );
    while(!$objRS->EOF){
        switch ($objRS->fields['gender']) {
            case 'gender_male':
                $objRS->fields['gender'] = "'m'";
                break;
            case 'gender_female':
                $objRS->fields['gender'] = "'f'";
                break;
            default:
                $objRS->fields['gender'] = 'null';
                break;
        }

        if($objRS->fields['phone_office'] != ''){
            $phone = $objRS->fields['phone_office'];
        }
        if($objRS->fields['phone_private'] != ''){
            $phone = $objRS->fields['phone_private'];
        }
        if($objRS->fields['phone_mobile'] != ''){
            $phone = $objRS->fields['phone_mobile'];
        }

        $arrUsers[] = array(
            'email'     => $objRS->fields['email'],
            'uri'       => $objRS->fields['website'],
            'sex'       => $objRS->fields['gender'],
            'lastname'  => $objRS->fields['lastname'],
            'firstname' => $objRS->fields['firstname'],
            'company'   => $objRS->fields['company'],
            'street'    => $objRS->fields['address'],
            'zip'       => $objRS->fields['zip'],
            'city'      => $objRS->fields['city'],
            'country'   => $objRS->fields['country'],
            'birthday'  => $objRS->fields['birthday'],
            'phone'     => $phone,
            'status'    => $_CONFIG['defaultUserStatus'],
        );
        $objRS->MoveNext();
    }
    return $arrUsers;
}

/**
 * Fetches the voting users
 *
 * @return array
 */
function fetchVotingUsers(){
    global $objDatabase, $_CONFIG;
    $arrUsers = array();
    $objRS = $objDatabase->Execute('
        SELECT  `forename`, `surname`, `phone`, `street`, `zip`, `city`, `email`
        FROM `'.DBPREFIX.'voting_additionaldata`
        WHERE INSTR(`email`, "@") > 0'
    );

    while(!$objRS->EOF){
        $arrUsers[] = array(
            'email'     => $objRS->fields['email'],
            'uri'       => '',
            'sex'       => 'null',
            'lastname'  => $objRS->fields['surname'],
            'firstname' => $objRS->fields['forename'],
            'company'   => '',
            'street'    => $objRS->fields['street'],
            'zip'       => $objRS->fields['zip'],
            'city'      => $objRS->fields['city'],
            'country'   => '',
            'birthday'  => '',
            'phone'     => $objRS->fields['phone'],
            'status'    => $_CONFIG['defaultUserStatus'],
        );
        $objRS->MoveNext();
    }
    return $arrUsers;
}

/**
 * Fetches the shop users
 *
 * @return array
 */
function fetchShopUsers(){
    global $objDatabase, $_CONFIG;
    $arrUsers = array();
    $objRS = $objDatabase->Execute('
        SELECT  `u`.`username`,  `u`.`email`,   `u`.`firstname`, `u`.`lastname`,
                `u`.`company`,   `u`.`address`, `u`.`city`,      `u`.`zip`, `c`.`countries_name`,   `u`.`phone`
        FROM `'.DBPREFIX.'module_shop_customers` AS `u`
        LEFT JOIN `'.DBPREFIX.'module_shop_countries` AS `c` ON (`u`.`country_id` = `c`.`countries_id`)
        WHERE INSTR(`u`.`email`, "@") > 0'
    );
    while(!$objRS->EOF){
        $arrUsers[] = array(
            'email'     => $objRS->fields['email'],
            'uri'       => '',
            'sex'       => 'null',
            'lastname'  => $objRS->fields['lastname'],
            'firstname' => $objRS->fields['firstname'],
            'company'   => $objRS->fields['company'],
            'street'    => $objRS->fields['address'],
            'zip'       => $objRS->fields['zip'],
            'city'      => $objRS->fields['city'],
            'country'   => $objRS->fields['countries_name'],
            'birthday'  => '',
            'phone'     => $objRS->fields['phone'],
            'status'    => $_CONFIG['defaultUserStatus'],
        );
        $objRS->MoveNext();
    }
    return $arrUsers;
}



/**
 * return ID of newsletter category name, create if non-existent
 *
 * @param string $strCategoryName
 * @return integer $newsletterListIdToImportTo
 */
function checkCreateNewsletterCategory($strCategoryName){
    global $objDatabase;
    $objRS = $objDatabase->SelectLimit('
        SELECT `id` FROM `'.DBPREFIX.'module_newsletter_category`
        WHERE `name` = '."'$strCategoryName'", 1
    );
    if($objRS->RecordCount() == 1){
        $newsletterListIdToImportTo = $objRS->fields['id'];
    } else {
        if($objDatabase->Execute("
            INSERT INTO ".DBPREFIX."module_newsletter_category (`name`, `status`, `notification_email`)
            VALUES ('".$strCategoryName."', 0, '')"
        )){
            $newsletterListIdToImportTo = $objDatabase->Insert_ID();
        }else{
            die("Failed to create newsletter category. Please contact the server admin.\n<br />
                 Newsletter Kategorie konnte nicht erstellt werden. Bitte wenden Sie sich an den Administartor.");
        }
    }
    return $newsletterListIdToImportTo;
}

?>
