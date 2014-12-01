<?php
/**
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Core_Modules\MediaBrowser\Model;


class ResourceRegister
{
    private static $register;
    private static $registerMediaBrowserCalled = false;
    private static $addedCode = false;

    public static function registerMediaBrowserRessource()
    {
        if (self::$registerMediaBrowserCalled) {
            return;
        }
        self::$registerMediaBrowserCalled = true;
        self::registerText("<script>var oldjQuery = jQuery</script>");
        self::registerRessource(
            '../lib/javascript/jquery/1.9.1/js/jquery.min.js'
        );

        self::registerText(
            "<script> window.MediaBrowserjQuery = jQuery</script>"
        );

        self::registerRessource(
            '../lib/plupload/js/moxie.min.js'
        );
        self::registerRessource(
            '../lib/plupload/js/plupload.full.min.js'
        );
        self::registerRessource(
            '../lib/javascript/angularjs/angular.js'
        );
        self::registerRessource(
            '../lib/javascript/angularjs/angular-route.js'
        );
        self::registerRessource(
            '../lib/javascript/angularjs/angular-animate.js'
        );
        self::registerRessource(
            '../lib/javascript/angularjs/ui-bootstrap-tpls-0.11.2.min.js'
        );
        self::registerRessource(
            '../lib/javascript/bootbox.min.js'
        );
        self::registerRessource(
            '../lib/javascript/twitter-bootstrap/3.1.0/js/bootstrap.min.js'
        );

        self::registerRessource(
            '../core_modules/MediaBrowser/View/Script/mediabrowser.js'
        );
        self::registerRessource(
            '../core_modules/MediaBrowser/View/Script/standalone-directives.js'
        );
    }

    public static function  registerRessource($link)
    {
        self::$register[] = "<script type=\"text/javascript\"  src=\"" . $link
            . "\"></script>\n";
    }

    private static function registerText($string)
    {
        self::$register[] = $string;
    }

    public static function getCode()
    {
        if (self::$addedCode) {
            return '';
        }
        self::$addedCode = true;
        return join('', self::$register);
    }
} 