<?php

//error_reporting(E_ALL);

/**
 * Partners library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Sureshkumar.C
 * @version     v 1.00
 * @package     contrexx
 * @subpackage  partners_partners
 */

/**
 * Includes
 */
class PartnersLibrary {
    var $_boolInnoDb = false;
    var $_intLanguageId;
    var $_intCurrentUserId;
    var $_arrSettings           = array();
    var $_arrLanguages          = array();
    var $_arrlistLevel = null;
    var $PaginactionCount;
    var $orderedResults,$orderofResult;

    /**
    * Constructor
    *
    */
    function __construct()
    {
        $this->setDatabaseEngine();
        $this->_arrSettings     = $this->createSettingsArray();
        $this->_arrLanguages    = $this->createLanguageArray();
    }


    /**
     * Reads out the used database engine and sets the local variable.
     * @global      array       $objDatabase
     */
    function setDatabaseEngine() {
        global $objDatabase;

        $objMetaResult = $objDatabase->Execute('SHOW TABLE STATUS LIKE "'.DBPREFIX.'module_partners_settings"');

        if (preg_match('/.*innodb.*/i', $objMetaResult->fields['Engine'])) {
            $this->_boolInnoDb = true;
        }
    }



 /*  function getCategory() {
     global $objDatabase;
     $objlevelResult= $objDatabase->Execute('SELECT name FROM "'.DBPREFIX.'"module_partners_categories_name');
       if ($objlevelResult->RecordCount() > 0) {

            while (!$objlevelResult->EOF) {
             // echo $intMessageId." ";
                $intMessageId = $objlevelResult->fields['name'];
                }
      $level="ravi";
      return $level;
   }


  */





    /**
    * used to create the array of categories
    * @global      array       $objDatabase
    */
    function createEntryArray($intLanguageId=0, $intStartingIndex=0, $intLimitIndex=0)
    {
        global $objDatabase,$_ARRAYLANG;

        if(!empty($_REQUEST['subject'])){
           $searchSubject = contrexx_addslashes(strip_tags($_REQUEST['subject']));
           $resultContent .= $_ARRAYLANG['TXT_PARTNERS_SEARCH_RESULTS'];
           $srchActive = true;
          }

          if($_REQUEST['level'] != 0) {
            $searchLevel = htmlentities($_REQUEST['level'],ENT_QUOTES,CONTREXX_CHARSET);
            $srchActive = true;
          }

          if($_REQUEST['profile'] != 0) {
            $searchProfile = htmlentities($_REQUEST['profile'],ENT_QUOTES,CONTREXX_CHARSET);
            $srchActive = true;
          }

          if($_REQUEST['country'] != 0) {
            $searchCountry = htmlentities($_REQUEST['country'],ENT_QUOTES,CONTREXX_CHARSET);
            $srchActive = true;
          }

          if($_REQUEST['vertical'] != 0) {
           $searchVertical = htmlentities($_REQUEST['vertical'],ENT_QUOTES,CONTREXX_CHARSET);
           $srchActive = true;
           }


          if(!empty($resultContent))
          {
          $this->_strOkMessage = $resultContent;
          }
         $arrReturn = array();

        if (intval($intLanguageId) > 0) {

            $strLanguageJoin  = ' INNER JOIN '.DBPREFIX.'module_partners_create_lang AS partnerscreateLanguage
                                    ON partnerscreate.message_id = partnerscreateLanguage.message_id
                                ';
            $strLanguageWhere = ' WHERE partnerscreateLanguage.lang_id='.$intLanguageId.' AND
                                            partnerscreateLanguage.is_active="1"
                                ';
         } else {
            $strLanguageJoin  = '';
            $strLanguageWhere = '';
         }

         if ($intLimitIndex == 0) {
            $intLimitIndex = $this->countEntries();
         }
         $objSettings = $objDatabase->Execute('SELECT sortorder FROM '.DBPREFIX.'module_partners_settings LIMIT 1');
          $order = trim(strtolower($objSettings->fields['sortorder']));

          if($order == "level"){

            $order = "partnersLevel.category_id";

            $strLevelJoin  = ' INNER JOIN '.DBPREFIX.'module_partners_message_to_level AS partnersLevel
                               ON partnersLevel.message_id = partnersMain.message_id';
          } else {
              $order = "partnersMain.".trim(strtolower($objSettings->fields['sortorder']));
              $strLevelJoin="";
          }

         //echo "search Archive".$srchActive;
         if($srchActive != true){

           // echo "coming inside";
            if($_REQUEST["test"]!=""){
             $Limit = "";
            }
            if($_REQUEST["test"]==""){
              $Limit =  "LIMIT " .$intStartingIndex.','.$intLimitIndex;
            }


                 $selOverview='SELECT     DISTINCT(partnersMessages.message_id),
                                                        partnersMessages.user_id,
                                                        partnersMessages.time_created,
                                                        partnersMessages.time_edited,
                                                        partnersMessages.hits,
                                                        user.username,
                                                        partnersMain.subject
                                             FROM        '.DBPREFIX.'module_partners_create        AS partnersMessages
                                            INNER JOIN    '.DBPREFIX.'access_users                AS user
                                            ON            partnersMessages.user_id = user.id
                                            INNER JOIN    '.DBPREFIX.'module_partners_create_lang AS partnersMain
                                            ON            partnersMessages.message_id = partnersMain.message_id
                                             '.$strLevelJoin.'
                                             '.$strLanguageJoin.'
                                             '.$strLanguageWhere.'
                                             ORDER BY '.$order.' ASC
                                             '.$Limit.'

                                        ';

                  $objResult = $objDatabase->Execute($selOverview);

                   $countQuery='SELECT COUNT(distinct(partnersMessages.message_id)) AS numberOfEntries
                             FROM        '.DBPREFIX.'module_partners_create        AS partnersMessages
                                            INNER JOIN    '.DBPREFIX.'access_users                AS user
                                            ON            partnersMessages.user_id = user.id
                                            INNER JOIN    '.DBPREFIX.'module_partners_create_lang AS partnersMain
                                            ON            partnersMessages.message_id = partnersMain.message_id

                                             '.$strLanguageJoin.'
                                             '.$strLanguageWhere.'
                                             ORDER BY '.$order.' ASC';
                $objResultCount = $objDatabase->Execute($countQuery);
                $cnt=$objResultCount->fields['numberOfEntries'];
                $this->PaginactionCount=$cnt;

            //$this->PaginactionCount=intval($objResultCount->RecordCount()/2);
        }                                     //LIMIT '.$intStartingIndex.','.$intLimitIndex.'
        else{



            /** Single Combinations... */

            /**Checking with the Subject..*/

             if(!empty($searchSubject) && empty($searchLevel) && empty($searchProfile) && empty($searchCountry) && empty($searchVertical) ) {

                $concat='( partnersMain.subject like "'.'%'.$searchSubject.'%'.'")';
             }

             /**Checking with Search Level.. */

             elseif(empty($searchSubject) && !empty($searchLevel)  && empty($searchProfile) && empty($searchCountry) && empty($searchVertical)) {

                $concat='(partnersLevel.category_id = "'.$searchLevel.'")';

             }

             /**Checking with Search Profile.. */

             elseif(empty($searchSubject)  && empty($searchLevel) && !empty($searchProfile) && empty($searchCountry) && empty($searchVertical)) {

                $concat='(partnersProfile.category_id= "'.$searchProfile.'")';

             }

             /**Checking with Search Country.. */

             elseif(empty($searchSubject) && empty($searchLevel) && empty($searchProfile) && !empty($searchCountry) && empty($searchVertical)) {

                $concat='(partnersCountry.category_id="'.$searchCountry.'")';

             }

             /**Checking with Search Vertical.. */

             elseif(empty($searchSubject) && empty($searchLevel) && empty($searchProfile) && empty($searchCountry) && !empty($searchVertical)) {

                $concat='(partnersVertical.category_id ="'.$searchVertical.'")';

             }


             /** Subject two combinations.....*/

             /** Checking Search Subject and Search Level... */

             elseif(!empty($searchSubject) && !empty($searchLevel) && empty($searchProfile) && empty($searchCountry) && empty($searchVertical)) {

                $concat='( partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersLevel.category_id = "'.$searchLevel.'")';
             }
             /** Checking Search Subject and Search Profile... */

             elseif(!empty($searchSubject) && empty($searchLevel) && !empty($searchProfile) && empty($searchCountry) && empty($searchVertical)) {

                $concat='( partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersProfile.category_id= "'.$searchProfile.'")';
             }

              /** Checking Search Subject and Search Country... */

             elseif(!empty($searchSubject) && empty($searchLevel) && empty($searchProfile) && !empty($searchCountry) && empty($searchVertical)) {

                $concat='( partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersCountry.category_id="'.$searchCountry.'")';
             }


             /** Checking Search Subject and Search Vertical... */

             elseif(!empty($searchSubject) && empty($searchLevel) && empty($searchProfile) && empty($searchCountry) && !empty($searchVertical)) {

                $concat='( partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'")';
             }


             /**Level Combinations */

             /**Checking with Level and Profile.. */

             elseif(empty($searchSubject) && !empty($searchLevel) && !empty($searchProfile) && empty($searchCountry) && empty($searchVertical)) {

                $concat='( partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersProfile.category_id= "'.$searchProfile.'")';
             }

             /**Checking with Level and Country.. */

             elseif(empty($searchSubject) && !empty($searchLevel) && empty($searchProfile) && !empty($searchCountry) && empty($searchVertical)) {

                $concat='( partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersCountry.category_id="'.$searchCountry.'")';
             }

             /**Checking with Level and Vetical.. */

             elseif(empty($searchSubject) && !empty($searchLevel) && empty($searchProfile) && empty($searchCountry) && !empty($searchVertical)) {

                $concat='( partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'")';
             }



             /**Profile two combinations..... */

             /** Checking with the Search Profile and Search Country.... */

             elseif(empty($searchSubject) && empty($searchLevel) && !empty($searchProfile) && !empty($searchCountry) && empty($searchVertical)) {

                $concat='( partnersProfile.category_id= "'.$searchProfile.'" AND
                           partnersCountry.category_id="'.$searchCountry.'")';
             }

             /**Checking with the searchProfile and Search vetical.. */

             elseif(empty($searchSubject) && empty($searchLevel)  && !empty($searchProfile) && empty($searchCountry) && !empty($searchVertical)) {

                $concat='( partnersProfile.category_id= "'.$searchProfile.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'")';
             }




             /**Country Combinations two */


             /** Checking with Search Country and Search Vetical... */

             elseif(empty($searchSubject) && empty($searchLevel)  && empty($searchProfile) && !empty($searchCountry) && !empty($searchVertical)) {

                $concat='(  partnersCountry.category_id="'.$searchCountry.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'"
                           )';
             }




             /**Three combinations.... */


              /** Checking with Subject,Level,Profile... abc*/

