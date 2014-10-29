<?php

/**
 * OAuth
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  lib_oauth
 */

namespace Cx\Lib\OAuth;

/**
 * OAuth superclass
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Ueli Kramer <ueli.kramer@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  lib_oauth
 */
interface OAuthInterface
{
    public function getEmail();
    public function getFirstname();
    public function getLastname();
    public function login();
}
