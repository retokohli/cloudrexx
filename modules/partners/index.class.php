<?php
/**
 * Partners
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Sureshkumar.C
 * @version     v 1.01
 * @package     contrexx
 * @subpackage  module_partners
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH.'/partners/lib/partnersLib.class.php';



class Partners extends PartnersLibrary  {

    var $_objTpl;
    var $_strStatusMessage = '';
    var $_strErrorMessage = '';



    /**
    * Constructor   -> Call parent-constructor, set language id and create local template-object
    *
    * @global   integer     $_LANGID
    */
    function __construct($strPageContent)
    {
        global $_LANGID;

        PartnersLibrary::__construct();

        $this->_intLanguageId = intval($_LANGID);

        $this->_objTpl = new HTML_Template_Sigma('.');
        CSRF::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
        $this->_objTpl->setTemplate($strPageContent);
    }


    /**
    * Reads $_GET['cmd'] and selects (depending on the value) an action
    *
    */
    function getPage()
    {
        if(!isset($_GET['cmd'])) {
            $_GET['cmd'] = '';
        }


        switch ($_GET['cmd']) {
            case 'detail':
                $this->showDetails($_GET['id']);
                break;
            default:
                $this->showEntries();
                break;
        }

        return $this->_objTpl->get();
    }



     /**
     * Shows all existing entries of the blog in descending order.
     *
     * @global  array       $_ARRAYLANG
     * @global  object      $objDatabase
     */
   function showEntries() {
        global $_ARRAYLANG,$objDatabase;

        $recipientTitle_Level    = trim($_REQUEST['level']);
        $recipientTitle_Profile  = trim($_REQUEST['profile']);
        $recipientTitle_Country  = trim($_REQUEST['country']);
        $recipientTitle_Vertical = trim($_REQUEST['vertical']);
        $titleName_level         = "level";
        $titleName_profile       = "profile";
        $titleName_country       = "country";
        $titleName_vertical      = "vertical";

        $this->_objTpl->setVariable(array(
            'TXT_HELLO_WORLD'           => $_ARRAYLANG['TXT_HELLO_WORLD'],
            'TXT_PARTNERS_LEVEL'        => $_ARRAYLANG['TXT_PARTNERS_LEVEL'],
            'TXT_PARTNERS_PROFILE'      => $_ARRAYLANG['TXT_PARTNERS_PROFILE'],
            'TXT_PARTNERS_COUNTRY'      => $_ARRAYLANG['TXT_PARTNERS_COUNTRY'],
            'PARTNERS_LEVEL'    		=> $this->_getListLevelMenu($recipientTitle_Level,$titleName_level,'name="level" size="1" style="width:90px;"','All'),
            'PARTNERS_PROFILE'    		=> $this->_getListLevelMenu($recipientTitle_Profile,$titleName_profile,'name="profile" size="1"','All'),
            'PARTNERS_COUNTRY'    		=> $this->_getListLevelMenu($recipientTitle_Country,$titleName_country,'name="country" size="1"','All'),
            'PARTNERS_VERTICAL'    		=> $this->_getListLevelMenu($recipientTitle_Vertical,$titleName_vertical,'name="vertical" size="1"','All'),
            'TXT_PARTNERS_VERTICAL'     => $_ARRAYLANG['TXT_PARTNERS_VERTICAL']
        ));

        $intSelectedCategory = (isset($_GET['catId'])) ? intval($_GET['catId']) : 0;
   		$intPagingPosition = (isset($_GET['pos'])) ? intval($_GET['pos']) : 0;

        /**Array of result returned from the Partnerlib.class...
          *It contains the ordered form of level...
          */

   		$arrEntries = $this->createEntryArrayFrontEnd(0, $intPagingPosition, $this->getPagingLimit());

        $cert_catid =  $this->_getcertificateId();
        foreach($cert_catid as $iicertKey => $iicertValue){
              $this->_objTpl->setVariable(array(
                                          'PARTNERS_CERTIFICATE_ALL'     => $this->_getcertificateText($iicertValue),
                                          'PARTNERS_CERTIFICATE_IMG'     => $this->_getcertificateImage($iicertValue),
                                          'PARTNERS_IMAGE_WIDTH_CERT'    => $this->_getImageWidth('cert'),
                                          'PARTNERS_IMAGE_HEIGHT_CERT'   => $this->_getImageHeight('cert')
                 ));
                 $this->_objTpl->parse('partnersCertificate');
         }
   		 if (count($arrEntries) > 0) {

           	 $intRowClass = 0;


         /**$intEntryId => is the variable of the Message ID of the Partners...
           *$LevelMsgID => is the Particular level of the Message ID.....
           */

        foreach ($arrEntries as $intEntryId => $newarrEntryValues) {

            foreach($newarrEntryValues as $LevelMsgID=>$arrEntryValues) {

                  if(!empty($arrEntryValues['subject'])) {

	   			                 $this->_objTpl->setVariable(array(
	   				                                        'TXT_IMGALT_EDIT'		=>	$_ARRAYLANG['TXT_BLOG_ENTRY_EDIT_TITLE'],
	   				                                        'TXT_IMGALT_DELETE'		=>	$_ARRAYLANG['TXT_BLOG_ENTRY_DELETE_TITLE']
                                 ));

               	        //Check active languages
                      $strActiveLanguages = '';
	   			      foreach ($arrEntryValues['translation'] as $intLangId => $arrEntryTranslations) {
	   			        $this->_objTpl->setVariable(array('ENTRY_STATUS' =>  $this->_getStatus($arrEntryTranslations['status'])));
	   				    if ($arrEntryTranslations['is_active'] && key_exists($intLangId,$this->_arrLanguages)) {
	   					   $strActiveLanguages .= '['.$this->_arrLanguages[$intLangId]['short'].']&nbsp;&nbsp;';

	   				    }
	   			     }


                     /**Checking with the Active State of the Message ID....
                       */

	   			     $is_active = $arrEntries[$intEntryId][$LevelMsgID]['translation'][$intLangId]['status'];

	   			     if($is_active!=0)  {

     			        $strActiveLanguages = substr($strActiveLanguages,0,-12);

                        $level_id = $arrEntries[$intEntryId][$LevelMsgID]['translation'][$intLangId]['level'];

                         /**Selecting the Display Properties of the Particular Message Id form the Table...
                           */

                        $objDisplayResult = $objDatabase->Execute('SELECT display_title,display_content,display_contactname,display_country,display_phone,display_address1,display_address2,display_city,display_zipcode,display_certificate_logo,display_logo,display_level_logo,display_level_text,display_quote
                                                      FROM '.DBPREFIX.'module_partners_display WHERE `display_level_id` = "'.$LevelMsgID.'" ');
                        while(!$objDisplayResult->EOF) {

                            $Displaytitle           = $objDisplayResult->fields['display_title'];
                            $Displaycontent         = $objDisplayResult->fields['display_content'];
                            $Displaycontactname     = $objDisplayResult->fields['display_contactname'];
                            $Displaycountry         = $objDisplayResult->fields['display_country'];
                            $Displayphone           = $objDisplayResult->fields['display_phone'];
                            $Displayaddress1        = $objDisplayResult->fields['display_address1'];
                            $Displayaddress2        = $objDisplayResult->fields['display_address2'];
                            $Displaycity            = $objDisplayResult->fields['display_city'];
                            $Displayzipcode         = $objDisplayResult->fields['display_zipcode'];
                            $Displayclogo           = $objDisplayResult->fields['display_certificate_logo'];
                            $Displaylogo            = $objDisplayResult->fields['display_logo'];
                            $Displayllogo           = $objDisplayResult->fields['display_level_logo'];
                            $Displaylevel           = $objDisplayResult->fields['display_level_text'];
                            $Displayquote           = $objDisplayResult->fields['display_quote'];
                            $objDisplayResult->MoveNext();
                        }

                        if($Displaytitle!=0) {

                            $this->_objTpl->setVariable(array(
                	                  'PARTNERS_TITLE'  => $arrEntries[$intEntryId][$LevelMsgID]['translation'][$intLangId]['subject']
                            ));
                        }
                        if($Displaycontent!=0) {

                            $this->_objTpl->setVariable(array(
                                      'PARTNERS_CONTENT'=> $arrEntries[$intEntryId][$LevelMsgID]['translation'][$intLangId]['content']
                            ));
                        }

                        if($Displaycontactname!=0) {

                            $this->_objTpl->setVariable(array(
                                     'PARTNERS_CONTACTNAME' =>  $arrEntries[$intEntryId][$LevelMsgID]['translation'][$intLangId]['contactname']
                            ));
                        }

                        if($Displaycountry!=0) {

                            $this->_objTpl->setVariable(array(
                                    'PARTNERS_COUNTRY_TXT'  =>  $this->_getText('country',$arrEntries[$intEntryId][$LevelMsgID]['translation'][$intLangId]['country'])
                           ));
                        }

                        if($Displayphone!=0) {
                            if($arrEntries[$intEntryId][$LevelMsgID]['translation'][$intLangId]['phone']!= ""){
                            $this->_objTpl->setVariable(array(
                                    'TXT_PARTNERS_PHONE'    => $_ARRAYLANG['TXT_PARTNERS_PHONES'],
                                    'PARTNERS_PHONE'        =>  $_ARRAYLANG['TXT_PARTNERS_PHONES_PLUS'].$arrEntries[$intEntryId][$LevelMsgID]['translation'][$intLangId]['phone']
                            ));
                            }
                        }

                        if($Displayaddress1!=0) {

                            $this->_objTpl->setVariable(array(
                                    'PARTNERS_ADDRESS1'    =>  $arrEntries[$intEntryId][$LevelMsgID]['translation'][$intLangId]['address1']
                            ));
                        }

                        if($Displayaddress2!=0)  {

                            $this->_objTpl->setVariable(array(
                                     'PARTNERS_ADDRESS2'    =>  $arrEntries[$intEntryId][$LevelMsgID]['translation'][$intLangId]['address2'],
                            ));
                        }

                        if($Displaycity!=0)  {

                            $this->_objTpl->setVariable(array(
                                    'PARTNERS_CITY'         =>  $arrEntries[$intEntryId][$LevelMsgID]['translation'][$intLangId]['city']
                            ));
                        }

                        if($Displayzipcode!=0)  {

                            $this->_objTpl->setVariable(array(
                                   'PARTNERS_ZIPCODE'        =>  $arrEntries[$intEntryId][$LevelMsgID]['translation'][$intLangId]['zipcode']
                           ));
                        }


                        if($Displaylogo!=0)  {

                            $this->_objTpl->setVariable(array(
                                   'PARTNERS_LOGO_PATH' =>  $arrEntries[$intEntryId][$LevelMsgID]['translation'][$intLangId]['image']
                           ));
                           $this->_objTpl->parse('showLogo');
                        }


                        if($Displayllogo!=0)  {

                            $this->_objTpl->setVariable(array(
                                  'PARTNERS_LEVEL_IMAGE'          =>  $arrEntries[$intEntryId][$LevelMsgID]['translation'][$intLangId]['level_image']
                            ));
                        }


                        if($Displaylevel!=0)  {


                            $this->_objTpl->setVariable(array(
                                 'PARTNERS_LEVEL_TXT'  =>  $arrEntries[$intEntryId][$LevelMsgID]['translation'][$intLangId]['level']."&nbsp;Partner"
                            ));
                           $this->_objTpl->parse('showLogo');
                        }


                        if($Displayquote!=0)   {

                            $this->_objTpl->setVariable(array(
                                  'PARTNERS_QUOTE'       =>  $arrEntries[$intEntryId][$LevelMsgID]['translation'][$intLangId]['quote']
                            ));
                        }

                        $this->_objTpl->setVariable(array(
                        'ENTRY_ROWCLASS'		        =>	($intRowClass % 2 == 0) ? 'row1' : 'row2',
	   				    'PARTNERS_ID'				    =>	$intEntryId,
                        'PARTNERS_IMAGE_WIDTH'          =>  $this->_getImageWidth(),
                        'PARTNERS_IMAGE_HEIGHT'         =>  $this->_getImageHeight(),
                        'PARTNERS_IMAGE_WIDTH_LEVEL'    =>  $this->_getImageWidth('level'),
                        'PARTNERS_IMAGE_HEIGHT_LEVEL'   =>  $this->_getImageHeight('level'),
                        'PARTNERS_VERTICAL_TXT'         =>  $arrEntries[$intEntryId][$LevelMsgID]['translation'][$intLangId]['vertical'],
                        'PARTNERS_PROFILE_TXT'          =>  $arrEntries[$intEntryId][$LevelMsgID]['translation'][$intLangId]['profile'],
                        'PARTNERS_EMAIL'                =>  $arrEntries[$intEntryId][$LevelMsgID]['translation'][$intLangId]['email'],
                        'PARTNERS_WEBSITE'              =>  $arrEntries[$intEntryId][$LevelMsgID]['translation'][$intLangId]['website'],
                        'PARTNERS_FAX'                  =>  $arrEntries[$intEntryId][$LevelMsgID]['translation'][$intLangId]['fax'],
                        'PARTNERS_REFERENCE'            =>  $arrEntries[$intEntryId][$LevelMsgID]['translation'][$intLangId]['reference'],
	   				    'ENTRY_LANGUAGES'		        =>	$strActiveLanguages,
                        'ENTRY_USER'			        =>	$arrEntryValues['user_name']
	   			        ));


                        if($Displayclogo!=0)  {

                            $cert_ind  = $this->_getCertIndImage('certificate_image',$intEntryId,$intLangId);

                            foreach($cert_ind as $cert_indKey => $cert_indValue) {


                                   $this->_objTpl->setVariable(array(
                                                                     'PARTNERS_CERTIFICATE_IMAGE' =>  $cert_indValue,
                                                                     'PARTNERS_IMAGE_WIDTH_CERT'    => $this->_getImageWidth('cert'),
                                                                     'PARTNERS_IMAGE_HEIGHT_CERT'    => $this->_getImageHeight('cert')
                                   ));
                                   $this->_objTpl->parse('partnersIndCertificate');
                            }
                        }

                        $this->_objTpl->parse('showEntries');
                        $intRowClass++;
	   			    }
                }
            }
        }


        if($intRowClass>1) {

            $this->_objTpl->setVariable(array('PARTNERS_COUNT' => $_ARRAYLANG['PARTNERS_COUNT']."&nbsp;".$intRowClass."&nbsp;".$_ARRAYLANG['PARTNERS_NAME']));

        }
        else if($intRowClass==1) {

  		    $this->_objTpl->setVariable(array('PARTNERS_COUNT' => $_ARRAYLANG['PARTNERS_COUNT']."&nbsp;".$intRowClass."&nbsp;".$_ARRAYLANG['PARTNERS_NAME']));
       	}
        else {
  		    $this->_objTpl->setVariable(array('PARTNERS_COUNT' => $_ARRAYLANG['PARTNERS_COUNT_ERROR']));
        }

	   	//	Show paging if needed
   		if ($this->PaginactionCount > $this->getPagingLimit()) {
		  		$strPaging = getPaging( $this->PaginactionCount, $intPagingPosition, '&amp;section=partners', '<strong>'.$_ARRAYLANG['TXT_PARTNERS_ENTRY_MANAGE_PAGING'].'</strong>', true, $this->getPagingLimit());
		   		$this->_objTpl->setVariable('ENTRIES_PAGING', $strPaging);
   		}
    }
    else {
           		    $this->_objTpl->setVariable(array('PARTNERS_COUNT' => $_ARRAYLANG['PARTNERS_COUNT_ERROR']));
    }


   }


    /**
     * Shows detail-page (content, voting & comments) for a single message. It checks also for new comments (POST) or votings (GET).
     *
     * @global  array       $_ARRAYLANG
     * @global  object      $objDatabase
     * @global  array       $_CONFIG
     * @param   integer     $intMessageId: The details of this page will be shown
     */
     function showDetails($id) {
        global $_ARRAYLANG, $objDatabase, $_CONFIG;

        $intDetailid = (isset($_GET['detId'])) ? intval($_GET['detId']) : 0;
        $intSelectedCategory = (isset($_GET['catId'])) ? intval($_GET['catId']) : 0;
   		$intPagingPosition = (isset($_GET['pos'])) ? intval($_GET['pos']) : 0;
        $arrEntries = $this->createDetailArray(0, $intPagingPosition, $this->getPagingLimit(),$intDetailid);

   		if (count($arrEntries) > 0) {

             $this->_objTpl->setVariable(array(
                 'PARTNERS_DETAIL_TITLE'         => $_ARRAYLANG['PARTNERS_DETAIL_TITLE'],
                 'PARTNERS_DETAIL_CONTACT_TXT'   => $_ARRAYLANG['PARTNERS_DETAIL_CONTACT_TXT'],
                 'PARTNERS_DETAIL_PHONE_TXT'     => $_ARRAYLANG['PARTNERS_DETAIL_PHONE_TXT'],
                 'PARTNERS_DETAIL_FAX_TXT'       => $_ARRAYLANG['PARTNERS_DETAIL_FAX_TXT'],
                 'PARTNERS_DETAIL_WEBSITE_TXT'   => $_ARRAYLANG['PARTNERS_DETAIL_WEBSITE_TXT'],
                 ));
   		     $intRowClass = 1;

   			 foreach ($arrEntries as $intEntryId => $arrEntryValues) {

                if(!empty($arrEntryValues['subject']))
                {
	   			 $this->_objTpl->setVariable(array(
	   				'TXT_IMGALT_EDIT'		=>	$_ARRAYLANG['TXT_BLOG_ENTRY_EDIT_TITLE'],
	   				'TXT_IMGALT_DELETE'		=>	$_ARRAYLANG['TXT_BLOG_ENTRY_DELETE_TITLE']
	   			 ));

	   			 //Check active languages
	   			 $strActiveLanguages = '';
	   			 foreach ($arrEntryValues['translation'] as $intLangId => $arrEntryTranslations) {
	   			 $this->_objTpl->setVariable(array('ENTRY_STATUS' =>  $this->_getStatus($arrEntryTranslations['is_active'])));
	   				if ($arrEntryTranslations['is_active'] && key_exists($intLangId,$this->_arrLanguages)) {
	   					$strActiveLanguages .= '['.$this->_arrLanguages[$intLangId]['short'].']&nbsp;&nbsp;';
	   				}
	   			 }
	   			 $strActiveLanguages = substr($strActiveLanguages,0,-12);
                 $this->_objTpl->setVariable(array(
                    'ENTRY_ROWCLASS'		     =>	($intRowClass % 2 == 0) ? 'row1' : 'row2',
	   				'ENTRY_ID'				     =>	 $intEntryId,
	   				'PARTNERS_TITLE'             =>  $arrEntries[$intEntryId]['translation'][$intLangId]['subject'],
                    'PARTNERS_LEVEL_TXT'         =>  $this->_getText('level',$arrEntries[$intEntryId]['translation'][$intLangId]['level']),
                    'PARTNERS_COUNTRY_TXT'       =>  $this->_getText('country',$arrEntries[$intEntryId]['translation'][$intLangId]['country']),
                    'PARTNERS_LEVEL_IMAGE'       =>  $this->_getText('level_image',$arrEntries[$intEntryId]['translation'][$intLangId]['level']),
                    'PARTNERS_CERTIFICATE_IMAGE' =>  $this->_getText('certificate_image',$intEntryId),
                    'PARTNERS_VERTICAL_TXT'      =>  $arrEntries[$intEntryId]['translation'][$intLangId]['vertical'],
                    'PARTNERS_PROFILE_TXT'       =>  $arrEntries[$intEntryId]['translation'][$intLangId]['profile'],
                    'PARTNERS_CONTACTNAME'       =>  $arrEntries[$intEntryId]['translation'][$intLangId]['contactname'],
                    'PARTNERS_EMAIL'             =>  $arrEntries[$intEntryId]['translation'][$intLangId]['email'],
                    'PARTNERS_WEBSITE'           =>  $arrEntries[$intEntryId]['translation'][$intLangId]['website'],
                    'PARTNERS_ADDRESS1'          =>  $arrEntries[$intEntryId]['translation'][$intLangId]['address1'],
                    'PARTNERS_ADDRESS2'          =>  $arrEntries[$intEntryId]['translation'][$intLangId]['address2'],
                    'PARTNERS_CITY'              =>  $arrEntries[$intEntryId]['translation'][$intLangId]['city'],
                    'PARTNERS_ZIPCODE'           =>  $arrEntries[$intEntryId]['translation'][$intLangId]['zipcode'],
                    'PARTNERS_PHONE'             =>  $arrEntries[$intEntryId]['translation'][$intLangId]['phone'],
                    'PARTNERS_FAX'               =>  $arrEntries[$intEntryId]['translation'][$intLangId]['fax'],
                    'PARTNERS_REFERENCE'         =>  $arrEntries[$intEntryId]['translation'][$intLangId]['reference'],
                    'PARTNERS_QUOTE'             =>  $arrEntries[$intEntryId]['translation'][$intLangId]['quote'],
                    'PARTNERS_LOGO_PATH'         =>  $arrEntries[$intEntryId]['translation'][$intLangId]['image'],
                    'PARTNERS_CONTENT'			 =>	 $arrEntries[$intEntryId]['translation'][$intLangId]['content'],
	   				'ENTRY_LANGUAGES'		     =>	 $strActiveLanguages,
                 	'ENTRY_USER'			     =>	 $arrEntryValues['user_name']
	   			));


	   			$intRowClass++;
	   				}

	   		}

	   		}
    }
}