             elseif(!empty($searchSubject) && !empty($searchLevel)  && !empty($searchProfile) && empty($searchCountry) && empty($searchVertical)) {

                $concat='( partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersProfile.category_id= "'.$searchProfile.'"
                           )';
             }

             /**Checking with Subject,Level and Country ...abd*/

             elseif(!empty($searchSubject) && !empty($searchLevel)  && empty($searchProfile) && !empty($searchCountry) && empty($searchVertical)) {

                $concat='( partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersCountry.category_id="'.$searchCountry.'"
                           )';
             }

             /**Checking with Subject,Level and Vertical...abe */

             elseif(!empty($searchSubject) && !empty($searchLevel)  && empty($searchProfile) && empty($searchCountry) && !empty($searchVertical)) {

                $concat='( partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'"
                           )';
             }


             /**Checking with Subject,profile and Country... acd*/

             elseif(!empty($searchSubject) && empty($searchLevel)  && !empty($searchProfile) && !empty($searchCountry) && empty($searchVertical)) {

                $concat='( partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersProfile.category_id= "'.$searchProfile.'" AND
                           partnersCountry.category_id="'.$searchCountry.'"
                           )';
             }


             /**Checking with Subject,Profile and vertical ..ace*/

             elseif(!empty($searchSubject) && empty($searchLevel)  && !empty($searchProfile) && empty($searchCountry) && !empty($searchVertical)) {

                $concat='( partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersProfile.category_id= "'.$searchProfile.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'"
                           )';
             }

              /**Checking with Subject,Profile and vertical ..ade*/

             elseif(!empty($searchSubject) && empty($searchLevel)  && empty($searchProfile) && !empty($searchCountry) && !empty($searchVertical)) {

                $concat='( partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersCountry.category_id="'.$searchCountry.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'"
                           )';
             }


              /**Combination with Level.... */

               /**Checking with Level,Profile and Country..bcd */

             elseif(empty($searchSubject) && !empty($searchLevel)  && !empty($searchProfile) && !empty($searchCountry) && empty($searchVertical)) {

                $concat='(
                           partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersProfile.category_id= "'.$searchProfile.'" AND
                           partnersCountry.category_id="'.$searchCountry.'"
                           )';
             }


              /**Checking with Level,Profile and Vertical..bde */

             elseif(empty($searchSubject) && !empty($searchLevel)  && empty($searchProfile) && !empty($searchCountry) && !empty($searchVertical)) {

                $concat='(
                           partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersCountry.category_id="'.$searchCountry.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'"
                           )';
             }

               /**Checking with Level,Profile and Vertical...bec */

             elseif(empty($searchSubject) && !empty($searchLevel)  && !empty($searchProfile) && empty($searchCountry) && !empty($searchVertical)) {

                $concat='(
                           partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersProfile.category_id= "'.$searchProfile.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'"
                           )';
             }

              /**Checking with Profile,Country and Vertical..cde */

             elseif(empty($searchSubject) && empty($searchLevel)  && !empty($searchProfile) && !empty($searchCountry) && !empty($searchVertical)) {

                $concat='(
                           partnersProfile.category_id= "'.$searchProfile.'" AND
                           partnersCountry.category_id="'.$searchCountry.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'"
                           )';
             }

             /**Four Combinations... */

             /** Search Subject,Level,Profile and Country..abc */

             elseif(!empty($searchSubject) && !empty($searchLevel)  && !empty($searchProfile) && !empty($searchCountry) && empty($searchVertical)) {

                $concat='(
                           partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersProfile.category_id= "'.$searchProfile.'" AND
                           partnersCountry.category_id="'.$searchCountry.'"
                           )';
             }


             /** Search Subject,Level,Profile and Vertical */

             elseif(!empty($searchSubject) && !empty($searchLevel)  && !empty($searchProfile) && empty($searchCountry) && !empty($searchVertical)) {

                $concat='(
                           partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersProfile.category_id= "'.$searchProfile.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'"
                           )';
             }

             /** Search Subject,Level,Profile and Vertical..acde*/

             elseif(!empty($searchSubject) && empty($searchLevel)  && !empty($searchProfile) && !empty($searchCountry) && !empty($searchVertical)) {

                $concat='(
                           partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersProfile.category_id= "'.$searchProfile.'" AND
                           partnersCountry.category_id="'.$searchCountry.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'"
                           )';
             }


             /** Search Subject,Level,country and Vertical..abde*/

             elseif(!empty($searchSubject) && !empty($searchLevel)  && empty($searchProfile) && !empty($searchCountry) && !empty($searchVertical)) {

                $concat='(
                           partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                            partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersCountry.category_id="'.$searchCountry.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'"
                           )';
             }


              /** Search Level,Profile,country and Vertical..bcde */

             elseif(empty($searchSubject) && !empty($searchLevel)  && !empty($searchProfile) && !empty($searchCountry) && !empty($searchVertical)) {

                $concat='(
                           partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersProfile.category_id= "'.$searchProfile.'" AND
                           partnersCountry.category_id="'.$searchCountry.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'"
                           )';
             }




             /**Five Combinations..... */

              elseif(!empty($searchSubject) && !empty($searchLevel)  && !empty($searchProfile) && !empty($searchCountry) && !empty($searchVertical)) {

                $concat='(
                           partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersProfile.category_id= "'.$searchProfile.'" AND
                           partnersCountry.category_id="'.$searchCountry.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'"
                           )';
             }


            $sql='SELECT  distinct(partnersMessages.message_id),
                                                        partnersMessages.user_id,
                                                        partnersMessages.time_created,
                                                        partnersMessages.time_edited,
                                                        partnersMessages.hits,
                                                        user.username,
                                                        partnersMain.level,
                                                        partnersMain.subject,
                                                        partnersMain.content,
                                                        partnersMain.vertical,
                                                        partnersMain.address1,
                                                        partnersMain.profile,
                                                        partnersMain.address2,
                                                        partnersMain.country,
                                                         partnersMain.message_id,
                                                        partnersLevel.category_id,
                                                        partnersLevel.message_id,
                                                        partnersProfile.category_id,
                                                        partnersProfile.message_id,
                                                        partnersCountry.category_id,
                                                        partnersCountry.message_id,
                                                        partnersVertical.category_id,
                                                        partnersVertical.message_id

                                            FROM  '.DBPREFIX.'module_partners_create        AS partnersMessages,
                                            '.DBPREFIX.'access_users AS user,'.DBPREFIX.'module_partners_create_lang    AS partnersMain,
                                            '.DBPREFIX.'module_partners_message_to_level    AS partnersLevel,
                                            '.DBPREFIX.'module_partners_message_to_profile    AS partnersProfile,
                                            '.DBPREFIX.'module_partners_message_to_country    AS partnersCountry,
                                            '.DBPREFIX.'module_partners_message_to_vertical    AS partnersVertical
                                            '.$strLanguageJoin.'
                                            '.$strLanguageWhere.'
                                               WHERE    partnersMessages.user_id = user_id  AND
                                            partnersMessages.message_id = partnersMain.message_id  AND
                                            partnersMessages.message_id = partnersLevel.message_id  AND
                                            partnersMessages.message_id = partnersProfile.message_id  AND
                                            partnersMessages.message_id = partnersCountry.message_id  AND
                                            partnersMessages.message_id = partnersVertical.message_id  AND
                                            '.$concat.'ORDER BY '.$order.' ASC
                                            LIMIT   '.$intStartingIndex.','.$intLimitIndex.'';
            $objResult = $objDatabase->Execute($sql);



            $countQuery='SELECT COUNT(distinct(partnersMessages.message_id)) AS numberOfEntries
                             FROM  '.DBPREFIX.'module_partners_create        AS partnersMessages,
                                            '.DBPREFIX.'access_users AS user,'.DBPREFIX.'module_partners_create_lang    AS partnersMain,
                                            '.DBPREFIX.'module_partners_message_to_level    AS partnersLevel,
                                            '.DBPREFIX.'module_partners_message_to_profile    AS partnersProfile,
                                            '.DBPREFIX.'module_partners_message_to_country    AS partnersCountry,
                                            '.DBPREFIX.'module_partners_message_to_vertical    AS partnersVertical
                                            '.$strLanguageJoin.'
                                            '.$strLanguageWhere.'
                                               WHERE    partnersMessages.user_id = user_id  AND
                                            partnersMessages.message_id = partnersMain.message_id  AND
                                            partnersMessages.message_id = partnersLevel.message_id  AND
                                            partnersMessages.message_id = partnersProfile.message_id  AND
                                            partnersMessages.message_id = partnersCountry.message_id  AND
                                            partnersMessages.message_id = partnersVertical.message_id  AND
                                            '.$concat.'ORDER BY '.$order.' ASC';
                $objResultCount = $objDatabase->Execute($countQuery);
                $cnt=$objResultCount->fields['numberOfEntries'];
                //echo "TotalCount".$cnt;
                $this->PaginactionCount=intval($cnt);


            }
         if ($objResult->RecordCount() > 0) {

            while (!$objResult->EOF) {
             // echo $intMessageId." ";
                $intMessageId = $objResult->fields['message_id'];
                $arrReturn[$intMessageId] = array(    'user_id'            =>    $objResult->fields['user_id'],
                                                    'user_name'            =>    htmlentities(stripslashes($objResult->fields['username']),ENT_QUOTES, CONTREXX_CHARSET),
                                                    'time_created'        =>    date(ASCMS_DATE_FORMAT,$objResult->fields['time_created']),
                                                    'time_created_ts'    =>    $objResult->fields['time_created'],
                                                    'time_edited'        =>    date(ASCMS_DATE_FORMAT,$objResult->fields['time_edited']),
                                                    'time_edited_ts'    =>    $objResult->fields['time_edited'],
                                                    'hits'                =>    $objResult->fields['hits'],
                                                    'subject'            =>    '',
                                                    'is_active'         =>  '',
                                                    'status'            =>  '',
                                                    'level'                 =>    '',
                                                    'profile'            =>    '',
                                                    'country'            =>    '',
                                                    'region'            =>    '',
                                                    'vertical'            =>    '',
                                                    'contactname'        =>    '',
                                                    'email'                =>    '',
                                                    'website'            =>    '',
                                                    'address1'            =>    '',
                                                    'address2'            =>    '',
                                                    'city'                =>    '',
                                                    'zipcode'            =>    '',
                                                    'phone'                =>    '',
                                                    'fax'                =>    '',
                                                    'reference'             =>    '',
                                                    'quote'             =>  '',
                                                    'category'          =>  '',
                                                    'categories'        =>    array(),
                                                    'translation'        =>    array(),
                                                    'condition'         => 'noresult'
                                                );



                //Fill the translation-part of the return-array with default values
                foreach (array_keys($this->_arrLanguages) as $intLanguageId) {
                    $arrReturn[$intMessageId]['categories'][$intLanguageId] = array();
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['is_active']     = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['subject']     = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['content']     = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['status']         = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['level']        = '';
                       $arrReturn[$intMessageId]['translation'][$intLanguageId]['profile']     = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['country']     = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['region']         = '';
                     $arrReturn[$intMessageId]['translation'][$intLanguageId]['vertical']     = '';
                     $arrReturn[$intMessageId]['translation'][$intLanguageId]['contactname'] = '';
                     $arrReturn[$intMessageId]['translation'][$intLanguageId]['email']         = '';
                     $arrReturn[$intMessageId]['translation'][$intLanguageId]['website']     = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['address1']    = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['address2']     = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['city']         = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['zipcode']     = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['phone']         = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['fax']         = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['reference']   = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['quote']         = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['category']    = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['image']         = '';
                }

                //Get assigned categories for this entry
                $objCategoryResult = $objDatabase->Execute('SELECT    category_id,
                                                                    lang_id
                                                            FROM    '.DBPREFIX.'module_partners_message_to_category
                                                            WHERE    message_id='.$intMessageId.'
                                                        ');
                while (!$objCategoryResult->EOF) {
                    $arrReturn[$intMessageId]['categories'][$objCategoryResult->fields['lang_id']][$objCategoryResult->fields['category_id']] = true;

                    $objCategoryResult->MoveNext();
                }

                //Get existing translations for the current entry

                 $selPartnersDetails='SELECT    lang_id,
                                                                    is_active,
                                                                    status,
                                                                    subject,
                                                                    level,
                                                                    profile,
                                                                    country,
                                                                    region,
                                                                    vertical,
                                                                    contactname,
                                                                    email,
                                                                    website,
                                                                    address1,
                                                                    address2,
                                                                    city,
                                                                    zipcode,
                                                                    phone,
                                                                    fax,
                                                                    reference,
                                                                    quote,
                                                                    content,
                                                                    image
                                                            FROM    '.DBPREFIX.'module_partners_create_lang
                                                            WHERE    message_id='.$intMessageId.'';

                $objResult = $objDatabase->Execute($selPartnersDetails);

                    while (!$objResult->EOF) {

                    $intLanguageId = $objResult->fields['lang_id'];


                    if ( ($intLanguageId == $this->_intLanguageId && !empty($objResult->fields['subject'])) ||
                         empty($arrReturn[$intMessageId]['subject']) )
                    {
                        $arrReturn[$intMessageId]['subject'] = htmlentities(stripslashes($objResult->fields['subject']), ENT_QUOTES, CONTREXX_CHARSET);
                    }

                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['is_active']          = $objResult->fields['is_active'];
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['subject']         = htmlentities(stripslashes($objResult->fields['subject']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['content']         = $objResult->fields['content'];
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['status']          = $objResult->fields['status'];
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['level']             = htmlentities(stripslashes($objResult->fields['level']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['profile']         = htmlentities(stripslashes($objResult->fields['profile']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['country']         = htmlentities(stripslashes($objResult->fields['country']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['region']           = htmlentities(stripslashes($objResult->fields['region']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['vertical']         = htmlentities(stripslashes($objResult->fields['vertical']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['category']         = htmlentities(stripslashes($objResult->fields['name']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['contactname']     = htmlentities(stripslashes($objResult->fields['contactname']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['email']             = htmlentities(stripslashes($objResult->fields['email']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['website']         = htmlentities(stripslashes($objResult->fields['website']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['address1']         = htmlentities(stripslashes($objResult->fields['address1']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['address2']         = htmlentities(stripslashes($objResult->fields['address2']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['city']             = htmlentities(stripslashes($objResult->fields['city']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['zipcode']         = htmlentities(stripslashes($objResult->fields['zipcode']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['phone']             = htmlentities(stripslashes($objResult->fields['phone']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['fax']             = htmlentities(stripslashes($objResult->fields['fax']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['reference']         = htmlentities(stripslashes($objResult->fields['reference']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['quote']             = htmlentities(stripslashes($objResult->fields['quote']), ENT_QUOTES, CONTREXX_CHARSET);
                       $arrReturn[$intMessageId]['translation'][$intLanguageId]['image']             = htmlentities(stripslashes($objResult->fields['image']), ENT_QUOTES, CONTREXX_CHARSET);

                    $objResult->MoveNext();
                }

                 $arrReturn[$intMessageId]['translation'][$intLanguageId]['count'] +=    $objResult->RecordCount();
                 $objResult->MoveNext();

            }

        }


        return $arrReturn;
    }







    /**
      */



      function createEntryArrayFrontEnd($intLanguageId=0, $intStartingIndex=0, $intLimitIndex=0) {
        global $objDatabase,$_ARRAYLANG;

        if(!empty($_REQUEST['subject'])){
           $searchSubject = contrexx_addslashes(strip_tags($_REQUEST['subject']));
           $resultContent .= $_ARRAYLANG['TXT_PARTNERS_SEARCH_RESULTS'];
           $srchActive = true;
          }

          if($_REQUEST['level'] != 0) {
            $searchLevel = htmlentities($_REQUEST['level'],ENT_QUOTES,CONTREXX_CHARSET);
            $srchActive = true;
          }

          if($_REQUEST['profile'] != 0) {
            $searchProfile = htmlentities($_REQUEST['profile'],ENT_QUOTES,CONTREXX_CHARSET);
            $srchActive = true;
          }

          if($_REQUEST['country'] != 0) {
            $searchCountry = htmlentities($_REQUEST['country'],ENT_QUOTES,CONTREXX_CHARSET);
            $srchActive = true;
          }

          if($_REQUEST['vertical'] != 0) {
           $searchVertical = htmlentities($_REQUEST['vertical'],ENT_QUOTES,CONTREXX_CHARSET);
           $srchActive = true;
           }


          if(!empty($resultContent))
          {
          $this->_strOkMessage = $resultContent;
          }
         $arrReturn = array();

        if (intval($intLanguageId) > 0) {

            $strLanguageJoin  = '     INNER JOIN    '.DBPREFIX.'module_partners_create_lang    AS partnerscreateLanguage
                                    ON            partnerscreate.message_id = partnerscreateLanguage.message_id
                                ';
            $strLanguageWhere = '    WHERE     partnerscreateLanguage.lang_id='.$intLanguageId.' AND
                                            partnerscreateLanguage.is_active="1"
                                ';


         } else {

            $strLanguageJoin  = '';
            $strLanguageWhere = '';
         }

         if ($intLimitIndex == 0) {
            $intLimitIndex = $this->countEntries();
         }
         $objSettings = $objDatabase->Execute('SELECT sortorder FROM '.DBPREFIX.'module_partners_settings LIMIT 1');
          $order = trim(strtolower($objSettings->fields['sortorder']));

          $this->orderofResult=$order;

          /**Order by Level when the Admin chosen the Order property of Level...
            *Otherwise it selects the Order by Subject from the Table.
            *Order by Level has been done by using the Sort_id of the Level which was set by Admin..
            */

          if($order == "level"){

            $order = "ORDER BY partnersUserLevel.sort_id ASC ";
          }

          else  {

            $order  = "ORDER BY partnersMain.subject ASC ";
          }

         //echo "search Archive".$srchActive;
         if($srchActive != true){

          //  echo "coming inside";
            if($_REQUEST["test"]!=""){
             $Limit = "";
            }
            if($_REQUEST["test"]==""){
              $Limit =  "LIMIT " .$intStartingIndex.','.$intLimitIndex;
            }
             //    print $order;
            $selOverview='SELECT DISTINCT(partnersMessages.message_id),
                                                partnersMessages.user_id,
                                                partnersMessages.time_created,
                                                partnersMessages.time_edited,
                                                partnersMessages.hits,
                                                partnersLevel.category_id,
                                                partnersUserLevel.level,
                                                user.username
                                                FROM contrexx_module_partners_create AS partnersMessages,
                                                '.DBPREFIX.'access_users AS user,
                                                '.DBPREFIX.'module_partners_create_lang AS partnersMain,
                                                '.DBPREFIX.'module_partners_message_to_level  AS partnersLevel,
                                                '.DBPREFIX.'module_partners_display  AS partnersDisplay,
                                                '.DBPREFIX.'module_partners_user_level  AS partnersUserLevel

                                                WHERE partnersMessages.user_id = user.id AND
                                                partnersMain.message_id =partnersMessages.message_id AND
                                                partnersLevel.message_id=partnersMain.message_id  AND
                                                partnersDisplay.display_level_id=partnersLevel.category_id AND
                                                partnersUserLevel.id=partnersLevel.category_id AND
                                                partnersMain.status!=0 AND
                                                partnersLevel.category_id !=0
                                                '.$order.$Limit.'';


                  $objDatabase->Execute($selOverview);

                  /**Getting the Total number of Level id is Appearing at the Front End
                    *Total Count has been calculated by using the PHP..
                    *Count has been supplied over by the Query...
                    */
                  $countQuery='SELECT COUNT(distinct(partnersMessages.message_id)) AS numberOfEntries

                                                FROM contrexx_module_partners_create AS partnersMessages,
                                                '.DBPREFIX.'access_users AS user,
                                                '.DBPREFIX.'module_partners_create_lang AS partnersMain,
                                                '.DBPREFIX.'module_partners_message_to_level  AS partnersLevel,
                                                '.DBPREFIX.'module_partners_display  AS partnersDisplay,
                                                '.DBPREFIX.'module_partners_user_level  AS partnersUserLevel

                                                WHERE partnersMessages.user_id = user.id AND
                                                partnersMain.message_id =partnersMessages.message_id AND
                                                partnersLevel.message_id=partnersMain.message_id  AND
                                                partnersDisplay.display_level_id=partnersLevel.category_id AND
                                                partnersUserLevel.id=partnersLevel.category_id AND
                                                partnersMain.status!=0 AND
                                                partnersLevel.category_id !=0
                                                GROUP BY partnersLevel.category_id '.$order.'';
                $objResultCount = $objDatabase->Execute($countQuery);

                $cnt="";
                while(!$objResultCount->EOF)  {

                    $cnt+=$objResultCount->fields['numberOfEntries'];
                    $objResultCount->MoveNext();
                }
               //echo "number of enteries".$cnt."<br><br>";
               $this->PaginactionCount=$cnt;

            //$this->PaginactionCount=intval($objResultCount->RecordCount()/2);
        }                                     //LIMIT '.$intStartingIndex.','.$intLimitIndex.'
        else  {



            /** Single Combinations... */

            /**Checking with the Subject..*/

             if(!empty($searchSubject) && empty($searchLevel) && empty($searchProfile) && empty($searchCountry) && empty($searchVertical) ) {

                $concat='( partnersMain.subject like "'.'%'.$searchSubject.'%'.'")';
             }

             /**Checking with Search Level.. */

             elseif(empty($searchSubject) && !empty($searchLevel)  && empty($searchProfile) && empty($searchCountry) && empty($searchVertical)) {

                $concat='(partnersLevel.category_id = "'.$searchLevel.'")';

             }

             /**Checking with Search Profile.. */

             elseif(empty($searchSubject)  && empty($searchLevel) && !empty($searchProfile) && empty($searchCountry) && empty($searchVertical)) {

                $concat='(partnersProfile.category_id= "'.$searchProfile.'")';

             }

             /**Checking with Search Country.. */

             elseif(empty($searchSubject) && empty($searchLevel) && empty($searchProfile) && !empty($searchCountry) && empty($searchVertical)) {

                $concat='(partnersCountry.category_id="'.$searchCountry.'")';

             }

             /**Checking with Search Vertical.. */

             elseif(empty($searchSubject) && empty($searchLevel) && empty($searchProfile) && empty($searchCountry) && !empty($searchVertical)) {

                $concat='(partnersVertical.category_id ="'.$searchVertical.'")';

             }


             /** Subject two combinations.....*/

             /** Checking Search Subject and Search Level... */

             elseif(!empty($searchSubject) && !empty($searchLevel) && empty($searchProfile) && empty($searchCountry) && empty($searchVertical)) {

                $concat='( partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersLevel.category_id = "'.$searchLevel.'")';
             }
             /** Checking Search Subject and Search Profile... */

             elseif(!empty($searchSubject) && empty($searchLevel) && !empty($searchProfile) && empty($searchCountry) && empty($searchVertical)) {

                $concat='( partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersProfile.category_id= "'.$searchProfile.'")';
             }

              /** Checking Search Subject and Search Country... */

             elseif(!empty($searchSubject) && empty($searchLevel) && empty($searchProfile) && !empty($searchCountry) && empty($searchVertical)) {

                $concat='( partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersCountry.category_id="'.$searchCountry.'")';
             }


             /** Checking Search Subject and Search Vertical... */

             elseif(!empty($searchSubject) && empty($searchLevel) && empty($searchProfile) && empty($searchCountry) && !empty($searchVertical)) {

                $concat='( partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'")';
             }


             /**Level Combinations */

             /**Checking with Level and Profile.. */

             elseif(empty($searchSubject) && !empty($searchLevel) && !empty($searchProfile) && empty($searchCountry) && empty($searchVertical)) {

                $concat='( partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersProfile.category_id= "'.$searchProfile.'")';
             }

             /**Checking with Level and Country.. */

             elseif(empty($searchSubject) && !empty($searchLevel) && empty($searchProfile) && !empty($searchCountry) && empty($searchVertical)) {

                $concat='( partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersCountry.category_id="'.$searchCountry.'")';
             }

             /**Checking with Level and Vetical.. */

             elseif(empty($searchSubject) && !empty($searchLevel) && empty($searchProfile) && empty($searchCountry) && !empty($searchVertical)) {

                $concat='( partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'")';
             }



             /**Profile two combinations..... */

             /** Checking with the Search Profile and Search Country.... */

             elseif(empty($searchSubject) && empty($searchLevel) && !empty($searchProfile) && !empty($searchCountry) && empty($searchVertical)) {

                $concat='( partnersProfile.category_id= "'.$searchProfile.'" AND
                           partnersCountry.category_id="'.$searchCountry.'")';
             }

             /**Checking with the searchProfile and Search vetical.. */

             elseif(empty($searchSubject) && empty($searchLevel)  && !empty($searchProfile) && empty($searchCountry) && !empty($searchVertical)) {

                $concat='( partnersProfile.category_id= "'.$searchProfile.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'")';
             }




             /**Country Combinations two */


             /** Checking with Search Country and Search Vetical... */

             elseif(empty($searchSubject) && empty($searchLevel)  && empty($searchProfile) && !empty($searchCountry) && !empty($searchVertical)) {

                $concat='(  partnersCountry.category_id="'.$searchCountry.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'"
                           )';
             }




             /**Three combinations.... */


              /** Checking with Subject,Level,Profile... abc*/

             elseif(!empty($searchSubject) && !empty($searchLevel)  && !empty($searchProfile) && empty($searchCountry) && empty($searchVertical)) {

                $concat='( partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersProfile.category_id= "'.$searchProfile.'"
                           )';
             }

             /**Checking with Subject,Level and Country ...abd*/

             elseif(!empty($searchSubject) && !empty($searchLevel)  && empty($searchProfile) && !empty($searchCountry) && empty($searchVertical)) {

                $concat='( partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersCountry.category_id="'.$searchCountry.'"
                           )';
             }

             /**Checking with Subject,Level and Vertical...abe */

             elseif(!empty($searchSubject) && !empty($searchLevel)  && empty($searchProfile) && empty($searchCountry) && !empty($searchVertical)) {

                $concat='( partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'"
                           )';
             }


             /**Checking with Subject,profile and Country... acd*/

             elseif(!empty($searchSubject) && empty($searchLevel)  && !empty($searchProfile) && !empty($searchCountry) && empty($searchVertical)) {

                $concat='( partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersProfile.category_id= "'.$searchProfile.'" AND
                           partnersCountry.category_id="'.$searchCountry.'"
                           )';
             }


             /**Checking with Subject,Profile and vertical ..ace*/

             elseif(!empty($searchSubject) && empty($searchLevel)  && !empty($searchProfile) && empty($searchCountry) && !empty($searchVertical)) {

                $concat='( partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersProfile.category_id= "'.$searchProfile.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'"
                           )';
             }

              /**Checking with Subject,Profile and vertical ..ade*/

             elseif(!empty($searchSubject) && empty($searchLevel)  && empty($searchProfile) && !empty($searchCountry) && !empty($searchVertical)) {

                $concat='( partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersCountry.category_id="'.$searchCountry.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'"
                           )';
             }


              /**Combination with Level.... */

               /**Checking with Level,Profile and Country..bcd */

             elseif(empty($searchSubject) && !empty($searchLevel)  && !empty($searchProfile) && !empty($searchCountry) && empty($searchVertical)) {

                $concat='(
                           partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersProfile.category_id= "'.$searchProfile.'" AND
                           partnersCountry.category_id="'.$searchCountry.'"
                           )';
             }


              /**Checking with Level,Profile and Vertical..bde */

             elseif(empty($searchSubject) && !empty($searchLevel)  && empty($searchProfile) && !empty($searchCountry) && !empty($searchVertical)) {

                $concat='(
                           partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersCountry.category_id="'.$searchCountry.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'"
                           )';
             }

               /**Checking with Level,Profile and Vertical...bec */

             elseif(empty($searchSubject) && !empty($searchLevel)  && !empty($searchProfile) && empty($searchCountry) && !empty($searchVertical)) {

                $concat='(
                           partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersProfile.category_id= "'.$searchProfile.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'"
                           )';
             }

              /**Checking with Profile,Country and Vertical..cde */

             elseif(empty($searchSubject) && empty($searchLevel)  && !empty($searchProfile) && !empty($searchCountry) && !empty($searchVertical)) {

                $concat='(
                           partnersProfile.category_id= "'.$searchProfile.'" AND
                           partnersCountry.category_id="'.$searchCountry.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'"
                           )';
             }

             /**Four Combinations... */

             /** Search Subject,Level,Profile and Country..abc */

             elseif(!empty($searchSubject) && !empty($searchLevel)  && !empty($searchProfile) && !empty($searchCountry) && empty($searchVertical)) {

                $concat='(
                           partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersProfile.category_id= "'.$searchProfile.'" AND
                           partnersCountry.category_id="'.$searchCountry.'"
                           )';
             }


             /** Search Subject,Level,Profile and Vertical */

             elseif(!empty($searchSubject) && !empty($searchLevel)  && !empty($searchProfile) && empty($searchCountry) && !empty($searchVertical)) {

                $concat='(
                           partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersProfile.category_id= "'.$searchProfile.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'"
                           )';
             }

             /** Search Subject,Level,Profile and Vertical..acde*/

             elseif(!empty($searchSubject) && empty($searchLevel)  && !empty($searchProfile) && !empty($searchCountry) && !empty($searchVertical)) {

                $concat='(
                           partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersProfile.category_id= "'.$searchProfile.'" AND
                           partnersCountry.category_id="'.$searchCountry.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'"
                           )';
             }


             /** Search Subject,Level,country and Vertical..abde*/

             elseif(!empty($searchSubject) && !empty($searchLevel)  && empty($searchProfile) && !empty($searchCountry) && !empty($searchVertical)) {

                $concat='(
                           partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                            partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersCountry.category_id="'.$searchCountry.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'"
                           )';
             }


              /** Search Level,Profile,country and Vertical..bcde */

             elseif(empty($searchSubject) && !empty($searchLevel)  && !empty($searchProfile) && !empty($searchCountry) && !empty($searchVertical)) {

                $concat='(
                           partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersProfile.category_id= "'.$searchProfile.'" AND
                           partnersCountry.category_id="'.$searchCountry.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'"
                           )';
             }




             /**Five Combinations..... */

              elseif(!empty($searchSubject) && !empty($searchLevel)  && !empty($searchProfile) && !empty($searchCountry) && !empty($searchVertical)) {

                $concat='(
                           partnersMain.subject like "'.'%'.$searchSubject.'%'.'" AND
                           partnersLevel.category_id = "'.$searchLevel.'" AND
                           partnersProfile.category_id= "'.$searchProfile.'" AND
                           partnersCountry.category_id="'.$searchCountry.'" AND
                           partnersVertical.category_id like "'.$searchVertical.'"
                           )';
             }




           $sql = 'SELECT DISTINCT(partnersMessages.message_id),
                                                partnersMessages.user_id,
                                                partnersMessages.time_created,
                                                partnersMessages.time_edited,
                                                partnersMessages.hits,
                                                partnersLevel.category_id,
                                                partnersUserLevel.level,
                                                user.username

                                                FROM contrexx_module_partners_create AS partnersMessages,
                                                '.DBPREFIX.'access_users AS user,
                                                '.DBPREFIX.'module_partners_create_lang AS partnersMain,
                                                '.DBPREFIX.'module_partners_message_to_level  AS partnersLevel,
                                                '.DBPREFIX.'module_partners_message_to_profile  AS partnersProfile,
                                                '.DBPREFIX.'module_partners_message_to_country  AS partnersCountry,
                                                '.DBPREFIX.'module_partners_message_to_vertical  AS partnersVertical,
                                                '.DBPREFIX.'module_partners_display  AS partnersDisplay,
                                                '.DBPREFIX.'module_partners_user_level  AS partnersUserLevel

                                                WHERE partnersMessages.user_id = user.id AND
                                                partnersMain.message_id =partnersMessages.message_id AND
                                                partnersLevel.message_id=partnersMain.message_id AND
                                                partnersMessages.message_id = partnersProfile.message_id  AND
                                                partnersMessages.message_id = partnersCountry.message_id  AND
                                                partnersMessages.message_id = partnersVertical.message_id  AND

                                                partnersUserLevel.id=partnersLevel.category_id AND
                                                partnersMain.status!=0 AND
                                                partnersLevel.category_id !=0 AND
                                                '.$concat.$order.'
                                                LIMIT   '.$intStartingIndex.','.$intLimitIndex.'';
            $objResult = $objDatabase->Execute($sql);

            $countQuery='SELECT COUNT(distinct(partnersMessages.message_id)) AS numberOfEntries
                                         FROM  '.DBPREFIX.'module_partners_create        AS partnersMessages,
                                            '.DBPREFIX.'access_users AS user,'.DBPREFIX.'module_partners_create_lang    AS partnersMain,
                                            '.DBPREFIX.'module_partners_message_to_level    AS partnersLevel,
                                            '.DBPREFIX.'module_partners_message_to_profile    AS partnersProfile,
                                            '.DBPREFIX.'module_partners_message_to_country    AS partnersCountry,
                                            '.DBPREFIX.'module_partners_message_to_vertical    AS partnersVertical,
                                            '.DBPREFIX.'module_partners_user_level  AS partnersUserLevel
                                            '.$strLanguageJoin.'
                                            '.$strLanguageWhere.'
                                           WHERE partnersMessages.user_id = user.id AND
                                                partnersMain.message_id =partnersMessages.message_id AND
                                                partnersLevel.message_id=partnersMain.message_id AND
                                                partnersMessages.message_id = partnersProfile.message_id  AND
                                                partnersMessages.message_id = partnersCountry.message_id  AND
                                                partnersMessages.message_id = partnersVertical.message_id  AND
                                                partnersUserLevel.id=partnersLevel.category_id AND
                                                partnersMain.status!=0 AND
                                                partnersLevel.category_id !=0 AND
                                                '.$concat.'
                                                GROUP BY partnersLevel.category_id '.$order.'';
                $objResultCount = $objDatabase->Execute($countQuery);

                $cnt="";
                while(!$objResultCount->EOF)  {

                    $cnt+=$objResultCount->fields['numberOfEntries'];
                    $objResultCount->MoveNext();
                }

                //echo "TotalCount".$cnt;
                $this->PaginactionCount=intval($cnt);


            }
   if ($objResult->RecordCount() > 0) {
            while (!$objResult->EOF) {
             // echo $intMessageId." ";

                $intMessageId = $objResult->fields['message_id'];
                $LevelCategoryID=$objResult->fields['category_id'];

                $this->orderedResults[]=$LevelCategoryID;

                //echo "MessageId".$intMessageId."Level".$levelMessageId."<br>";
                //$arr[]=array($intMessageId,$levelMessageId);
                // $r++;


                $arrReturn[$intMessageId][$LevelCategoryID] = array(    'user_id'            =>    $objResult->fields['user_id'],
                                                    'user_name'            =>    htmlentities(stripslashes($objResult->fields['username']),ENT_QUOTES, CONTREXX_CHARSET),
                                                    'time_created'        =>    date(ASCMS_DATE_FORMAT,$objResult->fields['time_created']),
                                                    'time_created_ts'    =>    $objResult->fields['time_created'],
                                                    'time_edited'        =>    date(ASCMS_DATE_FORMAT,$objResult->fields['time_edited']),
                                                    'time_edited_ts'    =>    $objResult->fields['time_edited'],
                                                    'hits'                =>    $objResult->fields['hits'],
                                                    'subject'            =>    '',
                                                    'is_active'         =>  '',
                                                    'status'            =>  '',
                                                    'level'                 =>    '',
                                                    'profile'            =>    '',
                                                    'country'            =>    '',
                                                    'region'            =>    '',
                                                    'vertical'            =>    '',
                                                    'contactname'        =>    '',
                                                    'email'                =>    '',
                                                    'website'            =>    '',
                                                    'address1'            =>    '',
                                                    'address2'            =>    '',
                                                    'city'                =>    '',
                                                    'zipcode'            =>    '',
                                                    'phone'                =>    '',
                                                    'fax'                =>    '',
                                                    'reference'             =>    '',
                                                    'quote'             =>  '',
                                                    'category'          =>  '',
                                                    'categories'        =>    array(),
                                                    'translation'        =>    array(),
                                                    'condition'         => 'noresult'
                                                );



                //Fill the translation-part of the return-array with default values
                foreach (array_keys($this->_arrLanguages) as $intLanguageId) {
                    $arrReturn[$intMessageId][$LevelCategoryID]['categories'][$intLanguageId] = array();
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['is_active']     = '';
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['subject']     = '';
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['content']     = '';
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['status']         = '';
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['level']        = '';
                       $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['profile']     = '';
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['country']     = '';
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['region']         = '';
                     $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['vertical']     = '';
                     $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['contactname'] = '';
                     $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['email']         = '';
                     $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['website']     = '';
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['address1']    = '';
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['address2']     = '';
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['city']         = '';
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['zipcode']     = '';
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['phone']         = '';
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['fax']         = '';
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['reference']   = '';
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['quote']         = '';
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['category']    = '';
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['image']         = '';
                }

                //Get assigned categories for this entry
                $objCategoryResult = $objDatabase->Execute('SELECT    category_id,
                                                                    lang_id
                                                            FROM    '.DBPREFIX.'module_partners_message_to_category
                                                            WHERE    message_id='.$intMessageId.'
                                                        ');
                while (!$objCategoryResult->EOF) {
                    $arrReturn[$intMessageId][$LevelCategoryID]['categories'][$objCategoryResult->fields['lang_id']][$objCategoryResult->fields['category_id']] = true;

                    $objCategoryResult->MoveNext();
                }

                //Get existing translations for the current entry

                 $selPartnersDetails='SELECT    lang_id,
                                                                    is_active,
                                                                    status,
                                                                    subject,
                                                                    level,
                                                                    profile,
                                                                    country,
                                                                    region,
                                                                    vertical,
                                                                    contactname,
                                                                    email,
                                                                    website,
                                                                    address1,
                                                                    address2,
                                                                    city,
                                                                    zipcode,
                                                                    phone,
                                                                    fax,
                                                                    reference,
                                                                    quote,
                                                                    content,
                                                                    image
                                                            FROM    '.DBPREFIX.'module_partners_create_lang
                                                            WHERE    message_id='.$intMessageId.'';

                $objResult = $objDatabase->Execute($selPartnersDetails);

                    while (!$objResult->EOF) {

                    $intLanguageId = $objResult->fields['lang_id'];


                    if ( ($intLanguageId == $this->_intLanguageId && !empty($objResult->fields['subject'])) ||
                         empty($arrReturn[$intMessageId]['subject']) )
                    {
                        $arrReturn[$intMessageId][$LevelCategoryID]['subject'] = htmlentities(stripslashes($objResult->fields['subject']), ENT_QUOTES, CONTREXX_CHARSET);
                    }
                    //echo "MessageID".$intMessageId."<br>";
                    //echo "LevelID".$LevelCategoryID."<br>";
                    //echo "Subject".$arrReturn[$intMessageId][$LevelCategoryID]['subject']."<br>";

                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['is_active']          = $objResult->fields['is_active'];
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['subject']         = htmlentities(stripslashes($objResult->fields['subject']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['content']         = $objResult->fields['content'];
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['status']          = $objResult->fields['status'];
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['level']             = htmlentities(stripslashes($objResult->fields['level']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['profile']         = htmlentities(stripslashes($objResult->fields['profile']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['country']         = htmlentities(stripslashes($objResult->fields['country']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['region']           = htmlentities(stripslashes($objResult->fields['region']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['vertical']         = htmlentities(stripslashes($objResult->fields['vertical']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['category']         = htmlentities(stripslashes($objResult->fields['name']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['contactname']     = htmlentities(stripslashes($objResult->fields['contactname']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['email']             = htmlentities(stripslashes($objResult->fields['email']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['website']         = htmlentities(stripslashes($objResult->fields['website']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['address1']         = htmlentities(stripslashes($objResult->fields['address1']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['address2']         = htmlentities(stripslashes($objResult->fields['address2']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['city']             = htmlentities(stripslashes($objResult->fields['city']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['zipcode']         = htmlentities(stripslashes($objResult->fields['zipcode']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['phone']             = htmlentities(stripslashes($objResult->fields['phone']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['fax']             = htmlentities(stripslashes($objResult->fields['fax']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['reference']         = htmlentities(stripslashes($objResult->fields['reference']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['quote']             = htmlentities(stripslashes($objResult->fields['quote']), ENT_QUOTES, CONTREXX_CHARSET);
                       $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['image']             = htmlentities(stripslashes($objResult->fields['image']), ENT_QUOTES, CONTREXX_CHARSET);

                    $objResult->MoveNext();
                }

                 $arrReturn[$intMessageId]['translation'][$intLanguageId]['count'] +=    $objResult->RecordCount();



                // $arrReturn["orderdLevel"][]=$LevelCategoryID;
                 $selLevelName='SELECT level,lang_id,imgpath FROM '.DBPREFIX.'module_partners_user_level
                                WHERE id='.$LevelCategoryID.'';
                 $objResultLevel=$objDatabase->Execute($selLevelName);

                 while (!$objResultLevel->EOF) {

                    $intLanguageId=$objResultLevel->fields['lang_id'];
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['level']             = htmlentities(stripslashes($objResultLevel->fields['level']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId][$LevelCategoryID]['translation'][$intLanguageId]['level_image']         = htmlentities(stripslashes($objResultLevel->fields['imgpath']), ENT_QUOTES, CONTREXX_CHARSET);


                    $objResultLevel->MoveNext();
                 }
               $objResult->MoveNext();
            }

        }
      // print "<pre>";
       // print_r($this->orderedResults);
        //print "</pre>";
       //echo "count".count($arrReturn);
        //array_multisort($newarrReturn[1], SORT_ASC);
    // print "<pre>";
     //print_r($arrReturn);
     //print "</pre>";


        return $arrReturn;
    }




    function getLevelId($id){
        global $objDatabase;
        $searchQuery='SELECT category_id FROM '.DBPREFIX.'module_partners_message_to_level WHERE message_id='.$id.'';
        $objResult=$objDatabase->Execute($searchQuery);
        if($objResult!=false){

            //while(!$objResult->EOF){
                $displayLevel= $objResult->fields['category_id'];
           // }
        }
        return $displayLevel;
    }






    /**
     * Create an array containing all settings of the partners-module.
     * Example: $arrSettings[$strSettingName] for the content of $strSettingsName
     *
     * @global  object      $objDatabase
     * @return  array       $arrReturn
     */
    function createSettingsArray() {
        global $objDatabase;

        $arrReturn = array();

        $objResult = $objDatabase->Execute('SELECT  name,
                                                    value
                                            FROM    '.DBPREFIX.'module_partners_settings
                                        ');
        if($objResult !== false){
            while (!$objResult->EOF) {
                $arrReturn[$objResult->fields['name']] = stripslashes(htmlspecialchars($objResult->fields['value'], ENT_QUOTES, CONTREXX_CHARSET));
                $objResult->MoveNext();
            }
        }
        return $arrReturn;
    }


    /**
     * Returns the allowed maximum element per page. Can be used for paging.
     *
     * @global     array        $_CONFIG
     * @return     integer        allowed maximum of elements per page.
     */
    function getPagingLimit() {
        global $_CONFIG;

                return intval($_CONFIG['corePagingLimit']);
              // return "2";

    }

    /**
     * Counts all existing entries in the database.
     *
     * @global     object        $objDatabase
     * @return     integer        number of entries in the database
     */
     function countEntries() {
        global $objDatabase;

        $objEntryResult = $objDatabase->Execute('    SELECT    COUNT(message_id) AS numberOfEntries
                                                    FROM    '.DBPREFIX.'module_partners_create
                                            ');

        return intval($objEntryResult->fields['numberOfEntries']);
    }

    /**
     * Creates an array containing all frontend-languages.
     *
     * Contents:
     * $arrValue[$langId]['short']      =>  For Example: en, de, fr, ...
     * $arrValue[$langId]['long']       =>  For Example: 'English', 'Deutsch', 'French', ...
     *
     * @global  object      $objDatabase
     * @return  array       $arrReturn
     */
    function createLanguageArray() {
        global $objDatabase;

        $arrReturn = array();

        $objResult = $objDatabase->Execute('SELECT      id,
                                                        lang,
                                                        name
                                            FROM        '.DBPREFIX.'languages
                                            WHERE       frontend=1
                                            ORDER BY    id
                                        ');
        while (!$objResult->EOF) {
            $arrReturn[$objResult->fields['id']] = array(   'short' =>  stripslashes($objResult->fields['lang']),
                                                            'long'  =>  htmlentities(stripslashes($objResult->fields['name']),ENT_QUOTES, CONTREXX_CHARSET)
                                                        );
            $objResult->MoveNext();
        }

        return $arrReturn;
    }



        /**
     * Counts all existing categories in the database.
     *
     * @global     object        $objDatabase
     * @return     integer        number of categories in the database
     */
    function countCategories() {
        global $objDatabase;

        $objCategoryResult = $objDatabase->Execute('SELECT    COUNT(DISTINCT category_id) AS numberOfCategories
                                                    FROM    '.DBPREFIX.'module_partners_categories
                                            ');

        return intval($objCategoryResult->fields['numberOfCategories']);
    }


    function createCategoryArray($intStartingIndex=0, $intLimitIndex=0, $category,$intCategoryId,$cat_id) {
        global $objDatabase,$_ARRAYLANG;

        $arrReturn = array();


    if ($intLimitIndex == 0) {
            $intLimitIndex = $this->countCategories();

        }

        if($category == "level")
        {
         $this->_objTpl->setVariable(array(
                                          'PARTNERS_CAT_NAME'     => "level",
                                          'PARTNERS_CAT_NAME_DEL' => "level",
                                          'TXT_OVERVIEW_TITLE'    => $_ARRAYLANG['PARTNERS_CATNAME_MANAGE'].$this->_getCategoryname('2'),
                                          'TXT_ADD_TITLE'         => $_ARRAYLANG['PARTNERS_CATNAME_ADD'].$this->_getCategoryname('2'),
                                          'TXT_RENAME_TITLE'      => $_ARRAYLANG['PARTNERS_CATNAME_RENAME'].$this->_getCategoryname('2'),
                                          'PARTNERS_LEVEL_IMG'    => $_ARRAYLANG['PARTNERS_LEVEL_IMAGE'],
                                          'TXT_ADD_DISPLAY'       => $_ARRAYLANG['TXT_ADD_DISPLAY'],
                                          'TXT_ADD_ALL'           => $_ARRAYLANG['TXT_ADD_ALL'],
                                          'TXT_ADD_DALL'          => $_ARRAYLANG['TXT_ADD_DALL'],
                                          'TXT_ADD_TITLES'        => $_ARRAYLANG['TXT_ADD_TITLES'],
                                          'TXT_ADD_CONTACT'       => $_ARRAYLANG['TXT_ADD_CONTACT'],
                                          'TXT_ADD_CONTENT'       => $_ARRAYLANG['TXT_ADD_CONTENT'],
                                          'TXT_ADD_PHONE'         => $_ARRAYLANG['TXT_ADD_PHONE'],
                                          'TXT_ADD_COUNTRY'       => $_ARRAYLANG['TXT_ADD_COUNTRY'],
                                          'TXT_ADD_ADDRESS2'      => $_ARRAYLANG['TXT_ADD_ADDRESS2'],
                                          'TXT_ADD_ADDRESS1'      => $_ARRAYLANG['TXT_ADD_ADDRESS1'],
                                          'TXT_ADD_ZIPCODE'       => $_ARRAYLANG['TXT_ADD_ZIPCODE'],
                                          'TXT_ADD_CITY'          => $_ARRAYLANG['TXT_ADD_CITY'],
                                          'TXT_ADD_LOGO'          => $_ARRAYLANG['TXT_ADD_LOGO'],
                                          'TXT_ADD_LEVEL'         => $_ARRAYLANG['TXT_ADD_LEVEL'],
                                          'TXT_ADD_LLOGO'         => $_ARRAYLANG['TXT_ADD_LLOGO'],
                                          'TXT_ADD_CLOGO'         => $_ARRAYLANG['TXT_ADD_CLOGO'],
                                          'TXT_ADD_QUOTE'         => $_ARRAYLANG['TXT_ADD_QUOTE'],
                                          'TXT_ADD_LANGUAGES'     => $_ARRAYLANG['TXT_ADD_LANGUAGES']
                                          ));

           if($intCategoryId!="")
           {
                     $objResult = $objDatabase->Execute('SELECT    DISTINCT id
                                            FROM    '.DBPREFIX.'module_partners_user_level
                                             WHERE  `id` = '.$intCategoryId.'
                                             ORDER BY `sort_id` ASC
                                            LIMIT  1
                                        ');
                                        }
                                        else
                                        {
            $objResult = $objDatabase->Execute('SELECT    DISTINCT id
                                            FROM    '.DBPREFIX.'module_partners_user_level
                                            ORDER BY `sort_id` ASC
                                        LIMIT     '.$intStartingIndex.','.$intLimitIndex.'
                                        ');

                                        }

        //Initialize Array
        if ($objResult->RecordCount() > 0) {
            while (!$objResult->EOF) {
                foreach(array_keys($this->_arrLanguages) as $intLangId) {
                                    //
                    $arrReturn[intval($objResult->fields['id'])][$intLangId] = array(    'name'        =>    '',
                                                                                        'sort_id'        =>    '',
                                                                                        'level'        =>    '',
                                                                                        'is_active'    =>    ''
                                                                                            );
                }
                $objResult->MoveNext();
            }
        }

        //Fill array if possible
        foreach ($arrReturn as $intCategoryId => $arrLanguages) {
            foreach (array_keys($arrLanguages) as $intLanguageId) {
                    $objResult = $objDatabase->Execute('SELECT  level,lang_id,is_active,sort_id,imgpath
                                                    FROM        '.DBPREFIX.'module_partners_user_level
                                                    WHERE id='.$intCategoryId.' AND     lang_id='.$intLanguageId.'
                                                ');
                    if ($objResult->RecordCount() > 0) {
                        $arrReturn[$intCategoryId][$intLanguageId]['name'] = htmlentities($objResult->fields['level'], ENT_QUOTES, CONTREXX_CHARSET);
                        $arrReturn[$intCategoryId][$intLanguageId]['sort_id'] = htmlentities($objResult->fields['sort_id'], ENT_QUOTES, CONTREXX_CHARSET);
                        $arrReturn[$intCategoryId][$intLanguageId]['is_active'] = intval($objResult->fields['is_active']);
                        $this->_objTpl->setVariable(array(
                                  'PARTNERS_LEVEL_IMAGE' => "Image"
                        ));
                   }
                 $objDisplay = $objDatabase->Execute('SELECT  display_title,display_content,display_contactname,display_country,display_phone,display_address1,display_address2,display_city,display_zipcode,display_certificate_logo,display_logo,display_level_logo,display_level_text,display_quote
                                                FROM        '.DBPREFIX.'module_partners_display
                                                WHERE display_level_id='.$intCategoryId.'  LIMIT 1
                                ');
                  if ($objDisplay->RecordCount() > 0) {
                       $this->_objTpl->setVariable(array(
                              'DIV_IMAGE_CAT' =>  $objResult->fields['imgpath']
                              ));

                     if($objDisplay->fields['display_title']!=0){
                        $this->_objTpl->setVariable(array(
                              'PARTNERS_TITLE' => "checked"
                              ));
                     }

                     if($objDisplay->fields['display_content']!=0){
                           $this->_objTpl->setVariable(array(
                              'PARTNERS_CONTENT' => "checked"
                             ));
                     }

                     if($objDisplay->fields['display_contactname']!=0){
                           $this->_objTpl->setVariable(array(
                               'PARTNERS_CONTACT' => "checked"
                             ));
                     }

                     if($objDisplay->fields['display_country']!=0){
                             $this->_objTpl->setVariable(array(
                               'PARTNERS_COUNTRY' => "checked"
                             ));
                     }

                     if($objDisplay->fields['display_phone']!=0){
                             $this->_objTpl->setVariable(array(
                               'PARTNERS_PHONE' => "checked"
                             ));
                     }

                     if($objDisplay->fields['display_address1']!=0){
                             $this->_objTpl->setVariable(array(
                                'PARTNERS_ADDRESS1' => "checked"
                             ));
                     }

                     if($objDisplay->fields['display_address2']!=0){
                             $this->_objTpl->setVariable(array(
                                'PARTNERS_ADDRESS2' => "checked"
                             ));
                     }

                     if($objDisplay->fields['display_city']!=0){
                             $this->_objTpl->setVariable(array(
                                'PARTNERS_CITY' => "checked"
                             ));
                     }

                     if($objDisplay->fields['display_zipcode']!=0){
                     $this->_objTpl->setVariable(array(
                                'PARTNERS_ZIPCODE' => "checked"
                             ));
                     }

                    if($objDisplay->fields['display_certificate_logo']!=0){
                            $this->_objTpl->setVariable(array(
                                'PARTNERS_CLOGO' => "checked"
                             ));
                    }

                    if($objDisplay->fields['display_logo']!=0){
                            $this->_objTpl->setVariable(array(
                                'PARTNERS_LOGO' => "checked"
                             ));
                    }

                    if($objDisplay->fields['display_level_text']!=0){
                            $this->_objTpl->setVariable(array(
                                'PARTNERS_LEVEL' => "checked"
                             ));
                    }

                    if($objDisplay->fields['display_quote']!=0){
                            $this->_objTpl->setVariable(array(
                                'PARTNERS_QUOTE' => "checked"
                             ));
                    }

                    if($objDisplay->fields['display_level_logo']!=0){
                            $this->_objTpl->setVariable(array(
                                'PARTNERS_LLOGO' => "checked"
                             ));
                    }
                 }
            }

        }
        $this->_objTpl->parse('Display_level');
          $objlevel = $objDatabase->Execute('SELECT    DISTINCT id
                                            FROM    '.DBPREFIX.'module_partners_user_level
                                            ORDER BY `sort_id` ASC
                                            ');
        $count_level= $objlevel->RecordCount();
        if($count_level > $this->getPagingLimit()){
                $intPagingPosition = (isset($_GET['pos'])) ? intval($_GET['pos']) : 0;
                   $strPaging = getPaging($count_level, $intPagingPosition, '&amp;cmd=partners&amp;act=manageCategory&category=level', '<strong>'.$_ARRAYLANG['TXT_PARTNERS_ENTRY_ADD_CATEGORIES'].'</strong>', true, $this->getPagingLimit());
                   $this->_objTpl->setVariable('OVERVIEW_PAGING', $strPaging);
         }

       }

       else if($category == "profile"){



            if($intCategoryId!=""){
                     $objResult = $objDatabase->Execute('SELECT    DISTINCT id
                                            FROM    '.DBPREFIX.'module_partners_user_profile
                                             WHERE  `id` = '.$intCategoryId.'
                                             ORDER BY `sort_id` ASC
                                             LIMIT  1
                                        ');
            }
            else{

                     $objResult = $objDatabase->Execute('SELECT    DISTINCT id
                                            FROM    '.DBPREFIX.'module_partners_user_profile
                                            ORDER BY `sort_id` ASC
                                            LIMIT     '.$intStartingIndex.','.$intLimitIndex.'
                                        ');

             }


             //Initialize Array
             if ($objResult->RecordCount() > 0) {

                while (!$objResult->EOF) {
                foreach(array_keys($this->_arrLanguages) as $intLangId) {
                    $arrReturn[intval($objResult->fields['id'])][$intLangId] = array(    'name'        =>    '',
                                                                                        'sort_id'   =>  '',
                                                                                        'is_active'    =>    ''
                                                                                            );
                }
                $objResult->MoveNext();
            }
        }

        //Fill array if possible
        foreach ($arrReturn as $intCategoryId => $arrLanguages) {

            foreach (array_keys($arrLanguages) as $intLanguageId) {
                $objResult = $objDatabase->Execute('SELECT  profile,sort_id,lang_id,is_active
                                                    FROM        '.DBPREFIX.'module_partners_user_profile
                                                    WHERE id='.$intCategoryId.' AND lang_id='.$intLanguageId.' LIMIT 1
                                                ');

                if ($objResult->RecordCount() > 0) {
                    $arrReturn[$intCategoryId][$intLanguageId]['name'] = htmlentities($objResult->fields['profile'], ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intCategoryId][$intLanguageId]['sort_id'] = htmlentities($objResult->fields['sort_id'], ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intCategoryId][$intLanguageId]['is_active'] = intval($objResult->fields['is_active']);
                }
            }

        }

                $objprofile = $objDatabase->Execute('SELECT    DISTINCT id
                                            FROM    '.DBPREFIX.'module_partners_user_profile
                                            ORDER BY `sort_id` ASC
                                            ');
                $count_profile= $objprofile->RecordCount();
                if($count_profile > $this->getPagingLimit())
                {
                $intPagingPosition = (isset($_GET['pos'])) ? intval($_GET['pos']) : 0;
                   $strPaging = getPaging($count_profile, $intPagingPosition, '&amp;cmd=partners&amp;act=manageCategory&category=profile', '<strong>'.$_ARRAYLANG['TXT_PARTNERS_ENTRY_ADD_CATEGORIES'].'</strong>', true, $this->getPagingLimit());
                   $this->_objTpl->setVariable('OVERVIEW_PAGING', $strPaging);
                   }
    }
    else if($category == "vertical")
    {

        if($intCategoryId!="")
        {
                     $objResult = $objDatabase->Execute('SELECT    DISTINCT id
                                            FROM    '.DBPREFIX.'module_partners_user_vertical
                                             WHERE  `id` = '.$intCategoryId.'
                                             ORDER BY `sort_id` ASC
                                             LIMIT  1
                                        ');
          }
        else
        {
                   $objResult = $objDatabase->Execute('SELECT    DISTINCT id
                                            FROM    '.DBPREFIX.'module_partners_user_vertical
                                            ORDER BY `sort_id` ASC
                                            LIMIT     '.$intStartingIndex.','.$intLimitIndex.'
                                        ');
        }


        //Initialize Array
        if ($objResult->RecordCount() > 0) {
            while (!$objResult->EOF) {
                foreach(array_keys($this->_arrLanguages) as $intLangId) {
                    $arrReturn[intval($objResult->fields['id'])][$intLangId] = array(    'name'        =>    '',
                                                                                        'sort_id'   =>  '',
                                                                                        'is_active'    =>    ''
                                                                                            );
                }
                $objResult->MoveNext();
            }
        }

        //Fill array if possible
        foreach ($arrReturn as $intCategoryId => $arrLanguages) {
            foreach (array_keys($arrLanguages) as $intLanguageId) {
                $objResult = $objDatabase->Execute('SELECT  vertical,sort_id,lang_id,is_active
                                                    FROM        '.DBPREFIX.'module_partners_user_vertical
                                                    WHERE id='.$intCategoryId.' AND lang_id='.$intLanguageId.' LIMIT 1
                                                ');

                if ($objResult->RecordCount() > 0) {
                    $arrReturn[$intCategoryId][$intLanguageId]['name'] = htmlentities($objResult->fields['vertical'], ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intCategoryId][$intLanguageId]['sort_id'] = htmlentities($objResult->fields['sort_id'], ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intCategoryId][$intLanguageId]['is_active'] = intval($objResult->fields['is_active']);
                }
            }

        }
                $objvertical = $objDatabase->Execute('SELECT    DISTINCT id
                                            FROM    '.DBPREFIX.'module_partners_user_vertical
                                            ORDER BY `sort_id` ASC
                                            ');
                $count_vertical= $objvertical->RecordCount();
                if($count_vertical > $this->getPagingLimit())
                {
                $intPagingPosition = (isset($_GET['pos'])) ? intval($_GET['pos']) : 0;
                   $strPaging = getPaging($count_vertical, $intPagingPosition, '&amp;cmd=partners&amp;act=manageCategory&category=vertical', '<strong>'.$_ARRAYLANG['TXT_PARTNERS_ENTRY_ADD_CATEGORIES'].'</strong>', true, $this->getPagingLimit());
                   $this->_objTpl->setVariable('OVERVIEW_PAGING', $strPaging);
                   }
    }
    else if($category == "country")
    {

        if($intCategoryId!="")
        {
                     $objResult = $objDatabase->Execute('SELECT    DISTINCT id
                                             FROM    '.DBPREFIX.'module_partners_user_country
                                             WHERE  `id` = '.$intCategoryId.'
                                             ORDER BY `sort_id` ASC
                                             LIMIT  1
                                        ');
        }
        else
        {
                    $objResult = $objDatabase->Execute('SELECT    DISTINCT id
                                            FROM    '.DBPREFIX.'module_partners_user_country
                                            ORDER BY `sort_id` ASC
                                            LIMIT     '.$intStartingIndex.','.$intLimitIndex.'
                                        ');
        }

        $count_country = $objResult->RecordCount();
        //Initialize Array
        if ($objResult->RecordCount() > 0) {
            while (!$objResult->EOF) {
                foreach(array_keys($this->_arrLanguages) as $intLangId) {
                    $arrReturn[intval($objResult->fields['id'])][$intLangId] = array(    'name'        =>    '',
                                                                                        'sort_id'   => '',
                                                                                        'regions'   => '',
                                                                                        'is_active'    =>    ''
                                                                                            );
                }
                $objResult->MoveNext();
            }
        }

        //Fill array if possible
        foreach ($arrReturn as $intCategoryId => $arrLanguages) {
            foreach (array_keys($arrLanguages) as $intLanguageId) {
                $objResult = $objDatabase->Execute('SELECT  country,sort_id,lang_id,is_active
                                                    FROM        '.DBPREFIX.'module_partners_user_country
                                                    WHERE id='.$intCategoryId.' AND lang_id='.$intLanguageId.' LIMIT 1
                                                ');

                if ($objResult->RecordCount() > 0) {
                    $arrReturn[$intCategoryId][$intLanguageId]['name'] = htmlentities($objResult->fields['country'], ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intCategoryId][$intLanguageId]['sort_id'] = htmlentities($objResult->fields['sort_id'], ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intCategoryId][$intLanguageId]['regions'] = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_ADD_REGIONS_LINK'];
                    $arrReturn[$intCategoryId][$intLanguageId]['is_active'] = intval($objResult->fields['is_active']);

                }
            }

        }
                $objCountry = $objDatabase->Execute('SELECT    DISTINCT id
                                            FROM    '.DBPREFIX.'module_partners_user_country
                                            ORDER BY `sort_id` ASC
                                            ');
                $count_country = $objCountry->RecordCount();
                if($count_country > $this->getPagingLimit())
                {
                $intPagingPosition = (isset($_GET['pos'])) ? intval($_GET['pos']) : 0;
                $intPagingPosition;
                   $strPaging = getPaging($count_country, $intPagingPosition, '&amp;cmd=partners&amp;act=manageCategory&category=country', '<strong>'.$_ARRAYLANG['TXT_PARTNERS_ENTRY_ADD_CATEGORIES'].'</strong>', true, $this->getPagingLimit());
                   $this->_objTpl->setVariable('OVERVIEW_PAGING', $strPaging);
                   }
    }
    else if($category == "Regions"){

                $this->_objTpl->setVariable(array(   'PARTNERS_CAT_NAME'     => "Regions",
                                          'PARTNERS_CAT_NAME_DEL' => "Regions",
                                          'TXT_OVERVIEW_TITLE'    => $_ARRAYLANG['PARTNERS_CATNAME_MANAGE'].$this->_getCategoryname('6')." ".$this->_getTitlecountry($cat_id),
                                          'TXT_ADD_TITLE'         => $_ARRAYLANG['PARTNERS_CATNAME_ADD'].$this->_getCategoryname('6')." ".$this->_getTitlecountry($cat_id),
                                          'TXT_RENAME_TITLE'      => $_ARRAYLANG['PARTNERS_CATNAME_RENAME'].$this->_getCategoryname('6')." ".$this->_getTitlecountry($cat_id)
                                          ));

         if($intCategoryId!="")
         {
                     $objResult = $objDatabase->Execute('SELECT    DISTINCT id
                                             FROM    '.DBPREFIX.'module_partners_user_region
                                             WHERE  `id` = '.$intCategoryId.'
                                             ORDER BY `sort_id` ASC LIMIT  1
                                        ');
         }
         else if($cat_id!="")
         {
                      $objResult = $objDatabase->Execute('SELECT    DISTINCT id
                                             FROM    '.DBPREFIX.'module_partners_user_region
                                             WHERE  `cat_id` = '.$cat_id.'
                                             ORDER BY `sort_id` ASC
                                        ');
         }
        else
        {
                     $objResult = $objDatabase->Execute('SELECT    DISTINCT id
                                            FROM    '.DBPREFIX.'module_partners_user_region ORDER BY `sort_id` ASC
                                            LIMIT     '.$intStartingIndex.','.$intLimitIndex.'
                                          ');

        }

        //Initialize Array
        if ($objResult->RecordCount() > 0) {

            while (!$objResult->EOF) {
                foreach(array_keys($this->_arrLanguages) as $intLangId) {
                    $arrReturn[intval($objResult->fields['id'])][$intLangId] = array(    'name'        =>    '',
                                                                                        'sort_id'   =>  '',
                                                                                        'is_active'    =>    ''
                                                                                            );
                }
                $objResult->MoveNext();
            }
        }

        //Fill array if possible
        foreach ($arrReturn as $intCategoryId => $arrLanguages) {
              foreach (array_keys($arrLanguages) as $intLanguageId) {
                $objResult = $objDatabase->Execute('SELECT  is_active,name,sort_id FROM    '.DBPREFIX.'module_partners_user_region
                           WHERE    id='.$intCategoryId.' AND     lang_id='.$intLanguageId.' LIMIT 1');

                if ($objResult->RecordCount() > 0) {
                    $arrReturn[$intCategoryId][$intLanguageId]['name'] = htmlentities($objResult->fields['name'], ENT_QUOTES, CONTREXX_CHARSET);
                     $arrReturn[$intCategoryId][$intLanguageId]['sort_id'] = htmlentities($objResult->fields['sort_id'], ENT_QUOTES, CONTREXX_CHARSET);
                     $objResult->fields['imgpath'];
                    $arrReturn[$intCategoryId][$intLanguageId]['is_active'] = intval($objResult->fields['is_active']);
                }
            }

        }
                $objCertificate = $objDatabase->Execute('SELECT    DISTINCT id
                                            FROM    '.DBPREFIX.'module_partners_user_region ORDER BY `sort_id` ASC
                                            ');
                $count_certificate= $objCertificate->RecordCount();
                if($count_certificate > $this->getPagingLimit())
                {
                $intPagingPosition = (isset($_GET['pos'])) ? intval($_GET['pos']) : 0;
                   $strPaging = getPaging($count_certificate, $intPagingPosition, '&amp;cmd=partners&amp;act=manageCategory', '<strong>'.$_ARRAYLANG['TXT_PARTNERS_ENTRY_ADD_CATEGORIES'].'</strong>', true, $this->getPagingLimit());
                   $this->_objTpl->setVariable('OVERVIEW_PAGING', $strPaging);
                   }

    }
    else
    {


         if($intCategoryId!="")
         {
                     $objResult = $objDatabase->Execute('SELECT    DISTINCT category_id
                                             FROM    '.DBPREFIX.'module_partners_categories
                                             WHERE  `category_id` = '.$intCategoryId.'
                                             ORDER BY `sort_id` ASC LIMIT  1
                                        ');
        }
        else
        {
                     $objResult = $objDatabase->Execute('SELECT    DISTINCT category_id
                                            FROM    '.DBPREFIX.'module_partners_categories ORDER BY `sort_id` ASC
                                            LIMIT     '.$intStartingIndex.','.$intLimitIndex.'
                                          ');

        }

        //Initialize Array
        if ($objResult->RecordCount() > 0) {
            while (!$objResult->EOF) {
                foreach(array_keys($this->_arrLanguages) as $intLangId) {
                    $arrReturn[intval($objResult->fields['category_id'])][$intLangId] = array(    'name'        =>    '',
                                                                                                'sort_id'   =>  '',
                                                                                                'is_active'    =>    ''
                                                                                            );
                }
                $objResult->MoveNext();
            }
        }

        //Fill array if possible
        foreach ($arrReturn as $intCategoryId => $arrLanguages) {
            foreach (array_keys($arrLanguages) as $intLanguageId) {
                $objResult = $objDatabase->Execute('SELECT  is_active,name,sort_id,imgpath FROM    '.DBPREFIX.'module_partners_categories
                           WHERE    category_id='.$intCategoryId.' AND     lang_id='.$intLanguageId.' LIMIT 1');

                if ($objResult->RecordCount() > 0) {
                    $arrReturn[$intCategoryId][$intLanguageId]['name'] = htmlentities($objResult->fields['name'], ENT_QUOTES, CONTREXX_CHARSET);
                     $arrReturn[$intCategoryId][$intLanguageId]['sort_id'] = htmlentities($objResult->fields['sort_id'], ENT_QUOTES, CONTREXX_CHARSET);
                     $objResult->fields['imgpath'];
                    $this->_objTpl->setVariable(array(
                                                      'PARTNERS_LEVEL_IMAGE' => "Image",
                                                      'DIV_IMAGE_CAT' => $objResult->fields['imgpath']
                                                      ));
                    $arrReturn[$intCategoryId][$intLanguageId]['is_active'] = intval($objResult->fields['is_active']);
                }
            }

        }
                $objCertificate = $objDatabase->Execute('SELECT    DISTINCT category_id
                                            FROM    '.DBPREFIX.'module_partners_categories ORDER BY `sort_id` ASC
                                            ');
                $count_certificate= $objCertificate->RecordCount();
                if($count_certificate > $this->getPagingLimit())
                {
                $intPagingPosition = (isset($_GET['pos'])) ? intval($_GET['pos']) : 0;
                   $strPaging = getPaging($count_certificate, $intPagingPosition, '&amp;cmd=partners&amp;act=manageCategory', '<strong>'.$_ARRAYLANG['TXT_PARTNERS_ENTRY_ADD_CATEGORIES'].'</strong>', true, $this->getPagingLimit());
                   $this->_objTpl->setVariable('OVERVIEW_PAGING', $strPaging);
                   }
        }

        return $arrReturn;
    }

      function CreateRegionArray($catId){
      global $objDatabase;
      $catId = intval($catId);
      $arrRename = array();
      foreach(array_keys($this->_arrLanguages) as $intLanguageId) {
                  $objRegion = $objDatabase->Execute('SELECT  is_active,name FROM    '.DBPREFIX.'module_partners_categories_name
                           WHERE    id = '.$catId.' AND     lang_id='.$intLanguageId.' LIMIT 1');

                         if($objRegion->RecordCount() > 0){

                         $arrRename[intval($objRegion->fields['id'])][$intLanguageId] = array(    'name'        =>    htmlentities($objRegion->fields['name'], ENT_QUOTES, CONTREXX_CHARSET),
                                                                                               'is_active'    =>  htmlentities($objRegion->fields['is_active'], ENT_QUOTES, CONTREXX_CHARSET)
                                                                                            );

                         }
                   }
     return $arrRename;
     }
    /**
    * function is used to get the status of the partners depends on the variable from the database
    */

    function _getStatus($status) {

      switch($status)
      {
       case '0':
         $status = "Inactive";
       break;

       default:
         $status = "Active";
      }
      return $status;
    }


    /**
    * function is used to set the status of the partners depends on the variable from the database
    */

    function _checkStatus($status,$attr,$Id,$jscript){

      switch($status)
      {
       case '0':
         $status = '<input type="radio" '.$attr.' '.$Id.' value="1" '.$jscript.'>Active &nbsp;&nbsp;&nbsp; <input type="radio" '.$attr.' '.$Id.' checked value="0" '.$jscript.'>In active';
       break;

       default:
         $status = '<input type="radio" '.$attr.' '.$Id.' checked value="1" '.$jscript.'>Active &nbsp;&nbsp;&nbsp; <input type="radio" '.$attr.' '.$Id.' value="0" '.$jscript.'>In active';
      }
      return $status;
    }









    function _getEditRegionMenu($entryId,$levelname,$attrs,$all,$intLanguageId,$default,$ajaxRequest,$intRegionId)
    {
     global $_ARRAYLANG,$objDatabase;

// TODO: $selected is not defined
$selected = $default;

     $entryarray = array();
     $regionarray = array();
     if($levelname=='regions'){
            $objFullLevel = $objDatabase->Execute("SELECT `category_id` FROM `".DBPREFIX."module_partners_message_to_country` where `message_id` = '".$entryId."' AND `lang_id` = '".$intLanguageId."'");
            while(!$objFullLevel->EOF){
                $entryarray[$objFullLevel->fields['category_id']] = $objFullLevel->fields['category_id'];
                $Reg_arr[]=$entryarray[$objFullLevel->fields['category_id']];
                $objFullLevel->MoveNext();
            }
            $objFullRegion = $objDatabase->Execute("SELECT `category_id` FROM `".DBPREFIX."module_partners_message_to_region` where `message_id` = '".$entryId."' AND `lang_id` = '".$intLanguageId."'");
            while(!$objFullRegion->EOF){
             $regionarray[$objFullRegion->fields['category_id']] =  $objFullRegion->fields['category_id'];
                //$entryarray[$objFullLevel->fields['category_id']] = $objFullLevel->fields['category_id'];
                //$Reg_arr[]=$entryarray[$objFullLevel->fields['category_id']];
                $objFullRegion->MoveNext();
            }

     }
//     print_r($regionarray);

     if($ajaxRequest==1)  {
//          print_r($entryarray);
       if($all == "backend") {

         $arrTitles = $this->_getListRegionLevel($levelname,$intLanguageId,$ajaxRequest,$intRegionId,$Reg_arr);
          $menu = '<select'.(!empty($attrs) ? ' '.$attrs : '').">\n";
          $menu .= '<option value="0"'.($selected == 0 ? ' ' : '').'>'.$default."</option>\n";
                foreach ($arrTitles as $id => $title) {
             //   print $id;
                if(array_key_exists($id,$regionarray)){

                        $menu .= '<option value="'.$id.'"'."selected".'>'.htmlentities($title, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
                        }else{
                         $menu .= '<option value="'.$id.'">'.htmlentities($title, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
                        }
                }
               $menu .= '</select>';
        }

        else {

         $menu = '<select'.(!empty($attrs) ? ' '.$attrs : '').">\n";
         $arrTitles = $this->_getListRegionLevel($levelname,$intLanguageId,$ajaxRequest,$intRegionId,$Reg_arr);
           $menu .= '<option value="0"'.($selected == 0 ? ' selected="selected"' : '').'>'.$all."</option>\n";

            foreach ($arrTitles as $id => $title) {
                //echo $title;
                if(array_key_exists($id,$regionarray)){
                    $menu .= '<option value="'.$id.'"'.($selected == $id ? ' selected="selected"' : '').'>'.htmlentities($title, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
                 }else{
                      $menu .= '<option value="'.$id.'">'.htmlentities($title, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
                   }
            }
          $menu.='</select>';
        }
        }

      elseif($ajaxRequest==2)  {
       if($all == "backend") {

         $arrTitles = $this->_getListRegionLevel_2($levelname,$intLanguageId,$ajaxRequest,$intRegionId,$Reg_arr);
          $menu = '<select'.(!empty($attrs) ? ' '.$attrs : '').">\n";
          $menu .= '<option value="0"'.($selected == 0 ? ' ' : '').'>'.$default."</option>\n";
//        print_r($entryarray);
                foreach ($arrTitles as $id => $title) {
                        if(array_key_exists($id,$regionarray)){
                        $menu .= '<option value="'.$id.'"'."selected".'>'.htmlentities($title, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
                        }else{
                         $menu .= '<option value="'.$id.'">'.htmlentities($title, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
                        }
                }
               $menu .= '</select>';
        }

        else {

         $menu = '<select'.(!empty($attrs) ? ' '.$attrs : '').">\n";
         $arrTitles = $this->_getListRegionLevel_2($levelname,$intLanguageId,$ajaxRequest,$intRegionId,$Reg_arr);
           $menu .= '<option value="0"'.($selected == 0 ? ' selected="selected"' : '').'>'.$all."</option>\n";

            foreach ($arrTitles as $id => $title) {
                echo $title;
                if(array_key_exists($id,$regionarray)){
                    $menu .= '<option value="'.$id.'"'.($selected == $id ? ' selected="selected"' : '').'>'.htmlentities($title, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
                 }else{
                      $menu .= '<option value="'.$id.'">'.htmlentities($title, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
                   }
            }
          $menu.='</select>';
        }
      }

      elseif($ajaxRequest==3)  {
       if($all == "backend") {

         $arrTitles = $this->_getListRegionLevel_3($levelname,$intLanguageId,$ajaxRequest,$intRegionId,$Reg_arr);
          $menu = '<select'.(!empty($attrs) ? ' '.$attrs : '').">\n";
          $menu .= '<option value="0"'.($selected == 0 ? ' ' : '').'>'.$default."</option>\n";
                foreach ($arrTitles as $id => $title) {
                if(array_key_exists($id,$regionarray)){
                        $menu .= '<option value="'.$id.'"'."selected".'>'.htmlentities($title, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
                        }else{
                         $menu .= '<option value="'.$id.'">'.htmlentities($title, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
                        }
                }
               $menu .= '</select>';
        }

        else {

         $menu = '<select'.(!empty($attrs) ? ' '.$attrs : '').">\n";
         $arrTitles = $this->_getListRegionLevel_3($levelname,$intLanguageId,$ajaxRequest,$intRegionId,$Reg_arr);
           $menu .= '<option value="0"'.($selected == 0 ? ' selected="selected"' : '').'>'.$all."</option>\n";

            foreach ($arrTitles as $id => $title) {
                //echo $title;
                if(array_key_exists($id,$regionarray)){
                    $menu .= '<option value="'.$id.'"'.($selected == $id ? ' selected="selected"' : '').'>'.htmlentities($title, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
                 }else{
                      $menu .= '<option value="'.$id.'">'.htmlentities($title, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
                   }
            }
          $menu.='</select>';
        }
      }


        return $menu;
   }

   function _getListRegionLevel($levelname,$intLanguageId,$ajaxRequest,$intRegionId,$Reg_arr){
   global $objDatabase;

              $this->_initlistregionLevel($levelname,$intLanguageId,$ajaxRequest,$intRegionId,$Reg_arr);
             return $this->_arrlistregionLevel;

   }


   function _getListRegionLevel_2($levelname,$intLanguageId,$ajaxRequest,$intRegionId,$Reg_arr){
   global $objDatabase;

              $this->_initlistregionLevel_2($levelname,$intLanguageId,$ajaxRequest,$intRegionId,$Reg_arr);
             return $this->_arrlistregionLevel_2;

   }

   function _getListRegionLevel_3($levelname,$intLanguageId,$ajaxRequest,$intRegionId,$Reg_arr){
   global $objDatabase;

              $this->_initlistregionLevel_3($levelname,$intLanguageId,$ajaxRequest,$intRegionId,$Reg_arr);
             return $this->_arrlistregionLevel_3;

   }



function _initlistregionLevel($levelname,$intLanguageId,$ajaxRequest,$intRegionId,$Reg_arr){
       global $objDatabase;
       $this->_arrlistregionLevel = array();
         if($Reg_arr[0]!=0){
                  $objLevel = $objDatabase->Execute("SELECT `id`, `name` FROM `".DBPREFIX."module_partners_user_region` WHERE `cat_id` = '".$Reg_arr[0]."' AND `lang_id` = '".$intLanguageId."' AND `is_active` = 1 ORDER BY `sort_id` ASC");
             }else{
                 // $objLevel = $objDatabase->Execute("SELECT `id`, `name` FROM `".DBPREFIX."module_partners_user_region` ORDER BY `sort_id` ASC");
             }
             if ($objLevel != false) {
                     while (!$objLevel->EOF) {
                     $this->_arrlistregionLevel[$objLevel->fields['id']] = $objLevel->fields['name'];
                        $objLevel->MoveNext();
                }
             }
}



function _initlistregionLevel_2($levelname,$intLanguageId,$ajaxRequest,$intRegionId,$Reg_arr){
       global $objDatabase;
        $this->_arrlistregionLevel_2 = array();
         if($Reg_arr[1]!=0){
                 $objLevel = $objDatabase->Execute("SELECT `id`, `name`,`is_active`,`lang_id` FROM `".DBPREFIX."module_partners_user_region` WHERE `cat_id` = '".$Reg_arr[1]."' AND `lang_id` = '".$intLanguageId."' AND `is_active` = 1 ORDER BY `sort_id` ASC");
             }else{
                 // $objLevel = $objDatabase->Execute("SELECT `id`, `name` FROM `".DBPREFIX."module_partners_user_region` ORDER BY `sort_id` ASC");
             }
             if ($objLevel != false) {

                     while (!$objLevel->EOF) {

                      $this->_arrlistregionLevel_2[$objLevel->fields['id']] = $objLevel->fields['name'];
// TODO: Huh?!
//                      $sdfsd .= $objLevel->fields['name'];
                      $objLevel->MoveNext();
                }

             }
}





function _initlistregionLevel_3($levelname,$intLanguageId,$ajaxRequest,$intRegionId,$Reg_arr){
       global $objDatabase;
       $this->_arrlistregionLevel_3 = array();
         if($Reg_arr[2]!=0){
                  $objLevel = $objDatabase->Execute("SELECT `id`, `name` FROM `".DBPREFIX."module_partners_user_region` WHERE `cat_id` = '".$Reg_arr[2]."' AND `lang_id` = '".$intLanguageId."' AND `is_active` = 1 ORDER BY `sort_id` ASC");
             }else{
                 // $objLevel = $objDatabase->Execute("SELECT `id`, `name` FROM `".DBPREFIX."module_partners_user_region` ORDER BY `sort_id` ASC");
             }
             if ($objLevel != false) {
                     while (!$objLevel->EOF) {
                      $this->_arrlistregionLevel_3[$objLevel->fields['id']] = $objLevel->fields['name'];
                        $objLevel->MoveNext();
                }
             }
}






    /**
     * Level list  containing the content for the level,vertical,country and profile dropdown
     *
     * @global  array       $_ARRAYLANG
     */

     function _getListLevelMenu($selected = 0, $levelname, $attrs,$all,$intLanguageId,$default,$ajaxRequest,$intRegionId)
    {
        global $_ARRAYLANG;
        if($all == "backend") {
         $menu = '<select'.(!empty($attrs) ? ' '.$attrs : '').">\n";

         $arrTitles = $this->_getListLevel($levelname,$intLanguageId,$ajaxRequest,$intRegionId);
          $menu .= '<option value="0"'.($selected == 0 ? ' selected="selected"' : '').'>'.$default."</option>\n";
            foreach ($arrTitles as $id => $title) {
                 $menu .= '<option value="'.$id.'"'.($selected == $id ? ' selected="selected"' : '').'>'.htmlentities($title, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
          }
         $menu .= "</select>\n";
        }

        else {
         $menu = '<select'.(!empty($attrs) ? ' '.$attrs : '').">\n";

         $arrTitles = $this->_getListLevel($levelname,$intLanguageId,$ajaxRequest,$intRegionId);

         $menu .= '<option value="0"'.($selected == 0 ? ' selected="selected"' : '').'>'.$all."</option>\n";
          foreach ($arrTitles as $id => $title) {
            $menu .= '<option value="'.$id.'"'.($selected == $id ? ' selected="selected"' : '').'>'.htmlentities($title, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
          }
         $menu .= "</select>\n";
        }
        return $menu;
    }







    function _getEditLevelMenu($entryId,$levelname,$attrs,$all,$intLanguageId,$default,$ajaxRequest,$intRegionId)
    {
        global $_ARRAYLANG,$objDatabase;

// TODO: $selected is not defined
$selected = $default;

        $entryarray = array();
        if($levelname=='level'){
            $objFullLevel = $objDatabase->Execute("SELECT `category_id` FROM `".DBPREFIX."module_partners_message_to_level` where `message_id` = '".$entryId."' AND `lang_id` = '".$intLanguageId."'");
            while(!$objFullLevel->EOF){
                $entryarray[$objFullLevel->fields['category_id']] = $objFullLevel->fields['category_id'];
                $objFullLevel->MoveNext();
            }
        }


        if($levelname=='profile'){
            $objFullLevel = $objDatabase->Execute("SELECT `category_id` FROM `".DBPREFIX."module_partners_message_to_profile` where `message_id` = '".$entryId."' AND `lang_id` = '".$intLanguageId."'");
            while(!$objFullLevel->EOF){
                $entryarray[$objFullLevel->fields['category_id']] = $objFullLevel->fields['category_id'];
                $objFullLevel->MoveNext();
            }
        }


        if($levelname=='country'){
            $objFullLevel = $objDatabase->Execute("SELECT `category_id` FROM `".DBPREFIX."module_partners_message_to_country` where `message_id` = '".$entryId."' AND `lang_id` = '".$intLanguageId."'");
            while(!$objFullLevel->EOF){
                $entryarray[$objFullLevel->fields['category_id']] = $objFullLevel->fields['category_id'];
                $objFullLevel->MoveNext();
            }
        }

        if($levelname=='vertical'){
            $objFullLevel = $objDatabase->Execute("SELECT `category_id` FROM `".DBPREFIX."module_partners_message_to_vertical` where `message_id` = '".$entryId."' AND `lang_id` = '".$intLanguageId."'");
            while(!$objFullLevel->EOF){
                $entryarray[$objFullLevel->fields['category_id']] = $objFullLevel->fields['category_id'];
                $objFullLevel->MoveNext();
            }
        }

        if($all == "backend") {

         $arrTitles = $this->_getListLevel($levelname,$intLanguageId,$ajaxRequest,$intRegionId);
         // $menu .= '<option value="0"'.($selected == 0 ? ' selected="selected"' : '').'>'.$default."</option>\n";
                foreach ($arrTitles as $id => $title) {
                if(array_key_exists($id,$entryarray)){
                        $menu .= '<option value="'.$id.'"'."selected".'>'.htmlentities($title, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
                        }
                }
        }

        else {


         $arrTitles = $this->_getListLevel($levelname,$intLanguageId,$ajaxRequest,$intRegionId);
           //$menu .= '<option value="0"'.($selected == 0 ? ' selected="selected"' : '').'>'.$all."</option>\n";

            foreach ($arrTitles as $id => $title) {
                if(array_key_exists($id,$entryarray)){
                    $menu .= '<option value="'.$id.'"'.($selected == $id ? ' selected="selected"' : '').'>'.htmlentities($title, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
                 }
            }

        }
        return $menu;
    }

  function _getEditOtherLevelMenu($entryId,$levelname,$attrs,$all,$intLanguageId,$default,$ajaxRequest,$intRegionId,$posId)
    {
        global $_ARRAYLANG,$objDatabase;

// TODO: $selected is not defined
$selected = $default;

        $entryarray = array();
        if($levelname=='level'){
            $objFullLevel = $objDatabase->Execute("SELECT `category_id` FROM `".DBPREFIX."module_partners_message_to_level` where `message_id` = '".$entryId."' AND `lang_id` = '".$intLanguageId."'");
            while(!$objFullLevel->EOF){
                $entryarray[$objFullLevel->fields['category_id']] = $objFullLevel->fields['category_id'];
                $objFullLevel->MoveNext();
            }
        }


        if($levelname=='profile'){
            $objFullLevel = $objDatabase->Execute("SELECT `category_id` FROM `".DBPREFIX."module_partners_message_to_profile` where `message_id` = '".$entryId."' AND `lang_id` = '".$intLanguageId."'");
            while(!$objFullLevel->EOF){
                $entryarray[$objFullLevel->fields['category_id']] = $objFullLevel->fields['category_id'];
                $objFullLevel->MoveNext();
            }
        }


        if($levelname=='country'){
            $objFullLevel = $objDatabase->Execute("SELECT `category_id` FROM `".DBPREFIX."module_partners_message_to_country` where `message_id` = '".$entryId."' AND `lang_id` = '".$intLanguageId."' AND `pos_id` = '".$posId."'");
            while(!$objFullLevel->EOF){

                $entryarray[$objFullLevel->fields['category_id']] = $objFullLevel->fields['category_id'];
                $objFullLevel->MoveNext();
            }
        }



        if($levelname=='vertical'){
            $objFullLevel = $objDatabase->Execute("SELECT `category_id` FROM `".DBPREFIX."module_partners_message_to_vertical` where `message_id` = '".$entryId."' AND `lang_id` = '".$intLanguageId."'");
            while(!$objFullLevel->EOF){
                $entryarray[$objFullLevel->fields['category_id']] = $objFullLevel->fields['category_id'];
                $objFullLevel->MoveNext();
            }
        }

        if($all == "backend") {
         $arrTitles = $this->_getListLevel($levelname,$intLanguageId,$ajaxRequest,$intRegionId);
          $menu = '<select'.(!empty($attrs) ? ' '.$attrs : '').">\n";
          $menu .= '<option value="0"'.($selected == 0 ? ' ' : '').'>'.$default."</option>\n";
                foreach ($arrTitles as $id => $title) {
                if(array_key_exists($id,$entryarray)){
                if($levelname == "country"){
                        $menu .= '<option value="'.$id.'"'."selected = 'selected'".'>'.htmlentities($title, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
                     }else{
                     //   $menu .= '<option value="'.$id.'"'."selected = 'selected'".'>'.htmlentities($title, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
                     }
                        }else{
                         $menu .= '<option value="'.$id.'">'.htmlentities($title, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
                        }
                }
               $menu .= '</select>';
        }

        else {

         $menu = '<select'.(!empty($attrs) ? ' '.$attrs : '').">\n";
         $arrTitles = $this->_getListLevel($levelname,$intLanguageId,$ajaxRequest,$intRegionId);
           $menu .= '<option value="0"'.($selected == 0 ? ' selected="selected"' : '').'>'.$all."</option>\n";

            foreach ($arrTitles as $id => $title) {
                if(array_key_exists($id,$entryarray)){
                    $menu .= '<option value="'.$id.'"'.($selected == $id ? ' selected="selected"' : '').'>'.htmlentities($title, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
                 }else{
                      $menu .= '<option value="'.$id.'">'.htmlentities($title, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
                   }
            }
          $menu.='</select>';
        }
        return $menu;
    }



   /**
     * Level list  containing the name for the level
     *
     * @global  object       $_objDatabase
     */

    function _getListLevel($levelname,$intLanguageId,$ajaxRequest,$intRegionId)
    {
        global $objDatabase;

         $this->_initlistLevel($levelname,$intLanguageId,$ajaxRequest,$intRegionId);

         return $this->_arrlistLevel;
    }

   /**
    * Move the Level to next position
    *
    * @global  object       $_objDatabase
    */

    function _initlistLevel($levelname,$intLanguageId,$ajaxRequest,$intRegionId)
    {
        global $objDatabase;
        $this->_arrlistLevel = array();
        if($levelname == "level")
        {
           if($intLanguageId!=0){
                $objLevel = $objDatabase->Execute("SELECT DISTINCT `id`, `level` FROM `".DBPREFIX."module_partners_user_level` where `lang_id` = '".$intLanguageId."' AND `is_active` = 1 ORDER BY `sort_id` ASC");
           }else{
            $objLevel = $objDatabase->Execute("SELECT DISTINCT `id`, `level` FROM `".DBPREFIX."module_partners_user_level` ORDER BY `sort_id` ASC");
           }
           if ($objLevel !== false) {
            foreach($this->_arrLanguages as $arrValues) {
             while (!$objLevel->EOF) {
                       $this->_arrlistLevel[$objLevel->fields['id']] = $objLevel->fields['level'];
                   $objLevel->MoveNext();
             }
            }
           }
        }
        if($levelname == "profile"){
            if($intLanguageId!=0){
                  $objLevel = $objDatabase->Execute("SELECT `id`, `profile` FROM `".DBPREFIX."module_partners_user_profile` where `lang_id` = '".$intLanguageId."' AND `is_active` = 1 ORDER BY `sort_id` ASC");
            }else{
                  $objLevel = $objDatabase->Execute("SELECT `id`, `profile` FROM `".DBPREFIX."module_partners_user_profile` ORDER BY `sort_id` ASC");
            }
            if ($objLevel !== false) {
                while (!$objLevel->EOF) {
                    $this->_arrlistLevel[$objLevel->fields['id']] = $objLevel->fields['profile'];
                    $objLevel->MoveNext();
                }
            }
        }

        if($levelname == "vertical"){
            if($intLanguageId!=0){
                  $objLevel = $objDatabase->Execute("SELECT `id`, `vertical` FROM `".DBPREFIX."module_partners_user_vertical` where `lang_id` = '".$intLanguageId."' AND `is_active` = 1 ORDER BY `sort_id` ASC");
            }else{
                  $objLevel = $objDatabase->Execute("SELECT `id`, `vertical` FROM `".DBPREFIX."module_partners_user_vertical` ORDER BY `sort_id` ASC");
            }
            if ($objLevel !== false) {
                  while (!$objLevel->EOF) {
                        $this->_arrlistLevel[$objLevel->fields['id']] = $objLevel->fields['vertical'];
                        $objLevel->MoveNext();
                  }
            }
        }

        if($levelname == "country"){
            if($intLanguageId!=0){
                  $objLevel = $objDatabase->Execute("SELECT `id`, `country` FROM `".DBPREFIX."module_partners_user_country` where `lang_id` = '".$intLanguageId."' AND `is_active` = 1 ORDER BY `sort_id` ASC");
            }else{
                  $objLevel = $objDatabase->Execute("SELECT `id`, `country` FROM `".DBPREFIX."module_partners_user_country` ORDER BY `sort_id` ASC");
            }
            if ($objLevel !== false) {
                  while (!$objLevel->EOF) {
                        $this->_arrlistLevel[$objLevel->fields['id']] = $objLevel->fields['country'];

                        $objLevel->MoveNext();
                  }
            }
        }

        if($levelname == "regions"){
             if($intRegionId!=0){
                  $objLevel = $objDatabase->Execute("SELECT `id`, `name` FROM `".DBPREFIX."module_partners_user_region` WHERE `cat_id` = '".$ajaxRequest."' AND `lang_id` = '".$intLanguageId."' AND `is_active` = 1 ORDER BY `sort_id` ASC");
             }else if($ajaxRequest!=0){
                  $objLevel = $objDatabase->Execute("SELECT `id`, `name` FROM `".DBPREFIX."module_partners_user_region` WHERE `cat_id` = '".$ajaxRequest."' AND `lang_id` = '".$intLanguageId."' AND `is_active` = 1 ORDER BY `sort_id` ASC");
             }else if($intLanguageId!=0){
                  $objLevel = $objDatabase->Execute("SELECT `id`  FROM `".DBPREFIX."module_partners_user_region` WHERE `lang_id` = '".$intLanguageId."' AND `is_active` = 1 ORDER BY `sort_id` ASC LIMIT 0");
             }else{
                  $objLevel = $objDatabase->Execute("SELECT `id`, `name` FROM `".DBPREFIX."module_partners_user_region` ORDER BY `sort_id` ASC");
             }
             if ($objLevel !== false) {
                  while (!$objLevel->EOF) {
                         $this->_arrlistLevel[$objLevel->fields['id']] = $objLevel->fields['name'];
                        $objLevel->MoveNext();
                  }
             }
        }

    }










    /**
    * get settings from the settings table
    * @global      array       $_CONFIG
    * @global      array       $_ARRAYLANG
    * @global      object      $objDatabase
    */
    function _getSettings(){

    global $_CONFIG, $_ARRAYLANG, $objDatabase;

    $this->_arrSettings = array();
    $intCategoryId = 1;
    $objResult = $objDatabase->Execute('SELECT  id,sortorder,width,height,lwidth,lheight,cwidth,cheight,lis_active,pis_active,cis_active,vis_active,ctis_active
                                       FROM        '.DBPREFIX.'module_partners_settings
                                       WHERE id='.$intCategoryId.' LIMIT 1');
        while (!$objResult->EOF) {
                 $this->_arrSettings['id'] = $objResult->fields['id'];
                $this->_arrSettings['sortorder'] = $objResult->fields['sortorder'];
                $this->_arrSettings['width'] = $objResult->fields['width'];
                $this->_arrSettings['height'] = $objResult->fields['height'];
                $this->_arrSettings['lwidth'] = $objResult->fields['lwidth'];
                $this->_arrSettings['lheight'] = $objResult->fields['lheight'];
                $this->_arrSettings['cwidth'] = $objResult->fields['cwidth'];
                $this->_arrSettings['cheight'] = $objResult->fields['cheight'];
                $this->_arrSettings['lis_active'] = $objResult->fields['lis_active'];
                $this->_arrSettings['pis_active'] = $objResult->fields['pis_active'];
                $this->_arrSettings['cis_active'] = $objResult->fields['cis_active'];
                $this->_arrSettings['vis_active'] = $objResult->fields['vis_active'];
                $this->_arrSettings['ctis_active'] = $objResult->fields['ctis_active'];
                $objResult->MoveNext();
            }

            return $this->_arrSettings;

    }


    /**
    * set the current settings of the sort order
    */

    function _getsettingsActive($active){

    switch($active){

      case 'Level':
       $radio = '<input type="radio" name="frmSettings_sort" checked value="Level" />Level&nbsp;
                            <input type="radio" name="frmSettings_sort" value="Subject" />Title&nbsp';
      break;
      default:
        $radio = '<input type="radio" name="frmSettings_sort" value="Level" />Level&nbsp;
                            <input type="radio" name="frmSettings_sort" checked value="Subject" />Title&nbsp';
       }
       return $radio;
       }



    function _getactiveProperties($value,$name){
      //echo $value."<br>";
      switch($value){

      case 0:

       $radio = '<input type="radio" value="1" name="'.$name.'">Activate&nbsp;
       <input type="radio" value="0" name="'.$name.'" checked>Deactivate';
      break;
      default:

        $radio = '<input type="radio" value="1" name="'.$name.'" checked>Activate&nbsp;
       <input type="radio" value="0" name="'.$name.'">Deactivate';
       }
      return $radio;
    }

     /**
     * function to get the text for partners frontend entries
     * @global      array       $_ARRAYLANG
     * @global      object      $objDatabase
     */

     function _getText($name,$catid,$language_export)
     {
       global $objDatabase,$_ARRAYLANG;
       switch($name) {

         case 'level':
             foreach($this->_arrLanguages as $intLangId => $arrValues) {
             $objResult = $objDatabase->Execute('SELECT  level
                                       FROM        '.DBPREFIX.'module_partners_user_level
                                       WHERE `id` = '.$catid.' AND `lang_id` = '.$intLangId.' LIMIT 1');
                                       }
             if($objResult->RecordCount > 0){
             while (!$objResult->EOF) {
             $txt_cat = $objResult->fields['level'];
             $objResult->MoveNext();
             }
             }else{
             $txt_cat = "";
             }
         break;
         case 'profile':
         foreach($this->_arrLanguages as $intLangId => $arrValues) {
             $objResult = $objDatabase->Execute('SELECT  profile
                                       FROM        '.DBPREFIX.'module_partners_user_profile
                                       WHERE id='.$catid.' AND `lang_id` = '.$intLangId.' LIMIT 1');
         }
         if($objResult->RecordCount > 0){
             while (!$objResult->EOF) {
                 $txt_cat = $objResult->fields['profile'];
                 $objResult->MoveNext();
             }
          }else{
                $txt_cat = "";
          }
          break;

          case 'vertical':
          foreach($this->_arrLanguages as $intLangId => $arrValues) {
             $objResult = $objDatabase->Execute('SELECT  vertical
                                       FROM        '.DBPREFIX.'module_partners_user_vertical
                                       WHERE id='.$catid.' AND `lang_id` = '.$intLangId.' LIMIT 1');
          }
          if($objResult->RecordCount > 0){
             while (!$objResult->EOF) {
                $txt_cat = $objResult->fields['vertical'];
                $objResult->MoveNext();
             }
          }else{
             $txt_cat = "";
          }
          break;

          case 'region':
          foreach($this->_arrLanguages as $intLangId => $arrValues) {
             $objResult = $objDatabase->Execute('SELECT  name
                                       FROM        '.DBPREFIX.'module_partners_user_region
                                       WHERE id='.$catid.' AND `lang_id` = '.$intLangId.' LIMIT 1');
          }
          if($objResult->RecordCount > 0){
             while (!$objResult->EOF) {
                $txt_cat = $objResult->fields['name'];
                $objResult->MoveNext();
             }
          }else{
             $txt_cat = "";
          }
          break;

          case 'certificate':
          $objResult = $objDatabase->Execute('SELECT  category_id
                                       FROM        '.DBPREFIX.'module_partners_message_to_category
                                       WHERE message_id='.$catid.'  LIMIT 1');
          if($objResult->RecordCount > 0){
                 $txt_cat_chk = $objResult->fields['category_id'];
                 $objData = $objDatabase->Execute('SELECT  name
                                     FROM        '.DBPREFIX.'module_partners_categories
                                   WHERE category_id='.$txt_cat_chk.' and `lang_id` = '.$language_export.'');
                 while (!$objData->EOF) {
                      $txt_cat .= $objData->fields['name'];
                      $objData->MoveNext();
                 }
          }else{
              $txt_cat = "";
          }
          break;

          case 'level_image':
          $objResult = $objDatabase->Execute('SELECT  imgpath
                                       FROM        '.DBPREFIX.'module_partners_user_level
                                       WHERE id='.$catid.' LIMIT 1');
          if($objResult->RecordCount > 0){
             while (!$objResult->EOF) {
                $txt_cat = $objResult->fields['imgpath'];
                $objResult->MoveNext();
             }
          }else{
             $txt_cat = "";
          }
          break;

          default:
          foreach(array_keys($this->_arrLanguages) as $intLangId) {
                $objResult = $objDatabase->Execute('SELECT  country
                                       FROM        '.DBPREFIX.'module_partners_user_country
                                       WHERE id='.$catid.' and `lang_id` = '.$intLangId.' LIMIT 1');
          }
          if($objResult->RecordCount > 0){
             while (!$objResult->EOF) {
                $txt_cat = $objResult->fields['country'];
                $objResult->MoveNext();
             }
          }else{
             $txt_cat = "";
          }
       }
       return $txt_cat;
     }


     /**
     * function to get the Id of partners certificate
     * @global      array       $_ARRAYLANG
     * @global      object      $objDatabase
     */

     function _getcertificateId(){
           global $objDatabase,$_ARRAYLANG;
           $cert = array();
           foreach(array_keys($this->_arrLanguages) as $intLangId) {
             $objData = $objDatabase->Execute('SELECT  name,imgpath,category_id
                                       FROM        '.DBPREFIX.'module_partners_categories WHERE `lang_id` = '.$intLangId.'
                                       ');
           }
           while (!$objData->EOF) {
               $cert[] = $objData->fields['category_id'];
               $objData->MoveNext();
           }
           return $cert;
      }


     /**
     * function to get the text for partners certificate
     * @global      array       $_ARRAYLANG
     * @global      object      $objDatabase
     */

     function _getcertificateText($catid){
             global $objDatabase,$_ARRAYLANG;

             $objData = $objDatabase->Execute('SELECT  name
                                       FROM        '.DBPREFIX.'module_partners_categories
                                       WHERE `category_id`="'.$catid.'"
                                       ');
             while (!$objData->EOF) {
             $cert = $objData->fields['name'];
             $objData->MoveNext();
             }
             return $cert;
     }


     /**
     * function to get the Image of partners certificate logo
     * @global      array       $_ARRAYLANG
     * @global      object      $objDatabase
     */

     function _getcertificateImage($catid){
             global $objDatabase,$_ARRAYLANG;

             $objData = $objDatabase->Execute('SELECT  imgpath
                                       FROM        '.DBPREFIX.'module_partners_categories
                                       WHERE `category_id`="'.$catid.'"
                                       ');
             while (!$objData->EOF) {
             $cert = $objData->fields['imgpath'];
             $objData->MoveNext();
             }
             return $cert;
     }



     /**
     * function to get the Image width for partners certificate from database
     * @global      array       $_ARRAYLANG
     * @global      object      $objDatabase
     */

     function _getImageWidth($setting){
       global $objDatabase,$_ARRAYLANG;
         switch($setting)
         {
          case 'level':
               $objSettings = $objDatabase->Execute('SELECT lwidth FROM '.DBPREFIX.'module_partners_settings LIMIT 1');
               $order = trim($objSettings->fields['lwidth']);
               break;
          case 'cert':
               $objSettings = $objDatabase->Execute('SELECT cwidth FROM '.DBPREFIX.'module_partners_settings LIMIT 1');
               $order = trim($objSettings->fields['cwidth']);
               break;
          default:
               $objSettings = $objDatabase->Execute('SELECT width FROM '.DBPREFIX.'module_partners_settings LIMIT 1');
               $order = trim($objSettings->fields['width']);
          }
          return $order;

     }


     /**
     * function to get the Image height for partners certificate from database
     * @global      array       $_ARRAYLANG
     * @global      object      $objDatabase
     */


     function _getImageHeight($setting){
       global $objDatabase,$_ARRAYLANG;
         switch($setting)
         {
          case 'level':
               $objSettings = $objDatabase->Execute('SELECT lheight FROM '.DBPREFIX.'module_partners_settings LIMIT 1');
               $order = trim($objSettings->fields['lheight']);
               break;
          case 'cert':
               $objSettings = $objDatabase->Execute('SELECT cheight FROM '.DBPREFIX.'module_partners_settings LIMIT 1');
               $order = trim($objSettings->fields['cheight']);
               break;
          default:
               $objSettings = $objDatabase->Execute('SELECT height FROM '.DBPREFIX.'module_partners_settings LIMIT 1');
               $order = trim($objSettings->fields['height']);
         }

         return $order;

     }


    /**
    * function to get the Category Id of partners from database
    * @global      array       $_ARRAYLANG
    * @global      object      $objDatabase
    */
    function _getCertIndImage($name,$catid,$intLangId)
    {
          global $objDatabase,$_ARRAYLANG;
        $txt_cat = array();
        $objResult = $objDatabase->Execute('SELECT  category_id
                                 FROM        '.DBPREFIX.'module_partners_message_to_category
                                 WHERE message_id='.$catid.' AND `lang_id` = '.$intLangId.' ');
        while (!$objResult->EOF) {
            $txt_cat_chk = $objResult->fields['category_id'];
            $objData = $objDatabase->Execute('SELECT  imgpath
                               FROM        '.DBPREFIX.'module_partners_categories
                               WHERE category_id='.$txt_cat_chk.' LIMIT 1');
            $txt_cat[] = $objData->fields['imgpath'];
            $objResult->MoveNext();
        }
        //print "<pre>";
        //print_r($txt_cat);
        // print "</pre>";
        return $txt_cat;
    }


     /**
     * function to create a Detail Array
     * @global      array       $_ARRAYLANG
     * @global      object      $objDatabase
     */
     function createDetailArray($intLanguageId=0, $intStartingIndex=0, $intLimitIndex=0,$intDetailid) {
        global $objDatabase,$_ARRAYLANG;
        if($_REQUEST['Submit_overview'])
        {
          if(!empty($_REQUEST['subject'])){
           $searchSubject = contrexx_addslashes(strip_tags($_REQUEST['subject']));
           $resultContent .= $_ARRAYLANG['TXT_PARTNERS_SEARCH_RESULTS'];
          }


          if($_REQUEST['level'] != 0) {
            $searchLevel = htmlentities($_REQUEST['level'],ENT_QUOTES,CONTREXX_CHARSET);
          }

          if($_REQUEST['profile'] != 0) {
            $searchProfile = htmlentities($_REQUEST['profile'],ENT_QUOTES,CONTREXX_CHARSET);
           }

          if($_REQUEST['country'] != 0) {
           $searchCountry = htmlentities($_REQUEST['country'],ENT_QUOTES,CONTREXX_CHARSET);
           }

          if($_REQUEST['vertical'] != 0) {
           $searchVertical = htmlentities($_REQUEST['vertical'],ENT_QUOTES,CONTREXX_CHARSET);
           }

          if(!empty($resultContent))
          {
             $this->_strOkMessage = $resultContent;
          }
          else{
           $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_SEARCH_ERROR'];
          }
         }

         $arrReturn = array();

        if (intval($intLanguageId) > 0) {

            $strLanguageJoin  = '     INNER JOIN    '.DBPREFIX.'module_partners_create_lang    AS partnerscreateLanguage
                                    ON            partnerscreate.message_id = partnerscreateLanguage.message_id
                                ';
            $strLanguageWhere = '    WHERE     partnerscreateLanguage.lang_id='.$intLanguageId.' AND
                                            partnerscreateLanguage.is_active="1"
                                ';

         } else {

            $strLanguageJoin = '';
            $strLanguageWhere = '';
         }

         if ($intLimitIndex == 0) {
            $intLimitIndex = $this->countEntries();
         }
         $objSettings = $objDatabase->Execute('SELECT sortorder FROM '.DBPREFIX.'module_partners_settings LIMIT 1');
         $order = "partnersMain.".trim(strtolower($objSettings->fields['sortorder']));


         $objResult = $objDatabase->Execute('SELECT        partnersMessages.message_id,
                                                        partnersMessages.user_id,
                                                        partnersMessages.time_created,
                                                        partnersMessages.time_edited,
                                                        partnersMessages.hits,
                                                        user.username,
                                                        partnersMain.level,
                                                        partnersMain.subject
                                            FROM        '.DBPREFIX.'module_partners_create        AS partnersMessages
                                            INNER JOIN    '.DBPREFIX.'access_users                AS user
                                            INNER JOIN    '.DBPREFIX.'module_partners_create_lang    AS partnersMain
                                            ON   partnersMessages.message_id = partnersMain.message_id
                                            '.$strLanguageJoin.'
                                            '.$strLanguageWhere.'
                                            WHERE  partnersMain.message_id = '.$intDetailid.'
                                            ORDER BY '.$order.' ASC
                                            LIMIT   '.$intStartingIndex.','.$intLimitIndex.'
                                        ');

         if ($objResult->RecordCount() > 0) {

            while (!$objResult->EOF) {

                $intMessageId = $objResult->fields['message_id'];

                $arrReturn[$intMessageId] = array(    'user_id'            =>    $objResult->fields['user_id'],
                                                    'user_name'            =>    htmlentities(stripslashes($objResult->fields['username']),ENT_QUOTES, CONTREXX_CHARSET),
                                                    'time_created'        =>    date(ASCMS_DATE_FORMAT,$objResult->fields['time_created']),
                                                    'time_created_ts'    =>    $objResult->fields['time_created'],
                                                    'time_edited'        =>    date(ASCMS_DATE_FORMAT,$objResult->fields['time_edited']),
                                                    'time_edited_ts'    =>    $objResult->fields['time_edited'],
                                                    'hits'                =>    $objResult->fields['hits'],
                                                    'subject'            =>    '',
                                                    'level'                 =>    '',
                                                    'profile'            =>    '',
                                                    'country'            =>    '',
                                                    'vertical'            =>    '',
                                                    'contactname'        =>    '',
                                                    'email'                =>    '',
                                                    'website'            =>    '',
                                                    'address1'            =>    '',
                                                    'address2'            =>    '',
                                                    'city'                =>    '',
                                                    'zipcode'            =>    '',
                                                    'phone'                =>    '',
                                                    'fax'                =>    '',
                                                    'reference'             =>    '',
                                                    'quote'             =>  '',
                                                    'category'          =>  '',
                                                    'categories'        =>    array(),
                                                    'translation'        =>    array()
                                                );

                //Get vote-avarage for this entry

                  //Fill the translation-part of the return-array with default values
                foreach (array_keys($this->_arrLanguages) as $intLanguageId) {
                    $arrReturn[$intMessageId]['categories'][$intLanguageId] = array();
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['is_active']     = 0;
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['subject']     = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['content']     = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['level']        = '';
                       $arrReturn[$intMessageId]['translation'][$intLanguageId]['profile']     = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['country']     = '';
                     $arrReturn[$intMessageId]['translation'][$intLanguageId]['vertical']     = '';
                     $arrReturn[$intMessageId]['translation'][$intLanguageId]['contactname'] = '';
                     $arrReturn[$intMessageId]['translation'][$intLanguageId]['email']         = '';
                     $arrReturn[$intMessageId]['translation'][$intLanguageId]['website']     = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['address1']    = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['address2']     = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['city']         = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['zipcode']     = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['phone']         = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['fax']         = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['reference']   = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['quote']         = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['category']         = '';
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['image']         = '';
                }

                //Get assigned categories for this entry
                $objCategoryResult = $objDatabase->Execute('SELECT    category_id,
                                                                    lang_id
                                                            FROM    '.DBPREFIX.'module_partners_message_to_category
                                                            WHERE    message_id='.$intMessageId.'
                                                        ');
                while (!$objCategoryResult->EOF) {
                    $arrReturn[$intMessageId]['categories'][$objCategoryResult->fields['lang_id']][$objCategoryResult->fields['category_id']] = true;

                    $objCategoryResult->MoveNext();
                }

                //Get existing translations for the current entry
                $objResult = $objDatabase->Execute('SELECT    lang_id,
                                                                    is_active,
                                                                    subject,
                                                                    level,
                                                                    profile,
                                                                    country,
                                                                    vertical,
                                                                    contactname,
                                                                    email,
                                                                    website,
                                                                    address1,
                                                                    address2,
                                                                    city,
                                                                    zipcode,
                                                                    phone,
                                                                    fax,
                                                                    reference,
                                                                    quote,
                                                                    content,
                                                                    image
                                                            FROM    '.DBPREFIX.'module_partners_create_lang
                                                            WHERE    message_id='.$intMessageId.' AND (subject like "'.'%'.$searchSubject.'%'.'"
                                                            OR content like "'.'%'.$searchSubject.'%'.'" OR address1 like "'.'%'.$searchSubject.'%'.'" OR address2 like "'.'%'.$searchSubject.'%'.'")
                                                            AND (level like "'.'%'.$searchLevel.'%'.'" AND profile like "'.'%'.$searchProfile.'%'.'" AND country like "'.'%'.$searchCountry.'%'.'"
                                                            AND vertical like "'.'%'.$searchVertical.'%'.'")
                                                        ');

                    while (!$objResult->EOF) {
                    $intLanguageId = $objResult->fields['lang_id'];

                    if ( ($intLanguageId == $this->_intLanguageId && !empty($objResult->fields['subject'])) ||
                         empty($arrReturn[$intMessageId]['subject']) )
                    {
                        $arrReturn[$intMessageId]['subject'] = htmlentities(stripslashes($objResult->fields['subject']), ENT_QUOTES, CONTREXX_CHARSET);
                    }

                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['is_active']          = $objResult->fields['is_active'];
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['subject']         = htmlentities(stripslashes($objResult->fields['subject']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['content']         = $objResult->fields['content'];
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['level']             = htmlentities(stripslashes($objResult->fields['level']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['profile']         = htmlentities(stripslashes($objResult->fields['profile']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['country']         = htmlentities(stripslashes($objResult->fields['country']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['vertical']         = htmlentities(stripslashes($objResult->fields['vertical']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['category']         = htmlentities(stripslashes($objResult->fields['name']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['contactname']     = htmlentities(stripslashes($objResult->fields['contactname']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['email']             = htmlentities(stripslashes($objResult->fields['email']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['website']         = htmlentities(stripslashes($objResult->fields['website']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['address1']         = htmlentities(stripslashes($objResult->fields['address1']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['address2']         = htmlentities(stripslashes($objResult->fields['address2']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['city']             = htmlentities(stripslashes($objResult->fields['city']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['zipcode']         = htmlentities(stripslashes($objResult->fields['zipcode']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['phone']             = htmlentities(stripslashes($objResult->fields['phone']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['fax']             = htmlentities(stripslashes($objResult->fields['fax']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['reference']         = htmlentities(stripslashes($objResult->fields['reference']), ENT_QUOTES, CONTREXX_CHARSET);
                    $arrReturn[$intMessageId]['translation'][$intLanguageId]['quote']             = htmlentities(stripslashes($objResult->fields['quote']), ENT_QUOTES, CONTREXX_CHARSET);
                       $arrReturn[$intMessageId]['translation'][$intLanguageId]['image']             = htmlentities(stripslashes($objResult->fields['image']), ENT_QUOTES, CONTREXX_CHARSET);

                    $objResult->MoveNext();
                }

                 $arrReturn['count'] +=    $objResult->RecordCount();
                $objResult->MoveNext();
            }

        }


        return $arrReturn;
    }


        //functions of the class
    function ExportExcel($f_name) //constructor
    {
        $this->filename=$f_name;
    }
    function setHeadersAndValues($hdrs,$all_vals) //set headers and query
    {
        $this->titles=$hdrs;
        $this->all_values=$all_vals;
    }
    function GenerateExcelFile() //function to generate excel file
    {

        foreach ($this->titles as $title_val)
         {
             $header .= $title_val."\t";
         }
         for($i=0;$i<sizeof($this->all_values);$i++)
         {
             $line = '';
             foreach($this->all_values[$i] as $value)
            {
                 if ((!isset($value)) OR ($value == ""))
                {
                     $value = "\t";
                 } //end of if
                else
                {
                     $value = str_replace('"', '""', $value);
                     $value = '"' . $value . '"' . "\t";
                 } //end of else
                 $line .= $value;
             } //end of foreach
             $data .= trim($line)."\n";
         }//end of the while
         $data = str_replace("\r", "", $data);
        if ($data == "")
         {
             $data = "\n(0) Records Found!\n";
         }
   return "$header\n$data";



    }

    /**
    * This function is used to get the every categories new name
    * @global  array       $_ARRAYLANG
    * @global  object      $objDatabase
    */
    function _getCategoryname($categoryId){
        global $_ARRAYLANG, $objDatabase;

        $objResult =   $objDatabase->Execute('SELECT  name
                                       FROM        '.DBPREFIX.'module_partners_categories_name
                                       WHERE id='.$categoryId.' LIMIT 1');
        while(!$objResult->EOF){
            $catname = $objResult->fields['name'];
            $objResult->MoveNext();
        }
        return $catname;
    }

    /**
    * This function is used to get the country names
    * @global  array       $_ARRAYLANG
    * @global  object      $objDatabase
    */


    function _getTitlecountry($categoryId){
        global $_ARRAYLANG, $objDatabase;

        $objResult =   $objDatabase->Execute('SELECT  country
                                       FROM        '.DBPREFIX.'module_partners_user_country
                                       WHERE id='.$categoryId.' LIMIT 1');
        while(!$objResult->EOF){
            $catcountry = $objResult->fields['country'];
            $objResult->MoveNext();
        }
        return $catcountry;

    }

    /**
     * Writes RSS feed containing the latest N messages to the feed-directory. This is done for every language seperately.
     * @global  array       $_CONFIG
     * @global  array       $_ARRAYLANG
     */

    function writeMessageRSS() {
        global $_CONFIG, $_ARRAYLANG;

        if (intval($this->_arrSettings['blog_rss_activated'])) {

            require_once ASCMS_FRAMEWORK_PATH.'/RSSWriter.class.php';

            foreach ($this->_arrLanguages as $intLanguageId => $arrLanguageValues) {
                $arrEntries = $this->createEntryArray($intLanguageId, 0, intval($this->_arrSettings['blog_rss_messages']) );
                $strItemLink = 'http://'.$_CONFIG['domainUrl'].($_SERVER['SERVER_PORT'] == 80 ? '' : ':'.intval($_SERVER['SERVER_PORT'])).ASCMS_PATH_OFFSET.'/'.FWLanguage::getLanguageParameter($intLanguageId, 'lang').'/'.CONTREXX_DIRECTORY_INDEX.'?section=blog&amp;cmd=details&amp;id=';

                if (count($arrEntries) > 0) {
                    $objRSSWriter = new RSSWriter();

                    $objRSSWriter->characterEncoding = CONTREXX_CHARSET;
                    $objRSSWriter->channelTitle = $_CONFIG['coreGlobalPageTitle'].' - '.$_ARRAYLANG['TXT_BLOG_LIB_RSS_MESSAGES_TITLE'];
                    $objRSSWriter->channelLink = 'http://'.$_CONFIG['domainUrl'].($_SERVER['SERVER_PORT'] == 80 ? '' : ':'.intval($_SERVER['SERVER_PORT'])).ASCMS_PATH_OFFSET.'/'.FWLanguage::getLanguageParameter($intLanguageId, 'lang').'/'.CONTREXX_DIRECTORY_INDEX.'?section=blog';
                    $objRSSWriter->channelDescription = $_CONFIG['coreGlobalPageTitle'].' - '.$_ARRAYLANG['TXT_BLOG_LIB_RSS_MESSAGES_TITLE'];
                    $objRSSWriter->channelLanguage = FWLanguage::getLanguageParameter($intLanguageId, 'lang');
                    $objRSSWriter->channelCopyright = 'Copyright '.date('Y').', http://'.$_CONFIG['domainUrl'];
                    $objRSSWriter->channelWebMaster = $_CONFIG['coreAdminEmail'];

                    foreach ($arrEntries as $intEntryId => $arrEntryValues) {
                        $objRSSWriter->addItem(
                            htmlspecialchars($arrEntryValues['subject'], ENT_QUOTES, CONTREXX_CHARSET),
                            $strItemLink.$intEntryId,
                            htmlspecialchars($arrEntryValues['translation'][$intLanguageId]['content'], ENT_QUOTES, CONTREXX_CHARSET),
                            htmlspecialchars($arrEntryValues['user_name'], ENT_QUOTES, CONTREXX_CHARSET),
                            '',
                            '',
                            '',
                            '',
                            $arrEntryValues['time_created_ts'],
                            ''
                        );
                    }

                    $objRSSWriter->xmlDocumentPath = ASCMS_FEED_PATH.'/blog_messages_'.$arrLanguageValues['short'].'.xml';
                    $objRSSWriter->write();

                    @chmod(ASCMS_FEED_PATH.'/blog_messages_'.$arrLanguageValues['short'].'.xml', 0777);
                }
            }
        }
    }

        /**
     * Writes RSS feed containing the latest N messages of each category the feed-directory. This is done for every language seperately.
     *
     * @global     array        $_CONFIG
     * @global     array        $_ARRAYLANG
     */

    function writeCategoryRSS()
    {
        global $_CONFIG, $_ARRAYLANG;

        if (intval($this->_arrSettings['blog_rss_activated'])) {

            require_once ASCMS_FRAMEWORK_PATH.'/RSSWriter.class.php';

            $arrCategories = $this->createCategoryArray();

            //Iterate over all languages
            foreach ($this->_arrLanguages as $intLanguageId => $arrLanguageValues) {
                $strItemLink = 'http://'.$_CONFIG['domainUrl'].($_SERVER['SERVER_PORT'] == 80 ? '' : ':'.intval($_SERVER['SERVER_PORT'])).ASCMS_PATH_OFFSET.'/'.FWLanguage::getLanguageParameter($intLanguageId, 'lang').'/'.CONTREXX_DIRECTORY_INDEX.'?section=blog&amp;cmd=details&amp;id=';

                $arrEntries = $this->createEntryArray($intLanguageId);

                //If there exist entries in this language go on, otherwise skip
                if (count($arrEntries) > 0) {

                    //Iterate over all categories
                    foreach ($arrCategories as $intCategoryId => $arrCategoryTranslation) {

                        //If the category is activated in this language, find assigned messages
                        if ($arrCategoryTranslation[$intLanguageId]['is_active']) {

                            $intNumberOfMessages = 0; //Counts found messages for this category

                            $objRSSWriter = new RSSWriter();
                            $objRSSWriter->characterEncoding = CONTREXX_CHARSET;
                            $objRSSWriter->channelTitle = $_CONFIG['coreGlobalPageTitle'].' - '.$_ARRAYLANG['TXT_BLOG_LIB_RSS_MESSAGES_TITLE'];
                            $objRSSWriter->channelLink = 'http://'.$_CONFIG['domainUrl'].($_SERVER['SERVER_PORT'] == 80 ? '' : ':'.intval($_SERVER['SERVER_PORT'])).ASCMS_PATH_OFFSET.'/'.FWLanguage::getLanguageParameter($intLanguageId, 'lang').'/'.CONTREXX_DIRECTORY_INDEX.'?section=blog';
                            $objRSSWriter->channelDescription = $_CONFIG['coreGlobalPageTitle'].' - '.$_ARRAYLANG['TXT_BLOG_LIB_RSS_MESSAGES_TITLE'].' ('.$arrCategoryTranslation[$intLanguageId]['name'].')';
                            $objRSSWriter->channelCopyright = 'Copyright '.date('Y').', http://'.$_CONFIG['domainUrl'];
                            $objRSSWriter->channelLanguage = FWLanguage::getLanguageParameter($intLanguageId, 'lang');
                            $objRSSWriter->channelWebMaster = $_CONFIG['coreAdminEmail'];

                            //Find assigned messages
                            foreach ($arrEntries as $intEntryId => $arrEntryValues) {
                                if ($this->categoryMatches($intCategoryId, $arrEntryValues['categories'][$intLanguageId])) {
                                    //Message is in category, add to feed
                                    $objRSSWriter->addItem(
                                        htmlspecialchars($arrEntryValues['subject'], ENT_QUOTES, CONTREXX_CHARSET),
                                        $strItemLink.$intEntryId,
                                        htmlspecialchars($arrEntryValues['translation'][$intLanguageId]['content'], ENT_QUOTES, CONTREXX_CHARSET),
                                        htmlspecialchars($arrEntryValues['user_name'], ENT_QUOTES, CONTREXX_CHARSET),
                                        '',
                                        '',
                                        '',
                                        '',
                                        $arrEntryValues['time_created_ts'],
                                        ''
                                    );

                                    $intNumberOfMessages++;

                                    //Check for message-limit
                                    if ($intNumberOfMessages >= intval($this->_arrSettings['blog_rss_messages'])) {
                                        break;
                                    }
                                }
                            }

                            $objRSSWriter->xmlDocumentPath = ASCMS_FEED_PATH.'/blog_category_'.$intCategoryId.'_'.$arrLanguageValues['short'].'.xml';
                            $objRSSWriter->write();

                            @chmod(ASCMS_FEED_PATH.'/blog_category_'.$intCategoryId.'_'.$arrLanguageValues['short'].'.xml', 0777);
                        }

                    }

                }
            }
        }
    }
}

?>
