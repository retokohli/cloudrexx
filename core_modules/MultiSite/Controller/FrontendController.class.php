<?php
/**
 * Specific FrontendController for this Component. Use this to easily create a frontent view
 *
 * @copyright   Comvation AG
 * @author      Manish Thakur <manishthakur@cdnsol.com>
 * @package     contrexx
 * @subpackage  modules_multisite
 */

namespace Cx\Core_Modules\MultiSite\Controller;

/**
 * Specific FrontendController for this Component. Use this to easily create a frontent view
 *
 * @copyright   Comvation AG
 * @author      Manish Thakur <manishthakur@cdnsol.com>
 * @package     contrexx
 * @subpackage  modules_multisite
 */
class FrontendController extends \Cx\Core\Core\Model\Entity\SystemComponentFrontendController {
    /**
     * Use this to parse your frontend page 
     * You will get a template based on the content of the resolved page
     * You can access Cx class using $this->cx
     * To show messages, use \Message class
     * @param \Cx\Core\Html\Sigma $template Template containing content of resolved page
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, $cmd) {
        $activityType=contrexx_input2raw($_REQUEST['cmd']); 
        //$template->setRoot($this->getDirectory().'/View/Template');
        
        switch ($activityType) {
            
            // Singup proccess step1 user register
			case "step1":
                    $setVariable=$this->stepOne($activityType);
            break;
            
            // Singup proccess step2 user setup site
            case "step2":
            
                    $setVariable=$this->stepTwo($activityType);
                   
            break;
            
            //Signup proccess step3 theme selection
            case "step3":
                
                    $setVariable=$this->stepThree($activityType);
                     
            break;
            
            //Signup proccess step4 "news", "blog" and "content pages" link
            case "step4":
            
                    $setVariable=$this->stepFour($activityType);
            
            break;
        }
        
        $template->setVariable($setVariable);
    }
    
    /**
     * Use this to stepOne your frontend page     
     * registration proccess step one
     * You can access Cx class using $this->cx
     * retrun step one respone
     * @param 
     */
    protected function stepOne($activityType){
        global $_ARRAYLANG, $_LANGID;  
        $post=$_POST;
       
        // get website minimum and maximum Name length
        //self::errorHandler();
        $websiteNameMinLength=\Cx\Core\Setting\Controller\Setting::getValue('websiteNameMinLength');
        $websiteNameMaxLength=\Cx\Core\Setting\Controller\Setting::getValue('websiteNameMaxLength');
        // TODO: implement protocol support / the protocol to use should be defined by a configuration option
        $protocol = 'https';
        if (in_array(\Cx\Core\Setting\Controller\Setting::getValue('mode'), array('manager', 'manager/service'))) {
            $configs = \Env::get('config');
            $multiSiteDomain = $protocol.'://'.$configs['domainUrl']; 
        } else {
            $multiSiteDomain = $protocol.'://'.\Cx\Core\Setting\Controller\Setting::getValue('managerHostname');
        }
        \JS::activate('cx');
        \ContrexxJavascript::getInstance()->setVariable('baseUrl', $multiSiteDomain, 'MultiSite');
        //check is exists user not allow registration page
        if(!empty($_COOKIE['userId']))
        {
            $userId=contrexx_input2raw($_COOKIE['userId']);
            if(!empty($userId))
                \CSRF::header('Location: blog-setup');
        }
        $isValidation=FALSE;
        //$template->loadTemplateFile('registration.html');
        $setVariable=array(
                            'TITLE'         => contrexx_raw2xhtml($_ARRAYLANG['TXT_MULTISITE_TITLE']),
                            'EMAIL_ADDRESS' => contrexx_raw2xhtml($_ARRAYLANG['TXT_MULTISITE_EMAIL_ADDRESS']),
                            'USER_NAME'     => contrexx_raw2xhtml($_ARRAYLANG['TXT_MULTISITE_USERS_NAME']),
                            'BLOG_ADDRESS'  => contrexx_raw2xhtml($_ARRAYLANG['TXT_MULTISITE_SITE_ADDRESS']),
                            'BLOG_SUBIMT'   => contrexx_raw2xhtml($_ARRAYLANG['TXT_MULTISITE_SUBMIT_BUTTON']),
                            'ISUPGRADING'   => contrexx_raw2xhtml($_ARRAYLANG['TXT_MULTISITE_IS_UPGRADING']),
                            'DOMAINURL'     => str_replace("www.","",\Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain')),
                            'FREEBLOG'      => contrexx_raw2xhtml($_ARRAYLANG['TXT_MULTISITE_FREE_SITE']),
                            'POST_URL'      => $this->cx->getRequest()->getUrl(),
                            'LANGID'        => $_LANGID
                        );
        
        //post form values     
        if(isset($post['createBlog'])){  
            //server side validation 
            if($post['emailAddress']==""){
                $setVariable['email_error'] =contrexx_raw2xhtml($_ARRAYLANG['TXT_MULTISITE_EMAIL_REQUIRED']);
                $isValidation=TRUE;
            }
            if($post['userName']==""){
                $setVariable['name_error'] =contrexx_raw2xhtml($_ARRAYLANG['TXT_MULTISITE_USER_NAME_REQUIRED']);
                $isValidation=TRUE;
            }
            if($post['blogAddress']==""){
                $setVariable['blog_error'] =contrexx_raw2xhtml($_ARRAYLANG['TXT_MULTISITE_SITE_ADDRESS_REQUIRED']);
                $isValidation=TRUE;
            }else{
                if(strlen($post['blogAddress'])<$websiteNameMinLength){
                    $setVariable['blog_error'] =contrexx_raw2xhtml(str_replace('{digits}',$websiteNameMinLength,$_ARRAYLANG['TXT_MULTISITE_WEBSITE_ADDRESS_MINIMUM_LENGTH']));
                    $isValidation=TRUE;
                }
                if(strlen($post['blogAddress'])>$websiteNameMaxLength){
                    $setVariable['blog_error'] =contrexx_raw2xhtml(str_replace('{digits}',$websiteNameMaxLength,$_ARRAYLANG['TXT_MULTISITE_WEBSITE_ADDRESS_MAXIMUM_LENGTH']));
                    $isValidation=TRUE;
                }
            }
            
            //If user fill required information only allow to post method 
            if($isValidation==FALSE){
             //call Plesk API to create new user website here 
             //If new website created at that time new use create
             //currently default Plesk api pending @isNewWebsites=true
             //Plesk api depand on @isNewWebsites true or false
            
                $isNewWebsites=TRUE;  
                //prepare website data
                $websiteData['post']['name'] = $post['blogAddress'];
                $websiteData['post']['mail'] = $post['emailAddress'];
                $userId = $this->createWebsites($websiteData);                  
                if($isNewWebsites==TRUE){
                   
                    if(!empty($userId)){ 
                        /*if(empty($sessionObj))
                            $sessionObj=new \cmsSession();
                            $sessionObj->cmsSessionUserUpdate($userId);
                            $sessionObj->cmsSessionStatusUpdate('frontend');
                            */ 
                        setcookie("userId", $userId);
                        setcookie("blogAddress", $post['blogAddress']);
                        \CSRF::header('Location: blog-setup');
                    }
                 
                }else{
                    $setVariable['blog_error'] =contrexx_raw2xhtml($_ARRAYLANG['TXT_MULTISITE_SITE_ADDRESS_INVALID']);
                }
            }else{
              
                $setVariable['EMAIL']   =contrexx_raw2xhtml($post['emailAddress']); 
                $setVariable['NAME']    =contrexx_raw2xhtml($post['userName']);
                $setVariable['ADDRESS'] =contrexx_raw2xhtml($post['blogAddress']); 
               
            }
          
        }
        
       return $setVariable; 
    }
    
    /**
     * Use this to stepTwo your frontend page     
     * registration proccess step two
     * You can access Cx class using $this->cx
     * retrun step two respone
     * @param 
     */
    protected function stepTwo($activityType){
        global $_ARRAYLANG;  
        $post=$_POST;
        $userId= contrexx_input2raw($_COOKIE['userId']);
        $blogTitle=contrexx_input2raw($_COOKIE['blogTitle']);
        $tagLine=contrexx_input2raw($_COOKIE['tagLine']);
        $selectedLang=contrexx_input2raw($_COOKIE['language']);
        
        //check if 1st step incompleted,not allow 2nd step it will redirct registration page
        if(empty($userId)) 
            \CSRF::header('Location: Register');
           
        //post form values
        if(isset($post['createBlogs'])){  
             setcookie("blogTitle", $post['blogTitle']);
             setcookie("tagLine", $post['tagLine']);
             setcookie("language", $post['language']);
            \CSRF::header('Location: themes');
        }else{
           
            //load languages active in the frontend
            $languageDetails = \FWLanguage::getActiveFrontendLanguages(); 
            //create language dropdown
            $languageDropdown = '<select id="language" name="language">';
            if(!empty($languageDetails))
            {
                foreach($languageDetails as $lang)
                {
                     $selected="";
                    if($lang['lang']==$selectedLang)
                        $selected='Selected="true"';
                     
                    $languageDropdown.='<option value="'.$lang['lang'].'" '.$selected.'>'.$lang['lang'].'-'.$lang['name'].'</option>';   
                    
                }
            }
            $languageDropdown .= '</select>'; 
            
            $setVariable=array(
                            'BLOGTITLE'         => contrexx_raw2xhtml($_ARRAYLANG['TXT_MULTISITE_SITE_TITLE']),
                            'TAGLINE'           => contrexx_raw2xhtml($_ARRAYLANG['TXT_MULTISITE_TAGLINE']),
                            'LANGUAGE'          => contrexx_raw2xhtml($_ARRAYLANG['TXT_MULTISITE_LANGUAGE']),
                            'LANGUAGE_DROPDOWN' => $languageDropdown,
                            'NEXTSTEP'          => contrexx_raw2xhtml($_ARRAYLANG['TXT_MULTISITE_NEXTSTEP']),
                            'OPTIONAL'          => contrexx_raw2xhtml($_ARRAYLANG['TXT_MULTISITE_OPTIONAL']),
                            'blogTitle'         => $blogTitle,
                            'tagLine'           => $tagLine,
                            'POST_URL'          =>$this->cx->getRequest()->getUrl()
                
                        );
          
        }
        
        
       return $setVariable; 
    }
    
    
    /**
     * Use this to stepThree your frontend page     
     * registration proccess step three
     * You can access Cx class using $this->cx
     * retrun step three respone
     * @param 
     */
    protected function stepThree($activityType){
        global $_ARRAYLANG;  
        $post=$_POST;
        $userId= contrexx_input2raw($_COOKIE['userId']);
        $blogTitle=contrexx_input2raw($_COOKIE['blogTitle']);
        $language=contrexx_input2raw($_COOKIE['language']);
        $themeId=contrexx_input2raw($_COOKIE['themeId']);
        
        //check if 2nd step incompleted,not allow 3rd step it will redirct previous step
        if(empty($userId)|| empty($blogTitle) || empty($language)) 
            \CSRF::header('Location: blog-setup');
        
        //post form values   
        if(isset($post['addTheme'])){
              
            setcookie("themeId", $post['themes']);
            \CSRF::header('Location: postblog');
            
        }else{ 
             
            //load exists theme previews list
            $skinsResult=\skins::getThemes();
            $theme='';
            
            if (!empty($skinsResult)) {
                foreach($skinsResult as $skins) {
                    $checked="";
                    if($skins['id']==$themeId){
                        $checked='checked';
                    } 
                    $theme.='<div class="themeImg">';
                    $theme.='<label for="lable'.$skins['id'].'"><div class="imageContent" style="';
                    $theme.="background:url('../themes/".$skins['foldername']."/images/preview.gif') no-repeat scroll center 7px rgba(0, 0, 0, 0)";
                    $theme.='"></div> ';
                    $theme.='<div class="imageTitle">';
                    $theme.='<input type="radio" name="themes" value="'.$skins['id'].'" '.$checked.' id="lable'.$skins['id'].'" >'.$skins['themesname'];
                    $theme.='</div> </lable></div> '; 
                
                }
            }
            
        $setVariable=array(
                            'THEMES'    => $theme,
                            'BACKSTEP'  =>contrexx_raw2xhtml($_ARRAYLANG['TXT_MULTISITE_BACKSTEP']),
                            'NEXTSTEP'  => contrexx_raw2xhtml($_ARRAYLANG['TXT_MULTISITE_NEXTSTEP']),
                            'POST_URL'  =>$this->cx->getRequest()->getUrl()
                
                        );
           
        }
        return $setVariable;    
    }
    
    /**
     * Use this to stepFour your frontend page     
     * registration proccess step four
     * You can access Cx class using $this->cx
     * retrun step four respone
     * @param 
     */
    protected function stepFour($activityType){
        global $_ARRAYLANG;  
        $post=$_POST;
        $userId= contrexx_input2raw($_COOKIE['userId']);   
        $blogTitle=contrexx_input2raw($_COOKIE['blogTitle']);
        $language=contrexx_input2raw($_COOKIE['language']);
        $themeId=contrexx_input2raw($_COOKIE['themeId']);
        $siteAddress=contrexx_input2raw($_COOKIE['blogAddress']);
        
        //check if 3rd step incompleted,not allow 4th step it will redirct previous step
        if(empty($userId)|| empty($blogTitle) || empty($language) || empty($themeId)) 
           \CSRF::header('Location: themes');
           
        $setVariable=array(
                            'FIRSTPOST'     => contrexx_raw2xhtml($_ARRAYLANG['TXT_MULTISITE_FIRSTPOST']),
                            'NEWS'          => contrexx_raw2xhtml($_ARRAYLANG['TXT_MULTISITE_NEWS']),
                            'BLOGS'         => contrexx_raw2xhtml($_ARRAYLANG['TXT_MULTISITE_BLOGS']),
                            'BACKSTEP'      =>contrexx_raw2xhtml($_ARRAYLANG['TXT_MULTISITE_BACKSTEP']),
                            'CONTENTMANAGER'=> contrexx_raw2xhtml($_ARRAYLANG['TXT_MULTISITE_CONTENT_MANAGER']),
                            'SITEADDRESS'   =>$siteAddress,
                            'DOMAINURL'     =>str_replace("www.","",\Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain')),
                            'POST_URL'      =>$this->cx->getRequest()->getUrl()
                
                    );
               
        return $setVariable;       
    }
}
