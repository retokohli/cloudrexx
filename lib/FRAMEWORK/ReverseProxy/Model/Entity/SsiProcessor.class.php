<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
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
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */
 
/**
 * Abstract representation of an SSI processor
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     cloudrexx
 * @subpackage  lib_reverseproxy
 * @link        http://www.cloudrexx.com/ cloudrexx homepage
 * @since       v5.0.0
 */

namespace Cx\Lib\ReverseProxy\Model\Entity;

/**
 * Abstract representation of an SSI processor
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     cloudrexx
 * @subpackage  lib_reverseproxy
 * @link        http://www.cloudrexx.com/ cloudrexx homepage
 * @since       v5.0.0
 */
abstract class SsiProcessor {
    protected $parseMode;
    protected $dirname;
    
    public function __construct() {
        $this->dirname = dirname(dirname(dirname(__FILE__)));
    }
    
    public function getIncludeCode($url) {
        $template = $this->getTemplateFile('IncludeTag');
        $template->setVariable('INCLUDE_FILE', $url);
        return $template->get();
    }
    
    public function getRandomizedIncludeCode($urls) {
        $template = $this->getTemplateFile('RandomIncludeTag');
        $this->parseRandomizedIncludeCode($template, $urls);
        return $template->get();
    }
    
    protected function getTemplateFile($filename) {
        $template = new \HTML_Template_Sigma($this->dirname . '/View/Template/Global');
        $template->loadTemplateFile($filename . strtoupper($this->parseMode) . '.html');
        return $template;
    }
    
    protected abstract function parseRandomizedIncludeCode($template, $urls);
}

