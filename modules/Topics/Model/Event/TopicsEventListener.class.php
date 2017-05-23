<?php
/**
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @copyright   Comvation AG
 * @link        http://www.comvation.com/
 * @package     comvation
 * @subpackage  module_topics
 */

namespace Cx\Modules\Topics\Model\Event;

/**
 * Listen to MediaBrowser's load event
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @copyright   Comvation AG
 * @link        http://www.comvation.com/
 * @package     comvation
 * @subpackage  module_topics
 */
class TopicsEventListener extends \Cx\Core\Event\Model\Entity\DefaultEventListener
{
    /**
     * Enable the Topics extension
     * @global  array   $_ARRAYLANG
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function mediaBrowserPluginInitialize()
    {
        global $_ARRAYLANG;
        \JS::registerCSS('modules/Topics/View/Style/TopicsBrowser.css');
        \JS::registerJS('modules/Topics/View/Script/TopicsBrowser.js');
        \Env::get('init')->loadLanguageData('Topics');
        $cxjs = \ContrexxJavascript::getInstance();
        foreach ($_ARRAYLANG as $key => $value) {
            if (!preg_match('/^TXT_MODULE_TOPICS_FILEBROWSER/', $key)) {
                continue;
            }
            // The scope used by the translate filter
            $cxjs->setVariable($key, $value, 'mediabrowser');
        }
    }

}
