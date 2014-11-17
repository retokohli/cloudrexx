<?php

/**
 * Contrexx
 *
 * @link      http://www.contrexx.com
 * @copyright Comvation AG 2007-2014
 * @version   Contrexx 4.0
 * 
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Contrexx" is a registered trademark of Comvation AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

function _recommendUpdate()
{
    global $objDatabase;

    /********************************
     * EXTENSION:   Captcha         *
     * ADDED:       Contrexx v3.0.0 *
     ********************************/
    try {
        // migrate content page to version 3.0.1
        $search = array(
        '/(.*)/ms',
        );
        $callback = function($matches) {
            $content = $matches[1];
            if (empty($content)) {
                return $content;
            }

            if (!preg_match('/<!--\s+BEGIN\s+recommend_captcha\s+-->.*<!--\s+END\s+recommend_captcha\s+-->/ms', $content)) {
                // migrate captcha stuff
                $content = preg_replace('/<img[^>]+\{RECOM_CAPTCHA_URL\}.*\{RECOM_CAPTCHA_OFFSET\}[^>]+>/ms', '{RECOM_CAPTCHA_CODE}', $content);

                // migration for very old versions
                $content = preg_replace('/(.*)(<tr[^>]*>.*?<td[^>]*>.*?\{RECOM_CAPTCHA_CODE\}.*?<\/td>.*?<\/tr>)/ms', '$1<!-- BEGIN recommend_captcha -->$2<!-- END recommend_captcha -->', $content, -1, $count);

                // migration for newer versions
                if (!$count) {
                    $content = preg_replace('/(.*)(<p[^>]*>.*?\{RECOM_CAPTCHA_.*?\}.*?<\/p>)/ms', '$1<!-- BEGIN recommend_captcha -->$2<!-- END recommend_captcha -->', $content);
                }
                $content = preg_replace('/(.*)(<p[^>]*><label.*<\/label>)(.*?\{RECOM_CAPTCHA_.*?\}.*?)(<\/p>)/ms', '$1$2{RECOM_CAPTCHA_CODE}$4', $content);
            }

            return $content;
        };
        \Cx\Lib\UpdateUtil::migrateContentPageUsingRegexCallback(array('module' => 'recommend'), $search, $callback, array('content'), '3.0.1');
    }
    catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}
