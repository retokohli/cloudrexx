<?php
/**
 * Define constants
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  config
 * @todo        Edit PHP DocBlocks!
 */

define('CRM_MODULE_LIB_PATH', ASCMS_MODULE_PATH.'/crm/lib/');
define('CRM_ACCESS_PROFILE_IMG_WEB_PATH', ASCMS_PATH_OFFSET.ASCMS_IMAGES_FOLDER.'/crm/profile');
define('CRM_ACCESS_PROFILE_IMG_PATH',     ASCMS_DOCUMENT_ROOT.ASCMS_IMAGES_FOLDER.'/crm/profile');
define('CRM_MEDIA_PATH', ASCMS_MEDIA_PATH.'/crm/');

define('CRM_EVENT_ON_USER_ACCOUNT_CREATED', 'crm_on_user_acc_created');
define('CRM_EVENT_ON_TASK_CREATED', 'crm_task_assigned');
define('CRM_EVENT_ON_ACCOUNT_UPDATED', 'crm_notify_staff_on_contact_added');