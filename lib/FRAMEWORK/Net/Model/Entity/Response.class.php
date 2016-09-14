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
 * A response to a request
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Michael Ritter <michael.ritter@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  lib_net
 * @link        http://www.cloudrexx.com/ cloudrexx homepage
 * @since       v5.0.0
 */

namespace Cx\Lib\Net\Model\Entity;

/**
 * A response to a request
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Michael Ritter <michael.ritter@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  lib_net
 * @link        http://www.cloudrexx.com/ cloudrexx homepage
 * @since       v5.0.0
 */
class Response {
    /**
     * @var \DateTime Expire date
     */
    protected $expireDate;

    /**
     * @var string Content type
     */
    protected $contentType = 'text/plain';

    /**
     * @var Request (optional) Request
     */
    protected $request = null;

    /**
     * @var int Response code
     */
    protected $code;

    /**
     * @var mixed Response content
     */
    protected $content;

    /**
     * Creates a new Response
     * @param mixed $response Response data
     * @param int $code (optional) Response code, default is 200
     * @param Request $request (optional) Request object, default is null
     * @param \DateTime $expireDate (optional) Expire date for this response, default is null
     */
    public function __construct($content, $code = 200, $request = null, $expireDate = null) {
        $this->setContent($content);
        $this->setCode($code);
        $this->setRequest($request);
        $this->setExpireDate($expireDate);
    }

    /**
     * Returns the expiration date for this Response
     * @return \DateTime Expire date
     */
    public function getExpireDate() {
        return $this->expires;
    }

    /**
     * Sets the expiration date for this Response
     * @param \DateTime $expires Expiration date (can be set to null)
     */
    public function setExpireDate($expires) {
        $this->expires = $expires;
    }

    /**
     * Returns the content type
     * @return String Content type
     */
    public function getContentType() {
        return $this->contentType;
    }

    /**
     * Sets the content type
     * @param string $contentType Content type
     */
    public function setContentType($contentType) {
        $this->contentType = $contentType;
    }

    /**
     * Returns the Request object for this Response
     * @return \Request Request this Response is for
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * Sets the Request object for this Response
     * @param \Request $request Request this Response is for
     */
    public function setRequest($request) {
        $this->request = $request;
    }

    /**
     * Returns the response code
     * @return int Response code
     */
    public function getCode() {
        return $this->code;
    }

    /**
     * Sets the response code
     * @param int $code Response code
     */
    public function setCode($code) {
        $this->code = $code;
    }

    /**
     * Returns the content
     * @return mixed Content
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * Sets the content
     * @param mixed $content Content
     */
    public function setContent($content) {
        $this->content = $content;
    }
}
